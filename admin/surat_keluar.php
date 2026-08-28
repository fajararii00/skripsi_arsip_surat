<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// Ambil kata kunci pencarian
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";

// Query surat keluar
if ($q) {
    $sql = "SELECT sk.*, ks.kode AS kode_surat_kode, ks.nama AS jenis_surat
            FROM surat_keluar sk
            LEFT JOIN kode_surat ks ON sk.kode_surat_id = ks.id
            WHERE sk.no_agenda LIKE '%$q%'
               OR sk.no_surat LIKE '%$q%'
               OR sk.tujuan LIKE '%$q%'
               OR sk.instansi LIKE '%$q%'
               OR sk.kategori LIKE '%$q%'
               OR sk.perihal LIKE '%$q%'
            ORDER BY sk.created_at DESC";
} else {
    $sql = "SELECT sk.*, ks.kode AS kode_surat_kode, ks.nama AS jenis_surat
            FROM surat_keluar sk
            LEFT JOIN kode_surat ks ON sk.kode_surat_id = ks.id
            ORDER BY sk.created_at DESC";
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
            placeholder="Cari surat keluar..." style="max-width:220px;">

      <button class="btn btn-sm btn-primary me-2" type="submit">
        <i class="fa fa-search"></i>
      </button>

      <a href="export.php?type=surat_keluar" class="btn btn-sm btn-success">
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
            <th>Kode Surat</th>
            <th>Tgl Surat</th>
            <th>Tujuan</th>
            <th>Instansi</th>
            <th>Perihal</th>
            <th>Status</th>
            <th>QR</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($surat) > 0): ?>
            <?php $no=1; while($row=mysqli_fetch_assoc($surat)): ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['no_surat']); ?></td>
              <td>
                <?php if($row['kode_surat_kode']): ?>
                  <span class="badge bg-info"><?= htmlspecialchars($row['kode_surat_kode']); ?></span>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['tgl_surat']); ?></td>
              <td><?= htmlspecialchars($row['tujuan']); ?></td>
              <td><?= htmlspecialchars($row['instansi']); ?></td>
              <td><?= htmlspecialchars($row['perihal']); ?></td>
              <td>
                <?= statusBadge($row['status']); ?>
              </td>
              <td class="text-center">
                <?php if($row['qr_code']): ?>
                  <a href="../assets/uploads/qr_codes/<?= htmlspecialchars($row['qr_code']); ?>" target="_blank" title="Lihat QR Code">
                    <img src="../assets/uploads/qr_codes/<?= htmlspecialchars($row['qr_code']); ?>" alt="QR" width="40" height="40" style="border-radius:4px;">
                  </a>
                <?php else: ?>
                  <button class="btn btn-sm btn-outline-primary generate-qr" data-id="<?= $row['id']; ?>" title="Generate QR Code">
                    <i class="fa fa-qrcode"></i>
                  </button>
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
              <td colspan="10" class="text-center text-muted">Tidak ada data surat keluar.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.generate-qr').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var btnEl = this;
        btnEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        btnEl.disabled = true;

        fetch('../generate_qr.php?id=' + id)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var td = btnEl.parentElement;
                    td.innerHTML = '<a href="../assets/uploads/qr_codes/' + data.qr + '" target="_blank" title="Lihat QR Code"><img src="../assets/uploads/qr_codes/' + data.qr + '" alt="QR" width="40" height="40" style="border-radius:4px;"></a>';
                } else {
                    btnEl.innerHTML = '<i class="fa fa-qrcode"></i> Gagal';
                    btnEl.disabled = false;
                }
            })
            .catch(function() {
                btnEl.innerHTML = '<i class="fa fa-qrcode"></i> Error';
                btnEl.disabled = false;
            });
    });
});
</script>

<?php include "../includes/footer.php"; ?>
