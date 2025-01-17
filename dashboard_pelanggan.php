<?php 
    session_start();

    // Konfigurasi koneksi database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "balap";

    // Membuat koneksi ke database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Fungsi Logout
    if (isset($_GET['logout'])) {
        session_unset(); // Hapus semua variabel sesi
        session_destroy(); // Hancurkan sesi
        header("Location: ../index.php"); // Redirect ke halaman index.php di luar folder customer
        exit();
    }

    // Cek apakah pengguna sudah login dan memiliki role 'customer'
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
        header("Location: login.php"); // Arahkan ke halaman login
        exit();
    }

    // Ambil data pengguna dari session
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $name = $_SESSION['name'];

    // Ambil data kategori dari database
    $query_kategori = "SELECT * FROM kategori";
    $result_kategori = $conn->query($query_kategori);

    // Ambil id_kategori dari URL
    $id_kategori = isset($_GET['id_kategori']) ? intval($_GET['id_kategori']) : 0;

    // Ambil kata kunci pencarian dari URL
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

    // Ambil data produk berdasarkan kategori atau pencarian
    if (!empty($search)) {
        $query_produk = "SELECT * FROM produk WHERE nama_produk LIKE ?";
        $stmt_produk = $conn->prepare($query_produk);
        $search_param = "%$search%";
        $stmt_produk->bind_param('s', $search_param);
    } else if ($id_kategori > 0) {
        $query_produk = "SELECT * FROM produk WHERE id_kategori = ?";
        $stmt_produk = $conn->prepare($query_produk);
        $stmt_produk->bind_param('i', $id_kategori);
    } else {
        $query_produk = "SELECT * FROM produk";
        $stmt_produk = $conn->prepare($query_produk);
    }

    // Eksekusi query untuk produk
    $stmt_produk->execute();
    $result_produk = $stmt_produk->get_result();

    // Hitung jumlah item dalam keranjang
    $keranjang_count = isset($_SESSION['keranjang']) ? array_sum(array_column($_SESSION['keranjang'], 'quantity')) : 0;

    // Query untuk menghitung jumlah pesanan yang belum selesai (status_pesanan digunakan di sini)
    $query_pesanan = "SELECT COUNT(*) FROM transaksi WHERE status_pesanan != 'Selesai' AND user_id = ?";
    $stmt_pesanan = $conn->prepare($query_pesanan);
    $stmt_pesanan->bind_param('i', $user_id); // Bind user_id ke query
    $stmt_pesanan->execute();
    $result_pesanan = $stmt_pesanan->get_result();
    $row_pesanan = $result_pesanan->fetch_array();
    $jumlah_pesanan = $row_pesanan[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan</title>
    <link rel="stylesheet" href="pelanggan.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1 class="logo">Produk Usa</h1>
            <!-- Search Bar -->
            <form class="search-bar " method="get" action="dashboard_pelanggan.php">
                <input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                
            </form>
            <!-- Navigasi -->
            <nav class="nav">
                <ul>
                    <li><a href="dashboard_pelanggan.php">Beranda</a></li>
                    <li><a href="../about.php">Tentang Kami</a></li>
                    <li>
                        <a href="keranjang.php">Keranjang
                            <?php if ($keranjang_count > 0): ?>
                                <span class="badge" style="background: red; color: white; border-radius: 50%; padding: 2px 8px; font-size: 12px;">
                                    <?= $keranjang_count ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="pesanan.php">Pesanan Saya
                            <?php if ($jumlah_pesanan > 0): ?>
                                <span class="badge" style="background: red; color: white; border-radius: 50%; padding: 2px 8px; font-size: 12px;">
                                    <?= $jumlah_pesanan ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="dashboard_pelanggan.php?logout=true" class="logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    

    <!-- Produk -->
    <section class="produk">
        <div class="container">
            <h2>Produk Kami</h2>
            <div class="produk-grid">
                <?php if ($result_produk->num_rows > 0): ?>
                    <?php while ($produk = $result_produk->fetch_assoc()): ?>
                        <div class="produk-item">
                            <img src="gambar_produk/<?= htmlspecialchars($produk['gambar_produk']) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                            <h4>
                                <a href="deskripsi.php?id=<?= htmlspecialchars($produk['id']) ?>" style="color: black; text-decoration: none;">
                                    <?= htmlspecialchars($produk['nama_produk']) ?>
                                </a>
                            </h4>
                            <?php if (!is_null($produk['harga_diskon'])): ?>
                                <p style="color: red; font-weight: bold;">Diskon: <?= number_format((($produk['harga'] - $produk['harga_diskon']) / $produk['harga']) * 100, 0) ?>%</p>
                                <p><s>Harga: Rp<?= number_format($produk['harga'], 2, ',', '.') ?></s></p>
                                <p>Harga Diskon: Rp<?= number_format($produk['harga_diskon'], 2, ',', '.') ?></p>
                            <?php else: ?>
                                <p>Harga: Rp<?= number_format($produk['harga'], 2, ',', '.') ?></p>
                            <?php endif; ?>
                            <p>Stok Tersedia: <?= htmlspecialchars($produk['stok_tersedia']) ?> unit</p>
                            <button onclick="location.href='keranjang.php?add=<?= $produk['id'] ?>'">Tambah ke Keranjang</button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Tidak ada produk ditemukan.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
</body>
</html>

<?php
    // Tutup koneksi
    $stmt_produk->close();
    $conn->close();
?>