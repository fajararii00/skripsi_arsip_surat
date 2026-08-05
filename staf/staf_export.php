<?php
include "../includes/auth.php";
include "../includes/db.php";

// pastikan role staf
if ($_SESSION['role'] != 'staf') {
    header("Location: ../login.php");
    exit;
}

// Ambil parameter jenis export
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Set nama file CSV
$filename = $type . "_export_" . date("Y-m-d") . ".csv";

// Header supaya otomatis download CSV
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");

// Buka output untuk tulis CSV
$output = fopen("php://output", "w");

// Tentukan query dan header berdasarkan type
switch ($type) {
    case "surat_masuk":
        fputcsv($output, ["No", "No Surat", "Tanggal Surat", "Tanggal Diterima", "Pengirim", "Instansi", "Kategori", "Perihal"]);
        $query = "SELECT * FROM surat_masuk ORDER BY tgl_surat DESC";
        $result = mysqli_query($conn, $query);
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $no++,
                $row['no_surat'],
                $row['tgl_surat'],
                $row['tgl_diterima'],
                $row['pengirim'],
                $row['instansi'],
                $row['kategori'],
                $row['perihal']
            ]);
        }
        break;

    case "surat_keluar":
        fputcsv($output, ["No", "No Agenda", "No Surat", "Tanggal Surat", "Tujuan", "Instansi", "Kategori", "Perihal", "Status"]);
        $query = "SELECT * FROM surat_keluar ORDER BY tgl_surat DESC";
        $result = mysqli_query($conn, $query);
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $no++,
                $row['no_agenda'],
                $row['no_surat'],
                $row['tgl_surat'],
                $row['tujuan'],
                $row['instansi'],
                $row['kategori'],
                $row['perihal'],
                $row['status']
            ]);
        }
        break;

    case "disposisi":
        fputcsv($output, ["No", "No Surat", "Perihal", "Pengirim", "Penerima", "Tanggal Disposisi", "Instruksi", "Status"]);
        $user_id = $_SESSION['user_id']; // hanya disposisi untuk staf login
        $query = "
          SELECT d.*, sm.no_surat, sm.perihal, u1.nama AS pengirim, u2.nama AS penerima
          FROM disposisi d
          JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
          JOIN users u1 ON d.pengirim_id = u1.id
          JOIN users u2 ON d.penerima_id = u2.id
          WHERE d.penerima_id = '$user_id'
          ORDER BY d.created_at DESC
        ";
        $result = mysqli_query($conn, $query);
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $no++,
                $row['no_surat'],
                $row['perihal'],
                $row['pengirim'],
                $row['penerima'],
                $row['tgl_disposisi'],
                $row['instruksi'],
                $row['status']
            ]);
        }
        break;

    default:
        fputcsv($output, ["Jenis data tidak valid atau tidak diizinkan"]);
        break;
}

fclose($output);
exit;
