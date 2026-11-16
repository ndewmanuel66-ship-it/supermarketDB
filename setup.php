<?php
/**
 * Setup & Database Migration Wizard
 * Digunakan untuk membuat database, mengeksekusi tabel, dan mengisi seed data.
 */

$message = '';
$status = '';
$configPath = __DIR__ . '/config/database.php';
require_once $configPath;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'setup_mysql') {
        try {
            $host = $_POST['db_host'] ?? '127.0.0.1';
            $port = $_POST['db_port'] ?? '3306';
            $user = $_POST['db_user'] ?? 'root';
            $pass = $_POST['db_pass'] ?? '';
            $dbname = $_POST['db_name'] ?? 'supermarket_db';

            // Koneksi tanpa nama database terlebih dahulu
            $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Buat Database jika belum ada
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $pdo->exec("USE `{$dbname}`;");

            // Baca file database.sql
            $sql = file_get_contents(__DIR__ . '/database.sql');
            
            // Eksekusi query SQL
            $pdo->exec($sql);

            $status = 'success';
            $message = "Database <strong>{$dbname}</strong> dan seluruh tabel berhasil dibuat beserta data awal!";
        } catch (Exception $e) {
            $status = 'danger';
            $message = "Terjadi kesalahan saat inisialisasi MySQL: " . $e->getMessage();
        }
    } elseif ($action === 'setup_sqlite') {
        try {
            $sqlitePath = __DIR__ . '/supermarket.sqlite';
            if (file_exists($sqlitePath)) {
                unlink($sqlitePath);
            }
            $pdo = new PDO("sqlite:" . $sqlitePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Schema kompatibel SQLite
            $schema = "
            CREATE TABLE IF NOT EXISTS kategori (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama_kategori TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS supplier (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama_supplier TEXT NOT NULL,
                kontak_person TEXT,
                no_telepon TEXT,
                alamat TEXT
            );

            CREATE TABLE IF NOT EXISTS pengguna (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama_pengguna TEXT NOT NULL,
                jabatan TEXT NOT NULL DEFAULT 'Kasir',
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS member (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama_member TEXT NOT NULL,
                no_telepon TEXT NOT NULL UNIQUE,
                poin INTEGER NOT NULL DEFAULT 0,
                tanggal_daftar DATE NOT NULL
            );

            CREATE TABLE IF NOT EXISTS promo (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nama_promo TEXT NOT NULL,
                jenis_diskon TEXT NOT NULL DEFAULT 'nominal',
                nilai_diskon REAL NOT NULL DEFAULT 0,
                tanggal_mulai DATE NOT NULL,
                tanggal_selesai DATE NOT NULL
            );

            CREATE TABLE IF NOT EXISTS produk (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                barcode TEXT NOT NULL UNIQUE,
                nama_produk TEXT NOT NULL,
                id_kategori INTEGER NOT NULL,
                harga_beli REAL NOT NULL DEFAULT 0,
                harga_jual REAL NOT NULL DEFAULT 0,
                stok INTEGER NOT NULL DEFAULT 0,
                satuan TEXT NOT NULL DEFAULT 'Pcs',
                FOREIGN KEY (id_kategori) REFERENCES kategori(id)
            );

            CREATE TABLE IF NOT EXISTS transaksi (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nomor_struk TEXT NOT NULL UNIQUE,
                id_kasir INTEGER NOT NULL,
                id_member INTEGER,
                id_promo INTEGER,
                subtotal REAL NOT NULL DEFAULT 0,
                total_diskon REAL NOT NULL DEFAULT 0,
                poin_dipakai INTEGER NOT NULL DEFAULT 0,
                poin_didapat INTEGER NOT NULL DEFAULT 0,
                total_akhir REAL NOT NULL DEFAULT 0,
                metode_bayar TEXT NOT NULL DEFAULT 'TUNAI',
                jumlah_bayar REAL NOT NULL DEFAULT 0,
                kembalian REAL NOT NULL DEFAULT 0,
                status TEXT NOT NULL DEFAULT 'SELESAI',
                waktu_transaksi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_kasir) REFERENCES pengguna(id),
                FOREIGN KEY (id_member) REFERENCES member(id),
                FOREIGN KEY (id_promo) REFERENCES promo(id)
            );

            CREATE TABLE IF NOT EXISTS detail_transaksi (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                id_transaksi INTEGER NOT NULL,
                id_produk INTEGER NOT NULL,
                harga_satuan REAL NOT NULL DEFAULT 0,
                jumlah INTEGER NOT NULL DEFAULT 1,
                subtotal REAL NOT NULL DEFAULT 0,
                FOREIGN KEY (id_transaksi) REFERENCES transaksi(id),
                FOREIGN KEY (id_produk) REFERENCES produk(id)
            );

            -- Sample Data
            INSERT INTO kategori (id, nama_kategori) VALUES
            (1, 'Makanan & Snack'),
            (2, 'Minuman'),
            (3, 'Kebutuhan Dapur & Sembako'),
            (4, 'Perawatan Tubuh & Kebersihan'),
            (5, 'Alat Tulis & Rumah Tangga');

            INSERT INTO supplier (id, nama_supplier, kontak_person, no_telepon, alamat) VALUES
            (1, 'PT Indofood Sukses Makmur', 'Budi Santoso', '081234567890', 'Jakarta'),
            (2, 'PT Unilever Indonesia Tbk', 'Siti Rahma', '081398765432', 'Tangerang');

            INSERT INTO pengguna (id, nama_pengguna, jabatan, username, password) VALUES
            (1, 'Super Admin', 'Admin', 'admin', '$2y$10$tZ2zV9G6yq7hFv1Y7N5i6.yB.3gQ5tD8w6uR6jB4H8c4wXpZ0bSra'),
            (2, 'Rina Kasir 1', 'Kasir', 'kasir1', '$2y$10$tZ2zV9G6yq7hFv1Y7N5i6.yB.3gQ5tD8w6uR6jB4H8c4wXpZ0bSra');

            INSERT INTO member (id, nama_member, no_telepon, poin, tanggal_daftar) VALUES
            (1, 'Ahmad Fajar', '081211112222', 150, '2026-01-10'),
            (2, 'Dewi Lestari', '081333334444', 320, '2026-02-15');

            INSERT INTO promo (id, nama_promo, jenis_diskon, nilai_diskon, tanggal_mulai, tanggal_selesai) VALUES
            (1, 'Promo Weekend Hemat 10%', 'persen', 10, '2026-01-01', '2026-12-31'),
            (2, 'Potongan Rp 5.000 Spesial Belanja', 'nominal', 5000, '2026-01-01', '2026-12-31');

            INSERT INTO produk (id, barcode, nama_produk, id_kategori, harga_beli, harga_jual, stok, satuan) VALUES
            (1, '899990900101', 'Indomie Goreng Original 85g', 1, 2800, 3500, 150, 'Bungkus'),
            (2, '899990900102', 'Indomie Kuah Ayam Bawang 69g', 1, 2700, 3300, 120, 'Bungkus'),
            (3, '899275321011', 'Aqua Air Mineral Botol 600ml', 2, 2800, 4000, 200, 'Botol'),
            (4, '899275321028', 'Teh Pucuk Harum 350ml', 2, 2900, 4000, 100, 'Botol'),
            (5, '899600130112', 'Minyak Goreng Sania Pouch 2L', 3, 31000, 36500, 45, 'Pouch'),
            (6, '899886610023', 'Beras Ramos Super Premium 5kg', 3, 64000, 74000, 30, 'Karung'),
            (7, '899300511019', 'Sabun Lifebuoy Total 10 85g', 4, 3800, 5000, 80, 'Batang'),
            (8, '899100210088', 'Pepsodent Pencegah Gigi Berlubang 190g', 4, 12500, 16000, 50, 'Pcs'),
            (9, '899293311201', 'Deterjen Rinso Molto Ultra 770g', 4, 21000, 26500, 40, 'Pcs'),
            (10, '899800100911', 'Kopi Kapal Api Spesial Mix 10x24g', 2, 11500, 14500, 60, 'Renteng');
            ";

            $pdo->exec($schema);
            $status = 'success';
            $message = "Database SQLite Portable (<code>supermarket.sqlite</code>) berhasil dibuat beserta seluruh data awal!";
        } catch (Exception $e) {
            $status = 'danger';
            $message = "Terjadi kesalahan saat inisialisasi SQLite: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - Sistem Supermarket POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white p-4 rounded-top-4">
                    <h3 class="mb-1"><i class="bi bi-database-fill-gear me-2"></i> Inisialisasi Database Supermarket</h3>
                    <p class="mb-0 text-white-50">Setup otomatis skema database & data sampel produk, kategori, dan promo.</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $status ?> alert-dismissible fade show" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i> <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php if ($status === 'success'): ?>
                            <div class="text-center my-3">
                                <a href="index.php" class="btn btn-success btn-lg px-4"><i class="bi bi-speedometer2 me-2"></i> Buka Dashboard Supermarket</a>
                                <a href="kasir.php" class="btn btn-primary btn-lg px-4 ms-2"><i class="bi bi-cart-check-fill me-2"></i> Buka Kasir (POS)</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="row g-4 mt-2">
                        <!-- Opsi 1: Setup MySQL Langsung -->
                        <div class="col-md-7">
                            <div class="border rounded-3 p-3 bg-white h-100">
                                <h5 class="fw-bold text-primary"><i class="bi bi-server me-2"></i>Inisialisasi ke MySQL Server</h5>
                                <p class="text-muted small">Mengeksekusi skrip <code>database.sql</code> langsung ke server MySQL Anda (XAMPP / Laragon / Docker).</p>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="setup_mysql">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">Host MySQL</label>
                                        <input type="text" name="db_host" class="form-control form-control-sm" value="127.0.0.1" required>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">Port</label>
                                            <input type="text" name="db_port" class="form-control form-control-sm" value="3306" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">Nama Database</label>
                                            <input type="text" name="db_name" class="form-control form-control-sm" value="supermarket_db" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">Username</label>
                                            <input type="text" name="db_user" class="form-control form-control-sm" value="root" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">Password</label>
                                            <input type="password" name="db_pass" class="form-control form-control-sm" placeholder="Kosong jika XAMPP">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Buat Database MySQL & Import Data
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Opsi 2: SQLite Standalone Portable -->
                        <div class="col-md-5">
                            <div class="border rounded-3 p-3 bg-white h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="fw-bold text-secondary"><i class="bi bi-filetype-sql me-2"></i>SQLite Portable Mode</h5>
                                    <p class="text-muted small">Jika server MySQL Anda belum aktif atau ingin langsung mencoba aplikasi secara instan tanpa konfigurasi server database.</p>
                                    <div class="alert alert-light border small text-muted">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i> Cocok untuk demo cepat atau pengetesan via built-in server PHP (<code>php -S</code>).
                                    </div>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="action" value="setup_sqlite">
                                    <button type="submit" class="btn btn-outline-secondary w-100">
                                        <i class="bi bi-play-circle-fill me-1"></i> Buat Database SQLite Standalone
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-center text-muted small">
                        Password akun kasir default: <code>admin</code> / <code>admin123</code> &bull; <code>kasir1</code> / <code>admin123</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
