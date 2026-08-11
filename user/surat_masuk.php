<?php
include "../includes/auth.php";
include "../includes/db.php";
include "../includes/tracking.php";

if ($_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM surat_masuk ORDER BY tgl_diterima DESC");
?>

<?php include "../includes/header_user.php"; ?>

<div class="container mt-4">
  <h3 class="fw-bold mb-3">Daftar Surat Masuk</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle">
        <thead class="bg-primary text-white">
          <tr>
            <th>No</th>
            <th>No Surat</th>
            <th>Tanggal Surat</th>
            <th>Pengirim</th>
            <th>Perihal</th>
            <th>Status</th>
            <th>File</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; while($row=mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['no_surat']); ?></td>
            <td><?= htmlspecialchars($row['tgl_surat']); ?></td>
            <td><?= htmlspecialchars($row['pengirim']); ?></td>
            <td><?= htmlspecialchars($row['perihal']); ?></td>
            <td><?= statusBadge($row['status']); ?></td>
            <td>
              <?php if($row['file_surat']): ?>
                <a href="../assets/uploads/surat_masuk/<?= $row['file_surat']; ?>" target="_blank" class="btn btn-sm btn-info text-white">Lihat</a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td>
              <a href="../tracking.php?type=surat_masuk&id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">
                <i class="fa fa-route"></i> Tracking
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
