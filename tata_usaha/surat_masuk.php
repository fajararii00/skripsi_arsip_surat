<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

if ($_SESSION['role'] != 'tata_usaha') {
    header("Location: ../login.php");
    exit;
}

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

if ($q) {
    $sql = "SELECT * FROM surat_masuk 
            WHERE no_surat LIKE '%$q%' 
               OR pengirim LIKE '%$q%' 
               OR perihal LIKE '%$q%' 
               OR instansi LIKE '%$q%' 
               OR kategori LIKE '%$q%'
            ORDER BY tgl_surat DESC";
} else {
    $sql = "SELECT * FROM surat_masuk ORDER BY tgl_surat DESC";
}
$surat = mysqli_query($conn, $sql);
?>

<?php include "../includes/header_tata_usaha.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Daftar Surat Masuk</h3>

  <!-- Tombol Tambah & Pencarian -->
  <div class="d-flex justify-content-between mb-3">
    <a href="surat_masuk_tambah.php" class="btn btn-success btn-sm mb-3">
      <i class="fa fa-plus"></i> Tambah Surat Masuk
    </a>

    <form class="d-flex mb-3" method="get" action="">
      <div class="input-group" style="max-width: 400px;">
        <input type="text" name="q" value="<?= htmlspecialchars($q); ?>"
              class="form-control form-control-sm"
              placeholder="Cari surat masuk...">
        <button class="btn btn-sm btn-primary" type="submit">
          <i class="fa fa-search"></i>
        </button>
      </div>
    </form>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="text-white" style="background: linear-gradient(90deg, #17a2b8, #28a745);">
          <tr>
            <th>No</th>
            <th>No Surat</th>
            <th>Tanggal Surat</th>
            <th>Tanggal Diterima</th>
            <th>Pengirim</th>
            <th>Instansi</th>
            <th>Kategori</th>
            <th>Perihal</th>
            <th>Status</th>
            <th>File</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($surat) > 0): ?>
            <?php $no=1; while($row=mysqli_fetch_assoc($surat)): ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['no_surat']); ?></td>
              <td><?= htmlspecialchars($row['tgl_surat']); ?></td>
              <td><?= htmlspecialchars($row['tgl_diterima']); ?></td>
              <td><?= htmlspecialchars($row['pengirim']); ?></td>
              <td><?= htmlspecialchars($row['instansi']); ?></td>
              <td><span class="badge bg-info"><?= htmlspecialchars($row['kategori']); ?></span></td>
              <td><?= htmlspecialchars($row['perihal']); ?></td>
              <td><?= statusBadge($row['status']); ?></td>
              <td>
                <?php if($row['file_surat']): ?>
                  <a href="../assets/uploads/surat_masuk/<?= htmlspecialchars($row['file_surat']); ?>"
                     class="btn btn-info btn-sm" target="_blank">Lihat</a>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="../tracking.php?type=surat_masuk&id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">
                  <i class="fa fa-route"></i> Tracking
                </a>
                <a href="surat_masuk_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
                  <i class="fa fa-edit"></i> Edit
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="11" class="text-center text-muted">Tidak ada data surat masuk.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>