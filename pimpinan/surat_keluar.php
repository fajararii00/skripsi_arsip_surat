<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// pastikan hanya staf yang bisa akses
if ($_SESSION['role'] != 'pimpinan') {
    header("Location: ../login.php");
    exit;
}

include "../includes/header_pimpinan.php"; // navbar pimpinan

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// Query data surat keluar
if ($q) {
    $sql = "SELECT * FROM surat_keluar 
            WHERE no_surat LIKE '%$q%' 
               OR tujuan LIKE '%$q%' 
               OR instansi LIKE '%$q%' 
               OR kategori LIKE '%$q%' 
               OR perihal LIKE '%$q%'
            ORDER BY tgl_surat DESC";
} else {
    $sql = "SELECT * FROM surat_keluar ORDER BY tgl_surat DESC";
}
$surat = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Daftar Surat Keluar</h3>

  <!-- Form Pencarian & Unduh -->
  <div class="d-flex justify-content-end mb-3">
    <form class="d-flex align-items-center" method="get" action="">
      <input type="text" name="q" value="<?= htmlspecialchars($q); ?>" 
             class="form-control form-control-sm me-2" 
             placeholder="Cari surat keluar..." style="max-width:220px;">

      <!-- Tombol Cari -->
      <button class="btn btn-sm btn-primary me-2" type="submit" title="Cari">
        <i class="fa fa-search"></i>
      </button>

      <!-- Tombol Unduh CSV -->
      <a href="pimpinan_export.php?type=surat_keluar" class="btn btn-sm btn-success">
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
            <th>No Surat</th>
            <th>Tanggal Surat</th>
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
              <td><?= htmlspecialchars($row['no_surat']); ?></td>
              <td><?= htmlspecialchars($row['tgl_surat']); ?></td>
              <td><?= htmlspecialchars($row['tujuan']); ?></td>
              <td><?= htmlspecialchars($row['instansi']); ?></td>
              <td><?= htmlspecialchars($row['kategori']); ?></td>
              <td><?= htmlspecialchars($row['perihal']); ?></td>
              <td><?= statusBadge($row['status']); ?></td>
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
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-center text-muted">Tidak ada data surat keluar.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
