<?php
// Koneksi ke database
$servername = "localhost";  // Ganti dengan server Anda
$username = "root";         // Ganti dengan username Anda
$password = "";             // Ganti dengan password Anda
$dbname = "balap";     // Nama database

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mengambil data penjualan (total per tanggal)
$sql = "SELECT DATE(tanggal) as tgl, SUM(grand_total) as total_penjualan FROM transaksi GROUP BY DATE(tanggal)";
$result = $conn->query($sql);

// Menyiapkan data untuk grafik
$labels = [];
$dataPenjualan = [];
$salesData = [];
$totalRevenue = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['tgl'];
        $dataPenjualan[] = $row['total_penjualan'];
        $salesData[] = $row;
        $totalRevenue += $row['total_penjualan'];
    }
} else {
    echo "0 results";
}

$conn->close();

// Fungsi untuk mengunduh data sebagai file Excel
function downloadExcel($data) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=penjualan.xls");
    echo "Tanggal\tTotal Penjualan\n";
    foreach ($data as $row) {
        echo $row['tgl'] . "\t" . $row['total_penjualan'] . "\n";
    }
    exit;
}

// Fungsi untuk mengunduh data sebagai file Word
function downloadWord($data) {
    header("Content-Type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=penjualan.doc");
    echo "<html><body><table border='1'><tr><th>Tanggal</th><th>Total Penjualan</th></tr>";
    foreach ($data as $row) {
        echo "<tr><td>" . $row['tgl'] . "</td><td>" . $row['total_penjualan'] . "</td></tr>";
    }
    echo "</table></body></html>";
    exit;
}

// Cek jika ada permintaan untuk mengunduh data
if (isset($_GET['download'])) {
    if ($_GET['download'] === 'excel') {
        downloadExcel($salesData);
    } elseif ($_GET['download'] === 'word') {
        downloadWord($salesData);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penjualan Admin</title>
    <style>
    body {
    font-family: 'Poppins', sans-serif;
    background-color: #1b1b1b; /* Darker background for more intensity */
    margin: 0;
    padding: 0;
    color: #ecf0f1; /* Light text for contrast */
}

.container {
    width: 85%;
    margin: 30px auto;
    background-color: #333; /* Dark container background */
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3); /* Strong shadow for depth */
}

h1 {
    font-size: 2.8em; /* Larger title for impact */
    color: #f39c12; /* Bold orange for a more energetic feel */
    margin-bottom: 30px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

a {
    text-decoration: none;
    font-weight: bold;
}

.back-button, .download-button {
    padding: 14px 30px;
    margin-top: 20px;
    margin-right: 15px;
    background-color: #e74c3c; /* Red for aggression */
    color: white;
    font-size: 1.2em;
    border-radius: 8px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.back-button:hover, .download-button:hover {
    background-color: #c0392b; /* Darker red on hover */
    transform: translateY(-3px);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 40px;
}

th, td {
    padding: 18px;
    text-align: center;
}

th {
    background-color: #2c3e50; /* Darker header for strength */
    color: #ecf0f1;
    font-size: 1.2em;
}

td {
    background-color: #34495e; /* Dark cells with contrast */
    color: #ecf0f1;
    font-size: 1.1em;
}

td:hover {
    background-color: #7f8c8d; /* Lighter on hover for interaction */
    cursor: pointer;
}

tr:nth-child(even) td {
    background-color: #2c3e50; /* Strong contrast for even rows */
}

tr:nth-child(odd) td {
    background-color: #34495e;
}

h2 {
    font-size: 2em; /* Larger for more impact */
    color: #e74c3c; /* Red to keep the intensity */
    margin-top: 30px;
}

.chart-container {
    margin-top: 40px;
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.chart-container canvas {
    width: 48%;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgb(250, 246, 246); /* Stronger shadow for depth */
}

/* Custom Button Styles */
.download-button {
    background-color: #3498db; /* Strong blue for download buttons */
}

.download-button:hover {
    background-color: #2980b9; /* Darker blue on hover */
}

.back-button {
    background-color: #e74c3c; /* Strong red for back button */
}

.back-button:hover {
    background-color: #c0392b; /* Darker red on hover */
}

/* Additional customizations for more intensity */
button, a {
    font-family: 'Poppins', sans-serif;
    font-weight: bold;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}


</style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>Penjualan Admin</h1>
        
        <!-- Tombol Kembali -->
        <a href="dashboard_admin.php" class="back-button">Kembali ke Dashboard</a>

        <!-- Tombol Download -->
        <a href="?download=excel" class="download-button">Download Excel</a>
        <a href="?download=word" class="download-button">Download Word</a>

        <!-- Tabel Penjualan -->
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Total Penjualan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salesData as $row): ?>
                    <tr>
                        <td><?= $row['tgl'] ?></td>
                        <td>Rp <?= number_format($row['total_penjualan'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Total Pendapatan -->
        <h2>Total Pendapatan: Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h2>

        <!-- Diagram Batang -->
        <canvas id="barChart"></canvas>

        <!-- Diagram Garis -->
        <canvas id="lineChart"></canvas>

        <!-- Diagram Lingkaran -->
        <canvas id="pieChart"></canvas>
    </div>

    <script>
        // Data untuk diagram (dari PHP)
        const labels = <?php echo json_encode($labels); ?>;
        const dataPenjualan = <?php echo json_encode($dataPenjualan); ?>;

        // Diagram Batang
        const ctxBar = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Penjualan',
                    data: dataPenjualan,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Diagram Garis
        const ctxLine = document.getElementById('lineChart').getContext('2d');
        const lineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Penjualan (Garis)',
                    data: dataPenjualan,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Diagram Lingkaran
        const ctxPie = document.getElementById('pieChart').getContext('2d');
        const pieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Distribusi Penjualan',
                    data: dataPenjualan,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#FF5733', '#33FF57'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>
