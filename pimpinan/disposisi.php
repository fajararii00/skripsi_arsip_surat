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

// Ambil kata kunci pencarian & filter status
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";

// Query disposisi yang dibuat oleh pimpinan (pengirim)
$where = "d.pengirim_id = '$user_id'";
if ($q) {
    $where .= " AND (sm.no_surat LIKE '%$q%'
                 OR sm.perihal LIKE '%$q%'
                 OR u2.nama LIKE '%$q%'
                 OR d.instruksi LIKE '%$q%'
                 OR d.status LIKE '%$q%')";
}
if ($status) {
    $where .= " AND d.status='$status'";
}
$query = "
  SELECT d.*, sm.no_surat, sm.perihal, sm.pengirim AS pengirim_surat,
         u2.nama AS penerima
  FROM disposisi d
  JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
  LEFT JOIN users u2 ON d.penerima_id = u2.id
  WHERE $where
  ORDER BY d.created_at DESC
";
$disposisi = mysqli_query($conn, $query);
?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Disposisi Saya</h3>

  <!-- Tombol Tambah, Filter & Pencarian -->
  <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
    <a href="disposisi_tambah.php" class="btn btn-success btn-sm mb-3">
      <i class="fa fa-plus"></i> Tambah Disposisi
    </a>

    <form class="d-flex align-items-center" method="get" action="">
      <select name="status" class="form-select form-select-sm me-2" style="max-width: 200px;" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <?php foreach (getStatusSteps() as $key => $label): ?>
          <option value="<?= $key; ?>" <?= $status == $key ? 'selected' : ''; ?>><?= $label; ?></option>
        <?php endforeach; ?>
      </select>
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

  <?php if ($status): ?>
    <div class="mb-2">
      <span class="badge bg-info">
        Filter: <?= htmlspecialchars(getStatusLabel($status)); ?>
        <a href="disposisi.php" class="text-white text-decoration-none ms-1"><i class="fa fa-xmark"></i></a>
      </span>
    </div>
  <?php endif; ?>

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