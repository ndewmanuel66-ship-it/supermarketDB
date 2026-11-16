<?php
// init_sqlite.php - generate local supermarket.sqlite
$sqlitePath = __DIR__ . '/supermarket.sqlite';
$pdo = new PDO("sqlite:" . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
";

$pdo->exec($schema);

// Isi Sample Data jika kosong
$stmt = $pdo->query("SELECT COUNT(*) FROM kategori");
if ($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO kategori (id, nama_kategori) VALUES
        (1, 'Makanan & Snack'),
        (2, 'Minuman'),
        (3, 'Kebutuhan Dapur & Sembako'),
        (4, 'Perawatan Tubuh & Kebersihan'),
        (5, 'Alat Tulis & Rumah Tangga')");

    $pdo->exec("INSERT INTO supplier (id, nama_supplier, kontak_person, no_telepon, alamat) VALUES
        (1, 'PT Indofood Sukses Makmur', 'Budi Santoso', '081234567890', 'Jakarta'),
        (2, 'PT Unilever Indonesia Tbk', 'Siti Rahma', '081398765432', 'Tangerang')");

    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmtUser = $pdo->prepare("INSERT INTO pengguna (id, nama_pengguna, jabatan, username, password) VALUES (?, ?, ?, ?, ?)");
    $stmtUser->execute([1, 'Super Admin', 'Admin', 'admin', $hash]);
    $stmtUser->execute([2, 'Rina Kasir 1', 'Kasir', 'kasir1', $hash]);

    $pdo->exec("INSERT INTO member (id, nama_member, no_telepon, poin, tanggal_daftar) VALUES
        (1, 'Ahmad Fajar', '081211112222', 150, '2026-01-10'),
        (2, 'Dewi Lestari', '081333334444', 320, '2026-02-15')");

    $pdo->exec("INSERT INTO promo (id, nama_promo, jenis_diskon, nilai_diskon, tanggal_mulai, tanggal_selesai) VALUES
        (1, 'Promo Weekend Hemat 10%', 'persen', 10, '2026-01-01', '2026-12-31'),
        (2, 'Potongan Rp 5.000 Spesial Belanja', 'nominal', 5000, '2026-01-01', '2026-12-31')");

    $pdo->exec("INSERT INTO produk (id, barcode, nama_produk, id_kategori, harga_beli, harga_jual, stok, satuan) VALUES
        (1, '899990900101', 'Indomie Goreng Original 85g', 1, 2800, 3500, 150, 'Bungkus'),
        (2, '899990900102', 'Indomie Kuah Ayam Bawang 69g', 1, 2700, 3300, 120, 'Bungkus'),
        (3, '899275321011', 'Aqua Air Mineral Botol 600ml', 2, 2800, 4000, 200, 'Botol'),
        (4, '899275321028', 'Teh Pucuk Harum 350ml', 2, 2900, 4000, 100, 'Botol'),
        (5, '899600130112', 'Minyak Goreng Sania Pouch 2L', 3, 31000, 36500, 45, 'Pouch'),
        (6, '899886610023', 'Beras Ramos Super Premium 5kg', 3, 64000, 74000, 30, 'Karung'),
        (7, '899300511019', 'Sabun Lifebuoy Total 10 85g', 4, 3800, 5000, 80, 'Batang'),
        (8, '899100210088', 'Pepsodent Pencegah Gigi Berlubang 190g', 4, 12500, 16000, 50, 'Pcs'),
        (9, '899293311201', 'Deterjen Rinso Molto Ultra 770g', 4, 21000, 26500, 40, 'Pcs'),
        (10, '899800100911', 'Kopi Kapal Api Spesial Mix 10x24g', 2, 11500, 14500, 60, 'Renteng')");
}

echo "Database SQLite siap dan valid!\n";
