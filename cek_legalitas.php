<?php
include "includes/db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID surat tidak valid.");
}

// Ambil data surat + join kode_surat dan users
$result = mysqli_query($conn, "
    SELECT sk.*, ks.kode AS kode_surat_nama, ks.nama AS jenis_surat, u.nama AS pembuat_nama
    FROM surat_keluar sk
    LEFT JOIN kode_surat ks ON sk.kode_surat_id = ks.id
    LEFT JOIN users u ON sk.pembuat_id = u.id
    WHERE sk.id = $id
");
$surat = mysqli_fetch_assoc($result);
if (!$surat) {
    die("Surat tidak ditemukan.");
}

// Status legalitas
$is_legal = in_array($surat['status'], ['terverifikasi', 'selesai']);
$status_label = $is_legal ? 'TERVERIFIKASI' : strtoupper(str_replace('_', ' ', $surat['status']));
$status_color = $is_legal ? '#1cc88a' : '#f6c23e';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Legalitas Surat - <?= htmlspecialchars($surat['no_surat'] ?? '-'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .legal-card {
            max-width: 600px;
            width: 100%;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .legal-header {
            background: linear-gradient(90deg, #1cc88a, #36b9cc);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .legal-header i {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 24px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
        }
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 140px;
        }
        .info-value {
            color: #333;
        }
        .footer-note {
            background: #f8f9fa;
            padding: 16px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="card legal-card">
        <div class="legal-header">
            <i class="fa fa-shield-alt"></i>
            <h4 class="mb-0">Cek Legalitas Surat</h4>
            <p class="mb-0 mt-2">Sistem Pengarsipan Surat</p>
        </div>

        <div class="card-body p-4">
            <div class="text-center mb-4">
                <span class="status-badge" style="background-color: <?= $status_color ?>;">
                    <i class="fa <?= $is_legal ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                    <?= $status_label; ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">No Surat</span>
                <span class="info-value">: <?= htmlspecialchars($surat['no_surat'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Jenis Surat</span>
                <span class="info-value">: <?= htmlspecialchars($surat['jenis_surat'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Surat</span>
                <span class="info-value">: <?= $surat['tgl_surat'] ? date('d M Y', strtotime($surat['tgl_surat'])) : '-'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tujuan</span>
                <span class="info-value">: <?= htmlspecialchars($surat['tujuan'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Instansi</span>
                <span class="info-value">: <?= htmlspecialchars($surat['instansi'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Kategori</span>
                <span class="info-value">: <?= htmlspecialchars($surat['kategori'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Perihal</span>
                <span class="info-value">: <?= htmlspecialchars($surat['perihal'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Pembuat</span>
                <span class="info-value">: <?= htmlspecialchars($surat['pembuat_nama'] ?? '-'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Dibuat pada</span>
                <span class="info-value">: <?= date('d M Y H:i', strtotime($surat['created_at'])); ?></span>
            </div>
        </div>

        <div class="footer-note">
            <i class="fa fa-info-circle"></i>
            Dokumen ini diperiksa secara otomatis oleh Sistem Pengarsipan Surat.
            <br>Surat dianggap legal jika statusnya <strong>Terverifikasi</strong> atau <strong>Selesai</strong>.
        </div>
    </div>
</body>
</html>
