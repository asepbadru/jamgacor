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

// Variabel untuk menangani error
$error = '';

// Proses tambah produk
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $deskripsi_produk = $_POST['deskripsi_produk'];
    $gambar_produk = $_FILES['gambar_produk']['name'];
    $gambar_temp = $_FILES['gambar_produk']['tmp_name'];
    $id_kategori = $_POST['id_kategori'];
    $stok_tersedia = $_POST['stok_tersedia'];
    $harga_diskon = $_POST['harga_diskon'];

    // Mengatur folder upload gambar
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($gambar_produk);

    // Pindahkan file gambar ke folder yang sudah ditentukan
    if (move_uploaded_file($gambar_temp, $target_file)) {
        // Menambahkan produk ke database
        $sql = "INSERT INTO produk (nama_produk, harga, deskripsi_produk, gambar_produk, id_kategori, stok_tersedia, harga_diskon) 
                VALUES ('$nama_produk', '$harga', '$deskripsi_produk', '$gambar_produk', '$id_kategori', '$stok_tersedia', '$harga_diskon')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Produk berhasil ditambahkan!'); window.location.href = 'produk.php';</script>";
        } else {
            $error = "Error: " . $conn->error;
        }
    } else {
        $error = "Sorry, there was an error uploading your file.";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk </title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
    background-color: #2c3e50;
    font-family: 'Poppins', sans-serif;
    color: #ecf0f1;
}

.container {
    margin-top: 50px;
    max-width: 700px;
    background-color: #34495e;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
}

h1 {
    font-size: 32px;
    margin-bottom: 30px;
    color: #ecf0f1;
    text-align: center;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
}

header {
    background-color: #1a252f;
    padding: 20px 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
}

.header-title {
    color: #ecf0f1;
    font-size: 28px;
    text-align: center;
    font-weight: bold;
    text-transform: uppercase;
}

form .form-control {
    background-color: #2c3e50;
    border: 1px solid #2980b9;
    color: #ecf0f1;
    font-size: 16px;
    font-weight: bold;
    padding: 12px;
    border-radius: 5px;
}

form .form-control:focus {
    background-color: #34495e;
    border-color: #3498db;
    box-shadow: 0 0 10px rgba(52, 152, 219, 0.5);
}

label.form-label {
    font-size: 18px;
    font-weight: bold;
    color: #ecf0f1;
}

.btn-primary {
    background-color: #e74c3c;
    border: none;
    padding: 15px;
    font-size: 18px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 5px;
    transition: background-color 0.3s, transform 0.2s;
}

.btn-primary:hover {
    background-color: #c0392b;
    transform: translateY(-2px);
}

.btn-primary:active {
    transform: translateY(2px);
}

.back-link {
    margin-top: 25px;
    text-align: center;
}

.back-link a {
    text-decoration: none;
    color: #3498db;
    font-size: 18px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.back-link a:hover {
    color: #2980b9;
}

.form-error {
    color: #e74c3c;
    font-size: 16px;
    text-align: center;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .container {
        padding: 20px;
    }

    h1 {
        font-size: 28px;
    }

    .btn-primary {
        font-size: 16px;
    }

    label.form-label {
        font-size: 16px;
    }

    .form-control {
        font-size: 14px;
    }
}

    </style>
</head>
<body>

<!-- Header Section -->
<header>
    <div class="container">
        <h1 class="header-title"></h1>
    </div>
</header>

<div class="container">
    <h1>Form Tambah Produk</h1>

    <?php if ($error): ?>
        <p class="form-error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <!-- Nama Produk -->
        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" id="nama_produk" name="nama_produk" class="form-control" required>
        </div>

        <!-- Harga -->
        <div class="mb-3">
            <label for="harga" class="form-label">Harga</label>
            <input type="number" id="harga" name="harga" class="form-control" required step="0.01">
        </div>

        <!-- Deskripsi Produk -->
        <div class="mb-3">
            <label for="deskripsi_produk" class="form-label">Deskripsi Produk</label>
            <textarea id="deskripsi_produk" name="deskripsi_produk" class="form-control" rows="4" required></textarea>
        </div>

        <!-- Gambar Produk -->
        <div class="mb-3">
            <label for="gambar_produk" class="form-label">Gambar Produk</label>
            <input type="file" id="gambar_produk" name="gambar_produk" class="form-control" accept="image/*" required>
        </div>

       

        <!-- Stok Tersedia -->
        <div class="mb-3">
            <label for="stok_tersedia" class="form-label">Stok Tersedia</label>
            <input type="number" id="stok_tersedia" name="stok_tersedia" class="form-control" required>
        </div>

        <!-- Harga Diskon -->
        <div class="mb-3">
            <label for="harga_diskon" class="form-label">Harga Diskon (Opsional)</label>
            <input type="number" id="harga_diskon" name="harga_diskon" class="form-control" step="0.01">
        </div>

        <!-- Submit Button -->
        <div class="mb-3">
            <button type="submit" class="btn btn-primary w-100">Tambah Produk</button>
        </div>
    </form>

    <div class="back-link">
        <a href="produk.php">Kembali ke Daftar Produk</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
