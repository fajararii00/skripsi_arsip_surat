<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// Pastikan hanya tata usaha
if ($_SESSION['role'] != 'tata_usaha') {
    header("Location: ../login.php");
    exit;
}

include "../includes/header_tata_usaha.php";

$user_id = $_SESSION['user_id'];

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// Query disposisi yang diterima tata usaha
if ($q) {
    $query = "
      SELECT d.*, sm.no_surat, sm.perihal, sm.pengirim AS pengirim_surat,
             u1.nama AS pengirim
      FROM disposisi d
      JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
      LEFT JOIN users u1 ON d.pengirim_id = u1.id
      WHERE d.penerima_id = '$user_id'
        AND (
            sm.no_surat LIKE '%$q%'
            OR sm.perihal LIKE '%$q%'
            OR u1.nama LIKE '%$q%'
            OR d.instruksi LIKE '%$q%'
            OR d.status LIKE '%$q%'
        )
      ORDER BY d.created_at DESC
    ";
} else {
    $query = "
      SELECT d.*, sm.no_surat, sm.perihal, sm.pengirim AS pengirim_surat,
             u1.nama AS pengirim
      FROM disposisi d
      JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
      LEFT JOIN users u1 ON d.pengirim_id = u1.id
      WHERE d.penerima_id = '$user_id'
      ORDER BY d.created_at DESC
    ";
}
$disposisi = mysqli_query($conn, $query);
?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Disposisi Diterima</h3>

  <!-- Form Pencarian -->
  <div class="d-flex justify-content-end mb-3">
    <form class="d-flex align-items-center" method="get" action="">
      <input type="text" name="q" value="<?= htmlspecialchars($q); ?>"
             class="form-control form-control-sm me-2"
             placeholder="Cari disposisi..." style="max-width:220px;">
      <button class="btn btn-sm btn-primary me-2" type="submit" title="Cari">
        <i class="fa fa-search"></i>
      </button>
    </form>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="text-white" style="background: linear-gradient(90deg, #17a2b8, #28a745);">
          <tr>
            <th>No</th>
            <th>No Surat</th>
            <th>Perihal</th>
            <th>Pengirim Disposisi</th>
            <th>Tanggal</th>
            <th>Instruksi</th>
            <th>Catatan Pimpinan</th>
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
              <td><?= htmlspecialchars($row['pengirim'] ?? '-'); ?></td>
              <td><?= htmlspecialchars($row['tgl_disposisi']); ?></td>
              <td><?= nl2br(htmlspecialchars($row['instruksi'])); ?></td>
              <td><?= nl2br(htmlspecialchars($row['catatan_pimpinan'] ?? '-')); ?></td>
              <td>
                <?= statusBadge($row['status']); ?>
              </td>
              <td>
                <a href="../tracking.php?type=disposisi&id=<?= $row['id']; ?>"
                   class="btn btn-sm btn-primary">
                   <i class="fa fa-route"></i> Tracking
                </a>
                <a href="disposisi_update.php?id=<?= $row['id']; ?>"
                   class="btn btn-sm btn-success">
                   <i class="fa fa-edit"></i> Update Status
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center text-muted">Belum ada disposisi yang diterima.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>