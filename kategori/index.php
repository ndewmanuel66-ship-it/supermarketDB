<?php
$pageTitle = "Data Kategori";
require_once __DIR__ . '/../includes/header.php';

$pdo = getDbConnection();

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        // Cek apakah ada produk dengan kategori ini
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE id_kategori = ?");
        $stmtCheck->execute([$id]);
        $productCount = $stmtCheck->fetchColumn();

        if ($productCount > 0) {
            set_flash('danger', "Kategori tidak dapat dihapus karena masih digunakan oleh {$productCount} produk!");
        } else {
            $stmtDel = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
            $stmtDel->execute([$id]);
            set_flash('success', "Kategori berhasil dihapus.");
        }
    }
    header("Location: index.php");
    exit;
}

// Search
$search = clean($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare("
        SELECT k.*, COUNT(p.id) as total_produk 
        FROM kategori k 
        LEFT JOIN produk p ON k.id = p.id_kategori 
        WHERE k.nama_kategori LIKE ?
        GROUP BY k.id 
        ORDER BY k.id DESC
    ");
    $stmt->execute(["%{$search}%"]);
} else {
    $stmt = $pdo->query("
        SELECT k.*, COUNT(p.id) as total_produk 
        FROM kategori k 
        LEFT JOIN produk p ON k.id = p.id_kategori 
        GROUP BY k.id 
        ORDER BY k.id DESC
    ");
}
$kategoris = $stmt->fetchAll();
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="bi bi-tags text-primary me-2"></i>Manajemen Kategori</h3>
        <p class="text-muted small mb-0">Kelola kategori produk untuk klasifikasi barang supermarket</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="create.php" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kategori Baru
        </a>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-4">
        <!-- Filter Pencarian -->
        <div class="row mb-3">
            <div class="col-md-5">
                <form method="GET" class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama kategori..." value="<?= htmlspecialchars($search) ?>">
                    <?php if (!empty($search)): ?>
                        <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                </form>
            </div>
            <div class="col-md-7 text-md-end text-muted small align-self-center mt-2 mt-md-0">
                Total Kategori: <strong><?= count($kategoris) ?></strong>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;" class="text-center">No</th>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th class="text-center">Jumlah Produk Terkait</th>
                        <th style="width: 160px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kategoris)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Tidak ada data kategori yang ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kategoris as $idx => $k): ?>
                            <tr>
                                <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                <td><span class="badge bg-light text-secondary border">#<?= $k['id'] ?></span></td>
                                <td class="fw-semibold text-dark">
                                    <i class="bi bi-tag-fill text-primary opacity-50 me-2"></i>
                                    <?= htmlspecialchars($k['nama_kategori']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-subtle-primary px-3 py-2 rounded-pill">
                                        <?= (int)$k['total_produk'] ?> Produk
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $k['id'] ?>" class="btn btn-outline-primary" title="Edit Kategori">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="index.php?action=delete&id=<?= $k['id'] ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="confirmDelete(event, this.href, 'Hapus Kategori <?= htmlspecialchars(addslashes($k['nama_kategori'])) ?>?');" 
                                           title="Hapus Kategori">
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
