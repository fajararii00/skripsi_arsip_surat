<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

// Pastikan hanya pimpinan
if ($_SESSION['role'] != 'pimpinan') {
    header("Location: ../login.php");
    exit;
}

$msg = "";
$preselect = isset($_GET['surat_id']) ? intval($_GET['surat_id']) : 0;

// Ambil daftar surat masuk yang belum selesai untuk dropdown
$surat = mysqli_query($conn, "SELECT id, no_surat, perihal FROM surat_masuk WHERE status != 'selesai' ORDER BY tgl_surat DESC");

// Ambil data tata usaha sebagai penerima
$tata_usaha = mysqli_query($conn, "SELECT id, nama FROM users WHERE role = 'tata_usaha'");
$penerima = mysqli_fetch_assoc($tata_usaha);

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $surat_masuk_id = intval($_POST['surat_masuk_id']);
    $pengirim_id    = $_SESSION['user_id']; // pimpinan yang login
    $penerima_id    = intval($_POST['penerima_id'] ?? 0);
    $instruksi      = mysqli_real_escape_string($conn, $_POST['instruksi'] ?? '');
    $catatan        = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
    $tgl_disposisi  = mysqli_real_escape_string($conn, $_POST['tgl_disposisi']);
    $status         = 'draft';

    if ($penerima_id <= 0) {
        $msg = "Belum ada user dengan role Tata Usaha. Buat dulu lewat Manajemen User.";
    } else {
        $query = "INSERT INTO disposisi (surat_masuk_id, pengirim_id, penerima_id, instruksi, catatan_pimpinan, tgl_disposisi, status)
                  VALUES ('$surat_masuk_id','$pengirim_id','$penerima_id','$instruksi','$catatan','$tgl_disposisi','$status')";

        if (mysqli_query($conn, $query)) {
            $new_id = mysqli_insert_id($conn);
            logStatus($conn, 'disposisi', $new_id, $status, 'Disposisi dibuat oleh pimpinan');

            // Tandai surat masuk sebagai terverifikasi (sudah didisposisi)
            $cek_sm = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM surat_masuk WHERE id=$surat_masuk_id"));
            if ($cek_sm && $cek_sm['status'] != 'terverifikasi') {
                mysqli_query($conn, "UPDATE surat_masuk SET status='terverifikasi' WHERE id=$surat_masuk_id");
                logStatus($conn, 'surat_masuk', $surat_masuk_id, 'terverifikasi', 'Diverifikasi melalui disposisi');
            }

            $_SESSION['toast_success'] = "Disposisi berhasil dibuat!";
            header("Location: disposisi.php");
            exit;
        } else {
            $msg = "Gagal menambah disposisi: " . mysqli_error($conn);
            $_SESSION['toast_error'] = $msg;
        }
    }
}

include "../includes/header_pimpinan.php";
?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Tambah Disposisi</h3>
  <?php if($msg): ?><div class="alert alert-danger"><?= $msg ?></div><?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Pilih Surat Masuk</label>
      <select name="surat_masuk_id" class="form-select" required>
        <option value="">-- Pilih Surat --</option>
        <?php while($s=mysqli_fetch_assoc($surat)): ?>
          <option value="<?= $s['id']; ?>" <?= $preselect == $s['id'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($s['no_surat']); ?> - <?= htmlspecialchars($s['perihal']); ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Penerima Disposisi (Tata Usaha)</label>
      <?php if ($penerima): ?>
        <input type="hidden" name="penerima_id" value="<?= $penerima['id']; ?>">
        <input type="text" class="form-control" value="<?= htmlspecialchars($penerima['nama']); ?>" readonly>
      <?php else: ?>
        <input type="text" class="form-control" value="(Belum ada user role Tata Usaha)" readonly>
      <?php endif; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Tanggal Disposisi</label>
      <input type="date" name="tgl_disposisi" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Instruksi</label>
      <textarea name="instruksi" class="form-control" placeholder="Arahan/tugas untuk Tata Usaha..." required></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Catatan <small class="text-muted">(Opsional)</small></label>
      <textarea name="catatan" class="form-control" placeholder="Catatan tambahan jika diperlukan..."></textarea>
    </div>

    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="disposisi.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include "../includes/footer.php"; ?>