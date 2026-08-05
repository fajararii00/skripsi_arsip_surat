<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/header_staf.php";

// Pastikan hanya staf
if ($_SESSION['role'] != 'staf') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// Query disposisi khusus staf
if ($q) {
    $query = "
      SELECT d.*, sm.no_surat, sm.perihal, sm.pengirim AS pengirim_surat,
             u1.nama AS pengirim
      FROM disposisi d
      JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
      JOIN users u1 ON d.pengirim_id = u1.id
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
      JOIN users u1 ON d.pengirim_id = u1.id
      WHERE d.penerima_id = '$user_id'
      ORDER BY d.created_at DESC
    ";
}
$disposisi = mysqli_query($conn, $query);
?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Disposisi Saya</h3>

  <!-- Form Pencarian & Unduh -->
  <div class="d-flex justify-content-end mb-3">
    <form class="d-flex align-items-center" method="get" action="">
      <input type="text" name="q" value="<?= htmlspecialchars($q); ?>" 
             class="form-control form-control-sm me-2" 
             placeholder="Cari disposisi..." style="max-width:220px;">

      <!-- Tombol Cari -->
      <button class="btn btn-sm btn-primary me-2" type="submit" title="Cari">
        <i class="fa fa-search"></i>
      </button>

      <!-- Tombol Unduh -->
      <a href="staf_export.php?type=disposisi" class="btn btn-sm btn-success">
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
            <th>Pengirim Disposisi</th>
            <th>Catatan</th>
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
              <td><?= htmlspecialchars($row['pengirim']); ?></td>
              <td><?= nl2br(htmlspecialchars($row['instruksi'])); ?></td>
              <td>
                <?php if($row['status'] == 'pending'): ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif($row['status'] == 'proses'): ?>
                  <span class="badge bg-primary">Proses</span>
                <?php else: ?>
                  <span class="badge bg-success">Selesai</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="disposisi_update.php?id=<?= $row['id']; ?>" 
                   class="btn btn-sm btn-success">
                   <i class="fa fa-edit"></i> Update Status
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center text-muted">Tidak ada data disposisi.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
