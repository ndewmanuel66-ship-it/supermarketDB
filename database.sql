-- ======================================================
-- DATABASE SCHEMA: SISTEM SUPERMARKET (POS & INVENTORY)
-- Engine: MySQL / MariaDB (InnoDB, UTF-8)
-- ======================================================

CREATE DATABASE IF NOT EXISTS `supermarket_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `supermarket_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabel Kategori
DROP TABLE IF EXISTS `kategori`;
CREATE TABLE `kategori` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_kategori` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Supplier
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_supplier` VARCHAR(150) NOT NULL,
    `kontak_person` VARCHAR(100) DEFAULT NULL,
    `no_telepon` VARCHAR(25) DEFAULT NULL,
    `alamat` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Pengguna / Kasir
DROP TABLE IF EXISTS `pengguna`;
CREATE TABLE `pengguna` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_pengguna` VARCHAR(100) NOT NULL,
    `jabatan` ENUM('Admin', 'Kasir', 'Manager') NOT NULL DEFAULT 'Kasir',
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Member / Pelanggan
DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_member` VARCHAR(100) NOT NULL,
    `no_telepon` VARCHAR(25) NOT NULL UNIQUE,
    `poin` INT NOT NULL DEFAULT 0,
    `tanggal_daftar` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Promo
DROP TABLE IF EXISTS `promo`;
CREATE TABLE `promo` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_promo` VARCHAR(100) NOT NULL,
    `jenis_diskon` ENUM('persen', 'nominal') NOT NULL DEFAULT 'nominal',
    `nilai_diskon` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Produk
DROP TABLE IF EXISTS `produk`;
CREATE TABLE `produk` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `barcode` VARCHAR(50) NOT NULL UNIQUE,
    `nama_produk` VARCHAR(150) NOT NULL,
    `id_kategori` INT NOT NULL,
    `harga_beli` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `harga_jual` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `stok` INT NOT NULL DEFAULT 0,
    `satuan` VARCHAR(30) NOT NULL DEFAULT 'Pcs',
    CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) 
        REFERENCES `kategori` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabel Transaksi
DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nomor_struk` VARCHAR(50) NOT NULL UNIQUE,
    `id_kasir` INT NOT NULL,
    `id_member` INT DEFAULT NULL,
    `id_promo` INT DEFAULT NULL,
    `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_diskon` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `poin_dipakai` INT NOT NULL DEFAULT 0,
    `poin_didapat` INT NOT NULL DEFAULT 0,
    `total_akhir` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `metode_bayar` ENUM('TUNAI', 'QRIS', 'DEBIT', 'TRANSFER') NOT NULL DEFAULT 'TUNAI',
    `jumlah_bayar` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `kembalian` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('SELESAI', 'PENDING', 'BATAL') NOT NULL DEFAULT 'SELESAI',
    `waktu_transaksi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_transaksi_kasir` FOREIGN KEY (`id_kasir`) 
        REFERENCES `pengguna` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_transaksi_member` FOREIGN KEY (`id_member`) 
        REFERENCES `member` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_transaksi_promo` FOREIGN KEY (`id_promo`) 
        REFERENCES `promo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabel Detail Transaksi
DROP TABLE IF EXISTS `detail_transaksi`;
CREATE TABLE `detail_transaksi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_transaksi` INT NOT NULL,
    `id_produk` INT NOT NULL,
    `harga_satuan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `jumlah` INT NOT NULL DEFAULT 1,
    `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`id_transaksi`) 
        REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) 
        REFERENCES `produk` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ======================================================
-- DATA AWAL / SAMPLE DATA
-- ======================================================

-- Kategori
INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'Makanan & Snack'),
(2, 'Minuman'),
(3, 'Kebutuhan Dapur & Sembako'),
(4, 'Perawatan Tubuh & Kebersihan'),
(5, 'Alat Tulis & Rumah Tangga');

-- Supplier
INSERT INTO `supplier` (`id`, `nama_supplier`, `kontak_person`, `no_telepon`, `alamat`) VALUES
(1, 'PT Indofood Sukses Makmur', 'Budi Santoso', '081234567890', 'Kawasan Industri Pulogadung, Jakarta'),
(2, 'PT Unilever Indonesia Tbk', 'Siti Rahma', '081398765432', 'BSD City, Tangerang'),
(3, 'PT Mayora Indah Tbk', 'Hendra Wijaya', '081512344321', 'Daan Mogot, Jakarta Barat');

-- Pengguna / Kasir (Password default: 'admin123' di-hash dengan password_hash BCRYPT)
INSERT INTO `pengguna` (`id`, `nama_pengguna`, `jabatan`, `username`, `password`) VALUES
(1, 'Super Admin', 'Admin', 'admin', '$2y$10$tZ2zV9G6yq7hFv1Y7N5i6.yB.3gQ5tD8w6uR6jB4H8c4wXpZ0bSra'),
(2, 'Rina Kasir 1', 'Kasir', 'kasir1', '$2y$10$tZ2zV9G6yq7hFv1Y7N5i6.yB.3gQ5tD8w6uR6jB4H8c4wXpZ0bSra');

-- Member / Pelanggan
INSERT INTO `member` (`id`, `nama_member`, `no_telepon`, `poin`, `tanggal_daftar`) VALUES
(1, 'Ahmad Fajar', '081211112222', 150, '2026-01-10'),
(2, 'Dewi Lestari', '081333334444', 320, '2026-02-15'),
(3, 'Rudi Hermawan', '085777778888', 50, '2026-03-01');

-- Promo
INSERT INTO `promo` (`id`, `nama_promo`, `jenis_diskon`, `nilai_diskon`, `tanggal_mulai`, `tanggal_selesai`) VALUES
(1, 'Promo Weekend Hemat 10%', 'persen', 10.00, '2026-01-01', '2026-12-31'),
(2, 'Potongan Rp 5.000 Spesial Belanja', 'nominal', 5000.00, '2026-01-01', '2026-12-31'),
(3, 'Diskon Kilat 15%', 'persen', 15.00, '2026-01-01', '2026-12-31');

-- Produk
INSERT INTO `produk` (`id`, `barcode`, `nama_produk`, `id_kategori`, `harga_beli`, `harga_jual`, `stok`, `satuan`) VALUES
(1, '899990900101', 'Indomie Goreng Original 85g', 1, 2800.00, 3500.00, 150, 'Bungkus'),
(2, '899990900102', 'Indomie Kuah Ayam Bawang 69g', 1, 2700.00, 3300.00, 120, 'Bungkus'),
(3, '899275321011', 'Aqua Air Mineral Botol 600ml', 2, 2800.00, 4000.00, 200, 'Botol'),
(4, '899275321028', 'Teh Pucuk Harum 350ml', 2, 2900.00, 4000.00, 100, 'Botol'),
(5, '899600130112', 'Minyak Goreng Sania Pouch 2L', 3, 31000.00, 36500.00, 45, 'Pouch'),
(6, '899886610023', 'Beras Ramos Super Premium 5kg', 3, 64000.00, 74000.00, 30, 'Karung'),
(7, '899300511019', 'Sabun Lifebuoy Total 10 85g', 4, 3800.00, 5000.00, 80, 'Batang'),
(8, '899100210088', 'Pepsodent Pencegah Gigi Berlubang 190g', 4, 12500.00, 16000.00, 50, 'Pcs'),
(9, '899293311201', 'Deterjen Rinso Molto Ultra 770g', 4, 21000.00, 26500.00, 40, 'Pcs'),
(10, '899800100911', 'Kopi Kapal Api Spesial Mix 10x24g', 2, 11500.00, 14500.00, 60, 'Renteng');

-- Contoh Transaksi Awal
INSERT INTO `transaksi` (`id`, `nomor_struk`, `id_kasir`, `id_member`, `id_promo`, `subtotal`, `total_diskon`, `poin_dipakai`, `poin_didapat`, `total_akhir`, `metode_bayar`, `jumlah_bayar`, `kembalian`, `status`, `waktu_transaksi`) VALUES
(1, 'TRX-20261116-0001', 1, 1, 1, 54500.00, 5450.00, 0, 5, 49050.00, 'TUNAI', 50000.00, 950.00, 'SELESAI', '2026-11-16 09:30:00');

-- Detail Transaksi Awal
INSERT INTO `detail_transaksi` (`id`, `id_transaksi`, `id_produk`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 3500.00, 5, 17500.00),
(2, 1, 3, 4000.00, 3, 12000.00),
(3, 1, 9, 25000.00, 1, 25000.00);
