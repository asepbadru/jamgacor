<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "balap"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Menampilkan produk
$sql = "SELECT produk.id, produk.nama_produk, produk.gambar_produk, produk.harga, produk.stok_tersedia, kategori.nama AS kategori_nama 
        FROM produk 
        LEFT JOIN kategori ON produk.id_kategori = kategori.id_kategori";
$result = $conn->query($sql);

// Hapus produk jika ada permintaan
if (isset($_GET['hapus'])) {
    $id_produk = $_GET['hapus'];
    // Pastikan ID produk valid sebelum melakukan penghapusan
    if (is_numeric($id_produk)) {
        $delete_sql = "DELETE FROM produk WHERE id = $id_produk";
        if ($conn->query($delete_sql) === TRUE) {
            echo "<script>alert('Produk berhasil dihapus'); window.location.href = 'produk.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "<script>alert('ID produk tidak valid'); window.location.href = 'produk.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
       body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #2c3e50;
    color: #ecf0f1;
}

header {
    background-color: #1a252f;
    padding: 20px 0;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

header h1 {
    color: #ecf0f1;
    font-size: 40px;
    margin: 0;
    font-weight: bold;
    text-transform: uppercase;
}

header .navbar {
    margin-top: 15px;
}

header .navbar a {
    color: #ecf0f1;
    font-size: 20px;
    text-decoration: none;
    margin: 0 15px;
    padding: 12px 25px;
    border-radius: 5px;
    background-color: #3498db;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: background-color 0.3s ease;
}

header .navbar a:hover {
    background-color: #2980b9;
}

.container {
    width: 85%;
    margin: 50px auto;
    padding: 40px 0;
    background-color: #34495e;
    border-radius: 8px;
}

h1 {
    text-align: center;
    font-size: 44px;
    margin-bottom: 40px;
    color: #ecf0f1;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
}

table, th, td {
    border: 1px solid #ecf0f1;
    text-align: center;
}

th, td {
    padding: 14px;
    font-size: 18px;
    font-weight: bold;
}

th {
    background-color: #1a252f;
    color: #ecf0f1;
}

td img {
    width: 110px;
    height: 110px;
    object-fit: cover;
    border-radius: 5px;
}

.harga {
    font-weight: bold;
    color: #e74c3c;
}

.produk-action {
    display: flex;
    justify-content: center;
    gap: 12px;
}

.produk-action a {
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 5px;
    background-color: #3498db;
    color: white;
    font-size: 16px;
    transition: background-color 0.3s ease, transform 0.2s;
}

.produk-action a:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
}

.produk-action a:active {
    transform: translateY(2px);
}

.btn-tambah {
    display: inline-block;
    margin-top: 30px;
    padding: 15px 25px;
    font-size: 22px;
    text-decoration: none;
    background-color: #2ecc71;
    color: white;
    border-radius: 5px;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s;
}

.btn-tambah:hover {
    background-color: #27ae60;
    transform: translateY(-2px);
}

.btn-tambah:active {
    transform: translateY(2px);
}

@media (max-width: 768px) {
    .container {
        padding: 20px 0;
    }

    h1 {
        font-size: 36px;
    }

    table, th, td {
        font-size: 14px;
    }

    .produk-action a {
        font-size: 14px;
        padding: 8px 15px;
    }
}

    </style>
</head>
<body>

<!-- Header Section -->
<header>
    <h1>Produk</h1>
    <div class="navbar">
        <a href="dashboard_admin.php">Branda</a>
    </div>
</header>

<div class="container">
    <h1>Daftar Produk</h1>

    <!-- Tabel Produk -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Gambar</th>
                <th>Nama Produk</th>
                <th>Kategori Produk</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
        <?php
if ($result->num_rows > 0) {
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        // URL gambar relatif terhadap file admin/produk.php
        $gambar_url = '../customer/gambar_produk/' . $row['gambar_produk'];

        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td><img src="' . $gambar_url . '" alt="' . htmlspecialchars($row['nama_produk']) . '" style="width: 100px; height: 100px;"></td>';
        echo '<td>' . htmlspecialchars($row['nama_produk']) . '</td>';
        echo '<td>' . htmlspecialchars($row['kategori_nama']) . '</td>';
        echo '<td class="harga">Rp ' . number_format($row['harga'], 2, ',', '.') . '</td>';
        echo '<td>' . htmlspecialchars($row['stok_tersedia']) . '</td>';
        echo '<td class="produk-action">
                <a href="edit_produk.php?id=' . $row['id'] . '">Ubah</a>
                <a href="?hapus=' . $row['id'] . '" onclick="return confirm(\'Apakah Anda yakin ingin menghapus produk ini?\')">Hapus</a>
              </td>';
        echo '</tr>';
    }
} else {
    echo "<tr><td colspan='7'>Tidak ada produk.</td></tr>";
}
?>
        </tbody>
    </table>

    <!-- Tombol Tambah Produk -->
    <a href="tambah_produk.php" class="btn-tambah">Tambah Produk</a>
</div>

<?php
$conn->close();
?>

</body>
</html>
