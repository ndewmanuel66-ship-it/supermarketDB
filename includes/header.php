<?php
/**
 * Layout Header Sistem Supermarket
 */
require_once __DIR__ . '/functions.php';
$activeUser = current_user();

// Tentukan base URL relatif
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$depth = substr_count(trim($scriptName, '/'), '/');
$baseUrl = ($depth > 1) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Supermarket POS</title>
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Custom Modern CSS -->
    <style>
        :root {
            --pos-primary: #0d6efd;
            --pos-primary-dark: #0a58ca;
            --pos-success: #198754;
            --pos-bg: #f4f6f9;
            --pos-card-border: #e2e8f0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--pos-bg);
            color: #2b3445;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand-badge {
            font-size: 0.68rem;
            padding: 3px 8px;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .main-navbar {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            border-bottom: 1px solid #edf2f7;
        }
        .nav-link {
            font-weight: 500;
            color: #4b566b;
            padding: 0.5rem 0.9rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--pos-primary);
            background-color: #f0f7ff;
        }
        .btn-pos-launch {
            background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
            border: none;
            color: white !important;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn-pos-launch:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(13, 110, 253, 0.35);
        }
        .card-custom {
            border: 1px solid var(--pos-card-border);
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
            background: #ffffff;
            transition: all 0.2s ease;
        }
        .badge-subtle-primary {
            background-color: #e0edff;
            color: #0d6efd;
            font-weight: 600;
        }
        .badge-subtle-success {
            background-color: #d1f4e0;
            color: #0f6838;
            font-weight: 600;
        }
        .badge-subtle-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            font-weight: 600;
        }
        .badge-subtle-warning {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: 600;
        }
        .table-custom th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding-top: 12px;
            padding-bottom: 12px;
        }
        .table-custom td {
            vertical-align: middle;
            padding-top: 12px;
            padding-bottom: 12px;
        }
        /* POS specific styles */
        .pos-product-card {
            cursor: pointer;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            transition: all 0.15s ease;
            background: #ffffff;
        }
        .pos-product-card:hover {
            border-color: #0d6efd;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(13,110,253,0.12);
        }
        .receipt-container {
            font-family: 'Courier New', Courier, monospace;
            background: #fff;
            padding: 20px;
            border: 1px dashed #bbb;
            max-width: 360px;
            margin: auto;
            color: #111;
        }
    </style>
</head>
<body>

<!-- Navbar Utama -->
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container-fluid px-lg-4">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary" href="<?= $baseUrl ?>index.php">
            <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2" style="width: 36px; height: 36px;">
                <i class="bi bi-cart3 fs-5"></i>
            </span>
            <span class="fs-5 tracking-tight">SUPER<span class="text-dark">MARKET</span></span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle navbar-brand-badge rounded-pill">POS PRO</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-display="static" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php' && $depth <= 1) ? 'active' : '' ?>" href="<?= $baseUrl ?>index.php">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </li>
                
                <!-- Dropdown Master Data -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= (strpos($_SERVER['PHP_SELF'], 'produk') !== false || strpos($_SERVER['PHP_SELF'], 'kategori') !== false) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-folder2-open me-1"></i> Data Master
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm rounded-3 mt-2">
                        <li>
                            <a class="dropdown-item py-2" href="<?= $baseUrl ?>produk/index.php">
                                <i class="bi bi-box-seam me-2 text-primary"></i> Data Produk
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= $baseUrl ?>kategori/index.php">
                                <i class="bi bi-tags me-2 text-primary"></i> Data Kategori
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-muted small" href="<?= $baseUrl ?>setup.php">
                                <i class="bi bi-database-fill-gear me-2"></i> Database & Migrasi
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'riwayat.php') ? 'active' : '' ?>" href="<?= $baseUrl ?>riwayat.php">
                        <i class="bi bi-receipt me-1"></i> Riwayat Transaksi
                    </a>
                </li>
            </ul>

            <!-- Tombol POS & Kasir Info -->
            <div class="d-flex align-items-center gap-3">
                <a href="<?= $baseUrl ?>kasir.php" class="btn btn-pos-launch rounded-pill px-3 py-2 d-flex align-items-center gap-2">
                    <i class="bi bi-upc-scan fs-6"></i>
                    <span>Buka Mesin Kasir (POS)</span>
                </a>

                <div class="vr d-none d-lg-block my-1 text-secondary opacity-25"></div>

                <div class="d-flex align-items-center gap-2">
                    <div class="bg-light rounded-circle p-2 text-primary border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <div class="d-none d-sm-block text-start leading-tight">
                        <div class="fw-semibold small text-dark"><?= htmlspecialchars($activeUser['nama_pengguna'] ?? 'Kasir') ?></div>
                        <span class="badge bg-secondary-subtle text-secondary py-0 px-2 rounded-pill" style="font-size: 0.7rem;">
                            <?= htmlspecialchars($activeUser['jabatan'] ?? 'Kasir') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Container Konten Utama -->
<main class="flex-grow-1 py-4">
    <div class="container-fluid px-lg-4">
        <?php render_flash(); ?>
