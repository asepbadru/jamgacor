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

// Ambil data kategori dari tabel
$sql = "SELECT * FROM kategori";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Produk</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Ganti sesuai dengan lokasi file CSS Anda -->
</head>
<body>
    <section class="categories">
        <div class="container">
            <h2>Kategori Produk</h2>
            <div class="category-grid">
                <?php
                if ($result->num_rows > 0) {
                    // Tampilkan setiap kategori
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="category">';
                        echo '<img src="' . $row['gambar'] . '" alt="' . $row['nama'] . '">';
                        echo '<h3>' . $row['nama'] . '</h3>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Tidak ada kategori yang tersedia.</p>';
                }
                ?>
            </div>
        </div>
    </section>
</body>
</html>

<?php
// Tutup koneksi database
$conn->close();
?>
