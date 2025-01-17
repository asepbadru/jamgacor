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

// Logout jika tombol logout diklik
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil total pendapatan dari transaksi
$sql_revenue = "SELECT SUM(grand_total) AS total_pendapatan FROM transaksi";
$result_revenue = $conn->query($sql_revenue);

$totalRevenue = 0;
if ($result_revenue && $result_revenue->num_rows > 0) {
    $row_revenue = $result_revenue->fetch_assoc();
    $totalRevenue = $row_revenue['total_pendapatan'];
}

// Ambil data total produk
$sql_products = "SELECT COUNT(*) AS total_products FROM produk";
$result_products = $conn->query($sql_products);

$products = 0;
if ($result_products && $result_products->num_rows > 0) {
    $row_products = $result_products->fetch_assoc();
    $products = $row_products['total_products'];
}

// Ambil data pesanan masuk (status 'Menunggu Konfirmasi')
$sql_orders = "SELECT COUNT(*) AS total_orders FROM transaksi WHERE status_pesanan = 'Menunggu Konfirmasi'";
$result_orders = $conn->query($sql_orders);

$orders = 0;
if ($result_orders && $result_orders->num_rows > 0) {
    $row_orders = $result_orders->fetch_assoc();
    $orders = $row_orders['total_orders'];
}

// Ambil data total pelanggan unik yang pernah bertransaksi
$sql_customers = "SELECT COUNT(DISTINCT user_id) AS total_customers FROM transaksi";
$result_customers = $conn->query($sql_customers);

$customers = 0;
if ($result_customers && $result_customers->num_rows > 0) {
    $row_customers = $result_customers->fetch_assoc();
    $customers = $row_customers['total_customers'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
    body {
    font-family: 'Roboto', sans-serif;
    background-color: #1d1f20; /* Dark background for a more intense feel */
    margin: 0;
    padding: 0;
    color: #ecf0f1; /* Light text for contrast */
}

.container {
    width: 90%;
    margin: 20px auto;
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #2c3e50; /* Darker header for bold look */
    color: #ecf0f1;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

header h1 {
    margin: 0;
    font-size: 2em;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
}

header nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
}

header nav ul li {
    margin: 0 15px;
}

header nav ul li a {
    color: #ecf0f1;
    text-decoration: none;
    font-size: 1.1em;
    transition: color 0.3s ease;
}

header nav ul li a:hover {
    color: #e74c3c; /* Red hover effect for intensity */
}

.dashboard-cards {
    display: flex;
    gap: 20px;
    margin-top: 40px;
    flex-wrap: wrap;
    justify-content: space-between;
}

.card {
    background: #34495e; /* Darker card background for strong contrast */
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    flex: 1;
    min-width: 280px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
}

.card h3 {
    margin: 0;
    font-size: 1.6em;
    color: #e74c3c; /* Red color for headings for a macho feel */
    font-weight: 700;
    transition: color 0.3s ease;
}

.card h3 a {
    color: inherit;
    text-decoration: none;
}

.card h3 a:hover {
    text-decoration: underline;
}

.card p {
    margin: 20px 0 0;
    font-size: 1.8em;
    font-weight: bold;
    color: #ecf0f1; /* Light text for readability */
}

.card a {
    text-decoration: none;
    color: inherit;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    header h1 {
        font-size: 1.6em;
    }

    header nav ul {
        flex-direction: column;
        align-items: center;
    }

    .dashboard-cards {
        flex-direction: column;
        align-items: center;
    }

    .card {
        width: 100%;
        margin-bottom: 20px;
    }
}

</style>

</head>
<body>
    <div class="container">
        <header>
            <h1>Dashboard Admin</h1>
            <nav>
                <ul>
                    <li><a href="dashboard_admin.php">Beranda</a></li>
                    <li><a href="dashboard_admin.php?logout=true">Logout</a></li>
                </ul>
            </nav>
        </header>

        <div class="dashboard-cards">
            <div class="card">
                <h3><a href="penjualan.php">Total Pendapatan</a></h3>
                <p>Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></p>
            </div>
            <div class="card">
                <h3><a href="produk.php">Produk Saya</a></h3>
                <p><?php echo $products; ?> Produk</p>
            </div>
            <div class="card">
                <h3><a href="pesanan_masuk.php">Pesanan Masuk</a></h3>
                <p><?php echo $orders; ?> Pesanan</p>
            </div>
            <div class="card">
                <h3><a href="pengguna.php">Pengguna</a></h3>
                <p><?php echo $customers; ?> Customer</p>
            </div>
        </div>
    </div>
</body>
</html>
