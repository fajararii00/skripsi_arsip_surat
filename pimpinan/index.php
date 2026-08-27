<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// Pastikan hanya pimpinan yang bisa akses
if ($_SESSION['role'] != 'pimpinan') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$nama    = $_SESSION['nama'];

// ===== Statistik =====
function countRow($conn, $sql) {
    return (int) mysqli_fetch_assoc(mysqli_query($conn, $sql))['jml'];
}

$total_masuk           = countRow($conn, "SELECT COUNT(*) as jml FROM surat_masuk");
$total_keluar          = countRow($conn, "SELECT COUNT(*) as jml FROM surat_keluar");
$verif_masuk           = countRow($conn, "SELECT COUNT(*) as jml FROM surat_masuk WHERE status='menunggu_verifikasi'");
$verif_keluar          = countRow($conn, "SELECT COUNT(*) as jml FROM surat_keluar WHERE status='menunggu_verifikasi'");
$disposisi_diberikan   = countRow($conn, "SELECT COUNT(*) as jml FROM disposisi WHERE pengirim_id='$user_id'");
$disposisi_belum       = countRow($conn, "SELECT COUNT(*) as jml FROM disposisi WHERE pengirim_id='$user_id' AND status != 'selesai'");
$selesai_masuk         = countRow($conn, "SELECT COUNT(*) as jml FROM surat_masuk WHERE status='selesai'");
$selesai_keluar        = countRow($conn, "SELECT COUNT(*) as jml FROM surat_keluar WHERE status='selesai'");

$perlu_verifikasi = $verif_masuk + $verif_keluar;
$surat_selesai    = $selesai_masuk + $selesai_keluar;

// ===== Breakdown status per jenis surat =====
$sts_masuk = [];
$rs = mysqli_query($conn, "SELECT status, COUNT(*) c FROM surat_masuk GROUP BY status");
while ($r = mysqli_fetch_assoc($rs)) $sts_masuk[$r['status']] = (int)$r['c'];
$sts_keluar = [];
$rs = mysqli_query($conn, "SELECT status, COUNT(*) c FROM surat_keluar GROUP BY status");
while ($r = mysqli_fetch_assoc($rs)) $sts_keluar[$r['status']] = (int)$r['c'];

// ===== Rekap 6 bulan terakhir =====
$labels = [];
for ($i = 5; $i >= 0; $i--) $labels[] = date('Y-m', strtotime("-$i months"));
$rekap_masuk = array_fill_keys($labels, 0);
$rs = mysqli_query($conn, "SELECT DATE_FORMAT(tgl_diterima,'%Y-%m') bln, COUNT(*) c FROM surat_masuk WHERE tgl_diterima IS NOT NULL GROUP BY bln");
while ($r = mysqli_fetch_assoc($rs)) if (isset($rekap_masuk[$r['bln']])) $rekap_masuk[$r['bln']] = (int)$r['c'];
$rekap_keluar = array_fill_keys($labels, 0);
$rs = mysqli_query($conn, "SELECT DATE_FORMAT(tgl_surat,'%Y-%m') bln, COUNT(*) c FROM surat_keluar WHERE tgl_surat IS NOT NULL GROUP BY bln");
while ($r = mysqli_fetch_assoc($rs)) if (isset($rekap_keluar[$r['bln']])) $rekap_keluar[$r['bln']] = (int)$r['c'];

// ===== Perlu Verifikasi Saya (surat masuk & keluar menunggu verifikasi) =====
$verifikasi = mysqli_query($conn, "
  SELECT 'surat_masuk' AS jenis, id, no_surat, perihal, status,
         pengirim AS asal, tgl_diterima AS tgl,
         DATEDIFF(CURDATE(), COALESCE(tgl_diterima, tgl_surat, CURDATE())) AS lama
  FROM surat_masuk
  WHERE status = 'menunggu_verifikasi'
  UNION ALL
  SELECT 'surat_keluar', id, no_surat, perihal, status,
         tujuan, tgl_surat,
         DATEDIFF(CURDATE(), COALESCE(tgl_surat, CURDATE()))
  FROM surat_keluar
  WHERE status = 'menunggu_verifikasi'
  ORDER BY lama DESC
  LIMIT 10
");

// ===== Disposisi yang perlu dipantau (belum selesai) =====
$pantau = mysqli_query($conn, "
  SELECT d.*, sm.no_surat, sm.perihal, u2.nama AS penerima,
         DATEDIFF(CURDATE(), COALESCE(d.tgl_disposisi, CURDATE())) AS lama
  FROM disposisi d
  JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
  LEFT JOIN users u2 ON d.penerima_id = u2.id
  WHERE d.pengirim_id='$user_id' AND d.status != 'selesai'
  ORDER BY lama DESC
  LIMIT 10
");

// ===== Aktivitas terbaru (status_history) =====
$aktivitas = mysqli_query($conn, "
  SELECT 'Surat Masuk' AS jenis, h.ref_id AS id, h.status, h.keterangan, h.created_at, u.nama AS aktor,
         sm.no_surat, sm.perihal
  FROM status_history h
  LEFT JOIN users u ON h.updated_by = u.id
  LEFT JOIN surat_masuk sm ON h.ref_type='surat_masuk' AND h.ref_id = sm.id
  WHERE h.ref_type='surat_masuk'
  UNION ALL
  SELECT 'Surat Keluar', h.ref_id, h.status, h.keterangan, h.created_at, u.nama,
         sk.no_surat, sk.perihal
  FROM status_history h
  LEFT JOIN users u ON h.updated_by = u.id
  LEFT JOIN surat_keluar sk ON h.ref_type='surat_keluar' AND h.ref_id = sk.id
  WHERE h.ref_type='surat_keluar'
  UNION ALL
  SELECT 'Disposisi', h.ref_id, h.status, h.keterangan, h.created_at, u.nama,
         sm.no_surat, sm.perihal
  FROM status_history h
  LEFT JOIN users u ON h.updated_by = u.id
  LEFT JOIN disposisi ds ON h.ref_type='disposisi' AND h.ref_id = ds.id
  LEFT JOIN surat_masuk sm ON ds.surat_masuk_id = sm.id
  WHERE h.ref_type='disposisi'
  ORDER BY created_at DESC
  LIMIT 8
");

$nama_jenis = ['surat_masuk' => 'Surat Masuk', 'surat_keluar' => 'Surat Keluar'];
?>

<?php include "../includes/header_pimpinan.php"; ?>

<style>
  .stat-card { transition: transform .15s ease, box-shadow .15s ease; }
  .stat-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important; }
  .stat-card .stat-num { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
  .stat-card .stat-label { font-size: .8rem; opacity: .9; margin: 0; }
  .act-item .btn { white-space: nowrap; }
  .timeline { list-style: none; padding: 0; margin: 0; }
  .timeline li { display: flex; gap: .75rem; padding: .55rem 0; border-bottom: 1px dashed #e3e8f0; }
  .timeline li:last-child { border-bottom: 0; }
  .timeline .t-icon { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 50%; display: inline-flex;
                     align-items: center; justify-content: center; color: #fff; font-size: .85rem; }
</style>

<div class="container mt-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1">Dashboard Pimpinan</h2>
      <p class="text-muted mb-0">Selamat datang, <strong><?= htmlspecialchars($nama); ?></strong>. Berikut ringkasan surat yang perlu diverifikasi & disposisi yang harus Anda pantau.</p>
    </div>
    <div class="text-end small text-muted">
      <div><i class="fa fa-calendar-day me-1"></i><?= date('d M Y'); ?></div>
    </div>
  </div>

  <!-- Statistik Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
      <a href="surat_masuk.php" class="stat-card card shadow-sm bg-info text-white p-3 text-center text-decoration-none d-block">
        <div class="stat-num"><?= $total_masuk; ?></div>
        <p class="stat-label"><i class="fa fa-inbox me-1"></i>Surat Masuk</p>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <a href="surat_keluar.php" class="stat-card card shadow-sm bg-primary text-white p-3 text-center text-decoration-none d-block">
        <div class="stat-num"><?= $total_keluar; ?></div>
        <p class="stat-label"><i class="fa fa-paper-plane me-1"></i>Surat Keluar</p>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <a href="#verifikasi" class="stat-card card shadow-sm bg-warning text-dark p-3 text-center text-decoration-none d-block">
        <div class="stat-num"><?= $perlu_verifikasi; ?></div>
        <p class="stat-label"><i class="fa fa-check-double me-1"></i>Perlu Verifikasi</p>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <a href="disposisi.php" class="stat-card card shadow-sm bg-secondary text-white p-3 text-center text-decoration-none d-block">
        <div class="stat-num"><?= $disposisi_diberikan; ?></div>
        <p class="stat-label"><i class="fa fa-tasks me-1"></i>Disposisi Diberikan</p>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <a href="disposisi.php" class="stat-card card shadow-sm bg-danger text-white p-3 text-center text-decoration-none d-block">
        <div class="stat-num"><?= $disposisi_belum; ?></div>
        <p class="stat-label"><i class="fa fa-clock me-1"></i>Disposisi Belum Selesai</p>
      </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <a href="surat_masuk.php" class="stat-card card shadow-sm bg-success text-white p-3 text-center text-decoration-none d-block">
        <div class="stat-num"><?= $surat_selesai; ?></div>
        <p class="stat-label"><i class="fa fa-check-circle me-1"></i>Surat Selesai</p>
      </a>
    </div>
  </div>

  <!-- Perlu Verifikasi Saya -->
  <div class="card shadow-sm mb-4" id="verifikasi">
    <div class="card-header bg-warning text-dark fw-semibold">
      <i class="fa fa-bell me-2"></i>Perlu Verifikasi Saya
      <span class="badge bg-danger ms-1"><?= mysqli_num_rows($verifikasi); ?> item</span>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>Jenis</th>
              <th>No Surat</th>
              <th>Perihal</th>
              <th>Asal/Tujuan</th>
              <th>Status</th>
              <th>Menunggu</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($verifikasi) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($verifikasi)):
                $jenis = $row['jenis'];
                $track = "../tracking.php?type=$jenis&id={$row['id']}";
              ?>
              <tr class="act-item">
                <td><span class="badge bg-info"><?= $nama_jenis[$jenis]; ?></span></td>
                <td><?= htmlspecialchars($row['no_surat']); ?></td>
                <td><?= htmlspecialchars($row['perihal']); ?></td>
                <td><?= htmlspecialchars($row['asal'] ?: '-'); ?></td>
                <td><?= statusBadge($row['status']); ?></td>
                <td>
                  <?php if ($row['lama'] <= 0): ?>
                    <span class="text-muted">hari ini</span>
                  <?php else: ?>
                    <span class="badge bg-<?= $row['lama'] >= 7 ? 'danger' : 'secondary'; ?>"><?= (int)$row['lama']; ?> hari</span>
                  <?php endif; ?>
                </td>
                <td class="text-nowrap">
                  <?php if ($jenis === 'surat_masuk'): ?>
                    <a href="disposisi_tambah.php?surat_id=<?= $row['id']; ?>" class="btn btn-sm btn-success"><i class="fa fa-paper-plane"></i> Beri Disposisi</a>
                  <?php else: ?>
                    <a href="aksi_verifikasi.php?type=surat_keluar&id=<?= $row['id']; ?>&next=terverifikasi" class="btn btn-sm btn-success"><i class="fa fa-check"></i> Verifikasi</a>
                  <?php endif; ?>
                  <a href="<?= $track; ?>" class="btn btn-sm btn-primary"><i class="fa fa-route"></i> Tracking</a>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center text-success py-4"><i class="fa fa-check-circle me-2"></i>Tidak ada surat yang menunggu verifikasi.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="surat_masuk.php?status=menunggu_verifikasi" class="btn btn-sm btn-primary">Surat Masuk Menunggu</a>
        <a href="surat_keluar.php?status=menunggu_verifikasi" class="btn btn-sm btn-primary">Surat Keluar Menunggu</a>
      </div>
    </div>
  </div>

  <!-- Disposisi Perlu Dipantau -->
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white fw-semibold">
      <i class="fa fa-tasks me-2"></i>Disposisi Perlu Dipantau
      <span class="badge bg-light text-danger ms-1"><?= mysqli_num_rows($pantau); ?> item</span>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>No Surat</th>
              <th>Perihal</th>
              <th>Penerima</th>
              <th>Instruksi</th>
              <th>Status</th>
              <th>Menunggu</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($pantau) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($pantau)): ?>
              <tr class="act-item">
                <td><?= htmlspecialchars($row['no_surat']); ?></td>
                <td><?= htmlspecialchars($row['perihal']); ?></td>
                <td><?= htmlspecialchars($row['penerima'] ?? '-'); ?></td>
                <td><?= nl2br(htmlspecialchars(mb_strimwidth($row['instruksi'] ?: '-', 0, 80, '...'))); ?></td>
                <td><?= statusBadge($row['status']); ?></td>
                <td>
                  <?php if ($row['lama'] <= 0): ?>
                    <span class="text-muted">hari ini</span>
                  <?php else: ?>
                    <span class="badge bg-<?= $row['lama'] >= 7 ? 'danger' : 'secondary'; ?>"><?= (int)$row['lama']; ?> hari</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="../tracking.php?type=disposisi&id=<?= $row['id']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-route"></i> Tracking</a>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center text-success py-4"><i class="fa fa-check-circle me-2"></i>Semua disposisi sudah selesai.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <a href="disposisi.php" class="btn btn-sm btn-primary">Lihat Semua Disposisi</a>
    </div>
  </div>

  <!-- Breakdown status surat masuk & keluar -->
  <div class="row g-4 mb-4">
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-info text-white fw-semibold"><i class="fa fa-inbox me-2"></i>Status Surat Masuk</div>
        <div class="card-body">
          <?php $max = max(array_sum($sts_masuk), 1); foreach (getStatusSteps() as $key => $label): $c = $sts_masuk[$key] ?? 0; $w = round($c / $max * 100); ?>
            <div class="mb-3">
              <div class="d-flex justify-content-between small mb-1">
                <span><?= $label; ?></span><span class="fw-semibold"><?= $c; ?></span>
              </div>
              <div class="progress" style="height:8px;">
                <div class="progress-bar bg-<?= getStatusColor($key); ?>" style="width: <?= $w; ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
          <p class="text-muted small mb-0 mt-2">Total: <strong><?= array_sum($sts_masuk); ?></strong> surat</p>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-primary text-white fw-semibold"><i class="fa fa-paper-plane me-2"></i>Status Surat Keluar</div>
        <div class="card-body">
          <?php $max = max(array_sum($sts_keluar), 1); foreach (getStatusSteps() as $key => $label): $c = $sts_keluar[$key] ?? 0; $w = round($c / $max * 100); ?>
            <div class="mb-3">
              <div class="d-flex justify-content-between small mb-1">
                <span><?= $label; ?></span><span class="fw-semibold"><?= $c; ?></span>
              </div>
              <div class="progress" style="height:8px;">
                <div class="progress-bar bg-<?= getStatusColor($key); ?>" style="width: <?= $w; ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
          <p class="text-muted small mb-0 mt-2">Total: <strong><?= array_sum($sts_keluar); ?></strong> surat</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Rekap 6 bulan terakhir -->
  <div class="row g-4 mb-4">
    <div class="col-lg-7">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-success text-white fw-semibold"><i class="fa fa-chart-column me-2"></i>Rekap Surat 6 Bulan Terakhir</div>
        <div class="card-body">
          <canvas id="rekapChart" height="220"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-secondary text-white fw-semibold"><i class="fa fa-clock-rotate-left me-2"></i>Aktivitas Terbaru</div>
        <div class="card-body">
          <ul class="timeline">
            <?php if (mysqli_num_rows($aktivitas) > 0): ?>
              <?php while ($a = mysqli_fetch_assoc($aktivitas)):
                $icon = $a['jenis'] == 'Surat Masuk' ? 'fa-inbox' : ($a['jenis'] == 'Surat Keluar' ? 'fa-paper-plane' : 'fa-tasks');
                $color = getStatusColor($a['status']);
                $aktor = $a['aktor'] ?: 'Sistem';
              ?>
              <li>
                <span class="t-icon bg-<?= $color; ?>"><i class="fa <?= $icon; ?>"></i></span>
                <div class="small">
                  <div>
                    <strong><?= htmlspecialchars($aktor); ?></strong>
                    <?php if ($a['no_surat']): ?>
                      - <a href="../tracking.php?type=<?= $a['jenis']=='Surat Masuk'?'surat_masuk':($a['jenis']=='Surat Keluar'?'surat_keluar':'disposisi'); ?>&id=<?= $a['id']; ?>" class="text-decoration-none"><?= htmlspecialchars($a['no_surat']); ?></a>
                    <?php endif; ?>
                  </div>
                  <div class="text-muted">
                    <?= $a['jenis']; ?>: <?= htmlspecialchars(mb_strimwidth($a['perihal'] ?: '-', 0, 60, '...')); ?>
                  </div>
                  <div class="text-muted">
                    <?= statusBadge($a['status']); ?>
                    <span class="ms-1"><?= date('d M Y H:i', strtotime($a['created_at'])); ?></span>
                  </div>
                </div>
              </li>
              <?php endwhile; ?>
            <?php else: ?>
              <li><span class="text-muted">Belum ada aktivitas.</span></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const lbl = <?= json_encode(array_map(fn($b) => date('M', strtotime($b . '-01')), $labels)); ?>;
  new Chart(document.getElementById('rekapChart'), {
    type: 'bar',
    data: {
      labels: lbl,
      datasets: [
        { label: 'Surat Masuk', data: <?= json_encode(array_values($rekap_masuk)); ?>, backgroundColor: 'rgba(23,162,184,.75)' },
        { label: 'Surat Keluar', data: <?= json_encode(array_values($rekap_keluar)); ?>, backgroundColor: 'rgba(40,167,69,.75)' }
      ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } },
               scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
</script>

<?php include "../includes/footer.php"; ?>