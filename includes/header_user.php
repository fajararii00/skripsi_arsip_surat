<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>User - Arsip Surat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background-color: #f9fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .navbar-custom { background: linear-gradient(90deg, #17a2b8, #28a745); }
    .navbar-brand { font-weight: bold; color: #fff !important; }
    .navbar .nav-link { color: #f8f9fc !important; }
    .navbar .nav-link.active, .navbar .nav-link:hover { font-weight: bold; color: #fff !important; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom shadow">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">User Panel</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='surat_masuk.php'?'active':'' ?>" href="surat_masuk.php">Surat Masuk</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='surat_keluar.php'?'active':'' ?>" href="surat_keluar.php">Surat Keluar</a></li>
      </ul>

      <div class="dropdown">
        <a class="btn btn-outline-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
          <i class="fa fa-user"></i> <?= htmlspecialchars($_SESSION['nama']); ?> (User)
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="profil.php">Profil</a></li>
          <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
