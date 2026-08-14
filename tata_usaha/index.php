<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// pastikan hanya tata usaha
if ($_SESSION['role'] != 'tata_usaha') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama    = $_SESSION['nama'];

// Statistik surat
$total_masuk  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM surat_masuk"))['jml'];
$total_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM surat_keluar"))['jml'];

// Statistik disposisi yang diterima
$total_disposisi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM disposisi WHERE penerima_id='$user_id'"))['jml'];
$pending         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM disposisi WHERE penerima_id='$user_id' AND status IN ('draft','menunggu_verifikasi')"))['jml'];
$proses          = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM disposisi WHERE penerima_id='$user_id' AND status IN ('terverifikasi','diproses_tata_usaha')"))['jml'];
$selesai         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM disposisi WHERE penerima_id='$user_id' AND status='selesai'"))['jml'];
?>

<?php include "../includes/header_tata_usaha.php"; ?>

<div class="container mt-4">
  <h2 class="fw-bold mb-4">Dashboard Tata Usaha</h2>
  <p>Selamat datang, <strong><?= htmlspecialchars($nama); ?></strong>.
     Berikut ringkasan surat yang Anda kelola & disposisi yang harus ditindaklanjuti:</p>

  <!-- Statistik Cards -->
  <div class="row g-4 mb-4">
    <div class="col-md-2">
      <div class="card shadow-sm bg-info text-white p-3 text-center">
        <h3><?= $total_masuk; ?></h3>
        <p>Surat Masuk</p>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card shadow-sm bg-primary text-white p-3 text-center">
        <h3><?= $total_keluar; ?></h3>
        <p>Surat Keluar</p>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card shadow-sm bg-secondary text-white p-3 text-center">
        <h3><?= $total_disposisi; ?></h3>
        <p>Total Disposisi</p>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card shadow-sm bg-warning text-dark p-3 text-center">
        <h3><?= $pending; ?></h3>
        <p>Pending</p>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card shadow-sm bg-info text-white p-3 text-center">
        <h3><?= $proses; ?></h3>
        <p>Proses</p>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card shadow-sm bg-success text-white p-3 text-center">
        <h3><?= $selesai; ?></h3>
        <p>Selesai</p>
      </div>
    </div>
  </div>

  <!-- Daftar 5 Disposisi Terbaru yang Diterima -->
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white fw-semibold">
      5 Disposisi Terbaru (Diterima)
    </div>
    <div class="card-body">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>No Surat</th>
            <th>Perihal</th>
            <th>Pengirim</th>
            <th>Tanggal</th>
            <th>Instruksi</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = mysqli_query($conn, "
            SELECT d.*, sm.no_surat, sm.perihal, u1.nama AS pengirim
            FROM disposisi d
            JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
            LEFT JOIN users u1 ON d.pengirim_id = u1.id
            WHERE d.penerima_id='$user_id'
            ORDER BY d.created_at DESC
            LIMIT 5
          ");
          while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['no_surat']); ?></td>
            <td><?= htmlspecialchars($row['perihal']); ?></td>
            <td><?= htmlspecialchars($row['pengirim'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['tgl_disposisi']); ?></td>
            <td><?= nl2br(htmlspecialchars($row['instruksi'])); ?></td>
            <td><?= statusBadge($row['status']); ?></td>
            <td>
              <a href="disposisi_update.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-success">
                <i class="fa fa-edit"></i> Update Status
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <a href="disposisi.php" class="btn btn-sm btn-primary">Lihat Semua</a>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>