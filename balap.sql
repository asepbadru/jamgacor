-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Jan 2025 pada 14.45
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `balap`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_stok_after_transaction_procedure` (IN `transaksi_id` INT)   BEGIN
    DECLARE produk_id INT;
    DECLARE jumlah INT;
    DECLARE stok_tersedia INT;
    DECLARE done INT DEFAULT FALSE;
    
    -- Deklarasikan cursor untuk mengambil produk_id dan jumlah dari transaksi_detail
    DECLARE cur CURSOR FOR 
        SELECT id_produk, jumlah 
        FROM transaksi_detail 
        WHERE id_transaksi = transaksi_id;

    -- Handler untuk penutupan cursor
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO produk_id, jumlah;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Periksa apakah stok mencukupi
        SELECT stok_tersedia INTO stok_tersedia
        FROM produk
        WHERE id = produk_id;

        IF stok_tersedia >= jumlah THEN
            -- Update stok produk
            UPDATE produk
            SET stok_tersedia = stok_tersedia - jumlah
            WHERE id = produk_id;
        ELSE
            -- Jika stok tidak mencukupi, batalkan transaksi dan set status menjadi 'canceled'
            UPDATE transaksi
            SET status = 'canceled'
            WHERE id = transaksi_id;
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok tidak mencukupi';
        END IF;
    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama`, `gambar`, `created_at`, `updated_at`) VALUES
(1, 'Laptop', 'logoLaptop.png', '2024-12-22 11:48:56', '2024-12-22 11:48:56'),
(2, 'Komputer', 'logoPc.png', '2024-12-22 11:48:56', '2024-12-22 11:48:56'),
(3, 'Jaringan', 'Logorouter.jpg', '2024-12-22 11:48:56', '2024-12-22 11:48:56'),
(4, 'Aksesoris', 'aksesoris.png', '2024-12-22 11:48:56', '2024-12-22 11:48:56'),
(5, 'Komponen Komputer', 'Logokomponen.png', '2024-12-22 11:48:56', '2024-12-22 11:48:56'),
(6, 'Monitor', 'logoMonitor.jpg', '2024-12-22 11:48:56', '2024-12-22 11:48:56'),
(7, 'Printer', 'logo printer.png', '2024-12-22 11:48:56', '2024-12-22 11:48:56'),
(8, 'Proyektor', 'proyektorlogo.png', '2024-12-22 11:48:56', '2024-12-22 11:48:56'),
(9, 'Lainnya', 'lainnya.png', '2024-12-22 11:48:56', '2024-12-22 11:48:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `keranjang`
--

INSERT INTO `keranjang` (`id`, `user_id`, `produk_id`, `quantity`) VALUES
(332, 18, 2, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `deskripsi_produk` text NOT NULL,
  `gambar_produk` varchar(255) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `stok_tersedia` int(11) NOT NULL DEFAULT 0,
  `harga_diskon` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `nama_produk`, `harga`, `deskripsi_produk`, `gambar_produk`, `id_kategori`, `stok_tersedia`, `harga_diskon`) VALUES
(1, 'Patek Philippe Pocket Watch', 12000000.00, 'Jam saku mewah dari Patek Philippe dengan desain klasik dan sangat dihargai oleh kolektor.', 'saku1.jpg', 7, 10, NULL),
(2, 'Tissot Savonnette', 500000.00, 'Jam saku Swiss dari Tissot, desain elegan dan kualitas tinggi.', 'saku2.jpg', 7, 14, NULL),
(3, 'Hamilton Pocket Watch', 600000.00, 'Jam saku dengan desain vintage dan kualitas mekanikal tinggi dari Hamilton.', 'saku3.jpg', 7, 19, NULL),
(4, 'Longines Pocket Watch', 900000.00, 'Jam saku mewah dengan mekanisme otomatis dan desain elegan dari Longines.', 'saku4.jpg', 7, 8, NULL),
(5, 'Breguet Classique Pocket Watch', 25000000.00, 'Jam saku premium dengan desain rumit dan kualitas mekanikal terbaik dari Breguet.', 'saku5.jpg', 7, 5, NULL),
(6, 'Hermès Clocks', 15000000.00, 'Jam dekoratif mewah dari Hermès dengan desain elegan yang cocok untuk dekorasi rumah.', 'dekoratif1.jpg', 8, 7, NULL),
(7, 'Howard Miller Clocks', 400000.00, 'Jam dekoratif dari Howard Miller, dengan desain klasik dan tampilan mewah.', 'dekoratif2.jpg', 8, 12, NULL),
(8, 'Lalique Crystal Clock', 1200000.00, 'Jam dekoratif berbahan kristal dari Lalique dengan desain artistik.', 'dekoratif3.jpg', 8, 6, NULL),
(9, 'Jaeger-LeCoultre Atmos Clock', 4500000.00, 'Jam dekoratif mewah tanpa baterai dengan desain yang sangat rumit dan canggih dari Jaeger-LeCoultre.', 'dekoratif4.jpg', 8, 4, NULL),
(10, 'Kieninger Clock', 2500000.00, 'Jam dekoratif dengan desain pendulum elegan, cocok untuk koleksi atau hadiah mewah.', 'dekoratif5.jpg', 8, 10, NULL),
(11, 'Rolex Submariner', 9000000.00, 'Jam tangan legendaris dari Rolex dengan ketahanan dan desain sporty, cocok untuk penyelam.', 'tangan1.jpg', 9, 15, NULL),
(12, 'Omega Speedmaster', 8000000.00, 'Jam tangan yang digunakan oleh NASA dalam misi luar angkasa dengan ketepatan tinggi.', 'tangan2.jpg', 9, 10, NULL),
(13, 'Patek Philippe Calatrava', 25000000.00, 'Jam tangan elegan dengan desain klasik dari Patek Philippe.', 'tangan3.jpg', 9, 5, NULL),
(14, 'Tag Heuer Monaco', 6500000.00, 'Jam tangan ikonik dari Tag Heuer dengan desain persegi yang unik dan stylish.', 'tangan4.jpg', 9, 12, NULL),
(15, 'Casio G-Shock', 120000.00, 'Jam tangan tahan banting dari Casio, ideal untuk aktivitas outdoor dan olahraga ekstrem.', 'tangan5.jpg', 9, 20, NULL),
(16, 'Seiko Alarm Clock', 300000.00, 'Jam meja dari Seiko dengan suara alarm keras dan desain minimalis.', 'meja1.jpg', 10, 30, NULL),
(17, 'Bulova Desk Clock', 900000.00, 'Jam meja elegan dari Bulova dengan desain yang cocok untuk kantor atau meja kerja.', 'meja2.jpg', 10, 18, NULL),
(18, 'Thompson Quartz Desk Clock', 500000.00, 'Jam meja dengan desain quartz klasik yang cocok untuk dekorasi rumah atau kantor.', 'meja3.jpg', 10, 25, NULL),
(19, 'Sharp Digital Desk Clock', 150000.00, 'Jam meja digital dari Sharp dengan tampilan waktu yang mudah dibaca dan fitur alarm.', 'meja4.jpg', 10, 40, NULL),
(20, 'Hermle Desk Clock', 2500000.00, 'Jam meja mewah dari Hermle, dengan desain modern dan kualitas tinggi.', 'meja5.jpg', 10, 10, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `alamat` text NOT NULL,
  `jasa_pengiriman` varchar(100) DEFAULT NULL,
  `ongkir` decimal(15,2) DEFAULT NULL,
  `metode_bayar` varchar(50) DEFAULT NULL,
  `status_pesanan` enum('Menunggu Konfirmasi','Dikemas','Dikirim','Selesai','Pesanan Dibatalkan') NOT NULL,
  `jumlah_item` int(11) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL,
  `diskon` decimal(15,2) DEFAULT 0.00,
  `total` decimal(15,2) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `grand_total` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `user_id`, `alamat`, `jasa_pengiriman`, `ongkir`, `metode_bayar`, `status_pesanan`, `jumlah_item`, `subtotal`, `diskon`, `total`, `tanggal`, `bukti_pembayaran`, `grand_total`) VALUES
(1, 32, 'Cijantung, Purwakarta, Jawa Barat, Jawa, 41113, Indonesia', 'Tiki', 14000.00, '0', 'Menunggu Konfirmasi', 2, 36000000.00, 1800000.00, 34200000.00, '2025-01-07 11:55:07', 'uploads/677d161b07d9c_anak.jpg', 34214000.00),
(2, 1, 'RW 02, Gambir, Jakarta Pusat, Daerah Khusus Ibukota Jakarta, Jawa, 10110, Indonesia', 'ECO Express', 12000.00, '0', 'Menunggu Konfirmasi', 1, 500000.00, 0.00, 500000.00, '2025-01-07 13:18:40', 'uploads/677d29b034ad6_anak6.jpg', 512000.00),
(3, 1, 'Jalan Kemandoran I, RW 03, Grogol Utara, Kebayoran Lama, Jakarta Selatan, Daerah Khusus Ibukota Jakarta, Jawa, 12210, Indonesia', 'TIKI', 0.00, '0', 'Menunggu Konfirmasi', 1, 600000.00, 0.00, 600000.00, '2025-01-07 13:41:41', 'uploads/677d2f1564c57_anak2.jpg', 600000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `jumlah_unit` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `harga_diskon` decimal(15,2) DEFAULT NULL,
  `total_item` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id`, `transaksi_id`, `produk_id`, `harga_satuan`, `jumlah_unit`, `subtotal`, `harga_diskon`, `total_item`) VALUES
(1, 1, 2, 18000000.00, 2, 36000000.00, 17100000.00, 34200000.00),
(2, 2, 2, 500000.00, 1, 500000.00, NULL, 0.00),
(3, 3, 3, 600000.00, 1, 600000.00, NULL, 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone_number`, `address`, `profile_picture`, `created_at`) VALUES
(1, 'ausa', 'ausa@gmail.com', '$2y$10$Qw4/fp884ECnkfoyl9OcxeLVx7fpR6AtiPl7Sy9E2XuIH0DXi0HrW', 'customer', '087656543213', 'asdfghjk', NULL, '2025-01-07 11:57:54'),
(2, 'Admin ', 'admin@gmail.com', '12345', 'admin', '08123456789', 'pwt', NULL, '2025-01-07 12:22:30');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=512;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
