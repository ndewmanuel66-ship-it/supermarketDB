<?php
$pageTitle = "Dashboard Supermarket";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

// Metrik 1: Penjualan Hari Ini
$today = date('Y-m-d');
$stmtToday = $pdo->prepare("
    SELECT COUNT(*) as total_trx, COALESCE(SUM(total_akhir), 0) as omzet_hari_ini 
    FROM transaksi 
    WHERE DATE(waktu_transaksi) = ? AND status = 'SELESAI'
");
$stmtToday->execute([$today]);
$salesToday = $stmtToday->fetch();

// Metrik 2: Total Item Produk
$totalProduk = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();

// Metrik 3: Stok Menipis (<= 10)
$stokMenipisCount = $pdo->query("SELECT COUNT(*) FROM produk WHERE stok <= 10")->fetchColumn();

// Metrik 4: Total Member Terdaftar
$totalMember = $pdo->query("SELECT COUNT(*) FROM member")->fetchColumn();

// Transaksi Terbaru (5 data terakhir)
$recentTrx = $pdo->query("
    SELECT t.*, p.nama_pengguna as nama_kasir, m.nama_member
    FROM transaksi t
    LEFT JOIN pengguna p ON t.id_kasir = p.id
    LEFT JOIN member m ON t.id_member = m.id
    ORDER BY t.id DESC 
    LIMIT 5
")->fetchAll();

// Produk Terlaris (Berdasarkan jumlah terjual di detail_transaksi)
$topProducts = $pdo->query("
    SELECT pr.nama_produk, pr.barcode, pr.satuan, SUM(dt.jumlah) as total_terjual, SUM(dt.subtotal) as total_omzet
    FROM detail_transaksi dt
    JOIN produk pr ON dt.id_produk = pr.id
    GROUP BY pr.id
    ORDER BY total_terjual DESC
    LIMIT 5
")->fetchAll();

// Daftar Produk Stok Kritis
$lowStockProducts = $pdo->query("
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    LEFT JOIN kategori k ON p.id_kategori = k.id 
    WHERE p.stok <= 10 
    ORDER BY p.stok ASC 
    LIMIT 5
")->fetchAll();
?>

<!-- Banner Sambutan & Aksi Kasir Cepat -->
<div class="card border-0 rounded-4 shadow-sm mb-4 text-white position-relative overflow-hidden" 
     style="background: linear-gradient(135deg, #0d6efd 0%, #0046b8 100%);">
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary fw-bold px-3 py-1 rounded-pill mb-2">POS SYSTEM V1.0</span>
                <h2 class="fw-bold mb-2">Selamat Datang di Sistem Kasir Supermarket</h2>
                <p class="mb-4 opacity-75">
                    Solusi terintegrasi untuk Point of Sale, manajemen stok otomatis realtime, sistem membership pelanggan, dan promo diskon supermarket.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="kasir.php" class="btn btn-light btn-lg text-primary fw-bold px-4 rounded-pill shadow-sm">
                        <i class="bi bi-cart3 me-2"></i> Buka Terminal Kasir
                    </a>
                    <a href="produk/create.php" class="btn btn-outline-light btn-lg px-4 rounded-pill">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Produk Baru
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-end opacity-25">
                <i class="bi bi-shop fs-1" style="font-size: 10rem !important;"></i>
            </div>
        </div>
    </div>
</div>

<!-- 4 Kartu KPI Ringkasan -->
<div class="row g-3 mb-4">
    <!-- Omzet Hari Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Omzet Penjualan Hari Ini</span>
                <div class="bg-success-subtle text-success p-2 rounded-3">
                    <i class="bi bi-wallet2 fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1 font-monospace"><?= format_rupiah($salesToday['omzet_hari_ini']) ?></h3>
            <span class="text-muted small"><i class="bi bi-receipt me-1"></i> <?= $salesToday['total_trx'] ?> Transaksi selesai</span>
        </div>
    </div>

    <!-- Total Produk -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Item Produk</span>
                <div class="bg-primary-subtle text-primary p-2 rounded-3">
                    <i class="bi bi-box-seam fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1 font-monospace"><?= number_format($totalProduk) ?></h3>
            <a href="produk/index.php" class="small text-primary text-decoration-none fw-semibold">
                Lihat Semua Produk <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Stok Menipis -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Stok Kritis (&le; 10)</span>
                <div class="bg-danger-subtle text-danger p-2 rounded-3">
                    <i class="bi bi-exclamation-triangle fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold <?= $stokMenipisCount > 0 ? 'text-danger' : 'text-success' ?> mb-1 font-monospace">
                <?= number_format($stokMenipisCount) ?> Item
            </h3>
            <span class="text-muted small">Perlu restock segera</span>
        </div>
    </div>

    <!-- Member Terdaftar -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Member Terdaftar</span>
                <div class="bg-info-subtle text-info-emphasis p-2 rounded-3">
                    <i class="bi bi-people fs-5"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1 font-monospace"><?= number_format($totalMember) ?></h3>
            <span class="text-muted small">Pelanggan loyal supermarket</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Tabel Transaksi Terbaru -->
    <div class="col-lg-8">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Transaksi Kasir Terbaru</h5>
                <a href="riwayat.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    Lihat Semua Riwayat <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No. Struk</th>
                                <th>Waktu</th>
                                <th>Kasir</th>
                                <th>Member</th>
                                <th class="text-end">Total Akhir</th>
                                <th class="text-center pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentTrx)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentTrx as $trx): ?>
                                    <tr>
                                        <td class="ps-3 font-monospace fw-bold small text-dark"><?= htmlspecialchars($trx['nomor_struk']) ?></td>
                                        <td class="small text-muted"><?= date('H:i:s, d/m', strtotime($trx['waktu_transaksi'])) ?></td>
                                        <td class="small"><?= htmlspecialchars($trx['nama_kasir'] ?? 'Kasir') ?></td>
                                        <td class="small"><?= htmlspecialchars($trx['nama_member'] ?? 'Umum') ?></td>
                                        <td class="text-end font-monospace fw-bold text-primary small"><?= format_rupiah($trx['total_akhir']) ?></td>
                                        <td class="text-center pe-3">
                                            <span class="badge badge-subtle-success rounded-pill px-2 py-1"><?= $trx['status'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Produk Terlaris & Stok Kritis -->
    <div class="col-lg-4">
        <!-- Produk Terlaris -->
        <div class="card card-custom mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-trophy-fill text-warning me-2"></i>Produk Terlaris</h5>
            </div>
            <div class="card-body p-3">
                <?php if (empty($topProducts)): ?>
                    <p class="text-muted small text-center mb-0">Belum ada data penjualan tercatat.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($topProducts as $idx => $tp): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-secondary border rounded-circle p-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">
                                        <?= $idx + 1 ?>
                                    </span>
                                    <div>
                                        <div class="fw-semibold small text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($tp['nama_produk']) ?>">
                                            <?= htmlspecialchars($tp['nama_produk']) ?>
                                        </div>
                                        <small class="text-muted font-monospace"><?= format_rupiah($tp['total_omzet']) ?></small>
                                    </div>
                                </div>
                                <span class="badge badge-subtle-primary rounded-pill">
                                    <?= $tp['total_terjual'] ?> <?= htmlspecialchars($tp['satuan']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Peringatan Stok Kritis -->
        <div class="card card-custom">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> Perlu Restock Segera</h6>
                <a href="produk/index.php?stok_status=menipis" class="small text-danger">Kelola</a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($lowStockProducts)): ?>
                    <div class="text-center text-success small py-2">
                        <i class="bi bi-check-circle fs-4 d-block mb-1"></i>
                        Semua stok produk dalam kondisi aman!
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($lowStockProducts as $lp): ?>
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold small text-truncate" style="max-width: 180px;"><?= htmlspecialchars($lp['nama_produk']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($lp['barcode']) ?></small>
                                </div>
                                <span class="badge <?= $lp['stok'] <= 0 ? 'bg-danger' : 'bg-warning text-dark' ?> rounded-pill px-2">
                                    Sisa: <?= $lp['stok'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
