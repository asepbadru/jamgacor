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
    header("Location: ../index.php"); // Redirect ke halaman login
    exit();
}

// Ambil data user dari sesi
$userId = $_SESSION['user_id'] ?? null; // Ambil user_id dari sesi, null jika belum login
if (!$userId) {
    header("Location: login.php"); // Redirect ke halaman login jika belum login
    exit();
}

// Ambil data pengguna dari database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("Pengguna tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        /* General body styling */
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f4f7fa;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Header Section */
header.navbar {
    background-color: #343a40;
    color: #fff;
    padding: 20px 0;
    font-size: 18px;
}

header .container {
    display: flex;
    justify-content: space-around;
    align-items: center;
}

header .nav-link {
    color: #fff;
    text-decoration: none;
    padding: 10px 20px;
    font-weight: 500;
    border-radius: 5px;
}

header .nav-link:hover {
    background-color: #495057;
}

/* Profile Content Section */
.profile-content {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 50px 0;
}

.profile-container {
    background-color: #fff;
    padding: 30px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    width: 80%;
    max-width: 900px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile-card {
    text-align: center;
    margin-bottom: 30px;
}

.profile-card .profile-picture {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid #007bff;
    margin-bottom: 20px;
}

.profile-name {
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.profile-email {
    font-size: 16px;
    color: #555;
    margin-bottom: 20px;
}

.profile-card .btn {
    background-color: #007bff;
    color: #fff;
    padding: 10px 25px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.profile-card .btn:hover {
    background-color: #0056b3;
}

/* Profile Details Table */
.profile-details {
    width: 100%;
}

.profile-details h2 {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

.profile-table {
    width: 100%;
    border-collapse: collapse;
}

.profile-table th,
.profile-table td {
    padding: 12px 20px;
    text-align: left;
    font-size: 16px;
    border-bottom: 1px solid #ddd;
}

.profile-table th {
    background-color: #f1f1f1;
    color: #333;
    font-weight: 600;
}

.profile-table td {
    color: #555;
}

.profile-table tr:hover {
    background-color: #f9f9f9;
}

        </style>
    
</head>
<body>
    <!-- Header Section -->
    <header class="navbar">
        <div class="container">
            <a href="dashboard_pelanggan.php" class="nav-link">Beranda</a>
            <a href="keranjang.php" class="nav-link">Keranjang</a>
            <a href="pesanan.php" class="nav-link">Pesanan Saya</a>
            <a href="?logout=true" class="nav-link">Logout</a>
        </div>
    </header>

    <!-- Main Content Section -->
    <main class="profile-content">
        <div class="profile-container">
            <div class="profile-card">
                <img src="<?php echo $user['profile_picture'] ? $user['profile_picture'] : 'default-profile.png'; ?>" alt="Profile Picture" class="profile-picture">
                <h1 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <a href="edit_profile.php" class="btn">Edit Profile</a>
            </div>
            <div class="profile-details">
                <h2>Detail Informasi</h2>
                <table class="profile-table">
                    <tr>
                        <th>Nama</th>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                    </tr>
                    <tr>
                        <th>Nomor Telepon</th>
                        <td><?php echo htmlspecialchars($user['phone_number'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td><?php echo htmlspecialchars($user['address'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Dibuat Pada</th>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
