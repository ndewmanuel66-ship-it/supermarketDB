<?php
$pageTitle = "Riwayat Transaksi Penjualan";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

// Filter & Pencarian
$filterDate = clean($_GET['date'] ?? '');
$searchStruk = clean($_GET['search'] ?? '');

$sql = "
    SELECT t.*, p.nama_pengguna as nama_kasir, m.nama_member, pr.nama_promo
    FROM transaksi t
    LEFT JOIN pengguna p ON t.id_kasir = p.id
    LEFT JOIN member m ON t.id_member = m.id
    LEFT JOIN promo pr ON t.id_promo = pr.id
    WHERE 1=1
";
$params = [];

if (!empty($filterDate)) {
    $sql .= " AND DATE(t.waktu_transaksi) = ?";
    $params[] = $filterDate;
}

if (!empty($searchStruk)) {
    $sql .= " AND (t.nomor_struk LIKE ? OR m.nama_member LIKE ?)";
    $params[] = "%{$searchStruk}%";
    $params[] = "%{$searchStruk}%";
}

$sql .= " ORDER BY t.id DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksis = $stmt->fetchAll();

// Statistik Cepat Riwayat
$totalPenjualan = array_sum(array_column($transaksis, 'total_akhir'));
$countTrans = count($transaksis);
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Riwayat Transaksi Kasir</h3>
        <p class="text-muted small mb-0">Daftar rekapan transaksi penjualan dan cetak ulang struk kasir</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="kasir.php" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-cart-plus-fill me-1"></i> Transaksi Kasir Baru
        </a>
    </div>
</div>

<!-- Ringkasan Filter -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Total Omzet (Daftar Ditampilkan)</div>
                    <h3 class="fw-bold mb-0 text-success"><?= format_rupiah($totalPenjualan) ?></h3>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Jumlah Transaksi</div>
                    <h3 class="fw-bold mb-0 text-primary"><?= number_format($countTrans) ?> Struk</h3>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-4">
        <!-- Filter Bar -->
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nomor struk / nama member..." value="<?= htmlspecialchars($searchStruk) ?>">
            </div>
            <div class="col-md-4">
                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <?php if (!empty($filterDate) || !empty($searchStruk)): ?>
                    <a href="riwayat.php" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Struk</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir</th>
                        <th>Member</th>
                        <th>Metode</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Diskon / Poin</th>
                        <th class="text-end">Total Akhir</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksis)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                Belum ada riwayat transaksi yang tercatat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transaksis as $t): ?>
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-dark">
                                        <?= htmlspecialchars($t['nomor_struk']) ?>
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <i class="bi bi-calendar3 me-1"></i> <?= date('d M Y, H:i', strtotime($t['waktu_transaksi'])) ?>
                                </td>
                                <td class="small fw-semibold"><?= htmlspecialchars($t['nama_kasir'] ?? 'Kasir') ?></td>
                                <td>
                                    <?php if ($t['nama_member']): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill">
                                            <i class="bi bi-person-check me-1"></i><?= htmlspecialchars($t['nama_member']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">Umum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($t['metode_bayar']) ?>
                                    </span>
                                </td>
                                <td class="text-end font-monospace small"><?= format_rupiah($t['subtotal']) ?></td>
                                <td class="text-end font-monospace small text-danger">
                                    <?php 
                                        $pot = $t['total_diskon'] + ($t['poin_dipakai'] * 100);
                                        echo $pot > 0 ? '- ' . format_rupiah($pot) : '-';
                                    ?>
                                </td>
                                <td class="text-end font-monospace fw-bold text-primary"><?= format_rupiah($t['total_akhir']) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-subtle-success px-3 py-1 rounded-pill"><?= htmlspecialchars($t['status']) ?></span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDetail(<?= $t['id'] ?>)" title="Lihat Detail & Cetak Ulang Struk">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt text-primary me-2"></i>Rincian Struk Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted small mt-2">Memuat rincian transaksi...</div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary flex-grow-1" onclick="printModalReceipt()">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Ulang Struk
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    async function viewDetail(transaksiId) {
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        modal.show();
        const content = document.getElementById('modalDetailContent');
        content.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="text-muted small mt-2">Memuat rincian transaksi...</div>
            </div>
        `;

        try {
            const res = await fetch(`api/get_transaction_detail.php?id=${transaksiId}`);
            const json = await res.json();
            if (json.success) {
                renderReceiptDetail(json.data);
            } else {
                content.innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
            }
        } catch (err) {
            content.innerHTML = `<div class="alert alert-danger">Gagal memuat: ${err.message}</div>`;
        }
    }

    function renderReceiptDetail(t) {
        let itemsHtml = '';
        t.items.forEach(item => {
            itemsHtml += `
                <tr>
                    <td colspan="2" class="pt-1">
                        <div>${item.nama_produk}</div>
                        <div class="text-muted">${item.jumlah} ${item.satuan} x ${formatRupiahJS(item.harga_satuan)}</div>
                    </td>
                    <td class="text-end align-bottom pt-1">${formatRupiahJS(item.subtotal)}</td>
                </tr>
            `;
        });

        document.getElementById('modalDetailContent').innerHTML = `
            <div class="receipt-container shadow-sm rounded-3" id="printableReprint">
                <div class="text-center mb-3">
                    <h5 class="fw-bold mb-0">SUPERMARKET PRO</h5>
                    <div class="small">Jl. Merdeka No. 88, Jakarta</div>
                    <div class="small">Telp: (021) 555-8989</div>
                    <div class="small text-muted">[CETAK ULANG STRUK]</div>
                </div>
                <div class="border-top border-bottom border-dark border-dashed py-2 my-2 small">
                    <div class="d-flex justify-content-between">
                        <span>No: <strong>${t.nomor_struk}</strong></span>
                        <span>${t.waktu_transaksi}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Kasir: ${t.nama_kasir || '-'}</span>
                        <span>Pelanggan: ${t.nama_member || 'Umum'}</span>
                    </div>
                </div>
                <table class="w-100 small mb-2">
                    ${itemsHtml}
                </table>
                <div class="border-top border-dark border-dashed pt-2 small">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <span>${formatRupiahJS(t.subtotal)}</span>
                    </div>
                    ${t.total_diskon > 0 ? `
                        <div class="d-flex justify-content-between">
                            <span>Diskon Promo:</span>
                            <span>- ${formatRupiahJS(t.total_diskon)}</span>
                        </div>
                    ` : ''}
                    ${t.poin_dipakai > 0 ? `
                        <div class="d-flex justify-content-between">
                            <span>Potongan Poin:</span>
                            <span>- ${formatRupiahJS(t.poin_dipakai * 100)}</span>
                        </div>
                    ` : ''}
                    <div class="d-flex justify-content-between fw-bold fs-6 my-1">
                        <span>TOTAL AKHIR:</span>
                        <span>${formatRupiahJS(t.total_akhir)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Bayar (${t.metode_bayar}):</span>
                        <span>${formatRupiahJS(t.jumlah_bayar)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Kembalian:</span>
                        <span>${formatRupiahJS(t.kembalian)}</span>
                    </div>
                </div>
                <div class="text-center mt-3 pt-2 small text-muted">
                    <div>=== TERIMA KASIH ===</div>
                </div>
            </div>
        `;
    }

    function printModalReceipt() {
        const el = document.getElementById('printableReprint');
        if (!el) return;
        const win = window.open('', '_blank', 'width=400,height=600');
        win.document.write(`
            <html>
            <head>
                <title>Cetak Ulang Struk</title>
                <style>
                    body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 10px; width: 300px; color: #000; }
                    .text-center { text-align: center; }
                    .text-end { text-align: right; }
                    .fw-bold { font-weight: bold; }
                    .d-flex { display: flex; }
                    .justify-content-between { justify-content: space-between; }
                    .border-top { border-top: 1px dashed #000; }
                    .border-bottom { border-bottom: 1px dashed #000; }
                    .py-2 { padding-top: 6px; padding-bottom: 6px; }
                    .my-2 { margin-top: 6px; margin-bottom: 6px; }
                    table { width: 100%; border-collapse: collapse; }
                </style>
            </head>
            <body onload="window.print(); window.close();">
                ${el.innerHTML}
            </body>
            </html>
        `);
        win.document.close();
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
