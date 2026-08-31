<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

if ($_SESSION['role'] != 'tata_usaha') {
    header("Location: ../login.php");
    exit;
}

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

<?php include "../includes/header_tata_usaha.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Daftar Surat Keluar</h3>

  <!-- Tombol Tambah & Pencarian -->
  <div class="d-flex justify-content-between mb-3">
    <a href="surat_keluar_tambah.php" class="btn btn-success btn-sm mb-3">
      <i class="fa fa-plus"></i> Tambah Surat Keluar
    </a>

    <form class="d-flex mb-3 align-items-center" method="get" action="">
      <select name="status" class="form-select form-select-sm me-2" style="max-width: 210px;" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <?php foreach (getStatusSteps() as $key => $label): ?>
          <option value="<?= $key; ?>" <?= $status == $key ? 'selected' : ''; ?>><?= $label; ?></option>
        <?php endforeach; ?>
      </select>
      <div class="input-group" style="max-width: 400px;">
        <input type="text" name="q" value="<?= htmlspecialchars($q); ?>"
              class="form-control form-control-sm"
              placeholder="Cari surat keluar...">
        <button class="btn btn-sm btn-primary" type="submit">
          <i class="fa fa-search"></i>
        </button>
      </div>
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
        <thead class="text-white" style="background: linear-gradient(90deg, #17a2b8, #28a745);">
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
                <?php if($row['status'] == 'selesai'): ?>
                  <a href="../tracking.php?type=surat_keluar&id=<?= $row['id']; ?>" class="btn btn-sm btn-info">
                    <i class="fa fa-eye"></i> Detail
                  </a>
                <?php else: ?>
                  <a href="../tracking.php?type=surat_keluar&id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-route"></i> Tracking
                  </a>
                  <a href="surat_keluar_edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
                    <i class="fa fa-edit"></i> Edit
                  </a>
                <?php endif; ?>
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
