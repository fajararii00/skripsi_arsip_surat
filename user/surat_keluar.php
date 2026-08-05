<?php
include "../includes/auth.php";
include "../includes/db.php";

if ($_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

// hanya tampilkan surat keluar yang sudah disetujui/dikirim
$result = mysqli_query($conn, "SELECT * FROM surat_keluar WHERE status IN ('disetujui','dikirim') ORDER BY tgl_surat DESC");
?>

<?php include "../includes/header_user.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Daftar Surat Keluar</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="bg-success text-white">
          <tr>
            <th>No</th>
            <th>No Surat</th>
            <th>Tanggal Surat</th>
            <th>Tujuan</th>
            <th>Perihal</th>
            <th>Status</th>
            <th>File</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; while($row=mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['no_surat']); ?></td>
            <td><?= htmlspecialchars($row['tgl_surat']); ?></td>
            <td><?= htmlspecialchars($row['tujuan']); ?></td>
            <td><?= htmlspecialchars($row['perihal']); ?></td>
            <td><span class="badge bg-success"><?= ucfirst($row['status']); ?></span></td>
            <td>
              <?php if($row['file_surat']): ?>
                <a href="../assets/uploads/surat_keluar/<?= $row['file_surat']; ?>" target="_blank" class="btn btn-sm btn-info text-white">Lihat</a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
