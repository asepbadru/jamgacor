<?php
session_start();

// Koneksi ke database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'balap';

$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu.'); window.location = 'login.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];

// Ambil data pesanan pengguna
$sql_pesanan = "SELECT t.id AS transaksi_id, t.alamat, t.jasa_pengiriman, t.metode_bayar, t.bukti_pembayaran, t.status_pesanan AS status, 
                t.subtotal, t.diskon, t.total, t.ongkir, t.grand_total, t.tanggal, 
                dt.produk_id, p.nama_produk, p.gambar_produk, 
                dt.jumlah_unit AS quantity, dt.harga_satuan AS harga, dt.total_item, dt.harga_diskon
                FROM transaksi t 
                JOIN transaksi_detail dt ON t.id = dt.transaksi_id
                JOIN produk p ON dt.produk_id = p.id
                WHERE t.user_id = ? 
                ORDER BY t.tanggal DESC";

$stmt = $conn->prepare($sql_pesanan);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pesanan = [];
while ($row = $result->fetch_assoc()) {
    $pesanan[$row['transaksi_id']][] = $row;
}

// Proses pembatalan pesanan jika tombol diklik
if (isset($_GET['batalkan'])) {
    $transaksi_id = $_GET['batalkan'];

    // Pastikan transaksi milik pengguna yang login dan statusnya 'Menunggu Konfirmasi'
    $sql_check = "SELECT id, user_id, status_pesanan FROM transaksi WHERE id = ? AND user_id = ? AND status_pesanan = 'Menunggu Konfirmasi'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $transaksi_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Ambil detail produk dari transaksi yang dibatalkan
        $sql_details = "SELECT dt.produk_id, dt.jumlah_unit FROM transaksi_detail dt WHERE dt.transaksi_id = ?";
        $stmt_details = $conn->prepare($sql_details);
        $stmt_details->bind_param("i", $transaksi_id);
        $stmt_details->execute();
        $result_details = $stmt_details->get_result();

        // Mulai transaksi untuk mengupdate stok
        $conn->begin_transaction();

        try {
            // Batalkan pesanan dengan mengubah status menjadi 'Pesanan Dibatalkan'
            $sql_update = "UPDATE transaksi SET status_pesanan = 'Pesanan Dibatalkan' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $transaksi_id);
            $stmt_update->execute();

            // Kembalikan stok produk yang dibatalkan
            while ($row = $result_details->fetch_assoc()) {
                $produk_id = $row['produk_id'];
                $jumlah_unit = $row['jumlah_unit'];

                // Update stok produk
                $sql_update_stok = "UPDATE produk SET stok_tersedia = stok_tersedia + ? WHERE id = ?";
                $stmt_update_stok = $conn->prepare($sql_update_stok);
                $stmt_update_stok->bind_param("ii", $jumlah_unit, $produk_id);
                $stmt_update_stok->execute();
            }

            // Commit transaksi jika semua query berhasil
            $conn->commit();

            // Redirect setelah pembatalan
            header("Location: pesanan.php");
            exit;
        } catch (Exception $e) {
            // Rollback transaksi jika ada error
            $conn->rollback();
            echo "<script>alert('Terjadi kesalahan. Pesanan tidak dapat dibatalkan.'); window.location = 'pesanan.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Pesanan tidak ditemukan, bukan pesanan Anda, atau sudah tidak dapat dibatalkan.'); window.location = 'pesanan.php';</script>";
        exit;
    }
}

// Hapus histori pembelian yang dipilih
if (isset($_POST['hapus_histori'])) {
    if (!empty($_POST['selected_orders'])) {
        foreach ($_POST['selected_orders'] as $transaksi_id) {
            // Pastikan transaksi milik pengguna yang login dan statusnya 'Pesanan Dibatalkan' atau 'Selesai'
            $sql_check = "SELECT id, user_id, status_pesanan FROM transaksi WHERE id = ? AND user_id = ? AND (status_pesanan = 'Pesanan Dibatalkan' OR status_pesanan = 'Selesai')";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ii", $transaksi_id, $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Hapus detail transaksi terkait
                $sql_delete_details = "DELETE FROM transaksi_detail WHERE transaksi_id = ?";
                $stmt_delete_details = $conn->prepare($sql_delete_details);
                $stmt_delete_details->bind_param("i", $transaksi_id);
                $stmt_delete_details->execute();

                // Hapus transaksi
                $sql_delete_transaksi = "DELETE FROM transaksi WHERE id = ?";
                $stmt_delete_transaksi = $conn->prepare($sql_delete_transaksi);
                $stmt_delete_transaksi->bind_param("i", $transaksi_id);
                $stmt_delete_transaksi->execute();
            } else {
                echo "<script>alert('Pesanan tidak dapat dihapus karena statusnya bukan Pesanan Dibatalkan atau Selesai.'); window.location = 'pesanan.php';</script>";
                exit;
            }
        }

        // Redirect setelah penghapusan
        header("Location: pesanan.php");
        exit;
    } else {
        echo "<script>alert('Tidak ada pesanan yang dipilih untuk dihapus.'); window.location = 'pesanan.php';</script>";
        exit;
    }
}

// Mengubah status pesanan jika tombol diklik
if (isset($_GET['ubah_status'])) {
    $transaksi_id = $_GET['ubah_status'];
    $new_status = $_GET['status'];

    // Pastikan transaksi milik pengguna yang login
    $sql_check = "SELECT id, user_id FROM transaksi WHERE id = ? AND user_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $transaksi_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Ubah status pesanan
        $sql_update = "UPDATE transaksi SET status_pesanan = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_status, $transaksi_id);
        $stmt_update->execute();

        // Redirect setelah pembaruan status
        header("Location: pesanan.php");
        exit;
    } else {
        echo "<script>alert('Pesanan tidak ditemukan atau bukan pesanan Anda.'); window.location = 'pesanan.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
       body {
    font-family: 'Poppins', sans-serif;
    background-color: #1e1e1e; /* Dark background for a more macho feel */
    color: #f0f0f0; /* Light text for contrast */
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

header {
    background-color: #343a40; /* Darker header color */
    color: #f0f0f0;
    padding: 25px 0;
    text-align: center;
    border-bottom: 5px solid #007bff; /* Strong accent line */
}

header h1 {
    margin: 0;
    font-weight: 700;
    font-size: 2.5rem;
}

header a {
    margin-top: 10px;
    display: inline-block;
    padding: 10px 25px;
    color: #007bff;
    background-color: #f0f0f0;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
}

header a:hover {
    background-color: #e0e0e0;
}

.container {
    margin: 30px auto;
    padding: 25px;
    background-color: #2c2f36; /* Dark container background */
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

h2 {
    font-weight: 700;
    margin-bottom: 20px;
    color: #f0f0f0;
    font-size: 1.8rem;
}

.table-responsive {
    margin-top: 20px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    background-color:rgb(173, 255, 254);
}

.table th,
.table td {
    padding: 15px 20px;
    text-align: left;
    border: 1px solid #444;
}

.table th {
    background-color: #007bff;
    color: #fff;
    font-weight: 600;
}

.table img {
    border-radius: 10px;
    border: 2px solid #444;
}

.table tbody tr:hover {
    background-color: #444;
}

.status {
    font-weight: 600;
    padding: 5px 10px;
    border-radius: 5px;
    display: inline-block;
    text-transform: capitalize;
}

.status-menunggu-konfirmasi {
    background-color: #ff8c00; /* Strong orange */
    color: #fff;
}

.status-selesai {
    background-color: #28a745;
    color: #fff;
}

.status-pesanan-dibatalkan {
    background-color: #dc3545;
    color: #fff;
}

.harga-asli {
    text-decoration: line-through;
    color: #999;
}

.harga-diskon {
    font-weight: 700;
    color: #dc3545;
}

.harga-normal {
    font-weight: 700;
    color: #f0f0f0;
}

.btn-danger {
    background-color: #dc3545;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    text-transform: uppercase;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-warning {
    background-color: #ff8c00;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    text-transform: uppercase;
}

.btn-warning:hover {
    background-color: #e07b00;
}

input[type="checkbox"] {
    width: 25px;
    height: 25px;
    cursor: pointer;
    border: 2px solid #007bff;
    background-color: #f0f0f0;
}

   </style>
</head>
<body>
<header class="bg-primary text-white text-center py-4">
    <h1>Pesanan Saya</h1>
    <a href="dashboard_pelanggan.php" class="btn btn-light">Kembali ke Dashboard</a>
</header>

<main>
    <div class="container">
        <h2>Daftar Pesanan Anda</h2>
        
        <!-- Form untuk hapus histori pesanan -->
        <form method="POST" action="pesanan.php">
            <div class="form-group">
                <button type="submit" name="hapus_histori" class="btn btn-danger">Hapus Histori Pesanan Terpilih</button>
            </div>
            <div class="table-responsive">
                <?php if (!empty($pesanan)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Alamat</th>
                                <th>Harga</th>
                                <th>Pengiriman</th>
                                <th>Ongkir</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesanan as $transaksi_id => $items): ?>
                                <tr>
                                    <td colspan="10">
                                        <strong>Pesanan #<?= $transaksi_id ?> - Status: 
                                            <span class="status status-<?= strtolower(str_replace(' ', '-', $items[0]['status'])) ?>">
                                                <?= $items[0]['status'] ?>
                                            </span>
                                        </strong>
                                        <?php if ($items[0]['status'] === 'Menunggu Konfirmasi'): ?>
                                            <a href="?batalkan=<?= $transaksi_id ?>" class="btn btn-warning">Batalkan Pesanan</a>
                                        <?php elseif ($items[0]['status'] === 'Selesai' || $items[0]['status'] === 'Pesanan Dibatalkan'): ?>
                                            <input type="checkbox" name="selected_orders[]" value="<?= $transaksi_id ?>" class="ml-3">
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <?php
                                $subtotal = 0;
                                $ongkir = $items[0]['ongkir']; // Ambil ongkir dari pesanan pertama
                                foreach ($items as $item):
                                    $harga_asli = $item['harga'];
                                    $harga_diskon = isset($item['harga_diskon']) ? $item['harga_diskon'] : null;
                                    
                                    // Hitung total berdasarkan harga diskon atau normal
                                    $total_item = $harga_diskon ? $harga_diskon * $item['quantity'] : $harga_asli * $item['quantity'];
                                    $subtotal += $total_item;
                                ?>
                                    <tr>
                                        <td><img src="gambar_produk/<?= $item['gambar_produk'] ?>" alt="<?= $item['nama_produk'] ?>" style="width: 100px; height: 100px;"></td>
                                        <td><?= $item['nama_produk'] ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="text-center"><?= $item['status'] ?></td>
                                        <td><?= $item['alamat'] ?></td>
                                        <td>
                                            <?php if ($harga_diskon && $harga_diskon < $harga_asli): ?>
                                                <span class="harga-asli">Rp <?= number_format($harga_asli, 0, ',', '.') ?></span><br>
                                                <span class="harga-diskon">Rp <?= number_format($harga_diskon, 0, ',', '.') ?></span>
                                            <?php else: ?>
                                                <span class="harga-normal">Rp <?= number_format($harga_asli, 0, ',', '.') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['jasa_pengiriman'] ?></td>
                                        <td>Rp <?= number_format($ongkir, 0, ',', '.') ?></td>
                                        <td><?= date('d M Y', strtotime($item['tanggal'])) ?></td>
                                        <td>Rp <?= number_format($total_item, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td colspan="9" class="text-right"><strong>Total Subtotal:</strong></td>
                                    <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Ongkir:</strong></td>
                                    <td>Rp <?= number_format($ongkir, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Total Grand:</strong></td>
                                    <td>Rp <?= number_format($items[0]['grand_total'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Anda belum memiliki pesanan.</p>
                <?php endif; ?>
            </div>
        </form>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
