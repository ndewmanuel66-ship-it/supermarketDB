<?php
$pageTitle = "Edit Kategori";
require_once __DIR__ . '/../includes/header.php';

$pdo = getDbConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM kategori WHERE id = ?");
$stmt->execute([$id]);
$kategori = $stmt->fetch();

if (!$kategori) {
    set_flash('danger', "Data kategori tidak ditemukan!");
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');

    if (empty($nama_kategori)) {
        $error = "Nama kategori tidak boleh kosong!";
    } else {
        // Cek duplikasi kecuali id sekarang
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM kategori WHERE LOWER(nama_kategori) = LOWER(?) AND id != ?");
        $stmtCheck->execute([$nama_kategori, $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "Kategori dengan nama tersebut sudah ada!";
        } else {
            $stmtUpdate = $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
            $stmtUpdate->execute([$nama_kategori, $id]);
            set_flash('success', "Kategori berhasil diperbarui menjadi '<strong>" . htmlspecialchars($nama_kategori) . "</strong>'!");
            header("Location: index.php");
            exit;
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Kategori</h3>
                <p class="text-muted small mb-0">Ubah nama kategori #<?= $kategori['id'] ?></p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card card-custom">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="nama_kategori" class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" 
                                   value="<?= htmlspecialchars($_POST['nama_kategori'] ?? $kategori['nama_kategori']) ?>" required autofocus>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save2-fill me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
