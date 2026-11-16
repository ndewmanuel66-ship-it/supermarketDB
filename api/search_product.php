<?php
/**
 * API: Pencarian Produk untuk Kasir (POS)
 * Input: query (bisa teks nama atau barcode)
 * Output: JSON list produk yang stoknya tersedia atau status
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$query = trim($_GET['query'] ?? '');
$exactBarcode = trim($_GET['barcode'] ?? '');

$pdo = getDbConnection();

try {
    if (!empty($exactBarcode)) {
        // Cari persis sesuai barcode (cocok untuk scanner tembak)
        $stmt = $pdo->prepare("
            SELECT p.*, k.nama_kategori 
            FROM produk p 
            LEFT JOIN kategori k ON p.id_kategori = k.id 
            WHERE p.barcode = ?
            LIMIT 1
        ");
        $stmt->execute([$exactBarcode]);
        $product = $stmt->fetch();

        if ($product) {
            echo json_encode([
                'success' => true,
                'type' => 'exact',
                'data' => $product
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Produk dengan barcode tersebut tidak ditemukan!'
            ]);
        }
        exit;
    }

    // Pencarian fleksibel berdasarkan nama atau barcode
    if (empty($query)) {
        // Ambil 12 produk teratas untuk grid kasir default
        $stmt = $pdo->query("
            SELECT p.*, k.nama_kategori 
            FROM produk p 
            LEFT JOIN kategori k ON p.id_kategori = k.id 
            ORDER BY p.id ASC 
            LIMIT 16
        ");
        $products = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("
            SELECT p.*, k.nama_kategori 
            FROM produk p 
            LEFT JOIN kategori k ON p.id_kategori = k.id 
            WHERE p.barcode LIKE ? OR p.nama_produk LIKE ? OR k.nama_kategori LIKE ?
            ORDER BY p.nama_produk ASC 
            LIMIT 20
        ");
        $term = "%{$query}%";
        $stmt->execute([$term, $term, $term]);
        $products = $stmt->fetchAll();
    }

    echo json_encode([
        'success' => true,
        'type' => 'list',
        'data' => $products
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
