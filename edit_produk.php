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

// Mengecek apakah parameter id ada
if (isset($_GET['id'])) {
    $id_produk = $_GET['id'];
    // Mengambil data produk dari database berdasarkan ID
    $sql = "SELECT * FROM produk WHERE id = $id_produk";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Data produk ditemukan
        $row = $result->fetch_assoc();
    } else {
        // Produk tidak ditemukan
        echo "<script>alert('Produk tidak ditemukan.'); window.location.href = 'produk.php';</script>";
        exit;
    }
} else {
    // ID tidak ada di URL
    echo "<script>alert('ID produk tidak valid.'); window.location.href = 'produk.php';</script>";
    exit;
}

// Proses update data produk
if (isset($_POST['update'])) {
    $nama_produk = $_POST['nama_produk'];
    $id_kategori = $_POST['id_kategori'];
    $harga = $_POST['harga'];
    $stok_tersedia = $_POST['stok_tersedia'];

    // Menangani upload gambar
    if (isset($_FILES['gambar_produk']) && $_FILES['gambar_produk']['error'] == 0) {
        $gambar_produk = $_FILES['gambar_produk']['name'];
        $target = "../customer/gambar_produk/" . basename($gambar_produk);  // Path yang benar untuk upload
        move_uploaded_file($_FILES['gambar_produk']['tmp_name'], $target);
    } else {
        $gambar_produk = $row['gambar_produk']; // Jika gambar tidak diubah, gunakan gambar lama
    }

    // Update data produk
    $update_sql = "UPDATE produk SET nama_produk = '$nama_produk', id_kategori = $id_kategori, harga = $harga, 
                   stok_tersedia = $stok_tersedia, gambar_produk = '$gambar_produk' WHERE id = $id_produk";
    
    if ($conn->query($update_sql) === TRUE) {
        echo "<script>alert('Produk berhasil diupdate.'); window.location.href = 'produk.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Comp.ID</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2c3e50;
            padding: 20px 0;
            text-align: center;
        }

        header h1 {
            color: white;
            font-size: 36px;
            margin: 0;
            font-weight: bold;
        }

        .container {
            width: 60%;
            margin: 0 auto;
            padding: 50px 0;
        }

        h1 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 30px;
            color: #2c3e50;
            font-weight: 600;
        }

        form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            font-size: 18px;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            font-size: 16px;
            text-decoration: none;
            background-color: #95a5a6;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #7f8c8d;
        }

    </style>
</head>
<body>

<!-- Header -->
<header>
    <h1>Edit Produk - Comp.ID</h1>
</header>

<div class="container">
    <h1>Ubah Data Produk</h1>

    <form action="edit_produk.php?id=<?php echo $id_produk; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nama_produk">Nama Produk</label>
            <input type="text" id="nama_produk" name="nama_produk" value="<?php echo $row['nama_produk']; ?>" required>
        </div>
        <div class="form-group">
            <label for="id_kategori">Kategori Produk</label>
            <select id="id_kategori" name="id_kategori" required>
                <?php
                // Menampilkan kategori
                $kategori_sql = "SELECT * FROM kategori";
                $kategori_result = $conn->query($kategori_sql);
                while ($kategori = $kategori_result->fetch_assoc()) {
                    $selected = $kategori['id_kategori'] == $row['id_kategori'] ? 'selected' : '';
                    echo "<option value='{$kategori['id_kategori']}' $selected>{$kategori['nama']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="harga">Harga</label>
            <input type="number" id="harga" name="harga" value="<?php echo $row['harga']; ?>" required>
        </div>
        <div class="form-group">
            <label for="stok_tersedia">Stok Tersedia</label>
            <input type="number" id="stok_tersedia" name="stok_tersedia" value="<?php echo $row['stok_tersedia']; ?>" required>
        </div>
        <div class="form-group">
            <label for="gambar_produk">Gambar Produk</label>
            <input type="file" id="gambar_produk" name="gambar_produk">
            <img src="../customer/gambar_produk/<?php echo $row['gambar_produk']; ?>" alt="Gambar Produk" width="100" height="100">
        </div>
        <div class="form-group">
            <input type="submit" name="update" value="Update Produk">
        </div>
    </form>

    <a href="produk.php" class="back-btn">Kembali ke Daftar Produk</a>
</div>

<?php
$conn->close();
?>

</body>
</html>
