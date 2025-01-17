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

// Ambil data produk berdasarkan id dari URL
$id_produk = $_GET['id'] ?? 1; // Default id = 1 jika tidak ada parameter id
$query = "SELECT * FROM produk WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if (!$produk) {
    die("Produk tidak ditemukan.");
}

// Hitung persentase diskon jika ada diskon
$harga_asli = $produk['harga'];
$harga_diskon = $produk['harga_diskon'];
$persentase_diskon = 0;
if ($harga_diskon && $harga_diskon < $harga_asli) {
    $persentase_diskon = round((($harga_asli - $harga_diskon) / $harga_asli) * 100);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deskripsi Produk</title>
    <style>
        /* General body and background styling */
body {
    font-family: 'Arial', sans-serif;
    background-color: #212121; /* Dark background */
    color: #ecf0f1; /* Light text color */
    margin: 0;
    padding: 0;
}

/* Header */
header {
    background-color: #333; /* Dark gray for a tough look */
    color: white;
    padding: 20px 0;
    text-align: center;
    border-bottom: 4px solid #e74c3c; /* Red underline for a bold effect */
}

header .logo {
    display: flex;
    align-items: center;
    justify-content: center;
}

header .logo h1 {
    font-size: 2.5em;
    color: #e74c3c; /* Red logo for intensity */
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
}

header nav a {
    color: #ecf0f1;
    text-decoration: none;
    margin: 0 20px;
    font-size: 1.2em;
    font-weight: bold;
}

header nav a:hover {
    color: #e74c3c; /* Red hover effect */
}

/* Footer */
footer {
    background-color: #333;
    color: white;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    width: 100%;
    bottom: 0;
    font-size: 1.1em;
}

/* Container for product details */
.container {
    max-width: 1200px;
    margin: 50px auto;
    padding: 20px;
    background-color: #2c3e50; /* Dark, industrial feel */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    border-radius: 10px;
}

.product-image {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

/* Product details section */
.product-details {
    margin-top: 30px;
}

.product-name {
    font-size: 2.5em;
    font-weight: bold;
    color: #e74c3c; /* Red for the product name */
    text-transform: uppercase;
    margin-bottom: 15px;
    letter-spacing: 2px;
}

.product-price {
    font-size: 2em;
    color: #f39c12; /* Yellow for price */
    font-weight: bold;
    margin-bottom: 20px;
}

.product-price .original-price {
    text-decoration: line-through;
    color: #888;
    margin-left: 15px;
}

.product-discount {
    font-size: 1.5em;
    color: #e74c3c; /* Bold red for discount */
    margin-bottom: 20px;
}

.product-description {
    font-size: 1.3em;
    line-height: 1.6;
    color: #ecf0f1;
    margin-bottom: 20px;
}

.product-stock {
    font-size: 1.5em;
    color: #f39c12; /* Yellow for stock availability */
    font-weight: bold;
    margin-bottom: 30px;
}

.button-container {
    margin-top: 30px;
}

.button {
    display: inline-block;
    background-color: #e74c3c; /* Red background for the button */
    color: white;
    padding: 15px 25px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 1.5em;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.button:hover {
    background-color: #c0392b; /* Darker red when hovering */
}

.button:active {
    transform: translateY(2px); /* Button presses down when clicked */
}

/* Secondary button for "Beli" */
.button.secondary {
    background-color: #007bff; /* Blue for 'Buy' button */
    margin-left: 20px;
}

.button.secondary:hover {
    background-color: #0056b3;
}

    </style>
</head>
<body>

<!-- Header -->
<header>
    <div class="logo">
        <h1>Produk Usa</h1> <!-- Nama toko -->
    </div>
    <nav>
        <a href="dashboard_pelanggan.php">Beranda</a> <!-- Tombol untuk kembali ke dashboard pelanggan -->
        <a href="keranjang.php">Keranjang</a> <!-- Tombol untuk melihat keranjang -->
    </nav>
</header>

<!-- Main Content -->
<div class="container">
    <img src="gambar_produk/<?php echo $produk['gambar_produk']; ?>" alt="<?php echo $produk['nama_produk']; ?>" class="product-image">
    <div class="product-details">
        <div class="product-name"><?php echo $produk['nama_produk']; ?></div>
        <div class="product-price">
            <?php if ($persentase_diskon > 0): ?>
                Rp<?php echo number_format($harga_diskon, 0, ',', '.'); ?>
                <span class="original-price">Rp<?php echo number_format($harga_asli, 0, ',', '.'); ?></span>
            <?php else: ?>
                Rp<?php echo number_format($harga_asli, 0, ',', '.'); ?>
            <?php endif; ?>
        </div>
        <?php if ($persentase_diskon > 0): ?>
            <div class="product-discount">Diskon: <?php echo $persentase_diskon; ?>%</div>
        <?php endif; ?>
        <div class="product-description"><?php echo nl2br($produk['deskripsi_produk']); ?></div>
        <div class="product-stock">
            Stok Tersedia: <?php echo $produk['stok_tersedia']; ?> unit
        </div> <!-- Menampilkan stok produk -->
        <div class="button-container">
            <a href="keranjang.php?add=<?php echo $produk['id']; ?>" class="button">+ Keranjang</a>
            <a href="keranjang.php?add=<?php echo $produk['id']; ?>" class="button" style="background-color: #007bff;">Beli</a>
        </div>
    </div>
</div>



</body>
</html>

<?php
$conn->close();
?>
