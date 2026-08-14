<?php
include "includes/auth.php";
include "includes/db.php";
include "includes/tracking.php";

// Parameter wajib: type & id
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id   = isset($_GET['id']) ? intval($_GET['id']) : 0;

$valid_types = ['surat_keluar', 'surat_masuk', 'disposisi'];
if (!in_array($type, $valid_types) || $id <= 0) {
    header("Location: login.php");
    exit;
}

$judul = '';
$detail = [];
$status = '';
$file_url = '';
$pembuat_nama = '';

// Ambil data berdasarkan tipe
if ($type == 'surat_keluar') {
    $q = mysqli_query($conn, "SELECT * FROM surat_keluar WHERE id=$id");
    $row = mysqli_fetch_assoc($q);
    if ($row) {
        $judul = 'Tracking Surat Keluar';
        $status = $row['status'];
        $detail = [
            'No Agenda'    => $row['no_agenda'],
            'No Surat'     => $row['no_surat'],
            'Tanggal Surat' => $row['tgl_surat'],
            'Tujuan'       => $row['tujuan'],
            'Instansi'     => $row['instansi'],
            'Kategori'     => $row['kategori'],
            'Perihal'      => $row['perihal'],
            'Isi Surat'    => $row['isi_surat'],
        ];
        if ($row['file_surat']) {
            $file_url = "assets/uploads/surat_keluar/" . $row['file_surat'];
        }
        $qr = mysqli_query($conn, "SELECT nama FROM users WHERE id='" . intval($row['pembuat_id']) . "'");
        $pr = mysqli_fetch_assoc($qr);
        $pembuat_nama = $pr['nama'] ?? '-';
    }
} elseif ($type == 'surat_masuk') {
    $q = mysqli_query($conn, "SELECT * FROM surat_masuk WHERE id=$id");
    $row = mysqli_fetch_assoc($q);
    if ($row) {
        $judul = 'Tracking Surat Masuk';
        $status = $row['status'];
        $detail = [
            'No Surat'        => $row['no_surat'],
            'Tanggal Surat'   => $row['tgl_surat'],
            'Tanggal Diterima' => $row['tgl_diterima'],
            'Pengirim'        => $row['pengirim'],
            'Instansi'        => $row['instansi'],
            'Kategori'        => $row['kategori'],
            'Perihal'         => $row['perihal'],
        ];
        if ($row['file_surat']) {
            $file_url = "assets/uploads/surat_masuk/" . $row['file_surat'];
        }
    }
} else { // disposisi
    $q = mysqli_query($conn, "
        SELECT d.*, sm.no_surat, sm.perihal, sm.pengirim AS pengirim_surat,
               u1.nama AS pengirim, u2.nama AS penerima
        FROM disposisi d
        JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
        LEFT JOIN users u1 ON d.pengirim_id = u1.id
        LEFT JOIN users u2 ON d.penerima_id = u2.id
        WHERE d.id=$id
    ");
    $row = mysqli_fetch_assoc($q);
    if ($row) {
        $judul = 'Tracking Disposisi';
        $status = $row['status'];
        $detail = [
            'No Surat'        => $row['no_surat'],
            'Perihal'         => $row['perihal'],
            'Pengirim Surat'  => $row['pengirim_surat'],
            'Pengirim'        => $row['pengirim'],
            'Penerima'        => $row['penerima'],
            'Tanggal Disposisi' => $row['tgl_disposisi'],
            'Instruksi'       => $row['instruksi'],
            'Catatan Pimpinan' => $row['catatan_pimpinan'],
        ];
    }
}

if (!$row) {
    die("Data tidak ditemukan.");
}

$msg = '';
// Ubah status sesuai izin role
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_status   = mysqli_real_escape_string($conn, $_POST['status']);
    $keterangan   = trim(mysqli_real_escape_string($conn, $_POST['keterangan'] ?? ''));

    $steps = getStatusSteps();
    if (!array_key_exists($new_status, $steps)) {
        $msg = "Status tidak valid.";
    } elseif (!canTransition($_SESSION['role'], $type, $status, $new_status)) {
        $msg = "Role Anda tidak diizinkan melakukan perubahan status ini.";
    } else {
        $table = $type;
        $upd = "UPDATE `$table` SET status='$new_status' WHERE id=$id";
        if (mysqli_query($conn, $upd)) {
            logStatus($conn, $type, $id, $new_status, $keterangan ?: null);
            header("Location: tracking.php?type=$type&id=$id&saved=1");
            exit;
        } else {
            $msg = "Gagal mengubah status: " . mysqli_error($conn);
        }
    }
}

// Ambil ulang status & riwayat setelah update
if ($type == 'surat_keluar') {
    $row['status'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM surat_keluar WHERE id=$id"))['status'];
} elseif ($type == 'surat_masuk') {
    $row['status'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM surat_masuk WHERE id=$id"))['status'];
} else {
    $row['status'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM disposisi WHERE id=$id"))['status'];
}
$status = $row['status'];
$history = getStatusHistory($conn, $type, $id);
$current_idx = getStatusIndex($status);
$steps = getStatusSteps();

// Pilih header sesuai role
if ($_SESSION['role'] == 'pimpinan') {
    include "includes/header_pimpinan.php";
} elseif ($_SESSION['role'] == 'tata_usaha') {
    include "includes/header_tata_usaha.php";
} else {
    include "includes/header.php";
}
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0"><?= $judul; ?></h3>
    <?php
    $role_dir = $_SESSION['role'] == 'admin' ? 'admin' : ($_SESSION['role'] == 'pimpinan' ? 'pimpinan' : 'tata_usaha');
    $list_map = [
        'surat_keluar' => 'surat_keluar.php',
        'surat_masuk'  => 'surat_masuk.php',
        'disposisi'    => 'disposisi.php',
    ];
    $back_url = "$role_dir/" . $list_map[$type];
    ?>
    <a href="<?= $back_url; ?>" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
  </div>

  <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success">Status berhasil diperbarui.</div>
  <?php endif; ?>
  <?php if ($msg): ?>
    <div class="alert alert-danger"><?= $msg; ?></div>
  <?php endif; ?>

  <!-- Info surat -->
  <div class="card shadow-sm mb-4">
    <div class="card-header text-white fw-semibold" style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
      <i class="fa fa-file-alt"></i> Detail Surat
    </div>
    <div class="card-body">
      <div class="row">
        <?php foreach ($detail as $label => $value): ?>
          <div class="col-md-6 mb-2">
            <strong><?= $label; ?>:</strong>
            <?= $value !== null && $value !== '' ? nl2br(htmlspecialchars($value)) : '<span class="text-muted">-</span>'; ?>
          </div>
        <?php endforeach; ?>
        <div class="col-md-6 mb-2">
          <strong>Dibuat oleh:</strong> <?= htmlspecialchars($pembuat_nama ?: '-'); ?>
        </div>
        <?php if ($file_url): ?>
          <div class="col-md-12 mb-2">
            <strong>File:</strong>
            <a href="<?= htmlspecialchars($file_url); ?>" target="_blank" class="btn btn-info btn-sm text-white">
              <i class="fa fa-eye"></i> Lihat File
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Progress bar 5 tahap -->
  <div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">
      <i class="fa fa-tasks"></i> Progres Status
      <span class="float-end"><?= statusBadge($status); ?></span>
    </div>
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <?php $i = 0; foreach ($steps as $key => $label): ?>
          <div class="text-center flex-fill">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                 style="width:40px;height:40px; <?= $i <= $current_idx ? 'background-color:#1cc88a;color:#fff;' : 'background-color:#e9ecef;color:#6c757d;' ?>">
              <?php if ($i < $current_idx): ?>
                <i class="fa fa-check"></i>
              <?php else: ?>
                <?= $i + 1; ?>
              <?php endif; ?>
            </div>
            <div class="mt-1 small" style="font-weight:<?= $i <= $current_idx ? 'bold' : 'normal'; ?>">
              <?= $label; ?>
            </div>
          </div>
          <?php if ($i < count($steps) - 1): ?>
            <div class="flex-fill mx-1" style="height:4px; background-color:<?= $i < $current_idx ? '#1cc88a' : '#e9ecef'; ?>; border-radius:2px;"></div>
          <?php endif; ?>
          <?php $i++; endforeach; ?>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Timeline riwayat -->
    <div class="col-md-7">
      <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold"><i class="fa fa-history"></i> Riwayat Tracking</div>
        <div class="card-body">
          <?php if (count($history) > 0): ?>
            <ul class="list-unstyled">
              <?php foreach ($history as $h): ?>
                <li class="border-start border-3 mb-3 ps-3" style="border-color:#1cc88a !important;">
                  <div class="d-flex justify-content-between">
                    <strong><?= statusBadge($h['status']); ?></strong>
                    <small class="text-muted"><?= date('d M Y H:i', strtotime($h['created_at'])); ?></small>
                  </div>
                  <?php if ($h['keterangan']): ?>
                    <div class="text-muted small mt-1"><i class="fa fa-comment"></i> <?= htmlspecialchars($h['keterangan']); ?></div>
                  <?php endif; ?>
                  <div class="text-muted small">Oleh: <strong><?= htmlspecialchars($h['nama'] ?? '-'); ?></strong></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-muted mb-0">Belum ada riwayat.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Form ubah status (sesuai izin role) -->
    <div class="col-md-5">
      <?php
      $allowed_next = getNextAllowedStatuses($_SESSION['role'], $type, $status);
      if (count($allowed_next) > 0):
      ?>
        <div class="card shadow-sm mb-4">
          <div class="card-header fw-semibold"><i class="fa fa-edit"></i> Ubah Status</div>
          <div class="card-body">
            <p class="text-muted small">Tahap berikutnya yang bisa Anda pilih:</p>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                  <?php foreach ($allowed_next as $key): ?>
                    <option value="<?= $key; ?>"><?= getStatusLabel($key); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Keterangan <small class="text-muted">(opsional)</small></label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Catatan untuk riwayat tracking..."></textarea>
              </div>
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Simpan Perubahan
              </button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <div class="card shadow-sm mb-4">
          <div class="card-body text-center text-muted">
            <i class="fa fa-lock fa-2x mb-2 d-block"></i>
            Role Anda tidak dapat mengubah status pada tahap ini.
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include "includes/footer.php"; ?>
