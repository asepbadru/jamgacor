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

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Hapus customer jika tombol hapus diklik
if (isset($_GET['hapus'])) {
    $user_id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: pengguna.php");
    exit();
}

// Ambil data customer
$result_customers = $conn->query("SELECT id, profile_picture, name, email, address, phone_number FROM users WHERE role = 'customer'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengguna - Admin</title>
    <style>
       body {
    font-family: 'Arial', sans-serif;
    background-color: #2c3e50; /* Dark background */
    color: #ecf0f1; /* Light text color for contrast */
    margin: 0;
    padding: 0;
}

.container {
    width: 90%;
    margin: 20px auto;
    text-align: center;
}

h1 {
    font-size: 2.8em;
    margin-bottom: 40px;
    color: #ecf0f1; /* Lighter color for the header */
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 3px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background-color: #34495e; /* Dark table background */
}

table, th, td {
    border: 1px solid #e74c3c; /* Red borders for bold look */
}

th, td {
    padding: 15px;
    text-align: center;
    font-size: 1.2em;
}

th {
    background-color: #1a252f; /* Darker header for contrast */
    color: #e74c3c; /* Red text in header for boldness */
}

.profile-picture {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e74c3c; /* Red border for profile pictures */
}

.back-button {
    display: inline-block;
    padding: 15px 30px;
    margin-top: 30px;
    background-color: #e74c3c;
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-size: 1.5em;
    text-transform: uppercase;
    font-weight: bold;
    letter-spacing: 2px;
    transition: background-color 0.3s, transform 0.3s;
}

.back-button:hover {
    background-color: #c0392b;
    transform: scale(1.1);
}

.delete-button {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1.1em;
    text-transform: uppercase;
    font-weight: bold;
    transition: background-color 0.3s, transform 0.2s;
}

.delete-button:hover {
    background-color: #c0392b;
    transform: scale(1.1);
}

.delete-button:active {
    transform: scale(0.95);
}

table tr:nth-child(odd) {
    background-color: #2c3e50; /* Slightly lighter dark rows */
}

table tr:nth-child(even) {
    background-color: #1a252f; /* Darker rows */
}

@media (max-width: 768px) {
    .container {
        width: 95%;
    }

    h1 {
        font-size: 2.2em;
    }

    table, th, td {
        font-size: 1em;
    }

    .delete-button {
        padding: 6px 12px;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Pengguna - Admin</h1>
        
        <!-- Tombol Kembali -->
        <a href="dashboard_admin.php" class="back-button">Kembali ke Dashboard</a>

        <!-- Tabel Pengguna -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Profile</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th>No HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_customers->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?php if ($row['profile_picture']): ?>
                                <img src="uploads/<?= $row['profile_picture'] ?>" alt="Profile Picture" class="profile-picture">
                            <?php else: ?>
                                <img src="uploads/default.png" alt="Profile Picture" class="profile-picture">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= htmlspecialchars($row['phone_number']) ?></td>
                        <td>
                            <a href="?hapus=<?= $row['id'] ?>" class="delete-button" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
