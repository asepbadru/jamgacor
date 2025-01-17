<?php
// Koneksi ke database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'balap';

$conn = new mysqli($host, $user, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk mengambil data pesanan
$sql = "
    SELECT 
        t.id AS transaksi_id,
        t.status_pesanan,
        t.jasa_pengiriman,
        t.alamat,
        t.tanggal,
        t.ongkir,  -- Ongkir dari tabel transaksi
        t.grand_total,  -- Grand total dari tabel transaksi
        td.produk_id,
        p.nama_produk,
        p.gambar_produk,
        td.jumlah_unit,
        td.total_item
    FROM transaksi t
    JOIN transaksi_detail td ON t.id = td.transaksi_id
    JOIN produk p ON td.produk_id = p.id
    ORDER BY t.tanggal DESC
";

$result = $conn->query($sql);

$current_transaksi_id = null;
$total_harga = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Masuk</title>
    <style>
        /* Your CSS styling here */
        /* Body */
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #1a1a1a;  /* Dark background for a bold, sleek feel */
    color: #f1f1f1;  /* Light text for contrast */
}

/* Heading */
h1 {
    text-align: center;
    margin: 20px 0;
    color: #f1f1f1;
    font-size: 36px;
    font-weight: bold;
    letter-spacing: 2px;
}

/* Container */
.container {
    width: 90%;
    margin: 20px auto;
    background-color: #333;  /* Dark container background */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    padding: 30px;
    border-radius: 12px;
}

/* Transaksi */
.transaksi {
    margin-bottom: 30px;
    border-bottom: 2px solid #555;
    padding-bottom: 20px;
}

.transaksi h2 {
    color: #e74c3c;  /* Red color for a strong statement */
    font-size: 24px;
    margin-bottom: 15px;
}

.transaksi p {
    font-size: 16px;
    line-height: 1.5;
    color: #ddd;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border: 1px solid #444;
    font-size: 16px;
    color: #ddd;
}

table th {
    background-color: #444;
    color: #fff;
    text-transform: uppercase;
    font-weight: bold;
}

table tr:nth-child(even) {
    background-color: #555;
}

table tr:hover {
    background-color: #666;
}

/* Images */
img {
    max-width: 100px;
    height: auto;
    display: block;
    border-radius: 5px;
}

/* Button */
.back-button {
    display: block;
    margin: 20px auto;
    padding: 15px 25px;
    background-color: #e74c3c;
    color: #fff;
    text-align: center;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    font-size: 18px;
}

.back-button:hover {
    background-color: #c0392b;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: #222;
    padding: 25px;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    position: relative;
}

.modal-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #ff4d4d;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
}

.modal-close:hover {
    background-color: #cc0000;
}

/* Map */
.map {
    width: 100%;
    height: 400px;
    border-radius: 8px;
}

/* Media Queries for smaller screens */
@media (max-width: 768px) {
    table th, table td {
        font-size: 14px;
    }

    h1 {
        font-size: 28px;
    }
}

    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <h1>Pesanan Masuk</h1>
    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php if ($current_transaksi_id !== $row['transaksi_id']): ?>
                    <?php if ($current_transaksi_id !== null): ?>
                        <!-- Display the Ongkir and Total Harga here -->
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Harga Ongkir:</strong></td>
                            <td>Rp <?php echo number_format($ongkir, 2, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Total Harga:</strong></td>
                            <td><strong>Rp <?php echo number_format(($total_harga + $ongkir), 2, ',', '.'); ?></strong></td>
                        </tr>
                        </table>
                    </div>
                    <?php $total_harga = 0; ?>
                    <?php endif; ?>
                    <?php 
                    $current_transaksi_id = $row['transaksi_id']; 
                    $ongkir = $row['ongkir'] ?? 0; // Default to 0 if ongkir is null
                    ?>
                    <div class="transaksi">
                        <h2>Transaksi ID: <?php echo htmlspecialchars($row['transaksi_id']); ?></h2>
                        <p>Status: <?php echo htmlspecialchars($row['status_pesanan']); ?></p>
                        <p>Jasa Pengiriman: <?php echo htmlspecialchars($row['jasa_pengiriman']); ?></p>
                        <p>Alamat Pengiriman: 
                            <span class="alamat"><?php echo htmlspecialchars($row['alamat']); ?></span>
                            <button class="lihat-peta" data-alamat="<?php echo htmlspecialchars($row['alamat']); ?>">Lihat Lokasi</button>
                        </p>
                        <p>Tanggal: <?php echo htmlspecialchars($row['tanggal']); ?></p>
                        <!-- Ongkir ditampilkan di bawah tanggal -->
                        <p><strong>Ongkir:</strong> Rp <?php echo number_format($ongkir, 2, ',', '.'); ?></p>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Produk</th>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Jumlah Unit</th>
                                    <th>Harga Keseluruhan</th>
                                </tr>
                            </thead>
                            <tbody>
                <?php endif; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['produk_id']); ?></td>
                                    <td><img src="/projek-uas/customer/gambar_produk/<?php echo htmlspecialchars($row['gambar_produk']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>"></td>
                                    <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jumlah_unit']); ?></td>
                                    <td>Rp <?php echo number_format($row['total_item'] ?? 0, 2, ',', '.'); ?></td>
                                </tr>
                                <?php $total_harga += $row['total_item'] ?? 0; ?>
            <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align: right;"><strong>Harga Ongkir:</strong></td>
                                    <td>Rp <?php echo number_format($ongkir, 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="text-align: right;"><strong>Total Harga:</strong></td>
                                    <td><strong>Rp <?php echo number_format(($total_harga + $ongkir), 2, ',', '.'); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
        <?php else: ?>
            <p>Tidak ada pesanan masuk.</p>
        <?php endif; ?>
    </div>
    <a href="dashboard_admin.php" class="back-button">Kembali ke Halaman Dashboard</a>

    <div class="modal" id="modal-peta">
        <div class="modal-content">
            <button class="modal-close">Tutup</button>
            <div id="map" class="map"></div>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal-peta');
    const mapContainer = document.getElementById('map');
    const closeModal = document.querySelector('.modal-close');
    let map, marker;

    // Fungsi untuk menampilkan peta dan marker
    function showMap(alamat) {
        modal.style.display = 'flex';
        if (!map) {
            map = L.map(mapContainer).setView([-6.200000, 106.816666], 13);  // default view jika tidak ada lokasi ditemukan
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
        }

        // Menangani alamat dan pencarian titik koordinat dari OpenStreetMap Nominatim API
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(alamat)}&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    // Menambahkan marker pada lokasi yang ditemukan
                    const latlng = [data[0].lat, data[0].lon];
                    map.setView(latlng, 15);
                    if (marker) {
                        marker.setLatLng(latlng);
                    } else {
                        marker = L.marker(latlng).addTo(map);
                    }
                } else {
                    alert('Lokasi tidak ditemukan. Cek alamat yang dimasukkan.');
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat memuat lokasi.');
                console.error(error);
            });
    }

    // Menangani klik pada tombol "Lihat Lokasi"
    document.querySelectorAll('.lihat-peta').forEach(button => {
        button.addEventListener('click', function() {
            const alamat = this.getAttribute('data-alamat');
            if (alamat) {
                showMap(alamat);
            } else {
                alert('Alamat tidak tersedia.');
            }
        });
    });

    // Menutup modal saat tombol tutup diklik
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });
});

    </script>
</body>
</html>

<?php
$conn->close();
?>
