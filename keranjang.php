<?php
session_start();

// Koneksi ke database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'balap';

$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu.'); window.location = 'login.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];

// Fungsi untuk menghitung ongkir berdasarkan jasa pengiriman
function hitungOngkir($jasa_pengiriman) {
    $ongkir = 0;

    switch ($jasa_pengiriman) {
        case 'JNE':
            $ongkir = 15000;
            break;
        case 'Tiki':
            $ongkir = 14000;
            break;
        case 'Pos':
            $ongkir = 13000;
            break;
        case 'SiCepat':
            $ongkir = 12000;
            break;
        case 'Gojek':
            $ongkir = 10000;
            break;
        case 'Grab':
            $ongkir = 10000;
            break;
        case 'Lion Parcel':
            $ongkir = 16000;
            break;
        case 'J&T Express':
            $ongkir = 15000;
            break;
        case 'Ninja Xpress':
            $ongkir = 17000;
            break;
        case 'Wahana':
            $ongkir = 11000;
            break;
        case 'ECO Express':
            $ongkir = 12000;
            break;
        case 'Anteraja':
            $ongkir = 13000;
            break;
        default:
            $ongkir = 0;  // Ongkir default jika jasa pengiriman tidak valid
    }

    return $ongkir;
}

// Fungsi untuk menyimpan keranjang ke database
function simpanKeranjang($conn, $user_id, $produk_id, $quantity) {
    $sql = "INSERT INTO keranjang (user_id, produk_id, quantity) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $produk_id, $quantity, $quantity);
    $stmt->execute();
}

// Fungsi untuk mengambil keranjang dari database
function ambilKeranjang($conn, $user_id) {
    $sql = "SELECT k.produk_id, k.quantity, p.nama_produk, p.harga, p.harga_diskon, p.gambar_produk 
            FROM keranjang k 
            JOIN produk p ON k.produk_id = p.id 
            WHERE k.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Menambah produk ke keranjang
if (isset($_GET['add'])) {
    $produk_id = intval($_GET['add']);

    // Validasi apakah produk tersedia
    $sql_produk = "SELECT id, nama_produk, harga, harga_diskon, gambar_produk, stok_tersedia FROM produk WHERE id = ?";
    $stmt_produk = $conn->prepare($sql_produk);
    $stmt_produk->bind_param("i", $produk_id);
    $stmt_produk->execute();
    $result_produk = $stmt_produk->get_result();

    if ($result_produk->num_rows > 0) {
        $produk = $result_produk->fetch_assoc();
        $stok_tersedia = $produk['stok_tersedia'];
        $quantity = 1; // Default quantity yang ditambahkan

        if (isset($_SESSION['keranjang'][$produk_id])) {
            // Update quantity jika sudah ada dalam keranjang
            $quantity = $_SESSION['keranjang'][$produk_id]['quantity'] + 1;
        }

        // Cek apakah stok cukup
        if ($quantity <= $stok_tersedia) {
            // Menambahkan produk ke keranjang
            $_SESSION['keranjang'][$produk_id] = [
                'id' => $produk['id'],
                'quantity' => $quantity,
                'nama_produk' => $produk['nama_produk'],
                'harga' => $produk['harga'],
                'harga_diskon' => $produk['harga_diskon'],
                'gambar' => $produk['gambar_produk']
            ];
            simpanKeranjang($conn, $user_id, $produk_id, $quantity);
        } else {
            echo "<script>alert('Stok tidak cukup untuk produk " . htmlspecialchars($produk['nama_produk']) . ".'); window.location = 'keranjang.php';</script>";
        }
    } else {
        echo "<script>alert('Produk tidak ditemukan.'); window.location = 'keranjang.php';</script>";
    }
    header('Location: keranjang.php');
    exit;
}

// Mengurangi jumlah produk di keranjang
if (isset($_GET['less'])) {
    $produk_id = intval($_GET['less']);
    if (isset($_SESSION['keranjang'][$produk_id]) && $_SESSION['keranjang'][$produk_id]['quantity'] > 1) {
        $_SESSION['keranjang'][$produk_id]['quantity'] -= 1;
        simpanKeranjang($conn, $user_id, $produk_id, $_SESSION['keranjang'][$produk_id]['quantity']);
    }
    header('Location: keranjang.php');
    exit;
}

// Menambah jumlah produk di keranjang
if (isset($_GET['more'])) {
    $produk_id = intval($_GET['more']);
    // Validasi apakah produk tersedia
    $sql_produk = "SELECT stok_tersedia FROM produk WHERE id = ?";
    $stmt_produk = $conn->prepare($sql_produk);
    $stmt_produk->bind_param("i", $produk_id);
    $stmt_produk->execute();
    $result_produk = $stmt_produk->get_result();

    if ($result_produk->num_rows > 0) {
        $produk = $result_produk->fetch_assoc();
        $stok_tersedia = $produk['stok_tersedia'];

        if (isset($_SESSION['keranjang'][$produk_id]) && $_SESSION['keranjang'][$produk_id]['quantity'] < $stok_tersedia) {
            $_SESSION['keranjang'][$produk_id]['quantity'] += 1;
            simpanKeranjang($conn, $user_id, $produk_id, $_SESSION['keranjang'][$produk_id]['quantity']);
        } else {
            echo "<script>alert('Stok tidak cukup untuk produk ini.'); window.location = 'keranjang.php';</script>";
        }
    }
    header('Location: keranjang.php');
    exit;
}

// Menghapus produk dari keranjang
if (isset($_GET['remove'])) {
    $produk_id = intval($_GET['remove']);
    if (isset($_SESSION['keranjang'][$produk_id])) {
        unset($_SESSION['keranjang'][$produk_id]);
        $sql_hapus = "DELETE FROM keranjang WHERE user_id = ? AND produk_id = ?";
        $stmt_hapus = $conn->prepare($sql_hapus);
        $stmt_hapus->bind_param("ii", $user_id, $produk_id);
        $stmt_hapus->execute();
    }
    header('Location: keranjang.php');
    exit;
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $jasa_pengiriman = $conn->real_escape_string($_POST['jasa_pengiriman']);
    $metode_bayar = $conn->real_escape_string($_POST['metode_bayar']); // Pastikan ini yang benar
    $bukti_pembayaran = $_FILES['bukti_pembayaran'];

    // Validasi data
    if (empty($alamat) || empty($jasa_pengiriman) || empty($metode_bayar) || $bukti_pembayaran['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Harap lengkapi semua data sebelum checkout.');</script>";
    } else {
        // Mengunggah bukti pembayaran
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $bukti_path = $upload_dir . uniqid() . "_" . basename($bukti_pembayaran['name']);
        if (!move_uploaded_file($bukti_pembayaran['tmp_name'], $bukti_path)) {
            echo "<script>alert('Gagal mengunggah bukti pembayaran.');</script>";
            exit;
        }

        // Hitung total jumlah barang, harga, subtotal, diskon, dan ongkir
        $total_jumlah = 0;
        $total_harga = 0.0;
        $total_diskon = 0.0;

        foreach ($_SESSION['keranjang'] as $item) {
            $total_jumlah += $item['quantity'];
            $total_harga += $item['quantity'] * $item['harga'];

            $diskon = $item['harga_diskon'] > 0 ? $item['harga'] - $item['harga_diskon'] : 0;
            $total_diskon += $diskon * $item['quantity'];
        }

        $total = $total_harga - $total_diskon;
        $ongkir = hitungOngkir($jasa_pengiriman); // Hitung ongkir berdasarkan jasa pengiriman (tanpa dikali jumlah barang)

        // Total pembayaran akhir
        $grand_total = $total + $ongkir;

        // Menyimpan transaksi ke tabel transaksi
        $sql_transaksi = "INSERT INTO transaksi 
            (user_id, alamat, jasa_pengiriman, ongkir, metode_bayar, status_pesanan, jumlah_item, subtotal, diskon, total, bukti_pembayaran, grand_total) 
            VALUES 
            (?, ?, ?, ?, ?, 'Menunggu Konfirmasi', ?, ?, ?, ?, ?, ?)";
        $stmt_transaksi = $conn->prepare($sql_transaksi);
        $stmt_transaksi->bind_param(
            "isssiddsdsd",  // Menyesuaikan dengan tipe data yang sesuai di database
            $user_id,
            $alamat,
            $jasa_pengiriman,
            $ongkir,
            $metode_bayar,
            $total_jumlah,
            $total_harga,
            $total_diskon,
            $total,
            $bukti_path,
            $grand_total
        );

        if (!$stmt_transaksi->execute()) {
            echo "Error: " . $stmt_transaksi->error;
        } else {
            $transaksi_id = $stmt_transaksi->insert_id;

            // Menyimpan detail transaksi
            foreach ($_SESSION['keranjang'] as $produk_id => $item) {
                $harga_satuan = $item['harga'];
                $subtotal = $item['quantity'] * $harga_satuan;
                $total_item = $item['quantity'] * $item['harga_diskon'];

                $sql_detail = "INSERT INTO transaksi_detail (transaksi_id, produk_id, harga_satuan, jumlah_unit, subtotal, harga_diskon, total_item) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_detail = $conn->prepare($sql_detail);
                $stmt_detail->bind_param("iiiddii", $transaksi_id, $produk_id, $harga_satuan, $item['quantity'], $subtotal, $item['harga_diskon'], $total_item);
                $stmt_detail->execute();

                // Update stok produk
                $sql_update_stok = "UPDATE produk SET stok_tersedia = stok_tersedia - ? WHERE id = ?";
                $stmt_update_stok = $conn->prepare($sql_update_stok);
                $stmt_update_stok->bind_param("ii", $item['quantity'], $produk_id);
                $stmt_update_stok->execute();
            }

            // Hapus keranjang setelah checkout
            $sql_hapus_keranjang = "DELETE FROM keranjang WHERE user_id = ?";
            $stmt_hapus_keranjang = $conn->prepare($sql_hapus_keranjang);
            $stmt_hapus_keranjang->bind_param("i", $user_id);
            $stmt_hapus_keranjang->execute();

            unset($_SESSION['keranjang']);
            echo "<script>alert('Checkout berhasil! Total pembayaran: Rp " . number_format($grand_total, 0, ',', '.') . "'); window.location = 'pesanan.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
    body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #000; /* Soft grey for a neutral background */
}

header {
    background-color: #004085; /* Darker blue */
    padding: 20px;
    color: white;
    text-align: center;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Adding subtle shadow for depth */
}

header h1 {
    margin: 0;
    font-weight: 700; /* Making the header text bold and strong */
    font-size: 2.5rem; /* Bigger font size for prominence */
}
h3 {
    font-size: 2rem; /* Ukuran font lebih besar untuk menonjol */
    font-weight: 700; /* Menebalkan teks */
    color:rgb(244, 235, 235); /* Warna merah menyala */
    text-transform: uppercase; /* Semua huruf kapital */
    letter-spacing: 2px; /* Jarak antar huruf lebih lebar */
    text-align: center; /* Posisi teks di tengah */
    padding: 20px 0; /* Padding atas dan bawah untuk jarak */
    margin-bottom: 30px; /* Jarak bawah yang lebih besar */
    font-family: 'Poppins', sans-serif; /* Font modern dan bersih */
    border-bottom: 3px solid #ff3b3f; /* Garis bawah dengan warna yang sama */
}

main {
    padding: 30px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.table-bordered th, .table-bordered td {
    vertical-align: middle;
    text-align: center;
    font-weight: 500; /* Adding boldness for a strong presence */
}

.table-bordered th {
    background-color: #004085;
    color: white;
}

.table-bordered td {
    background-color: #f9f9f9; /* Light background for content cells */
    color: #333; /* Darker text color for better contrast */
}

.total-row {
    font-weight: bold;
    background-color: #29a745; /* Strong green for contrast */
}

.summary {
    border: 1px solid #ddd;
    padding: 25px;
    margin-top: 30px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Adding shadow to summary for a floating effect */
}

.summary h4 {
    margin-bottom: 20px;
    font-size: 1.5rem;
    color: #333;
}

.btn-primary, .btn-warning, .btn-danger {
    margin: 5px;
    font-weight: bold;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-warning {
    background-color: #f39c12;
    border-color: #f39c12;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

footer {
    background-color: #343a40;
    color: white;
    text-align: center;
    padding: 15px 0;
    margin-top: 20px;
    box-shadow: 0px -4px 6px rgba(0, 0, 0, 0.1); /* Subtle footer shadow */
}

#map {
    height: 300px;
    margin-top: 15px;
    border: 2px solid #ddd;
    border-radius: 5px;
}

.form-group label {
    font-weight: 600;
    font-size: 1.1rem;
}

select.form-control, input.form-control {
    font-size: 1rem;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

select.form-control:focus, input.form-control:focus {
    border-color: #007bff;
    box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5); /* Focus effect for better UX */
}

/* Custom Hover Effects */
a:hover, .btn:hover {
    opacity: 0.8;
    transform: translateY(-2px); /* Subtle hover effect */
    transition: transform 0.2s ease, opacity 0.2s ease;
}

</style>

</head>
<body>

<header>
    <h1>Keranjang Belanja</h1>
    <a href="dashboard_pelanggan.php" class="btn btn-info">Beranda</a>
</header>
<main>
<div class="container">
    <?php if (!empty($_SESSION['keranjang'])): ?>
        <h3>Produk Anda</h3>
        <table class="table table-bordered">
    <thead>
        <tr>
            <th>Gambar</th>
            <th>Nama Produk</th>
            <th>Jumlah</th>
            <th>Harga Satuan</th>
            <th>Diskon (%)</th>
            <th>Subtotal Item</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_harga = 0;
        foreach ($_SESSION['keranjang'] as $item):
            $harga_produk = $item['harga'];
            $harga_diskon = $item['harga_diskon'] > 0 ? $item['harga_diskon'] : $harga_produk;
            $subtotal = $item['quantity'] * $harga_diskon;
            $total_harga += $subtotal;

            $persentase_diskon = 0;
            if ($harga_produk > 0 && $harga_diskon < $harga_produk) {
                $persentase_diskon = round((($harga_produk - $harga_diskon) / $harga_produk) * 100);
            }
        ?>
            <tr>
                <td><img src="gambar_produk/<?= htmlspecialchars($item['gambar']) ?>" alt="Product"></td>
                <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                <td><?= htmlspecialchars($item['quantity']) ?></td>
                <td>
                    <span style="text-decoration: <?= $harga_diskon < $harga_produk ? 'line-through' : 'none'; ?>;">Rp <?= number_format($harga_produk, 0, ',', '.') ?></span> 
                    <br> 
                    <strong>Rp <?= number_format($harga_diskon, 0, ',', '.') ?></strong>
                </td>
                <td><?= $persentase_diskon ?>%</td>
                <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                <td>
                    <a href="?more=<?= $item['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i></a>
                    <a href="?less=<?= $item['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-minus"></i></a>
                    <a href="?remove=<?= $item['id'] ?>" class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5">Total Harga Barang:</td>
            <td>Rp <?= number_format($total_harga, 0, ',', '.') ?></td>
            <td></td>
        </tr>
        <tr class="total-row">
            <td colspan="5">Ongkos Kirim:</td>
            <td id="ongkir-display">Rp 0</td>
            <td></td>
        </tr>
        <tr class="total-row">
            <td colspan="5">Total Harga Keseluruhan:</td>
            <td id="total-harga-display">Rp <?= number_format($total_harga, 0, ',', '.') ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>
<div class="summary">
    <h4>Ringkasan Pesanan</h4>
    <h4>Total Harga: <span id="final-total">Rp <?= number_format($total_harga, 0, ',', '.') ?></span></h4>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="alamat">Alamat Pengiriman</label>
            <textarea name="alamat" id="alamat" class="form-control" required></textarea>
            <div id="map"></div>
        </div>
        <div class="form-group">
            <label for="jasa_pengiriman">Jasa Pengiriman</label>
            <select name="jasa_pengiriman" id="jasa-pengiriman" class="form-control" required>
                <option value="" data-ongkir="0">Pilih Jasa Pengiriman</option>
                <option value="JNE" data-ongkir="15000">JNE - Rp 15,000</option>
                <option value="Tiki" data-ongkir="14000">Tiki - Rp 14,000</option>
                <option value="Pos" data-ongkir="13000">Pos Indonesia - Rp 13,000</option>
                <option value="SiCepat" data-ongkir="12000">SiCepat - Rp 12,000</option>
                <option value="Gojek" data-ongkir="10000">Gojek - Rp 10,000</option>
                <option value="Grab" data-ongkir="10000">Grab - Rp 10,000</option>
                <option value="Lion Parcel" data-ongkir="16000">Lion Parcel - Rp 16,000</option>
                <option value="J&T Express" data-ongkir="15000">J&T Express - Rp 15,000</option>
                <option value="Ninja Xpress" data-ongkir="17000">Ninja Xpress - Rp 17,000</option>
                <option value="Wahana" data-ongkir="11000">Wahana - Rp 11,000</option>
                <option value="TIKI" data-ongkir="14000">TIKI - Rp 14,000</option>
                <option value="ECO Express" data-ongkir="12000">ECO Express - Rp 12,000</option>
                <option value="Anteraja" data-ongkir="13000">Anteraja - Rp 13,000</option>
            </select>
        </div>
        <div class="form-group">
            <label for="metode_bayar">Metode Pembayaran</label>
            <select name="metode_bayar" class="form-control" required>
                <option value="Transfer">Transfer Bank</option>
                <option value="COD">Cash on Delivery (COD)</option>
                <option value="OVO">OVO</option>
                <option value="GoPay">GoPay</option>
                <option value="DANA">DANA</option>
                <option value="CreditCard">Credit/Debit Card</option>
                <option value="Indomaret">Indomaret</option>
                <option value="Alfamart">Alfamart</option>
            </select>
        </div>
        <div class="form-group">
            <label for="bukti_pembayaran">Bukti Pembayaran</label>
            <input type="file" name="bukti_pembayaran" class="form-control" required>
        </div>
        <button type="submit" name="checkout" class="btn btn-primary">Checkout</button>
    </form>
</div>
    <?php else: ?>
        <p>Keranjang Anda kosong!</p>
    <?php endif; ?>
</div>
</main>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    $(document).ready(function() {
        let map = L.map('map').setView([-6.200000, 106.816666], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker;

        map.on('click', function(e) {
            let latlng = e.latlng;

            if (marker) {
                map.removeLayer(marker);
            }

            marker = L.marker([latlng.lat, latlng.lng]).addTo(map);

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latlng.lat}&lon=${latlng.lng}`)
                .then(response => response.json())
                .then(data => {
                    $('#alamat').val(data.display_name || '');
                });
        });

        $('#jasa-pengiriman').change(function() {
            const ongkir = parseInt($(this).find(':selected').data('ongkir')) || 0;
            const totalHargaBarang = <?= $total_harga ?>;

            $('#ongkir-display').text('Rp ' + ongkir.toLocaleString('id-ID'));
            const totalKeseluruhan = totalHargaBarang + ongkir;
            $('#total-harga-display').text('Rp ' + totalKeseluruhan.toLocaleString('id-ID'));
            $('#final-total').text('Rp ' + totalKeseluruhan.toLocaleString('id-ID'));
        });
    });
</script>
</body>
</html>
