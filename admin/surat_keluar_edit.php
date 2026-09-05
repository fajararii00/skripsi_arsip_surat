<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

requireRole(['admin']);

if (!isset($_GET['id'])) {
    header("Location: surat_keluar.php");
    exit;
}
$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM surat_keluar WHERE id=$id");
$surat = mysqli_fetch_assoc($result);
if (!$surat) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan</div>";
    exit;
}

// Ambil daftar kode surat
$kode_list = mysqli_query($conn, "SELECT * FROM kode_surat ORDER BY kode ASC");

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kode_surat_id = intval($_POST['kode_surat_id']);
    $no_surat      = mysqli_real_escape_string($conn, $_POST['no_surat']);
    $tgl_surat     = mysqli_real_escape_string($conn, $_POST['tgl_surat']);
    $tujuan        = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $instansi      = mysqli_real_escape_string($conn, $_POST['instansi']);
    $kategori      = mysqli_real_escape_string($conn, $_POST['kategori']);
    $perihal       = mysqli_real_escape_string($conn, $_POST['perihal']);
    $isi_surat     = mysqli_real_escape_string($conn, $_POST['isi_surat']);
    $status        = mysqli_real_escape_string($conn, $_POST['status']);

    if ($kode_surat_id <= 0) {
        $msg = "Pilih kode surat terlebih dahulu!";
    } else {
        // Auto-generate no_surat jika kosong
        if (empty($no_surat)) {
            $bulan = date('m', strtotime($tgl_surat));
            $tahun = date('Y', strtotime($tgl_surat));
            $count_result = mysqli_query($conn,
                "SELECT COUNT(*) as total FROM surat_keluar
                 WHERE kode_surat_id=$kode_surat_id
                 AND MONTH(tgl_surat)='$bulan'
                 AND YEAR(tgl_surat)='$tahun'
                 AND id!=$id"
            );
            $count_row = mysqli_fetch_assoc($count_result);
            $nomor = str_pad($count_row['total'] + 1, 4, '0', STR_PAD_LEFT);
            $kode_result = mysqli_query($conn, "SELECT kode FROM kode_surat WHERE id=$kode_surat_id");
            $kode_row = mysqli_fetch_assoc($kode_result);
            $no_surat = $kode_row['kode'] . "/$bulan/$tahun/$nomor";
        }

        // File upload
        $update_file = "";
        if (!empty($_FILES['file_surat']['name'])) {
            $file_name = time() . "_" . basename($_FILES['file_surat']['name']);
            $target_dir = __DIR__ . "/../assets/uploads/surat_keluar/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $target)) {
                $update_file = ", file_surat='$file_name'";
            } else {
                $msg = "Upload file gagal!";
            }
        }

        if (!$msg) {
            $query = "UPDATE surat_keluar SET
                        kode_surat_id=$kode_surat_id,
                        no_surat='$no_surat',
                        tgl_surat='$tgl_surat',
                        tujuan='$tujuan',
                        instansi='$instansi',
                        kategori='$kategori',
                        perihal='$perihal',
                        isi_surat='$isi_surat',
                        status='$status'
                        $update_file
                      WHERE id=$id";
            if (mysqli_query($conn, $query)) {
                if ($status != $surat['status']) {
                    logStatus($conn, 'surat_keluar', $id, $status);
                }

                // Regenerate QR code (jangan block redirect jika gagal)
                try {
                    include "../libs/phpqrcode/qrlib.php";
                    $qr_dir = __DIR__ . "/../assets/uploads/qr_codes/";
                    if (!is_dir($qr_dir)) @mkdir($qr_dir, 0777, true);
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                                ? 'https' : 'http';
                    $base_path = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
                    $cek_url   = $protocol . '://' . $_SERVER['HTTP_HOST'] . $base_path . '/cek_legalitas.php?id=' . $id;
                    $qr_file   = $qr_dir . 'surat_' . $id . '.png';
                    @QRcode::png($cek_url, $qr_file, QR_ECLEVEL_M, 8, 2);

                    if (file_exists($qr_file) && filesize($qr_file) > 0) {
                        $qr_path = 'surat_' . $id . '.png';
                        mysqli_query($conn, "UPDATE surat_keluar SET qr_code='$qr_path' WHERE id=$id");
                    }
                } catch (Exception $e) {
                    // QR gagal generate, tidak block proses utama
                }

                $_SESSION['toast_success'] = "Surat keluar berhasil diperbarui!";
                header("Location: surat_keluar.php");
                exit;
            } else {
                $msg = "Gagal update: " . mysqli_error($conn);
                $_SESSION['toast_error'] = $msg;
            }
        }
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Edit Surat Keluar</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Kode Surat <span class="text-danger">*</span></label>
        <select name="kode_surat_id" id="kode_surat" class="form-select" required>
          <option value="">-- Pilih Kode Surat --</option>
          <?php while($k = mysqli_fetch_assoc($kode_list)): ?>
            <option value="<?= $k['id']; ?>" data-kode="<?= htmlspecialchars($k['kode']); ?>"
              <?= $surat['kode_surat_id'] == $k['id'] ? 'selected' : ''; ?>>
              <?= htmlspecialchars($k['kode']); ?> - <?= htmlspecialchars($k['nama']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
        <input type="date" name="tgl_surat" id="tgl_surat" class="form-control" required
               value="<?= $surat['tgl_surat']; ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">No Surat <small class="text-muted">(otomatis jika kosong)</small></label>
      <input type="text" name="no_surat" id="no_surat" class="form-control"
             value="<?= htmlspecialchars($surat['no_surat']); ?>">
      <small class="text-muted" id="nomor_preview"></small>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Tujuan <span class="text-danger">*</span></label>
        <input type="text" name="tujuan" class="form-control" required
               value="<?= htmlspecialchars($surat['tujuan']); ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Instansi <span class="text-danger">*</span></label>
        <input type="text" name="instansi" class="form-control"
               value="<?= htmlspecialchars($surat['instansi']); ?>">
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Kategori <span class="text-danger">*</span></label>
        <input type="text" name="kategori" class="form-control"
               value="<?= htmlspecialchars($surat['kategori']); ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Perihal <span class="text-danger">*</span></label>
        <input type="text" name="perihal" class="form-control" required
               value="<?= htmlspecialchars($surat['perihal']); ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Isi Surat</label>
      <textarea name="isi_surat" class="form-control" rows="4"><?= htmlspecialchars($surat['isi_surat']); ?></textarea>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <?php foreach (getStatusSteps() as $key => $label): ?>
            <option value="<?= $key; ?>" <?= $surat['status'] == $key ? 'selected' : ''; ?>><?= $label; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">File Surat</label>
        <input type="file" name="file_surat" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        <small class="text-muted">File lama: <?= htmlspecialchars($surat['file_surat']); ?></small>
      </div>
    </div>

    <?php if($surat['qr_code']): ?>
    <div class="mb-3">
      <label class="form-label">QR Code Saat Ini</label><br>
      <img src="../assets/uploads/qr_codes/<?= htmlspecialchars($surat['qr_code']); ?>" alt="QR Code" width="120">
    </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
    <a href="surat_keluar.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<script>
function fetchNomor() {
    var kodeSelect = document.getElementById('kode_surat');
    var tglInput = document.getElementById('tgl_surat');
    var noSuratInput = document.getElementById('no_surat');
    var preview = document.getElementById('nomor_preview');

    var kodeId = kodeSelect.value;
    var tgl = tglInput.value;

    if (!kodeId || !tgl) {
        preview.textContent = '';
        return;
    }

    fetch('../get_next_nomor.php?kode_surat_id=' + kodeId + '&tgl_surat=' + tgl)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.preview) {
                noSuratInput.value = data.preview;
                preview.textContent = '→ Nomor urut: ' + data.nomor + ' untuk kode ' + data.kode + ' bulan ' + data.bulan + '/' + data.tahun;
            }
        });
}

document.getElementById('kode_surat').addEventListener('change', fetchNomor);
document.getElementById('tgl_surat').addEventListener('change', fetchNomor);
</script>

<?php include "../includes/footer.php"; ?>
