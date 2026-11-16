<?php
/**
 * API: Proses Checkout Transaksi Kasir (POS)
 * Menjalankan validasi, kalkulasi server-side, penyimpanan transaksi,
 * dan PENYESUAIAN STOK PRODUK OTOMATIS secara transaksional (ACID).
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Nilai konversi 1 poin = Rp 100
define('POINT_REDEEM_RATE', 100);
// Dapatkan 1 poin setiap belanja Rp 10.000
define('POINT_EARN_THRESHOLD', 10000);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request harus POST!']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    // Coba ambil dari $_POST jika bukan json body
    $data = $_POST;
}

$items = $data['items'] ?? [];
$id_kasir = (int)($data['id_kasir'] ?? (current_user()['id'] ?? 1));
$id_member = !empty($data['id_member']) ? (int)$data['id_member'] : null;
$id_promo = !empty($data['id_promo']) ? (int)$data['id_promo'] : null;
$poin_dipakai = max(0, (int)($data['poin_dipakai'] ?? 0));
$metode_bayar = strtoupper(trim($data['metode_bayar'] ?? 'TUNAI'));
$jumlah_bayar = (float)($data['jumlah_bayar'] ?? 0);

if (empty($items) || !is_array($items)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja masih kosong!']);
    exit;
}

$pdo = getDbConnection();

try {
    // Mulai Database Transaction untuk integritas data
    $pdo->beginTransaction();

    // 1. Ambil & Validasi Kasir
    $stmtKasir = $pdo->prepare("SELECT * FROM pengguna WHERE id = ?");
    $stmtKasir->execute([$id_kasir]);
    $kasir = $stmtKasir->fetch();
    if (!$kasir) {
        throw new Exception("Kasir tidak valid atau tidak ditemukan.");
    }

    // 2. Validasi & Hitung Subtotal dari Database (Anti-Tampering)
    $subtotal = 0;
    $processedItems = [];

    foreach ($items as $item) {
        $idProduk = (int)($item['id'] ?? $item['id_produk'] ?? 0);
        $qty = (int)($item['qty'] ?? $item['jumlah'] ?? 0);

        if ($idProduk <= 0 || $qty <= 0) {
            throw new Exception("Item produk atau kuantitas belanja tidak valid.");
        }

        // Kunci baris produk jika didukung atau ambil data terbaru
        $stmtP = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
        $stmtP->execute([$idProduk]);
        $prod = $stmtP->fetch();

        if (!$prod) {
            throw new Exception("Produk ID #{$idProduk} tidak ditemukan dalam database.");
        }

        // Cek kecukupan stok produk
        if ($prod['stok'] < $qty) {
            throw new Exception("Stok untuk produk '{$prod['nama_produk']}' tidak mencukupi! Tersedia: {$prod['stok']} {$prod['satuan']}, diminta: {$qty} {$prod['satuan']}.");
        }

        $hargaSatuan = (float)$prod['harga_jual'];
        $itemSubtotal = $hargaSatuan * $qty;
        $subtotal += $itemSubtotal;

        $processedItems[] = [
            'id_produk' => $idProduk,
            'nama_produk' => $prod['nama_produk'],
            'barcode' => $prod['barcode'],
            'satuan' => $prod['satuan'],
            'harga_satuan' => $hargaSatuan,
            'jumlah' => $qty,
            'subtotal' => $itemSubtotal
        ];
    }

    // 3. Hitung Promo Diskon
    $total_diskon = 0;
    $promoNama = null;
    if ($id_promo) {
        $today = date('Y-m-d');
        $stmtPromo = $pdo->prepare("SELECT * FROM promo WHERE id = ? AND tanggal_mulai <= ? AND tanggal_selesai >= ?");
        $stmtPromo->execute([$id_promo, $today, $today]);
        $promo = $stmtPromo->fetch();

        if ($promo) {
            $promoNama = $promo['nama_promo'];
            if ($promo['jenis_diskon'] === 'persen') {
                $total_diskon = round($subtotal * ((float)$promo['nilai_diskon'] / 100), 2);
            } else {
                $total_diskon = min($subtotal, (float)$promo['nilai_diskon']);
            }
        } else {
            $id_promo = null; // Promo tidak berlaku/kedaluwarsa
        }
    }

    // 4. Hitung Poin Member
    $memberData = null;
    $potongan_poin = 0;
    $poin_didapat = 0;

    if ($id_member) {
        $stmtMember = $pdo->prepare("SELECT * FROM member WHERE id = ?");
        $stmtMember->execute([$id_member]);
        $memberData = $stmtMember->fetch();

        if (!$memberData) {
            $id_member = null;
            $poin_dipakai = 0;
        } else {
            // Validasi jika poin yang dipakai melebihi saldo member
            if ($poin_dipakai > $memberData['poin']) {
                $poin_dipakai = (int)$memberData['poin'];
            }
            $potongan_poin = $poin_dipakai * POINT_REDEEM_RATE;
        }
    } else {
        $poin_dipakai = 0;
    }

    // 5. Total Akhir
    $total_akhir = $subtotal - $total_diskon - $potongan_poin;
    if ($total_akhir < 0) {
        $total_akhir = 0;
    }

    // Hitung poin yang didapatkan dari transaksi ini
    if ($id_member) {
        $poin_didapat = (int)floor($total_akhir / POINT_EARN_THRESHOLD);
    }

    // 6. Validasi Pembayaran & Kembalian
    if (!in_array($metode_bayar, ['TUNAI', 'QRIS', 'DEBIT', 'TRANSFER'])) {
        $metode_bayar = 'TUNAI';
    }

    if ($metode_bayar === 'TUNAI') {
        if ($jumlah_bayar < $total_akhir) {
            throw new Exception("Uang pembayaran kurang! Total transaksi: " . format_rupiah($total_akhir) . ", dibayar: " . format_rupiah($jumlah_bayar));
        }
        $kembalian = $jumlah_bayar - $total_akhir;
    } else {
        // Non-tunai langsung lunas sesuai nominal
        $jumlah_bayar = $total_akhir;
        $kembalian = 0;
    }

    // 7. Generate Nomor Struk Unik
    $nomor_struk = generate_nomor_struk($pdo);
    $waktu_transaksi = date('Y-m-d H:i:s');
    $status = 'SELESAI';

    // 8. Simpan Transaksi ke Tabel `transaksi`
    $sqlInsertTrx = "
        INSERT INTO transaksi (
            nomor_struk, id_kasir, id_member, id_promo, subtotal, 
            total_diskon, poin_dipakai, poin_didapat, total_akhir, 
            metode_bayar, jumlah_bayar, kembalian, status, waktu_transaksi
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmtTrx = $pdo->prepare($sqlInsertTrx);
    $stmtTrx->execute([
        $nomor_struk,
        $id_kasir,
        $id_member,
        $id_promo,
        $subtotal,
        $total_diskon,
        $poin_dipakai,
        $poin_didapat,
        $total_akhir,
        $metode_bayar,
        $jumlah_bayar,
        $kembalian,
        $status,
        $waktu_transaksi
    ]);

    $idTransaksi = (int)$pdo->lastInsertId();

    // 9. Simpan Detail Transaksi & Kurangi Stok Produk Otomatis
    $stmtDetail = $pdo->prepare("
        INSERT INTO detail_transaksi (id_transaksi, id_produk, harga_satuan, jumlah, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtUpdateStok = $pdo->prepare("
        UPDATE produk SET stok = stok - ? WHERE id = ?
    ");

    foreach ($processedItems as $pItem) {
        // Insert detail transaksi
        $stmtDetail->execute([
            $idTransaksi,
            $pItem['id_produk'],
            $pItem['harga_satuan'],
            $pItem['jumlah'],
            $pItem['subtotal']
        ]);

        // Penyesuaian stok otomatis (Deduction) karena transaksi berstatus SELESAI
        if ($status === 'SELESAI') {
            $stmtUpdateStok->execute([
                $pItem['jumlah'],
                $pItem['id_produk']
            ]);
        }
    }

    // 10. Update Poin Member jika ada
    if ($id_member && ($poin_dipakai > 0 || $poin_didapat > 0)) {
        $stmtUpdatePoin = $pdo->prepare("
            UPDATE member 
            SET poin = poin - ? + ? 
            WHERE id = ?
        ");
        $stmtUpdatePoin->execute([$poin_dipakai, $poin_didapat, $id_member]);
    }

    // Commit Transaction jika seluruh operasi sukses
    $pdo->commit();

    // Respons sukses dengan payload lengkap untuk pencetakan struk
    echo json_encode([
        'success' => true,
        'message' => 'Transaksi berhasil diproses!',
        'data' => [
            'transaksi_id' => $idTransaksi,
            'nomor_struk' => $nomor_struk,
            'waktu_transaksi' => $waktu_transaksi,
            'kasir' => $kasir['nama_pengguna'],
            'member' => $memberData ? [
                'nama' => $memberData['nama_member'],
                'no_telepon' => $memberData['no_telepon'],
                'poin_awal' => $memberData['poin'],
                'poin_dipakai' => $poin_dipakai,
                'poin_didapat' => $poin_didapat,
                'sisa_poin' => ($memberData['poin'] - $poin_dipakai + $poin_didapat)
            ] : null,
            'promo_nama' => $promoNama,
            'subtotal' => $subtotal,
            'total_diskon' => $total_diskon,
            'potongan_poin' => $potongan_poin,
            'poin_dipakai' => $poin_dipakai,
            'poin_didapat' => $poin_didapat,
            'total_akhir' => $total_akhir,
            'metode_bayar' => $metode_bayar,
            'jumlah_bayar' => $jumlah_bayar,
            'kembalian' => $kembalian,
            'items' => $processedItems
        ]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
