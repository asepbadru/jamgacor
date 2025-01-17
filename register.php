<?php
session_start();

// Sertakan file koneksi
$host = "localhost";
$dbname = "balap"; // Nama database
$username = "root";
$password = "";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password untuk keamanan
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $role = 'customer'; // Role default customer

    // Validasi dan simpan data pengguna
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone_number, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $role, $phone_number, $address);

    if ($stmt->execute()) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan. Silakan coba lagi.'); window.location.href='index.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
