<?php
/**
 * API: Ambil Rincian Transaksi beserta Detail Item untuk Cetak Ulang Struk
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Transaksi tidak valid']);
    exit;
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare("
        SELECT t.*, p.nama_pengguna as nama_kasir, m.nama_member, pr.nama_promo
        FROM transaksi t
        LEFT JOIN pengguna p ON t.id_kasir = p.id
        LEFT JOIN member m ON t.id_member = m.id
        LEFT JOIN promo pr ON t.id_promo = pr.id
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    $trx = $stmt->fetch();

    if (!$trx) {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
        exit;
    }

    $stmtItems = $pdo->prepare("
        SELECT dt.*, pr.nama_produk, pr.barcode, pr.satuan
        FROM detail_transaksi dt
        JOIN produk pr ON dt.id_produk = pr.id
        WHERE dt.id_transaksi = ?
    ");
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll();

    $trx['items'] = $items;

    echo json_encode(['success' => true, 'data' => $trx]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
