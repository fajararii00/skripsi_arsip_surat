<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/header.php"; 

// Hitung data statistik
$total_users     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM users"))['jml'];
$total_masuk     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM surat_masuk"))['jml'];
$total_keluar    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM surat_keluar"))['jml'];
$total_disposisi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM disposisi"))['jml'];

// Data grafik surat masuk per bulan (12 bulan terakhir)
$surat_masuk_bulan = [];
$jumlah_surat_masuk = [];
$q1 = mysqli_query($conn, "
  SELECT MONTH(tgl_surat) as bulan, COUNT(*) as jml 
  FROM surat_masuk 
  WHERE YEAR(tgl_surat)=YEAR(CURDATE())
  GROUP BY MONTH(tgl_surat)
");
while($row = mysqli_fetch_assoc($q1)) {
    $surat_masuk_bulan[] = date("M", mktime(0,0,0,$row['bulan'],1));
    $jumlah_surat_masuk[] = $row['jml'];
}

// Data perbandingan surat masuk vs surat keluar
$jml_masuk = $total_masuk;
$jml_keluar = $total_keluar;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - Arsip Surat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background-color: #f9fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .navbar-custom { background: linear-gradient(90deg, #4e73df, #1cc88a); }
    .navbar-brand { font-weight: bold; letter-spacing: 1px; color: #fff !important; }
    .nav-link { color: #f8f9fc !important; margin-right: 10px; }
    .nav-link.active, .nav-link:hover { font-weight: bold; color: #fff !important; }

    .card-custom { border: none; border-radius: 1rem; transition: transform .2s; }
    .card-custom:hover { transform: translateY(-5px); }
    .card-title { font-weight: 600; font-size: 1.05rem; }
    .chart-container { position: relative; height:300px; }

    .bg-users { background: linear-gradient(45deg,#36b9cc,#2c9faf); }
    .bg-masuk { background: linear-gradient(45deg,#1cc88a,#17a673); }
    .bg-keluar { background: linear-gradient(45deg,#f6c23e,#dda20a); }
    .bg-disposisi { background: linear-gradient(45deg,#e74a3b,#be2617); }
  </style>
</head>
<body>

<div class="container mt-4">
  <h2 class="mb-4 fw-bold">Dashboard Admin</h2>

  <!-- Statistik Cards -->
  <div class="row g-4">
    <div class="col-md-3">
      <div class="card card-custom bg-users text-white shadow-sm p-3 text-center">
        <h3><?= $total_users; ?></h3>
        <p class="card-title">User Terdaftar</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-custom bg-masuk text-white shadow-sm p-3 text-center">
        <h3><?= $total_masuk; ?></h3>
        <p class="card-title">Surat Masuk</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-custom bg-keluar text-white shadow-sm p-3 text-center">
        <h3><?= $total_keluar; ?></h3>
        <p class="card-title">Surat Keluar</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-custom bg-disposisi text-white shadow-sm p-3 text-center">
        <h3><?= $total_disposisi; ?></h3>
        <p class="card-title">Disposisi</p>
      </div>
    </div>
  </div>

  <!-- Grafik -->
  <div class="row mt-5 g-4">
    <div class="col-md-6">
      <div class="card card-custom shadow-sm">
        <div class="card-header bg-primary text-white fw-semibold">Statistik Surat Masuk per Bulan</div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="chartSuratMasuk"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card card-custom shadow-sm">
        <div class="card-header bg-success text-white fw-semibold">Perbandingan Surat Masuk & Keluar</div>
        <div class="card-body">
          <div class="chart-container">
            <canvas id="chartPerbandingan"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Ambil data PHP → ke JS
const bulan = <?= json_encode($surat_masuk_bulan); ?>;
const jumlah = <?= json_encode($jumlah_surat_masuk); ?>;

new Chart(document.getElementById('chartSuratMasuk'), {
  type: 'line',
  data: {
    labels: bulan,
    datasets: [{
      label: 'Surat Masuk',
      data: jumlah,
      borderColor: '#4e73df',
      backgroundColor: 'rgba(78,115,223,0.2)',
      fill: true,
      tension: 0.4
    }]
  }
});

new Chart(document.getElementById('chartPerbandingan'), {
  type: 'doughnut',
  data: {
    labels: ['Surat Masuk', 'Surat Keluar'],
    datasets: [{
      data: [<?= $jml_masuk; ?>, <?= $jml_keluar; ?>],
      backgroundColor: ['#1cc88a', '#f6c23e']
    }]
  }
});
</script>

</body>
</html>
