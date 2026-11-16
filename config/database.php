<?php
/**
 * Konfigurasi Koneksi Database Supermarket
 * Menggunakan PDO (PHP Data Objects) untuk keamanan (Prepared Statements) & efisiensi
 */

// Konfigurasi session lokal agar tidak terjadi permission denied di direktori default C:\xampp\tmp
$sessionSavePath = __DIR__ . '/../sessions';
if (!is_dir($sessionSavePath)) {
    @mkdir($sessionSavePath, 0777, true);
}
if (is_dir($sessionSavePath) && is_writable($sessionSavePath)) {
    @session_save_path($sessionSavePath);
}
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Konfigurasi Database MySQL
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'super');
define('DB_USER', 'root');
define('DB_PASS', '');

// Opsi Driver: 'mysql' (utama) atau 'sqlite' (otomatis fallback)
define('DB_DRIVER', 'mysql'); 

function getDbConnection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $driver = DB_DRIVER;

    if ($driver === 'mysql') {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
        } catch (PDOException $e) {
            // Cek apakah database belum ada
            if ($e->getCode() == 1049) {
                die("<div style='font-family:sans-serif;padding:20px;max-width:600px;margin:40px auto;border:1px solid #f5c6cb;background:#f8d7da;color:#721c24;border-radius:8px;'>"
                    . "<h3>Database Belum Ditemukan</h3>"
                    . "<p>Database <strong>" . htmlspecialchars(DB_NAME) . "</strong> belum dibuat di MySQL.</p>"
                    . "<p>Silakan buka file <a href='setup.php' style='color:#0056b3;font-weight:bold;'>setup.php</a> untuk inisialisasi database secara otomatis.</p>"
                    . "</div>");
            }

            // Jika MySQL gagal dihubungkan, cek apakah ada database sqlite lokal
            $sqlitePath = __DIR__ . '/../supermarket.sqlite';
            if (file_exists($sqlitePath)) {
                $pdo = new PDO("sqlite:" . $sqlitePath);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return $pdo;
            }

            // Tampilkan pesan error informatif jika MySQL belum berjalan
            die("<div style='font-family:sans-serif;padding:25px;max-width:680px;margin:40px auto;border:1px solid #dc3545;background:#fff5f5;color:#333;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1);'>"
                . "<h2 style='color:#dc3545;margin-top:0;'>Gagal Terhubung ke Database MySQL</h2>"
                . "<p>Error: <code>" . htmlspecialchars($e->getMessage()) . "</code></p>"
                . "<hr style='border:none;border-top:1px solid #ddd;margin:20px 0;'>"
                . "<h4>Langkah Penyelesaian:</h4>"
                . "<ol style='line-height:1.6;'>"
                . "<li>Pastikan service <strong>MySQL / XAMPP / Laragon</strong> sudah berjalan.</li>"
                . "<li>Pastikan konfigurasi di <code>config/database.php</code> sudah sesuai (Host: " . DB_HOST . ", User: " . DB_USER . ").</li>"
                . "<li>Atau jalankan skrip <a href='setup.php' style='color:#0d6efd;font-weight:bold;'>setup.php</a> untuk inisialisasi database.</li>"
                . "</ol>"
                . "</div>");
        }
    } else {
        // Driver SQLite
        $sqlitePath = __DIR__ . '/../supermarket.sqlite';
        $pdo = new PDO("sqlite:" . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }
}
