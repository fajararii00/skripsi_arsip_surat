<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// Hanya admin yang boleh akses laporan
requireRole(['admin']);

// Tentukan jenis laporan
$jenis = isset($_GET['jenis']) && in_array($_GET['jenis'], ['surat_masuk', 'surat_keluar', 'disposisi']) ? $_GET['jenis'] : 'surat_masuk';

// Filter
$tgl_awal  = isset($_GET['tgl_awal']) && $_GET['tgl_awal'] != '' ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) && $_GET['tgl_akhir'] != '' ? $_GET['tgl_akhir'] : date('Y-m-d');
if (strtotime($tgl_awal) > strtotime($tgl_akhir)) {
    $tmp = $tgl_awal;
    $tgl_awal = $tgl_akhir;
    $tgl_akhir = $tmp;
}
$status   = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// Judul & label kolom per jenis
$judul = '';
$tabel_kolom = [];
if ($jenis == 'surat_masuk') {
    $judul = 'Surat Masuk';
    $tabel_kolom = ['No', 'No Surat', 'Tgl Surat', 'Tgl Diterima', 'Pengirim', 'Instansi', 'Kategori', 'Perihal', 'Status'];
} elseif ($jenis == 'surat_keluar') {
    $judul = 'Surat Keluar';
    $tabel_kolom = ['No', 'No Agenda', 'No Surat', 'Tgl Surat', 'Tujuan', 'Instansi', 'Kategori', 'Perihal', 'Status'];
} else {
    $judul = 'Disposisi';
    $tabel_kolom = ['No', 'No Surat', 'Perihal', 'Pengirim', 'Penerima', 'Tgl Disposisi', 'Instruksi', 'Status'];
}

// Bangun kondisi WHERE per jenis
$where = [];
if ($status != '') {
    $where[] = ($jenis == 'disposisi' ? "d.status" : "status") . " = '$status'";
}
if ($kategori != '') {
    $where[] = ($jenis == 'disposisi' ? "sm.kategori" : "kategori") . " = '$kategori'";
}

if ($jenis == 'disposisi') {
    $where[] = "d.tgl_disposisi BETWEEN '$tgl_awal' AND '$tgl_akhir'";
} else {
    $where[] = "tgl_surat BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Query data
if ($jenis == 'disposisi') {
    $sql = "SELECT d.*, sm.no_surat, sm.perihal, sm.kategori AS kategori_surat,
                   u1.nama AS pengirim, u2.nama AS penerima
            FROM disposisi d
            JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
            LEFT JOIN users u1 ON d.pengirim_id = u1.id
            LEFT JOIN users u2 ON d.penerima_id = u2.id
            $where_sql
            ORDER BY d.tgl_disposisi DESC";
} else {
    $sql = "SELECT * FROM $jenis $where_sql ORDER BY tgl_surat DESC";
}
$result = mysqli_query($conn, $sql);
$total = mysqli_num_rows($result);

// Hitung per status (dengan filter yang sama, tanpa filter status)
$count_by_status = [];
$count_where = [];
if ($kategori != '') {
    $count_where[] = ($jenis == 'disposisi' ? "sm.kategori" : "kategori") . " = '$kategori'";
}
if ($jenis == 'disposisi') {
    $count_where[] = "d.tgl_disposisi BETWEEN '$tgl_awal' AND '$tgl_akhir'";
} else {
    $count_where[] = "tgl_surat BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}
$count_where_sql = count($count_where) > 0 ? "AND " . implode(" AND ", $count_where) : "";
foreach (getStatusSteps() as $key => $label) {
    if ($jenis == 'disposisi') {
        $q = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM disposisi d JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
                                  WHERE d.status='$key' $count_where_sql");
    } else {
        $q = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM $jenis WHERE status='$key' $count_where_sql");
    }
    $count_by_status[$key] = mysqli_fetch_assoc($q)['jml'];
}

// Opsi kategori untuk dropdown
if ($jenis == 'disposisi') {
    $kategori_list = mysqli_query($conn, "SELECT DISTINCT kategori FROM surat_masuk WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori");
} else {
    $kategori_list = mysqli_query($conn, "SELECT DISTINCT kategori FROM $jenis WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori");
}
?>

<?php include "../includes/header.php"; ?>

<style>
  @media print {
    nav.navbar, .no-print { display: none !important; }
    body { background: #fff; }
    .laporan-kop { display: block; }
  }
  .laporan-kop { display: none; }
  .kartu-status { border: 1px solid #dee2e6; border-radius: .5rem; }
</style>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Laporan <?= $judul; ?></h3>

  <!-- Tab jenis -->
  <ul class="nav nav-tabs no-print mb-3">
    <?php foreach (['surat_masuk' => 'Surat Masuk', 'surat_keluar' => 'Surat Keluar', 'disposisi' => 'Disposisi'] as $key => $label): ?>
      <li class="nav-item">
        <a class="nav-link <?= $jenis == $key ? 'active' : ''; ?>"
           href="laporan.php?jenis=<?= $key; ?>&tgl_awal=<?= $tgl_awal; ?>&tgl_akhir=<?= $tgl_akhir; ?>&status=<?= $status; ?>&kategori=<?= urlencode($kategori); ?>"><?= $label; ?></a>
      </li>
    <?php endforeach; ?>
  </ul>

  <!-- Form filter -->
  <div class="card shadow-sm mb-4 no-print">
    <div class="card-body">
      <form method="get" action="laporan.php" class="row g-3 align-items-end">
        <input type="hidden" name="jenis" value="<?= $jenis; ?>">
        <div class="col-md-3">
          <label class="form-label">Tanggal Awal</label>
          <input type="date" name="tgl_awal" value="<?= $tgl_awal; ?>" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal Akhir</label>
          <input type="date" name="tgl_akhir" value="<?= $tgl_akhir; ?>" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            <?php foreach (getStatusSteps() as $key => $label): ?>
              <option value="<?= $key; ?>" <?= $status == $key ? 'selected' : ''; ?>><?= $label; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Kategori</label>
          <select name="kategori" class="form-select form-select-sm">
            <option value="">Semua Kategori</option>
            <?php while ($k = mysqli_fetch_assoc($kategori_list)): ?>
              <option value="<?= htmlspecialchars($k['kategori']); ?>" <?= $kategori == $k['kategori'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($k['kategori']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Tampilkan</button>
          <button type="button" class="btn btn-success btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Cetak</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Kop laporan (terlihat saat cetak) -->
  <div class="laporan-kop mb-4 text-center">
    <h4 class="mb-1">LAPORAN <?= strtoupper($judul); ?></h4>
    <p class="mb-0">Periode: <?= date('d M Y', strtotime($tgl_awal)); ?> s/d <?= date('d M Y', strtotime($tgl_akhir)); ?></p>
    <p class="mb-0">Dicetak pada: <?= date('d M Y H:i'); ?></p>
  </div>

  <!-- Ringkasan status -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="kartu-status p-3 text-center bg-light">
        <h4 class="mb-0 fw-bold"><?= $total; ?></h4>
        <small>Total Data</small>
      </div>
    </div>
    <?php foreach ($count_by_status as $key => $jml): ?>
      <div class="col-md-3">
        <div class="kartu-status p-3 text-center">
          <h4 class="mb-0 fw-bold"><?= $jml; ?></h4>
          <small><?= htmlspecialchars(getStatusLabel($key)); ?></small>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Tabel data -->
  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="text-white" style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
          <tr>
            <?php foreach ($tabel_kolom as $kolom): ?>
              <th><?= $kolom; ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?= $no++; ?></td>
                <?php if ($jenis == 'surat_masuk'): ?>
                  <td><?= htmlspecialchars($row['no_surat']); ?></td>
                  <td><?= htmlspecialchars($row['tgl_surat']); ?></td>
                  <td><?= htmlspecialchars($row['tgl_diterima']); ?></td>
                  <td><?= htmlspecialchars($row['pengirim']); ?></td>
                  <td><?= htmlspecialchars($row['instansi']); ?></td>
                  <td><span class="badge bg-info"><?= htmlspecialchars($row['kategori']); ?></span></td>
                  <td><?= htmlspecialchars($row['perihal']); ?></td>
                  <td><?= statusBadge($row['status']); ?></td>
                <?php elseif ($jenis == 'surat_keluar'): ?>
                  <td><?= htmlspecialchars($row['no_agenda']); ?></td>
                  <td><?= htmlspecialchars($row['no_surat']); ?></td>
                  <td><?= htmlspecialchars($row['tgl_surat']); ?></td>
                  <td><?= htmlspecialchars($row['tujuan']); ?></td>
                  <td><?= htmlspecialchars($row['instansi']); ?></td>
                  <td><span class="badge bg-info"><?= htmlspecialchars($row['kategori']); ?></span></td>
                  <td><?= htmlspecialchars($row['perihal']); ?></td>
                  <td><?= statusBadge($row['status']); ?></td>
                <?php else: ?>
                  <td><?= htmlspecialchars($row['no_surat']); ?></td>
                  <td><?= htmlspecialchars($row['perihal']); ?></td>
                  <td><?= htmlspecialchars($row['pengirim']); ?></td>
                  <td><?= htmlspecialchars($row['penerima']); ?></td>
                  <td><?= htmlspecialchars($row['tgl_disposisi']); ?></td>
                  <td><?= nl2br(htmlspecialchars($row['instruksi'])); ?></td>
                  <td><?= statusBadge($row['status']); ?></td>
                <?php endif; ?>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="<?= count($tabel_kolom); ?>" class="text-center text-muted">Tidak ada data pada periode ini.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
