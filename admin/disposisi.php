<?php
include "../includes/auth.php";
include "../includes/db.php";

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// Query disposisi
if ($_SESSION['role'] == 'admin') {
    $query = "
      SELECT d.*, sm.no_surat, sm.perihal, u1.nama AS pengirim, u2.nama AS penerima
      FROM disposisi d
      JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
      JOIN users u1 ON d.pengirim_id = u1.id
      JOIN users u2 ON d.penerima_id = u2.id
      WHERE sm.no_surat LIKE '%$q%' 
         OR sm.perihal LIKE '%$q%'
         OR u1.nama LIKE '%$q%'
         OR u2.nama LIKE '%$q%'
         OR d.instruksi LIKE '%$q%'
      ORDER BY d.created_at DESC
    ";
} else {
    $user_id = $_SESSION['user_id'];
    $query = "
      SELECT d.*, sm.no_surat, sm.perihal, u1.nama AS pengirim, u2.nama AS penerima
      FROM disposisi d
      JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
      JOIN users u1 ON d.pengirim_id = u1.id
      JOIN users u2 ON d.penerima_id = u2.id
      WHERE d.penerima_id = '$user_id'
        AND (
            sm.no_surat LIKE '%$q%' 
         OR sm.perihal LIKE '%$q%'
         OR u1.nama LIKE '%$q%'
         OR u2.nama LIKE '%$q%'
         OR d.instruksi LIKE '%$q%'
        )
      ORDER BY d.created_at DESC
    ";
}
$disposisi = mysqli_query($conn, $query);
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Daftar Disposisi</h3>

  <!-- Tombol tambah & search -->
  <div class="d-flex justify-content-between mb-3">
    <?php if ($_SESSION['role'] == 'admin'): ?>
      <a href="disposisi_tambah.php" class="btn btn-success btn-sm mb-3">
        <i class="fa fa-plus"></i> Tambah Disposisi
      </a>

    <?php endif; ?>
    <form class="d-flex mb-3" method="get" action="">
      <div class="input-group" style="max-width: 400px;">
        <input type="text" name="q" value="<?= htmlspecialchars($q ?? ""); ?>" 
              class="form-control form-control-sm" 
              placeholder="Cari data...">

        <button class="btn btn-sm btn-primary" type="submit">
          <i class="fa fa-search"></i>
        </button>

          <a href="export.php?type=disposisi" class="btn btn-sm btn-success ms-2">
            <i class="fa fa-download"></i>
          </a>
      </div>
    </form>


  </div>

  <div class="card shadow-sm">
    <div class="card-body">
     <table class="table table-bordered table-striped align-middle">
  <thead class="text-white" style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
    <tr>
      <th>No</th>
      <th>Nomor Surat</th>
      <th>Perihal</th>
      <th>Pengirim</th>
      <th>Penerima</th>
      <th>Tanggal Disposisi</th>
      <th>Instruksi</th>
      <th>Catatan Pimpinan</th> <!-- kolom baru -->
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
        <td><?= htmlspecialchars($row['penerima']); ?></td>
        <td><?= htmlspecialchars($row['tgl_disposisi']); ?></td>
        <td><?= nl2br(htmlspecialchars($row['instruksi'])); ?></td>
        <td><?= nl2br(htmlspecialchars($row['catatan_pimpinan'] ?? '-')); ?></td> <!-- tampilkan catatan -->
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
          <?php if ($_SESSION['role'] == 'admin'): ?>
            <a href="disposisi_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
              <i class="fa fa-edit"></i> Edit
            </a>
            <a href="disposisi_hapus.php?id=<?= $row['id']; ?>" 
               onclick="return confirm('Yakin hapus disposisi ini?');" 
               class="btn btn-sm btn-danger">
              <i class="fa fa-trash"></i> Hapus
            </a>
          <?php else: ?>
            <span class="text-muted">-</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="10" class="text-center text-muted">Tidak ada data disposisi.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
