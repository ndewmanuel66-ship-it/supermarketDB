<?php
/**
 * Helper Functions untuk Sistem Supermarket
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Format angka ke format Rupiah Indonesia
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Sanitasi string input untuk mencegah XSS
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message (Bootstrap alert type: success, danger, warning, info)
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Ambil dan bersihkan flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Ambil data kasir yang sedang aktif / login
 * Jika belum login, gunakan kasir default (ID 1 - Super Admin) untuk kemudahan simulasi
 */
function current_user() {
    $pdo = getDbConnection();
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) return $user;
    }

    // Default Fallback User
    $stmt = $pdo->query("SELECT * FROM pengguna ORDER BY id ASC LIMIT 1");
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama_pengguna'] = $user['nama_pengguna'];
        $_SESSION['jabatan'] = $user['jabatan'];
        return $user;
    }

    return [
        'id' => 1,
        'nama_pengguna' => 'Admin Kasir',
        'jabatan' => 'Kasir',
        'username' => 'kasir'
    ];
}

/**
 * Generate nomor struk unik otomatis: TRX-YYYYMMDD-0001
 */
function generate_nomor_struk($pdo) {
    $dateCode = date('Ymd');
    $prefix = "TRX-{$dateCode}-";

    $stmt = $pdo->prepare("SELECT nomor_struk FROM transaksi WHERE nomor_struk LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(["{$prefix}%"]);
    $lastStruk = $stmt->fetchColumn();

    if ($lastStruk) {
        $lastNumber = (int) substr($lastStruk, -4);
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $nextNumber = '0001';
    }

    return $prefix . $nextNumber;
}

/**
 * Render Flash Alert HTML
 */
function render_flash() {
    $flash = get_flash();
    if ($flash) {
        $icon = match($flash['type']) {
            'success' => 'check-circle-fill',
            'danger' => 'exclamation-triangle-fill',
            'warning' => 'exclamation-circle-fill',
            default => 'info-circle-fill'
        };
        echo "
        <div class='alert alert-{$flash['type']} alert-dismissible fade show d-flex align-items-center shadow-sm' role='alert'>
            <i class='bi bi-{$icon} fs-5 me-2'></i>
            <div>{$flash['message']}</div>
            <button type='button' class='btn-close ms-auto' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}
