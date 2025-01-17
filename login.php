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
    $email = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    if (!empty($email) && !empty($password) && !empty($role)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['username'] = $user['email'];

                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard_admin.php");
                    exit();
                } elseif ($user['role'] === 'customer') {
                    header("Location: customer/dashboard_pelanggan.php");
                    exit();
                }
            } else {
                echo "<script>alert('Password salah!'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('Email atau role salah!'); window.location.href='index.php';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Harap lengkapi semua data!'); window.location.href='index.php';</script>";
    }
}

$conn->close();
?>
