<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya user yang login yang bisa akses
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin - Arsip Surat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background-color: #f9fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .navbar-custom { background: linear-gradient(90deg, #4e73df, #1cc88a); }
    .navbar-brand { font-weight: bold; letter-spacing: 1px; color: #fff !important; }
    .nav-link { color: #f8f9fc !important; margin-right: 10px; }
    .nav-link.active, .nav-link:hover { font-weight: bold; color: #fff !important; }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-custom shadow">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Admin Panel</a>

    <!-- 🔹 Tombol Toggle (untuk HP) -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- 🔹 Menu Navbar -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='surat_masuk.php'?'active':'' ?>" href="surat_masuk.php">Surat Masuk</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='surat_keluar.php'?'active':'' ?>" href="surat_keluar.php">Surat Keluar</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='disposisi.php'?'active':'' ?>" href="disposisi.php">Disposisi</a></li>
        <?php if ($_SESSION['role'] == 'admin'): ?>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='users.php'?'active':'' ?>" href="users.php">Manajemen User</a></li>
        <?php endif; ?>
      </ul>

      <!-- 🔹 Dropdown User -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-user-circle"></i> <?= htmlspecialchars($_SESSION['nama']); ?> (<?= ucfirst($_SESSION['role']); ?>)
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="profil.php"><i class="fa fa-user"></i> Profil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">

<!-- 🔹 Script Bootstrap agar dropdown berfungsi -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
