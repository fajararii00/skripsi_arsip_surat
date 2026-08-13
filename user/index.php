<?php
include "../includes/auth.php";
include "../includes/db.php";

// pastikan hanya user
if ($_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$nama = $_SESSION['nama'];

// Hitung surat
$total_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM surat_masuk"))['jml'];
$total_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM surat_keluar WHERE status IN ('terverifikasi','diproses_kasi_pais','selesai')"))['jml'];
?>

<?php include "../includes/header_user.php"; ?>

<div class="container mt-4">
  <h2 class="fw-bold">Dashboard User</h2>
  <p>Selamat datang, <strong><?= htmlspecialchars($nama); ?></strong>.  
     Berikut ringkasan arsip surat:</p>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow-sm bg-info text-white p-4 text-center">
        <h3><?= $total_masuk; ?></h3>
        <p>Surat Masuk</p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm bg-success text-white p-4 text-center">
        <h3><?= $total_keluar; ?></h3>
        <p>Surat Keluar</p>
      </div>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
