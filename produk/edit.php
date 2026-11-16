<?php
$pageTitle = "Edit Produk";
require_once __DIR__ . '/../includes/header.php';

$pdo = getDbConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$id]);
$produk = $stmt->fetch();

if (!$produk) {
    set_flash('danger', "Data produk tidak ditemukan!");
    header("Location: index.php");
    exit;
}

$kategoris = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $harga_beli = (float)($_POST['harga_beli'] ?? 0);
    $harga_jual = (float)($_POST['harga_jual'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $satuan = trim($_POST['satuan'] ?? 'Pcs');

    // Validasi
    if (empty($barcode) || empty($nama_produk) || $id_kategori <= 0) {
        $error = "Barcode, Nama Produk, dan Kategori wajib diisi!";
    } elseif ($harga_jual < 0 || $harga_beli < 0 || $stok < 0) {
        $error = "Harga dan stok tidak boleh bernilai negatif!";
    } else {
        // Cek barcode unik selain id ini
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE barcode = ? AND id != ?");
        $stmtCheck->execute([$barcode, $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            $error = "Barcode <strong>" . htmlspecialchars($barcode) . "</strong> sudah dipakai produk lain!";
        } else {
            $stmtUpdate = $pdo->prepare("
                UPDATE produk 
                SET barcode = ?, nama_produk = ?, id_kategori = ?, harga_beli = ?, harga_jual = ?, stok = ?, satuan = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([$barcode, $nama_produk, $id_kategori, $harga_beli, $harga_jual, $stok, $satuan, $id]);
            set_flash('success', "Produk '<strong>" . htmlspecialchars($nama_produk) . "</strong>' berhasil diperbarui!");
            header("Location: index.php");
            exit;
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Produk</h3>
                <p class="text-muted small mb-0">Ubah data master produk #<?= $produk['id'] ?> (<?= htmlspecialchars($produk['nama_produk']) ?>)</p>
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
                    <div class="row g-3">
                        <!-- Barcode -->
                        <div class="col-md-6">
                            <label for="barcode" class="form-label fw-semibold">Kode Barcode <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-upc"></i></span>
                                <input type="text" class="form-control font-monospace" id="barcode" name="barcode" 
                                       value="<?= htmlspecialchars($_POST['barcode'] ?? $produk['barcode']) ?>" required autofocus>
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div class="col-md-6">
                            <label for="id_kategori" class="form-label fw-semibold">Kategori Produk <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_kategori" name="id_kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategoris as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= ((isset($_POST['id_kategori']) ? $_POST['id_kategori'] : $produk['id_kategori']) == $k['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Nama Produk -->
                        <div class="col-12">
                            <label for="nama_produk" class="form-label fw-semibold">Nama Produk Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                                   value="<?= htmlspecialchars($_POST['nama_produk'] ?? $produk['nama_produk']) ?>" required>
                        </div>

                        <!-- Harga Beli & Harga Jual -->
                        <div class="col-md-6">
                            <label for="harga_beli" class="form-label fw-semibold">Harga Beli / Modal (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="100" class="form-control" id="harga_beli" name="harga_beli" 
                                       value="<?= htmlspecialchars($_POST['harga_beli'] ?? $produk['harga_beli']) ?>" required oninput="calcMargin()">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="harga_jual" class="form-label fw-semibold">Harga Jual Konsumen (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="100" class="form-control fw-bold text-primary" id="harga_jual" name="harga_jual" 
                                       value="<?= htmlspecialchars($_POST['harga_jual'] ?? $produk['harga_jual']) ?>" required oninput="calcMargin()">
                            </div>
                        </div>

                        <!-- Live Margin Box -->
                        <div class="col-12">
                            <div class="alert alert-light border py-2 px-3 small d-flex justify-content-between align-items-center mb-0" id="marginBox">
                                <span><i class="bi bi-graph-up-arrow me-1 text-success"></i> Estimasi Laba Bersih: <strong id="lblProfit">Rp 0</strong></span>
                                <span>Margin: <strong id="lblMargin" class="text-success">0%</strong></span>
                            </div>
                        </div>

                        <!-- Stok & Satuan -->
                        <div class="col-md-6">
                            <label for="stok" class="form-label fw-semibold">Stok Tersedia <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stok" name="stok" 
                                   value="<?= htmlspecialchars($_POST['stok'] ?? $produk['stok']) ?>" required min="0">
                        </div>

                        <div class="col-md-6">
                            <label for="satuan" class="form-label fw-semibold">Satuan Kemasan <span class="text-danger">*</span></label>
                            <input type="text" list="satuanList" class="form-control" id="satuan" name="satuan" 
                                   value="<?= htmlspecialchars($_POST['satuan'] ?? $produk['satuan']) ?>" required>
                            <datalist id="satuanList">
                                <option value="Pcs">
                                <option value="Bungkus">
                                <option value="Botol">
                                <option value="Pouch">
                                <option value="Karung">
                                <option value="Renteng">
                                <option value="Kotak / Box">
                                <option value="Kg">
                                <option value="Liter">
                            </datalist>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
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

<script>
    function calcMargin() {
        const beli = parseFloat(document.getElementById('harga_beli').value) || 0;
        const jual = parseFloat(document.getElementById('harga_jual').value) || 0;
        const profit = jual - beli;
        const margin = jual > 0 ? ((profit / jual) * 100).toFixed(1) : 0;

        document.getElementById('lblProfit').textContent = formatRupiahJS(profit);
        document.getElementById('lblMargin').textContent = margin + '%';

        const lblMargin = document.getElementById('lblMargin');
        if (profit < 0) {
            lblMargin.className = 'text-danger';
        } else {
            lblMargin.className = 'text-success';
        }
    }
    calcMargin();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
