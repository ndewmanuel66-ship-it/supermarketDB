<?php
$pageTitle = "Data Produk";
require_once __DIR__ . '/../includes/header.php';

$pdo = getDbConnection();

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        // Cek apakah produk sudah ada di riwayat detail transaksi
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM detail_transaksi WHERE id_produk = ?");
        $stmtCheck->execute([$id]);
        $trxCount = $stmtCheck->fetchColumn();

        if ($trxCount > 0) {
            set_flash('danger', "Produk ini tidak dapat dihapus karena tercatat dalam {$trxCount} transaksi penjualan!");
        } else {
            $stmtDel = $pdo->prepare("DELETE FROM produk WHERE id = ?");
            $stmtDel->execute([$id]);
            set_flash('success', "Produk berhasil dihapus!");
        }
    }
    header("Location: index.php");
    exit;
}

// Ambil Kategori untuk dropdown filter
$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

// Parameter Filter & Search
$search = clean($_GET['search'] ?? '');
$filterKategori = (int)($_GET['kategori'] ?? 0);
$filterStok = clean($_GET['stok_status'] ?? '');

$sql = "
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    LEFT JOIN kategori k ON p.id_kategori = k.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.barcode LIKE ? OR p.nama_produk LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($filterKategori > 0) {
    $sql .= " AND p.id_kategori = ?";
    $params[] = $filterKategori;
}

if ($filterStok === 'habis') {
    $sql .= " AND p.stok <= 0";
} elseif ($filterStok === 'menipis') {
    $sql .= " AND p.stok > 0 AND p.stok <= 10";
} elseif ($filterStok === 'aman') {
    $sql .= " AND p.stok > 10";
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produks = $stmt->fetchAll();

// Summary Statistik Cepat
$totalProduk = count($produks);
$stokMenipisCount = $pdo->query("SELECT COUNT(*) FROM produk WHERE stok <= 10")->fetchColumn();
$totalNilaiAset = $pdo->query("SELECT SUM(harga_beli * stok) FROM produk")->fetchColumn() ?: 0;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="bi bi-box-seam-fill text-primary me-2"></i>Manajemen Produk & Stok</h3>
        <p class="text-muted small mb-0">Kelola master data produk, harga jual/beli, barcode, dan kuantitas stok barang</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="create.php" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Produk Baru
        </a>
    </div>
</div>

<!-- Kartu Ringkasan Stok -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-medium">Total Item Produk</div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($totalProduk) ?></h3>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-box-seam fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-medium">Stok Kritis / Menipis (&le; 10)</div>
                    <h3 class="fw-bold mb-0 <?= $stokMenipisCount > 0 ? 'text-danger' : 'text-success' ?>"><?= number_format($stokMenipisCount) ?></h3>
                </div>
                <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                    <i class="bi bi-exclamation-octagon fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-medium">Estimasi Nilai Aset Stok</div>
                    <h3 class="fw-bold mb-0 text-success"><?= format_rupiah($totalNilaiAset) ?></h3>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-4">
        <!-- Filter Bar -->
        <form method="GET" class="row g-2 mb-4">
            <div class="col-lg-4 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Cari barcode / nama produk..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <select name="kategori" class="form-select">
                    <option value="0">-- Semua Kategori --</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filterKategori == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <select name="stok_status" class="form-select">
                    <option value="">-- Status Stok --</option>
                    <option value="aman" <?= $filterStok === 'aman' ? 'selected' : '' ?>>Stok Aman (&gt; 10)</option>
                    <option value="menipis" <?= $filterStok === 'menipis' ? 'selected' : '' ?>>Stok Menipis (&le; 10)</option>
                    <option value="habis" <?= $filterStok === 'habis' ? 'selected' : '' ?>>Stok Habis (0)</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <?php if (!empty($search) || $filterKategori > 0 || !empty($filterStok)): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-center">Stok</th>
                        <th>Satuan</th>
                        <th style="width: 140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produks)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-box2 fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Tidak ada data produk yang cocok dengan pencarian / filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($produks as $idx => $p): 
                            $profit = $p['harga_jual'] - $p['harga_beli'];
                            $margin = ($p['harga_jual'] > 0) ? round(($profit / $p['harga_jual']) * 100, 1) : 0;
                        ?>
                            <tr>
                                <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                <td>
                                    <span class="font-monospace fw-bold text-dark bg-light px-2 py-1 rounded border">
                                        <i class="bi bi-upc me-1 text-primary"></i><?= htmlspecialchars($p['barcode']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['nama_produk']) ?></div>
                                    <small class="text-muted">Margin laba: <span class="text-success fw-semibold"><?= $margin ?>%</span> (+<?= format_rupiah($profit) ?>)</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">
                                        <?= htmlspecialchars($p['nama_kategori'] ?? 'Tanpa Kategori') ?>
                                    </span>
                                </td>
                                <td class="text-end text-muted font-monospace"><?= format_rupiah($p['harga_beli']) ?></td>
                                <td class="text-end fw-bold text-primary font-monospace"><?= format_rupiah($p['harga_jual']) ?></td>
                                <td class="text-center">
                                    <?php if ($p['stok'] <= 0): ?>
                                        <span class="badge badge-subtle-danger px-3 py-2 rounded-pill">Habis (0)</span>
                                    <?php elseif ($p['stok'] <= 10): ?>
                                        <span class="badge badge-subtle-warning px-3 py-2 rounded-pill">Menipis (<?= $p['stok'] ?>)</span>
                                    <?php else: ?>
                                        <span class="badge badge-subtle-success px-3 py-2 rounded-pill"><?= $p['stok'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['satuan']) ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary" title="Edit Produk">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="index.php?action=delete&id=<?= $p['id'] ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="confirmDelete(event, this.href, 'Hapus produk <?= htmlspecialchars(addslashes($p['nama_produk'])) ?>?');" 
                                           title="Hapus Produk">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
