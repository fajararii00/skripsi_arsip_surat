<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// Pastikan hanya pimpinan yang bisa akses
if ($_SESSION['role'] != 'pimpinan') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama    = $_SESSION['nama'];

// Statistik untuk pimpinan
$total_masuk            = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM surat_masuk"))['jml'];
$menunggu_verifikasi    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM surat_masuk WHERE status='menunggu_verifikasi'"))['jml'];
$total_disposisi        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM disposisi WHERE pengirim_id='$user_id'"))['jml'];
$disposisi_aktif        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM disposisi WHERE pengirim_id='$user_id' AND status NOT IN ('selesai')"))['jml'];
?>

<?php include "../includes/header_pimpinan.php"; ?>

<div class="container mt-4">
  <h2 class="fw-bold mb-4">Dashboard Pimpinan</h2>
  <p>Selamat datang, <strong><?= htmlspecialchars($nama); ?></strong>.
     Berikut ringkasan surat yang perlu diverifikasi & disposisi yang telah Anda berikan:</p>

  <!-- Statistik Cards -->
  <div class="row g-4 mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm bg-primary text-white p-3 text-center">
        <h3><?= $total_masuk; ?></h3>
        <p>Total Surat Masuk</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm bg-warning text-dark p-3 text-center">
        <h3><?= $menunggu_verifikasi; ?></h3>
        <p>Menunggu Verifikasi</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm bg-info text-white p-3 text-center">
        <h3><?= $total_disposisi; ?></h3>
        <p>Disposisi Diberikan</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm bg-success text-white p-3 text-center">
        <h3><?= $disposisi_aktif; ?></h3>
        <p>Disposisi Sedang Berjalan</p>
      </div>
    </div>
  </div>

  <!-- Daftar 5 Surat Masuk Menunggu Verifikasi -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-semibold">
      Daftar teratas 5 Surat Masuk Menunggu Verifikasi/Disposisi
    </div>
    <div class="card-body">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>No Surat</th>
            <th>Perihal</th>
            <th>Pengirim</th>
            <th>Tanggal Diterima</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = mysqli_query($conn, "
            SELECT * FROM surat_masuk
            WHERE status = 'menunggu_verifikasi'
            ORDER BY tgl_diterima DESC
            LIMIT 5
          ");
          while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['no_surat']); ?></td>
            <td><?= htmlspecialchars($row['perihal']); ?></td>
            <td><?= htmlspecialchars($row['pengirim']); ?></td>
            <td><?= htmlspecialchars($row['tgl_diterima']); ?></td>
            <td><?= statusBadge($row['status']); ?></td>
            <td>
              <a href="disposisi_tambah.php?surat_id=<?= $row['id']; ?>" class="btn btn-sm btn-success">
                <i class="fa fa-paper-plane"></i> Beri Disposisi
              </a>
              <a href="../tracking.php?type=surat_masuk&id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">
                <i class="fa fa-route"></i> Tracking
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <a href="surat_masuk.php" class="btn btn-sm btn-primary">Lihat Semua</a>
    </div>
  </div>

  <!-- Daftar 5 Disposisi Terbaru yang Diberikan -->
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white fw-semibold">
      Daftar teratas 5 Disposisi Terbaru (Diberikan)
    </div>
    <div class="card-body">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>No Surat</th>
            <th>Perihal</th>
            <th>Penerima</th>
            <th>Tanggal</th>
            <th>Instruksi</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = mysqli_query($conn, "
            SELECT d.*, sm.no_surat, sm.perihal, u2.nama AS penerima
            FROM disposisi d
            JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
            LEFT JOIN users u2 ON d.penerima_id = u2.id
            WHERE d.pengirim_id='$user_id'
            ORDER BY d.created_at DESC
            LIMIT 5
          ");
          while($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['no_surat']); ?></td>
            <td><?= htmlspecialchars($row['perihal']); ?></td>
            <td><?= htmlspecialchars($row['penerima'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['tgl_disposisi']); ?></td>
            <td><?= nl2br(htmlspecialchars($row['instruksi'])); ?></td>
            <td><?= statusBadge($row['status']); ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <a href="disposisi.php" class="btn btn-sm btn-primary">Lihat Semua</a>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>