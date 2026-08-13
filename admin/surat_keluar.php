<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// Query surat keluar
if ($q) {
    $sql = "SELECT * FROM surat_keluar 
            WHERE no_agenda LIKE '%$q%' 
               OR no_surat LIKE '%$q%' 
               OR tujuan LIKE '%$q%' 
               OR instansi LIKE '%$q%' 
               OR kategori LIKE '%$q%' 
               OR perihal LIKE '%$q%'
            ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM surat_keluar ORDER BY created_at DESC";
}
$surat = mysqli_query($conn, $sql);
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Daftar Surat Keluar</h3>

  <!-- Tombol tambah + form pencarian -->
  <div class="d-flex justify-content-between mb-3">
    <a href="surat_keluar_tambah.php" class="btn btn-success btn-sm mb-3">
      <i class="fa fa-plus"></i> Tambah Surat Keluar
    </a>

  <form class="d-flex align-items-center" method="get" action="">
    <input type="text" name="q" value="<?= htmlspecialchars($q); ?>" 
          class="form-control form-control-sm me-2" 
          placeholder="Cari surat masuk..." style="max-width:220px;">
    
    <button class="btn btn-sm btn-primary me-2" type="submit">
      <i class="fa fa-search"></i>
    </button>
    
      <a href="export.php?type=surat_keluar" class="btn btn-sm btn-success ">
      <i class="fa fa-download"></i>
      </a>
  </form>

  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="text-white" style="background: linear-gradient(90deg, #1cc88a, #36b9cc);">
          <tr>
            <th>No</th>
            <th>No Agenda</th>
            <th>No Surat</th>
            <th>Tgl Surat</th>
            <th>Tujuan</th>
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
              <td><?= htmlspecialchars($row['no_agenda']); ?></td>
              <td><?= htmlspecialchars($row['no_surat']); ?></td>
              <td><?= htmlspecialchars($row['tgl_surat']); ?></td>
              <td><?= htmlspecialchars($row['tujuan']); ?></td>
              <td><?= htmlspecialchars($row['instansi']); ?></td>
              <td><span class="badge bg-info"><?= htmlspecialchars($row['kategori']); ?></span></td>
              <td><?= htmlspecialchars($row['perihal']); ?></td>
              <td>
                <?= statusBadge($row['status']); ?>
              </td>
              <td>
                <?php if($row['file_surat']): ?>
                  <a href="../assets/uploads/surat_keluar/<?= htmlspecialchars($row['file_surat']); ?>" 
                     class="btn btn-info btn-sm" target="_blank">Lihat</a>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="../tracking.php?type=surat_keluar&id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">
                  <i class="fa fa-route"></i> Tracking
                </a>
                <a href="surat_keluar_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
                  <i class="fa fa-edit"></i> Edit
                </a>
                <a href="surat_keluar_hapus.php?id=<?= $row['id']; ?>" 
                   onclick="return confirm('Yakin hapus surat ini?');" 
                   class="btn btn-sm btn-danger">
                  <i class="fa fa-trash"></i> Hapus
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="11" class="text-center text-muted">Tidak ada data surat keluar.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
