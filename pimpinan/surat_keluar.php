<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// pastikan hanya pimpinan yang bisa akses
if ($_SESSION['role'] != 'pimpinan') {
    header("Location: ../login.php");
    exit;
}

include "../includes/header_pimpinan.php";

// Ambil kata kunci pencarian & filter status
$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "";
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : "";

$where = "1=1";
if ($q) {
    $where .= " AND (sk.no_surat LIKE '%$q%'
                 OR sk.tujuan LIKE '%$q%'
                 OR sk.instansi LIKE '%$q%'
                 OR sk.kategori LIKE '%$q%'
                 OR sk.perihal LIKE '%$q%')";
}
if ($status) {
    $where .= " AND sk.status='$status'";
}
$surat = mysqli_query($conn, "
    SELECT sk.*, ks.kode AS kode_surat_kode, ks.nama AS jenis_surat
    FROM surat_keluar sk
    LEFT JOIN kode_surat ks ON sk.kode_surat_id = ks.id
    WHERE $where
    ORDER BY sk.tgl_surat DESC
");
?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Daftar Surat Keluar</h3>

  <!-- Form Pencarian, Filter & Unduh -->
  <div class="d-flex justify-content-end flex-wrap gap-2 mb-3">
    <form class="d-flex align-items-center" method="get" action="">
      <select name="status" class="form-select form-select-sm me-2" style="max-width: 200px;" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <?php foreach (getStatusSteps() as $key => $label): ?>
          <option value="<?= $key; ?>" <?= $status == $key ? 'selected' : ''; ?>><?= $label; ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="q" value="<?= htmlspecialchars($q); ?>"
             class="form-control form-control-sm me-2"
             placeholder="Cari surat keluar..." style="max-width:220px;">

      <button class="btn btn-sm btn-primary me-2" type="submit" title="Cari">
        <i class="fa fa-search"></i>
      </button>

      <a href="pimpinan_export.php?type=surat_keluar" class="btn btn-sm btn-success">
        <i class="fa fa-download"></i>
      </a>
    </form>
  </div>

  <?php if ($status): ?>
    <div class="mb-2">
      <span class="badge bg-info">
        Filter: <?= htmlspecialchars(getStatusLabel($status)); ?>
        <a href="surat_keluar.php" class="text-white text-decoration-none ms-1"><i class="fa fa-xmark"></i></a>
      </span>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="text-white" style="background: linear-gradient(90deg, #1cc88a, #36b9cc);">
          <tr>
            <th>No</th>
            <th>No Surat</th>
            <th>Kode Surat</th>
            <th>Tanggal Surat</th>
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
              <td><?= statusBadge($row['status']); ?></td>
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
                <?php if($row['status'] == 'menunggu_verifikasi'): ?>
                  <a href="aksi_verifikasi.php?type=surat_keluar&id=<?= $row['id']; ?>&next=terverifikasi" class="btn btn-sm btn-success">
                    <i class="fa fa-check"></i> Verifikasi
                  </a>
                <?php endif; ?>
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
