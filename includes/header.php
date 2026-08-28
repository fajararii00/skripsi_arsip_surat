<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya user yang login yang bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!function_exists('getRoleLabel')) {
    include "auth.php";
}

// Basis URL aplikasi (agar link navbar benar dari halaman di folder maupun di root)
$app_root = str_replace('\\', '/', dirname(dirname(__FILE__)));
$base_url = rtrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $app_root), '/');

$nama_user = $_SESSION['nama'];
$inisial   = strtoupper(substr(trim($nama_user), 0, 1));
$label     = getRoleLabel($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin - Arsip Surat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
  <style>
    body { background-color: #f4f6fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .navbar-custom {
      background: linear-gradient(90deg, #4e73df 0%, #2e59d9 50%, #1cc88a 100%);
      box-shadow: 0 2px 12px rgba(30, 60, 114, .18);
    }
    .navbar-custom .navbar-brand { color: #fff !important; }
    .navbar-custom .navbar-brand .brand-sub { color: rgba(255,255,255,.75); }
    .navbar-custom .nav-link {
      color: #f8f9fc !important;
      font-weight: 500;
      padding: .5rem .8rem;
      margin-right: .2rem;
      border-radius: .5rem;
      transition: background-color .2s ease, color .2s ease;
    }
    .navbar-custom .nav-link i { margin-right: .4rem; width: 1rem; text-align: center; }
    .navbar-custom .nav-link.active { background: rgba(255,255,255,.22); color: #fff !important; font-weight: 600; }
    .navbar-custom .nav-link:hover { background: rgba(255,255,255,.14); color: #fff !important; }
    .avatar { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
              background: #fff; color: #4e73df; font-weight: 700; font-size: 1rem; border-radius: 50%; }
    .dropdown-menu { border: none; border-radius: .75rem; box-shadow: 0 8px 24px rgba(0,0,0,.15); }
    .dropdown-menu .dropdown-header { color: #4e73df; font-weight: 600; }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="<?= $base_url ?>/admin/index.php">
      <img src="<?= $base_url ?>/images.jpg" alt="Logo" width="36" height="36" class="rounded-circle me-2 border border-2 border-white shadow-sm">
      <span class="lh-1">
        <span class="d-block fw-bold">Arsip Surat</span>
        <small class="brand-sub d-block" style="font-size:.68rem;">DISDIKBUD Muaro Jambi</small>
      </span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>" href="<?= $base_url ?>/admin/index.php"><i class="fa fa-gauge-high"></i>Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='surat_masuk.php'?'active':'' ?>" href="<?= $base_url ?>/admin/surat_masuk.php"><i class="fa fa-inbox"></i>Surat Masuk</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='surat_keluar.php'?'active':'' ?>" href="<?= $base_url ?>/admin/surat_keluar.php"><i class="fa fa-paper-plane"></i>Surat Keluar</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='disposisi.php'?'active':'' ?>" href="<?= $base_url ?>/admin/disposisi.php"><i class="fa fa-tasks"></i>Disposisi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='laporan.php'?'active':'' ?>" href="<?= $base_url ?>/admin/laporan.php"><i class="fa fa-chart-pie"></i>Laporan</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='kode_surat.php'?'active':'' ?>" href="<?= $base_url ?>/admin/kode_surat.php"><i class="fa fa-tags"></i>Kode Surat</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='users.php'?'active':'' ?>" href="<?= $base_url ?>/admin/users.php"><i class="fa fa-users"></i>Manajemen User</a>
        </li>
      </ul>

      <ul class="navbar-nav ms-lg-3">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="avatar"><?= htmlspecialchars($inisial); ?></span>
            <span><?= htmlspecialchars($nama_user); ?> <small class="text-white-50">(<?= $label; ?>)</small></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><h6 class="dropdown-header"><i class="fa fa-circle-user me-1"></i><?= htmlspecialchars($nama_user); ?></h6></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= $base_url ?>/admin/profil.php"><i class="fa fa-user me-2"></i>Profil</a></li>
            <li><a class="dropdown-item text-danger" href="<?= $base_url ?>/logout.php"><i class="fa fa-right-from-bracket me-2"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
