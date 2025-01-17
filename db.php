<?php
// Koneksi ke database balap
$servername = "localhost";  // Ganti dengan server database Anda
$username_db = "root";      // Ganti dengan username database Anda
$password_db = "";          // Ganti dengan password database Anda
$dbname = "balap";     // Nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
