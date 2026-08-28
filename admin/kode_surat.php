<?php
include "../includes/auth.php";
include "../includes/db.php";

requireRole(['admin']);

$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

if ($q) {
    $sql = "SELECT * FROM kode_surat WHERE kode LIKE '%$q%' OR nama LIKE '%$q%' ORDER BY kode ASC";
} else {
    $sql = "SELECT * FROM kode_surat ORDER BY kode ASC";
}
$kode_surat = mysqli_query($conn, $sql);
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3"><i class="fa fa-tags"></i> Daftar Kode Surat</h3>

  <div class="d-flex justify-content-between mb-3">
    <a href="kode_surat_tambah.php" class="btn btn-success btn-sm mb-3">
      <i class="fa fa-plus"></i> Tambah Kode Surat
    </a>

    <form class="d-flex align-items-center" method="get" action="">
      <input type="text" name="q" value="<?= htmlspecialchars($q); ?>"
            class="form-control form-control-sm me-2"
            placeholder="Cari kode surat..." style="max-width:220px;">
      <button class="btn btn-sm btn-primary" type="submit">
        <i class="fa fa-search"></i>
      </button>
    </form>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="text-white" style="background: linear-gradient(90deg, #1cc88a, #36b9cc);">
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Jenis Surat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($kode_surat) > 0): ?>
            <?php $no=1; while($row=mysqli_fetch_assoc($kode_surat)): ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><span class="badge bg-info fs-6"><?= htmlspecialchars($row['kode']); ?></span></td>
              <td><?= htmlspecialchars($row['nama']); ?></td>
              <td>
                <a href="kode_surat_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
                  <i class="fa fa-edit"></i> Edit
                </a>
                <a href="kode_surat_hapus.php?id=<?= $row['id']; ?>"
                   onclick="return confirm('Yakin hapus kode surat ini?');"
                   class="btn btn-sm btn-danger">
                  <i class="fa fa-trash"></i> Hapus
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted">Tidak ada data kode surat.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
