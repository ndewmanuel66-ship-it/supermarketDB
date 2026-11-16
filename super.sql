-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 16, 2026 at 05:57 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `super`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `id_produk` int NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL DEFAULT '0.00',
  `jumlah` int NOT NULL DEFAULT '1',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `id_transaksi`, `id_produk`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, '3500.00', 5, '17500.00'),
(2, 1, 3, '4000.00', 3, '12000.00'),
(3, 1, 9, '25000.00', 1, '25000.00');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'Makanan & Snack'),
(2, 'Minuman'),
(3, 'Kebutuhan Dapur & Sembako'),
(4, 'Perawatan Tubuh & Kebersihan'),
(5, 'Alat Tulis & Rumah Tangga');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `id` int NOT NULL,
  `nama_member` varchar(100) NOT NULL,
  `no_telepon` varchar(25) NOT NULL,
  `poin` int NOT NULL DEFAULT '0',
  `tanggal_daftar` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`id`, `nama_member`, `no_telepon`, `poin`, `tanggal_daftar`) VALUES
(1, 'Ahmad Fajar', '081211112222', 150, '2026-01-10'),
(2, 'Dewi Lestari', '081333334444', 320, '2026-02-15'),
(3, 'Rudi Hermawan', '085777778888', 50, '2026-03-01');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int NOT NULL,
  `nama_pengguna` varchar(100) NOT NULL,
  `jabatan` enum('Admin','Kasir','Manager') NOT NULL DEFAULT 'Kasir',
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama_pengguna`, `jabatan`, `username`, `password`) VALUES
(1, 'Super Admin', 'Admin', 'admin', '$2y$10$tZ2zV9G6yq7hFv1Y7N5i6.yB.3gQ5tD8w6uR6jB4H8c4wXpZ0bSra'),
(2, 'Rina Kasir 1', 'Kasir', 'kasir1', '$2y$10$tZ2zV9G6yq7hFv1Y7N5i6.yB.3gQ5tD8w6uR6jB4H8c4wXpZ0bSra');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `id_kategori` int NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL DEFAULT '0.00',
  `harga_jual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stok` int NOT NULL DEFAULT '0',
  `satuan` varchar(30) NOT NULL DEFAULT 'Pcs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `barcode`, `nama_produk`, `id_kategori`, `harga_beli`, `harga_jual`, `stok`, `satuan`) VALUES
(1, '899990900101', 'Indomie Goreng Original 85g', 1, '2800.00', '3500.00', 150, 'Bungkus'),
(2, '899990900102', 'Indomie Kuah Ayam Bawang 69g', 1, '2700.00', '3300.00', 120, 'Bungkus'),
(3, '899275321011', 'Aqua Air Mineral Botol 600ml', 2, '2800.00', '4000.00', 200, 'Botol'),
(4, '899275321028', 'Teh Pucuk Harum 350ml', 2, '2900.00', '4000.00', 100, 'Botol'),
(5, '899600130112', 'Minyak Goreng Sania Pouch 2L', 3, '31000.00', '36500.00', 45, 'Pouch'),
(6, '899886610023', 'Beras Ramos Super Premium 5kg', 3, '64000.00', '74000.00', 30, 'Karung'),
(7, '899300511019', 'Sabun Lifebuoy Total 10 85g', 4, '3800.00', '5000.00', 80, 'Batang'),
(8, '899100210088', 'Pepsodent Pencegah Gigi Berlubang 190g', 4, '12500.00', '16000.00', 50, 'Pcs'),
(9, '899293311201', 'Deterjen Rinso Molto Ultra 770g', 4, '21000.00', '26500.00', 40, 'Pcs'),
(10, '899800100911', 'Kopi Kapal Api Spesial Mix 10x24g', 2, '11500.00', '14500.00', 60, 'Renteng');

-- --------------------------------------------------------

--
-- Table structure for table `promo`
--

CREATE TABLE `promo` (
  `id` int NOT NULL,
  `nama_promo` varchar(100) NOT NULL,
  `jenis_diskon` enum('persen','nominal') NOT NULL DEFAULT 'nominal',
  `nilai_diskon` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `promo`
--

INSERT INTO `promo` (`id`, `nama_promo`, `jenis_diskon`, `nilai_diskon`, `tanggal_mulai`, `tanggal_selesai`) VALUES
(1, 'Promo Weekend Hemat 10%', 'persen', '10.00', '2026-01-01', '2026-12-31'),
(2, 'Potongan Rp 5.000 Spesial Belanja', 'nominal', '5000.00', '2026-01-01', '2026-12-31'),
(3, 'Diskon Kilat 15%', 'persen', '15.00', '2026-01-01', '2026-12-31');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int NOT NULL,
  `nama_supplier` varchar(150) NOT NULL,
  `kontak_person` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(25) DEFAULT NULL,
  `alamat` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `nama_supplier`, `kontak_person`, `no_telepon`, `alamat`) VALUES
(1, 'PT Indofood Sukses Makmur', 'Budi Santoso', '081234567890', 'Kawasan Industri Pulogadung, Jakarta'),
(2, 'PT Unilever Indonesia Tbk', 'Siti Rahma', '081398765432', 'BSD City, Tangerang'),
(3, 'PT Mayora Indah Tbk', 'Hendra Wijaya', '081512344321', 'Daan Mogot, Jakarta Barat');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `nomor_struk` varchar(50) NOT NULL,
  `id_kasir` int NOT NULL,
  `id_member` int DEFAULT NULL,
  `id_promo` int DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_diskon` decimal(12,2) NOT NULL DEFAULT '0.00',
  `poin_dipakai` int NOT NULL DEFAULT '0',
  `poin_didapat` int NOT NULL DEFAULT '0',
  `total_akhir` decimal(12,2) NOT NULL DEFAULT '0.00',
  `metode_bayar` enum('TUNAI','QRIS','DEBIT','TRANSFER') NOT NULL DEFAULT 'TUNAI',
  `jumlah_bayar` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('SELESAI','PENDING','BATAL') NOT NULL DEFAULT 'SELESAI',
  `waktu_transaksi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `nomor_struk`, `id_kasir`, `id_member`, `id_promo`, `subtotal`, `total_diskon`, `poin_dipakai`, `poin_didapat`, `total_akhir`, `metode_bayar`, `jumlah_bayar`, `kembalian`, `status`, `waktu_transaksi`) VALUES
(1, 'TRX-20261116-0001', 1, 1, 1, '54500.00', '5450.00', 0, 5, '49050.00', 'TUNAI', '50000.00', '950.00', 'SELESAI', '2026-11-16 09:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detail_transaksi` (`id_transaksi`),
  ADD KEY `fk_detail_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_telepon` (`no_telepon`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `fk_produk_kategori` (`id_kategori`);

--
-- Indexes for table `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_struk` (`nomor_struk`),
  ADD KEY `fk_transaksi_kasir` (`id_kasir`),
  ADD KEY `fk_transaksi_member` (`id_member`),
  ADD KEY `fk_transaksi_promo` (`id_promo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `promo`
--
ALTER TABLE `promo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `pengguna` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_promo` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
