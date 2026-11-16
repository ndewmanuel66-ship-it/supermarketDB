<?php
/**
 * API: Pencarian Data Member / Pelanggan
 * Input: phone atau query
 * Output: JSON data member
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$term = trim($_GET['term'] ?? '');
$id = (int)($_GET['id'] ?? 0);

$pdo = getDbConnection();

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM member WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();
        if ($member) {
            echo json_encode(['success' => true, 'data' => $member]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Member tidak ditemukan']);
        }
        exit;
    }

    if (!empty($term)) {
        $stmt = $pdo->prepare("
            SELECT * FROM member 
            WHERE no_telepon LIKE ? OR nama_member LIKE ? 
            LIMIT 10
        ");
        $like = "%{$term}%";
        $stmt->execute([$like, $like]);
        $members = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $members]);
        exit;
    }

    // Ambil daftar member umum
    $stmt = $pdo->query("SELECT * FROM member ORDER BY nama_member ASC LIMIT 20");
    $members = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $members]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
