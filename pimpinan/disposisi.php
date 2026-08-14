<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// Pastikan hanya pimpinan
if ($_SESSION['role'] != 'pimpinan') {
    header("Location: ../login.php");
    exit;
}

include "../includes/header_pimpinan.php";

$user_id = $_SESSION['user_id'];

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// Query disposisi yang dibuat oleh pimpinan (pengirim)
if ($q) {
    $query = "
      SELECT d.*, sm.no_surat, sm.perihal, sm.pengirim AS pengirim_surat,
             u2.nama AS penerima
      FROM disposisi d
      JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
      LEFT JOIN users u2 ON d.penerima_id = u2.id
      WHERE d.pengirim_id = '$user_id'
        AND (
            sm.no_surat LIKE '%$q%'
            OR sm.perihal LIKE '%$q%'
            OR u2.nama LIKE '%$q%'
            OR d.instruksi LIKE '%$q%'
            OR d.status LIKE '%$q%'
        )
      ORDER BY d.created_at DESC
    ";
} else {
    $query = "
      SELECT d.*, sm.no_surat, sm.perihal, sm.pengirim AS pengirim_surat,
             u2.nama AS penerima
      FROM disposisi d
      JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
      LEFT JOIN users u2 ON d.penerima_id = u2.id
      WHERE d.pengirim_id = '$user_id'
      ORDER BY d.created_at DESC
    ";
}
$disposisi = mysqli_query($conn, $query);
?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Disposisi Saya</h3>

  <!-- Tombol Tambah & Pencarian -->
  <div class="d-flex justify-content-between mb-3">
    <a href="disposisi_tambah.php" class="btn btn-success btn-sm mb-3">
      <i class="fa fa-plus"></i> Tambah Disposisi
    </a>

    <form class="d-flex align-items-center" method="get" action="">
      <input type="text" name="q" value="<?= htmlspecialchars($q); ?>"
             class="form-control form-control-sm me-2"
             placeholder="Cari disposisi..." style="max-width:220px;">
      <button class="btn btn-sm btn-primary me-2" type="submit" title="Cari">
        <i class="fa fa-search"></i>
      </button>
      <a href="pimpinan_export.php?type=disposisi" class="btn btn-sm btn-success">
        <i class="fa fa-download"></i>
      </a>
    </form>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="text-white" style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
          <tr>
            <th>No</th>
            <th>No Surat</th>
            <th>Perihal</th>
            <th>Penerima</th>
            <th>Tanggal</th>
            <th>Instruksi</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($disposisi) > 0): ?>
            <?php $no=1; while($row=mysqli_fetch_assoc($disposisi)): ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['no_surat']); ?></td>
              <td><?= htmlspecialchars($row['perihal']); ?></td>
              <td><?= htmlspecialchars($row['penerima'] ?? '-'); ?></td>
              <td><?= htmlspecialchars($row['tgl_disposisi']); ?></td>
              <td><?= nl2br(htmlspecialchars($row['instruksi'])); ?></td>
              <td>
                <?= statusBadge($row['status']); ?>
              </td>
              <td>
                <a href="../tracking.php?type=disposisi&id=<?= $row['id']; ?>"
                   class="btn btn-sm btn-primary">
                   <i class="fa fa-route"></i> Tracking
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted">Belum ada disposisi yang Anda buat.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>