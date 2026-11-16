<?php
$pageTitle = "Mesin Kasir (Point of Sale)";
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$activeUser = current_user();

// Ambil Promo Aktif Hari Ini
$today = date('Y-m-d');
$promos = $pdo->prepare("SELECT * FROM promo WHERE tanggal_mulai <= ? AND tanggal_selesai >= ? ORDER BY id DESC");
$promos->execute([$today, $today]);
$promoList = $promos->fetchAll();

// Ambil Member
$members = $pdo->query("SELECT * FROM member ORDER BY nama_member ASC")->fetchAll();

// Ambil Kategori untuk tab filter
$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
?>

<div class="row g-3">
    <!-- Kolom Kiri: Barcode Scanner & Katalog Produk (60%) -->
    <div class="col-lg-7 col-xl-7">
        <div class="card card-custom h-100 d-flex flex-column">
            <div class="card-header bg-white border-bottom p-3">
                <div class="row g-2 align-items-center">
                    <!-- Scanner Barcode Input -->
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white border-primary">
                                <i class="bi bi-upc-scan fs-5"></i>
                            </span>
                            <input type="text" id="barcodeScannerInput" class="form-control form-control-lg font-monospace fw-bold" 
                                   placeholder="Scan barcode / ketik lalu Enter..." autofocus autocomplete="off">
                            <button class="btn btn-outline-primary" type="button" id="btnScanSubmit">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Pencarian Teks Bebas -->
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchProductInput" class="form-control border-start-0 ps-0" placeholder="Cari nama barang...">
                        </div>
                    </div>
                </div>

                <!-- Tab Kategori -->
                <div class="d-flex gap-2 overflow-x-auto pt-3 pb-1" id="categoryTabs">
                    <button class="btn btn-sm btn-primary rounded-pill px-3 active category-pill" data-category="0">Semua</button>
                    <?php foreach ($kategoriList as $kat): ?>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 category-pill" data-category="<?= $kat['id'] ?>">
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Grid Produk -->
            <div class="card-body p-3 overflow-y-auto" style="max-height: 600px; min-height: 480px;" id="productGridContainer">
                <div class="row g-2" id="productGrid">
                    <!-- Diisi dinamis via Javascript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Keranjang Kasir & Panel Pembayaran (40%) -->
    <div class="col-lg-5 col-xl-5">
        <div class="card card-custom h-100 d-flex flex-column">
            <!-- Header Kasir & Info Struk -->
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cart3 text-primary me-2"></i>Daftar Belanja</h5>
                    <small class="text-muted">Kasir: <strong class="text-dark"><?= htmlspecialchars($activeUser['nama_pengguna']) ?></strong></small>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-danger" id="btnClearCart" title="Kosongkan Keranjang">
                        <i class="bi bi-trash3 me-1"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Tabel Item Keranjang -->
            <div class="card-body p-0 overflow-y-auto flex-grow-1" style="max-height: 280px; min-height: 200px;">
                <table class="table table-hover align-middle mb-0" id="cartTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3" style="width: 45%;">Produk</th>
                            <th class="text-center" style="width: 25%;">Qty</th>
                            <th class="text-end" style="width: 25%;">Subtotal</th>
                            <th class="text-center pe-3" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <!-- Keranjang Kosong State -->
                        <tr id="emptyCartRow">
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-bag-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                Belum ada item belanja.<br>
                                <small>Silakan scan barcode atau klik produk di samping.</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Form Opsi Member & Promo -->
            <div class="card-footer bg-light border-top p-3">
                <div class="row g-2 mb-2">
                    <!-- Member / Pelanggan -->
                    <div class="col-6">
                        <label class="form-label small fw-semibold mb-1"><i class="bi bi-person-badge me-1"></i> Member</label>
                        <select class="form-select form-select-sm" id="selectMember">
                            <option value="">-- Non Member --</option>
                            <?php foreach ($members as $m): ?>
                                <option value="<?= $m['id'] ?>" data-points="<?= $m['poin'] ?>" data-name="<?= htmlspecialchars($m['nama_member']) ?>">
                                    <?= htmlspecialchars($m['nama_member']) ?> (<?= $m['poin'] ?> Poin)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Promo / Diskon -->
                    <div class="col-6">
                        <label class="form-label small fw-semibold mb-1"><i class="bi bi-ticket-perforated me-1"></i> Promo Diskon</label>
                        <select class="form-select form-select-sm" id="selectPromo">
                            <option value="">-- Tanpa Promo --</option>
                            <?php foreach ($promoList as $pr): ?>
                                <option value="<?= $pr['id'] ?>" data-type="<?= $pr['jenis_diskon'] ?>" data-val="<?= $pr['nilai_diskon'] ?>">
                                    <?= htmlspecialchars($pr['nama_promo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Opsi Tukar Poin Member (Muncul jika member punya poin) -->
                <div id="memberPointBox" class="p-2 mb-2 bg-white rounded border d-none small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>Poin Tersedia: <strong id="lblMemberAvailablePoints" class="text-primary">0</strong></span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="checkRedeemPoints">
                            <label class="form-check-label fw-semibold" for="checkRedeemPoints">Gunakan Poin</label>
                        </div>
                    </div>
                    <div class="input-group input-group-sm d-none mt-2" id="redeemPointsInputGroup">
                        <span class="input-group-text">Poin</span>
                        <input type="number" class="form-control" id="inputPoinDipakai" min="1" value="0">
                        <span class="input-group-text" id="lblPointDiscountVal">= Potongan Rp 0</span>
                    </div>
                </div>

                <!-- Rincian Total -->
                <div class="bg-white p-3 rounded-3 border mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Subtotal Barang:</span>
                        <span class="fw-semibold text-dark" id="lblSubtotal">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1" id="rowPromoDiscount">
                        <span>Diskon Promo:</span>
                        <span class="fw-semibold text-danger" id="lblDiskonPromo">- Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1" id="rowPointDiscount">
                        <span>Potongan Poin Member:</span>
                        <span class="fw-semibold text-danger" id="lblPotonganPoin">- Rp 0</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold fs-6 text-dark d-block">TOTAL AKHIR</span>
                            <small class="text-success" id="lblEarnPointsNotice">+0 Poin baru</small>
                        </div>
                        <h3 class="fw-extrabold text-primary mb-0 font-monospace" id="lblTotalAkhir">Rp 0</h3>
                    </div>
                </div>

                <!-- Metode Pembayaran & Jumlah Bayar -->
                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Metode Pembayaran</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="metode_bayar" id="payTunai" value="TUNAI" checked>
                        <label class="btn btn-outline-primary btn-sm" for="payTunai"><i class="bi bi-cash me-1"></i> Tunai</label>

                        <input type="radio" class="btn-check" name="metode_bayar" id="payQRIS" value="QRIS">
                        <label class="btn btn-outline-primary btn-sm" for="payQRIS"><i class="bi bi-qr-code-scan me-1"></i> QRIS</label>

                        <input type="radio" class="btn-check" name="metode_bayar" id="payDebit" value="DEBIT">
                        <label class="btn btn-outline-primary btn-sm" for="payDebit"><i class="bi bi-credit-card me-1"></i> Debit</label>

                        <input type="radio" class="btn-check" name="metode_bayar" id="payTransfer" value="TRANSFER">
                        <label class="btn btn-outline-primary btn-sm" for="payTransfer"><i class="bi bi-bank me-1"></i> Transfer</label>
                    </div>
                </div>

                <!-- Kotak Uang Tunai & Kembalian -->
                <div id="cashPaymentSection">
                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <label class="form-label small fw-semibold mb-1">Jumlah Uang Diterima (Rp)</label>
                            <input type="number" id="inputBayar" class="form-control form-control-lg fw-bold font-monospace text-end" placeholder="0">
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold mb-1">Kembalian</label>
                            <div class="p-2 border rounded bg-white text-end h-75 d-flex align-items-center justify-content-end">
                                <span class="fw-bold font-monospace fs-5 text-success" id="lblKembalian">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Cepat Pecahan Uang Tunai -->
                    <div class="d-flex gap-1 flex-wrap mb-3">
                        <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="pas">Uang Pas</button>
                        <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="10000">10 Rb</button>
                        <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="20000">20 Rb</button>
                        <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="50000">50 Rb</button>
                        <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="100000">100 Rb</button>
                        <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-val="200000">200 Rb</button>
                    </div>
                </div>

                <!-- Tombol Proses Transaksi -->
                <button class="btn btn-success btn-lg w-100 py-3 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnProsesBayar">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <span>PROSES TRANSAKSI & CETAK STRUK</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Struk Pembayaran -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="receiptModalLabel"><i class="bi bi-check2-circle text-success me-2"></i>Transaksi Selesai!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="receipt-container shadow-sm rounded-3" id="printableReceipt">
                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-0">SUPERMARKET PRO</h5>
                        <div class="small">Jl. Merdeka No. 88, Jakarta</div>
                        <div class="small">Telp: (021) 555-8989</div>
                        <div class="small text-muted">NPWP: 01.234.567.8-012.000</div>
                    </div>
                    <div class="border-top border-bottom border-dark border-dashed py-2 my-2 small">
                        <div class="d-flex justify-content-between">
                            <span>No: <strong id="recNoStruk">-</strong></span>
                            <span id="recWaktu">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Kasir: <span id="recKasir">-</span></span>
                            <span>Pelanggan: <span id="recMember">-</span></span>
                        </div>
                    </div>

                    <!-- Item List -->
                    <table class="w-100 small mb-2" id="recItemsTable">
                        <!-- Diisi via JS -->
                    </table>

                    <div class="border-top border-dark border-dashed pt-2 small">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="recSubtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between" id="recRowPromo">
                            <span>Diskon Promo:</span>
                            <span id="recDiskonPromo">- Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between" id="recRowPoin">
                            <span>Potongan Poin:</span>
                            <span id="recPotonganPoin">- Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold fs-6 my-1">
                            <span>TOTAL AKHIR:</span>
                            <span id="recTotalAkhir">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Bayar (<span id="recMetode">-</span>):</span>
                            <span id="recBayar">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Kembalian:</span>
                            <span id="recKembalian">Rp 0</span>
                        </div>
                    </div>

                    <!-- Informasi Poin Member -->
                    <div class="border-top border-dark border-dashed mt-2 pt-2 small text-center" id="recBoxPoinInfo">
                        <div>Poin Didapat: +<span id="recPoinDidapat">0</span> | Sisa Poin: <span id="recSisaPoin">0</span></div>
                    </div>

                    <div class="text-center mt-3 pt-2 small text-muted">
                        <div>=== TERIMA KASIH ===</div>
                        <div>Barang yang dibeli tidak dapat ditukar</div>
                        <div>Layanan Konsumen: 0812-3456-7890</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary flex-grow-1" onclick="printReceipt()">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Audio Synthesizer Beep for Cashier Barcode Scanner -->
<script>
    function playBeep(freq = 1200, duration = 80) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            osc.start();
            setTimeout(() => {
                osc.stop();
                ctx.close();
            }, duration);
        } catch (e) {
            // Audio context not allowed or unsupported
        }
    }
</script>

<!-- POS Core Application Logic -->
<script>
    // State Keranjang
    let cart = []; // [{id, barcode, nama_produk, harga_jual, satuan, stok, qty}]
    let allProducts = [];
    let currentCategoryId = 0;
    const POINT_RATE = 100; // 1 poin = Rp 100
    const POINT_EARN_THRESHOLD = 10000; // 1 poin per Rp 10.000

    // DOM Elements
    const barcodeInput = document.getElementById('barcodeScannerInput');
    const searchInput = document.getElementById('searchProductInput');
    const productGrid = document.getElementById('productGrid');
    const cartTableBody = document.getElementById('cartTableBody');
    const emptyCartRow = document.getElementById('emptyCartRow');
    const selectMember = document.getElementById('selectMember');
    const selectPromo = document.getElementById('selectPromo');
    const checkRedeemPoints = document.getElementById('checkRedeemPoints');
    const inputPoinDipakai = document.getElementById('inputPoinDipakai');
    const memberPointBox = document.getElementById('memberPointBox');
    const redeemPointsInputGroup = document.getElementById('redeemPointsInputGroup');
    const lblMemberAvailablePoints = document.getElementById('lblMemberAvailablePoints');
    const lblPointDiscountVal = document.getElementById('lblPointDiscountVal');
    const inputBayar = document.getElementById('inputBayar');
    const lblKembalian = document.getElementById('lblKembalian');
    const btnProsesBayar = document.getElementById('btnProsesBayar');

    // 1. Fetch & Render Katalog Produk
    async function fetchProducts(query = '', categoryId = 0) {
        try {
            const res = await fetch(`api/search_product.php?query=${encodeURIComponent(query)}`);
            const data = await res.json();
            if (data.success) {
                allProducts = data.data;
                renderProductGrid(categoryId);
            }
        } catch (err) {
            console.error("Gagal mengambil data produk:", err);
        }
    }

    function renderProductGrid(filterCatId = 0) {
        productGrid.innerHTML = '';
        const filtered = filterCatId == 0 
            ? allProducts 
            : allProducts.filter(p => p.id_kategori == filterCatId);

        if (filtered.length === 0) {
            productGrid.innerHTML = `
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-search fs-2 opacity-50 d-block mb-1"></i>
                    Produk tidak ditemukan.
                </div>
            `;
            return;
        }

        filtered.forEach(p => {
            const isOutOfStock = p.stok <= 0;
            const col = document.createElement('div');
            col.className = 'col-sm-6 col-md-4 col-xl-3';
            col.innerHTML = `
                <div class="card h-100 pos-product-card p-2 position-relative ${isOutOfStock ? 'opacity-50' : ''}" 
                     data-id="${p.id}" ${isOutOfStock ? 'style="pointer-events:none;"' : ''}>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-light text-secondary border font-monospace small" style="font-size:0.65rem;">
                            ${p.barcode}
                        </span>
                        <span class="badge ${p.stok <= 5 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'} rounded-pill" style="font-size:0.7rem;">
                            Stok: ${p.stok}
                        </span>
                    </div>
                    <div class="fw-semibold text-dark text-truncate small" title="${p.nama_produk}">${p.nama_produk}</div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="fw-bold text-primary font-monospace small">${formatRupiahJS(p.harga_jual)}</span>
                        <button class="btn btn-sm btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:26px; height:26px;" ${isOutOfStock ? 'disabled' : ''}>
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
            `;
            col.querySelector('.pos-product-card').addEventListener('click', () => {
                addToCart(p);
            });
            productGrid.appendChild(col);
        });
    }

    // 2. Barcode Scanner Handling
    barcodeInput.addEventListener('keydown', async (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcodeVal = barcodeInput.value.trim();
            if (barcodeVal) {
                await processBarcodeScan(barcodeVal);
            }
        }
    });

    document.getElementById('btnScanSubmit').addEventListener('click', async () => {
        const barcodeVal = barcodeInput.value.trim();
        if (barcodeVal) {
            await processBarcodeScan(barcodeVal);
        }
    });

    async function processBarcodeScan(barcode) {
        try {
            const res = await fetch(`api/search_product.php?barcode=${encodeURIComponent(barcode)}`);
            const json = await res.json();
            if (json.success && json.data) {
                playBeep(1400, 70); // Success beep sound
                addToCart(json.data);
                barcodeInput.value = '';
                barcodeInput.focus();
            } else {
                playBeep(400, 150); // Error low beep
                Swal.fire({
                    icon: 'warning',
                    title: 'Barcode Tidak Ditemukan',
                    text: `Produk dengan barcode '${barcode}' belum terdaftar di sistem.`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } catch (err) {
            console.error("Error scan:", err);
        }
    }

    // 3. Tambah Item ke Keranjang Belanja
    function addToCart(product) {
        const existing = cart.find(item => item.id == product.id);

        if (existing) {
            if (existing.qty + 1 > product.stok) {
                Swal.fire('Stok Terbatas', `Stok barang hanya tersedia ${product.stok} ${product.satuan}`, 'warning');
                return;
            }
            existing.qty += 1;
        } else {
            if (product.stok <= 0) {
                Swal.fire('Stok Habis', `Produk '${product.nama_produk}' sedang kosong!`, 'error');
                return;
            }
            cart.push({
                id: product.id,
                barcode: product.barcode,
                nama_produk: product.nama_produk,
                harga_jual: parseFloat(product.harga_jual),
                satuan: product.satuan,
                stok: parseInt(product.stok),
                qty: 1
            });
        }
        playBeep(1200, 50);
        renderCart();
    }

    // 4. Update Qty di Keranjang
    function updateQty(id, delta) {
        const item = cart.find(i => i.id == id);
        if (!item) return;

        const newQty = item.qty + delta;
        if (newQty <= 0) {
            removeFromCart(id);
        } else if (newQty > item.stok) {
            Swal.fire('Stok Terbatas', `Stok tersedia hanya ${item.stok} ${item.satuan}`, 'warning');
        } else {
            item.qty = newQty;
            renderCart();
        }
    }

    function setQtyDirect(id, value) {
        const item = cart.find(i => i.id == id);
        if (!item) return;
        const qtyVal = parseInt(value) || 1;
        if (qtyVal <= 0) {
            removeFromCart(id);
        } else if (qtyVal > item.stok) {
            Swal.fire('Stok Terbatas', `Stok tersedia hanya ${item.stok} ${item.satuan}`, 'warning');
            item.qty = item.stok;
            renderCart();
        } else {
            item.qty = qtyVal;
            renderCart();
        }
    }

    function removeFromCart(id) {
        cart = cart.filter(i => i.id != id);
        renderCart();
    }

    // 5. Render Tabel Keranjang & Hitung Total
    function renderCart() {
        if (cart.length === 0) {
            cartTableBody.innerHTML = `
                <tr id="emptyCartRow">
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="bi bi-bag-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                        Belum ada item belanja.<br>
                        <small>Silakan scan barcode atau klik produk di samping.</small>
                    </td>
                </tr>
            `;
        } else {
            cartTableBody.innerHTML = '';
            cart.forEach(item => {
                const sub = item.qty * item.harga_jual;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-3">
                        <div class="fw-semibold text-dark small text-truncate" style="max-width: 150px;" title="${item.nama_produk}">
                            ${item.nama_produk}
                        </div>
                        <span class="text-muted small">${formatRupiahJS(item.harga_jual)} / ${item.satuan}</span>
                    </td>
                    <td class="text-center">
                        <div class="input-group input-group-sm justify-content-center" style="width: 105px; margin: auto;">
                            <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${item.id}, -1)">-</button>
                            <input type="number" class="form-control text-center px-1 font-monospace" value="${item.qty}" min="1" max="${item.stok}" onchange="setQtyDirect(${item.id}, this.value)">
                            <button class="btn btn-outline-secondary px-2" type="button" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                    </td>
                    <td class="text-end fw-bold font-monospace small">
                        ${formatRupiahJS(sub)}
                    </td>
                    <td class="text-center pe-3">
                        <button class="btn btn-link text-danger p-0" onclick="removeFromCart(${item.id})" title="Hapus">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </td>
                `;
                cartTableBody.appendChild(tr);
            });
        }

        calculateBill();
    }

    // 6. Hitung Subtotal, Diskon Promo, Poin, Total Akhir, dan Kembalian
    function calculateBill() {
        const subtotal = cart.reduce((acc, item) => acc + (item.qty * item.harga_jual), 0);
        document.getElementById('lblSubtotal').textContent = formatRupiahJS(subtotal);

        // Promo Diskon
        let diskonPromo = 0;
        const selectedPromoOpt = selectPromo.options[selectPromo.selectedIndex];
        if (selectedPromoOpt && selectedPromoOpt.value) {
            const promoType = selectedPromoOpt.getAttribute('data-type');
            const promoVal = parseFloat(selectedPromoOpt.getAttribute('data-val')) || 0;
            if (promoType === 'persen') {
                diskonPromo = Math.round(subtotal * (promoVal / 100));
            } else {
                diskonPromo = Math.min(subtotal, promoVal);
            }
        }
        document.getElementById('lblDiskonPromo').textContent = `- ${formatRupiahJS(diskonPromo)}`;

        // Poin Member
        let potonganPoin = 0;
        let poinDipakai = 0;
        if (checkRedeemPoints.checked && selectMember.value) {
            poinDipakai = parseInt(inputPoinDipakai.value) || 0;
            potonganPoin = poinDipakai * POINT_RATE;
        }
        document.getElementById('lblPotonganPoin').textContent = `- ${formatRupiahJS(potonganPoin)}`;

        // Total Akhir
        let totalAkhir = subtotal - diskonPromo - potonganPoin;
        if (totalAkhir < 0) totalAkhir = 0;
        document.getElementById('lblTotalAkhir').textContent = formatRupiahJS(totalAkhir);

        // Poin Baru yang Didapat Member
        if (selectMember.value) {
            const earnedPoints = Math.floor(totalAkhir / POINT_EARN_THRESHOLD);
            document.getElementById('lblEarnPointsNotice').textContent = `+${earnedPoints} Poin baru didapat`;
        } else {
            document.getElementById('lblEarnPointsNotice').textContent = '+0 Poin baru';
        }

        // Kembalian
        calculateChange(totalAkhir);
    }

    function calculateChange(totalAkhir) {
        if (typeof totalAkhir === 'undefined') {
            totalAkhir = getCleanTotalAkhir();
        }

        const isTunai = document.getElementById('payTunai').checked;
        if (!isTunai) {
            inputBayar.value = totalAkhir;
            lblKembalian.textContent = formatRupiahJS(0);
            lblKembalian.className = 'fw-bold font-monospace fs-5 text-success';
            return;
        }

        const bayarVal = parseFloat(inputBayar.value) || 0;
        const kembalian = bayarVal - totalAkhir;

        if (kembalian >= 0) {
            lblKembalian.textContent = formatRupiahJS(kembalian);
            lblKembalian.className = 'fw-bold font-monospace fs-5 text-success';
        } else {
            lblKembalian.textContent = `Kurang ${formatRupiahJS(Math.abs(kembalian))}`;
            lblKembalian.className = 'fw-bold font-monospace fs-6 text-danger';
        }
    }

    function getCleanTotalAkhir() {
        const subtotal = cart.reduce((acc, item) => acc + (item.qty * item.harga_jual), 0);
        let diskonPromo = 0;
        const selectedPromoOpt = selectPromo.options[selectPromo.selectedIndex];
        if (selectedPromoOpt && selectedPromoOpt.value) {
            const promoType = selectedPromoOpt.getAttribute('data-type');
            const promoVal = parseFloat(selectedPromoOpt.getAttribute('data-val')) || 0;
            if (promoType === 'persen') diskonPromo = Math.round(subtotal * (promoVal / 100));
            else diskonPromo = Math.min(subtotal, promoVal);
        }
        let potonganPoin = 0;
        if (checkRedeemPoints.checked && selectMember.value) {
            const poinDipakai = parseInt(inputPoinDipakai.value) || 0;
            potonganPoin = poinDipakai * POINT_RATE;
        }
        let total = subtotal - diskonPromo - potonganPoin;
        return total > 0 ? total : 0;
    }

    // 7. Event Listeners untuk Interaktivitas Kasir
    // Pencarian Teks
    searchInput.addEventListener('input', (e) => {
        fetchProducts(e.target.value.trim(), currentCategoryId);
    });

    // Tab Kategori
    document.querySelectorAll('.category-pill').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.category-pill').forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary', 'active');

            currentCategoryId = parseInt(btn.getAttribute('data-category')) || 0;
            renderProductGrid(currentCategoryId);
        });
    });

    // Member Change
    selectMember.addEventListener('change', () => {
        const opt = selectMember.options[selectMember.selectedIndex];
        if (opt && opt.value) {
            const availablePoints = parseInt(opt.getAttribute('data-points')) || 0;
            lblMemberAvailablePoints.textContent = availablePoints;
            memberPointBox.classList.remove('d-none');
            inputPoinDipakai.max = availablePoints;
            inputPoinDipakai.value = 0;
        } else {
            memberPointBox.classList.add('d-none');
            checkRedeemPoints.checked = false;
            redeemPointsInputGroup.classList.add('d-none');
            inputPoinDipakai.value = 0;
        }
        calculateBill();
    });

    // Poin Switch & Input
    checkRedeemPoints.addEventListener('change', () => {
        if (checkRedeemPoints.checked) {
            redeemPointsInputGroup.classList.remove('d-none');
            const maxPoints = parseInt(inputPoinDipakai.max) || 0;
            inputPoinDipakai.value = maxPoints;
            lblPointDiscountVal.textContent = `= Potongan ${formatRupiahJS(maxPoints * POINT_RATE)}`;
        } else {
            redeemPointsInputGroup.classList.add('d-none');
            inputPoinDipakai.value = 0;
        }
        calculateBill();
    });

    inputPoinDipakai.addEventListener('input', () => {
        let val = parseInt(inputPoinDipakai.value) || 0;
        const max = parseInt(inputPoinDipakai.max) || 0;
        if (val > max) {
            val = max;
            inputPoinDipakai.value = max;
        }
        lblPointDiscountVal.textContent = `= Potongan ${formatRupiahJS(val * POINT_RATE)}`;
        calculateBill();
    });

    // Promo Change
    selectPromo.addEventListener('change', () => {
        calculateBill();
    });

    // Reset Cart
    document.getElementById('btnClearCart').addEventListener('click', () => {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            text: 'Semua item belanja kasir akan dihapus!',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kosongkan',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) {
                cart = [];
                renderCart();
                barcodeInput.focus();
            }
        });
    });

    // Metode Pembayaran Change
    document.querySelectorAll('input[name="metode_bayar"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const isTunai = document.getElementById('payTunai').checked;
            const cashSection = document.getElementById('cashPaymentSection');
            if (isTunai) {
                cashSection.classList.remove('d-none');
                inputBayar.value = '';
                lblKembalian.textContent = 'Rp 0';
            } else {
                cashSection.classList.add('d-none');
            }
            calculateBill();
        });
    });

    // Input Bayar & Quick Cash Buttons
    inputBayar.addEventListener('input', () => {
        calculateChange();
    });

    document.querySelectorAll('.quick-cash-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const total = getCleanTotalAkhir();
            const val = btn.getAttribute('data-val');
            if (val === 'pas') {
                inputBayar.value = total;
            } else {
                inputBayar.value = parseFloat(val);
            }
            calculateChange(total);
        });
    });

    // 8. Submit & Proses Transaksi Checkout
    btnProsesBayar.addEventListener('click', async () => {
        if (cart.length === 0) {
            Swal.fire('Keranjang Kosong', 'Silakan pilih atau scan produk terlebih dahulu!', 'warning');
            return;
        }

        const totalAkhir = getCleanTotalAkhir();
        const metodeBayar = document.querySelector('input[name="metode_bayar"]:checked').value;
        const bayarNominal = parseFloat(inputBayar.value) || 0;

        if (metodeBayar === 'TUNAI' && bayarNominal < totalAkhir) {
            Swal.fire('Uang Kurang', `Jumlah pembayaran masih kurang ${formatRupiahJS(totalAkhir - bayarNominal)}!`, 'error');
            inputBayar.focus();
            return;
        }

        // Tampilkan Loading
        btnProsesBayar.disabled = true;
        btnProsesBayar.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memproses Transaksi...`;

        const payload = {
            id_kasir: <?= (int)$activeUser['id'] ?>,
            id_member: selectMember.value || null,
            id_promo: selectPromo.value || null,
            poin_dipakai: (checkRedeemPoints.checked && selectMember.value) ? parseInt(inputPoinDipakai.value) : 0,
            metode_bayar: metodeBayar,
            jumlah_bayar: metodeBayar === 'TUNAI' ? bayarNominal : totalAkhir,
            items: cart.map(i => ({
                id_produk: i.id,
                jumlah: i.qty,
                harga_satuan: i.harga_jual,
                subtotal: i.qty * i.harga_jual
            }))
        };

        try {
            const res = await fetch('api/process_transaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            btnProsesBayar.disabled = false;
            btnProsesBayar.innerHTML = `<i class="bi bi-check-circle-fill fs-5 me-2"></i> PROSES TRANSAKSI & CETAK STRUK`;

            if (result.success) {
                // Tampilkan Struk
                showReceiptModal(result.data);

                // Reset State Kasir
                cart = [];
                renderCart();
                inputBayar.value = '';
                selectMember.value = '';
                selectPromo.value = '';
                checkRedeemPoints.checked = false;
                memberPointBox.classList.add('d-none');
                
                // Refresh list produk untuk memperbarui sisa stok terbaru
                fetchProducts('', currentCategoryId);
            } else {
                Swal.fire('Transaksi Gagal', result.message, 'error');
            }
        } catch (err) {
            btnProsesBayar.disabled = false;
            btnProsesBayar.innerHTML = `<i class="bi bi-check-circle-fill fs-5 me-2"></i> PROSES TRANSAKSI & CETAK STRUK`;
            Swal.fire('Terjadi Kesalahan', err.message, 'error');
        }
    });

    // 9. Modal Struk Pembayaran
    function showReceiptModal(data) {
        document.getElementById('recNoStruk').textContent = data.nomor_struk;
        document.getElementById('recWaktu').textContent = data.waktu_transaksi;
        document.getElementById('recKasir').textContent = data.kasir;
        document.getElementById('recMember').textContent = data.member ? data.member.nama : '-';

        // Render Item Struk
        const itemsTable = document.getElementById('recItemsTable');
        itemsTable.innerHTML = '';
        data.items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="2" class="pt-1">
                    <div>${item.nama_produk}</div>
                    <div class="text-muted">${item.jumlah} ${item.satuan} x ${formatRupiahJS(item.harga_satuan)}</div>
                </td>
                <td class="text-end align-bottom pt-1">${formatRupiahJS(item.subtotal)}</td>
            `;
            itemsTable.appendChild(tr);
        });

        document.getElementById('recSubtotal').textContent = formatRupiahJS(data.subtotal);

        // Diskon Promo Row
        const recRowPromo = document.getElementById('recRowPromo');
        if (data.total_diskon > 0) {
            recRowPromo.classList.remove('d-none');
            document.getElementById('recDiskonPromo').textContent = `- ${formatRupiahJS(data.total_diskon)}`;
        } else {
            recRowPromo.classList.add('d-none');
        }

        // Potongan Poin Row
        const recRowPoin = document.getElementById('recRowPoin');
        if (data.potongan_poin > 0) {
            recRowPoin.classList.remove('d-none');
            document.getElementById('recPotonganPoin').textContent = `- ${formatRupiahJS(data.potongan_poin)}`;
        } else {
            recRowPoin.classList.add('d-none');
        }

        document.getElementById('recTotalAkhir').textContent = formatRupiahJS(data.total_akhir);
        document.getElementById('recMetode').textContent = data.metode_bayar;
        document.getElementById('recBayar').textContent = formatRupiahJS(data.jumlah_bayar);
        document.getElementById('recKembalian').textContent = formatRupiahJS(data.kembalian);

        // Info Poin
        const recBoxPoinInfo = document.getElementById('recBoxPoinInfo');
        if (data.member) {
            recBoxPoinInfo.classList.remove('d-none');
            document.getElementById('recPoinDidapat').textContent = data.poin_didapat;
            document.getElementById('recSisaPoin').textContent = data.member.sisa_poin;
        } else {
            recBoxPoinInfo.classList.add('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    }

    function printReceipt() {
        const printContent = document.getElementById('printableReceipt').innerHTML;
        const win = window.open('', '_blank', 'width=400,height=600');
        win.document.write(`
            <html>
            <head>
                <title>Cetak Struk Transaksi</title>
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
                ${printContent}
            </body>
            </html>
        `);
        win.document.close();
    }

    // Inisialisasi awal
    fetchProducts();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
