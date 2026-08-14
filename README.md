# Sistem Informasi Arsip Surat

Sistem Informasi Arsip Surat (SIAS) berbasis web untuk **Dinas Pendidikan dan Kebudayaan Kabupaten Muaro Jambi (DISDIKBUD)**. Aplikasi ini digunakan untuk mengelola arsip surat masuk, surat keluar, dan disposisi secara digital, lengkap dengan fitur tracking status surat beserta riwayatnya.

Dibangun dengan **PHP** (native / tanpa framework) dan **MySQL (MariaDB)**, menggunakan **Bootstrap 5** dan **Chart.js** sebagai pustaka pendukung tampilan.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Struktur Project](#struktur-project)
- [Role Pengguna](#role-pengguna)
- [Alur Status Surat](#alur-status-surat)
- [Halaman / Modul](#halaman--modul)
- [Struktur Database](#struktur-database)
- [Instalasi](#instalasi)
- [Akun Default](#akun-default)
- [Migrations / SQL](#migrations--sql)
- [Keamanan](#keamanan)

---

## Fitur Utama

| Fitur | Keterangan |
|---|---|
| **Manajemen Surat Masuk** | Pencatatan surat masuk: no surat, tanggal, pengirim, instansi, kategori, perihal, dan lampiran file. |
| **Manajemen Surat Keluar** | Pencatatan surat keluar: no agenda, tujuan, instansi, kategori, isi surat, dan lampiran file. |
| **Disposisi** | Pimpinan dapat membuat disposisi atas surat masuk yang diteruskan ke Tata Usaha, lengkap dengan instruksi dan catatan. |
| **Tracking Surat** | Pemantauan status surat dari tahap `draft` hingga `selesai` beserta progress bar dan timeline riwayat status. |
| **Riwayat Status** | Semua pergerakan status tercatat di tabel `status_history` (siapa, kapan, dan keterangan). |
| **Laporan** | Laporan surat masuk, surat keluar, dan disposisi dengan filter periode tanggal, status, kategori, serta ringkasan per status. Dapat dicetak. |
| **Ekspor CSV** | Unduh data sebagai file CSV (surat masuk, surat keluar, disposisi, dan user). |
| **Dashboard Statistik** | Grafik surat masuk per bulan (line chart) dan perbandingan surat masuk vs keluar (doughnut chart) menggunakan Chart.js. |
| **Manajemen User** | CRUD user oleh admin (nama, email, password, role). |
| **Pencarian Data** | Pencarian kata kunci pada semua halaman daftar. |
| **Upload File Surat** | Lampiran file surat (gambar/PDF/dll) disimpan di `assets/uploads/`. |

---

## Teknologi

- **Bahasa:** PHP 8.2+
- **Database:** MySQL / MariaDB 10.4+
- **Frontend:** HTML, CSS, Bootstrap 5.3, Font Awesome 6
- **Charting:** Chart.js (CDN)
- **Server:** Apache (XAMPP)
- **Webserver lokasi:** `htdocs`

---

## Struktur Project

```
arsip_surat/
├── login.php                     # Halaman login
├── logout.php                    # Proses logout
├── tracking.php                  # Halaman tracking status surat (semua role)
├── generate_password.php         # Utility hash password (contoh)
├── images.jpg                    # Logo DISDIKBUD
├── nav_test.php                  # Halaman uji coba navbar
├── admin/                        # Halaman khusus role Admin
│   ├── index.php                 # Dashboard admin + grafik statistik
│   ├── surat_masuk.php           # Daftar surat masuk
│   ├── surat_masuk_tambah.php    # Tambah surat masuk
│   ├── surat_masuk_edit.php      # Edit surat masuk
│   ├── surat_masuk_hapus.php     # Hapus surat masuk
│   ├── surat_keluar.php          # Daftar surat keluar
│   ├── surat_keluar_tambah.php   # Tambah surat keluar
│   ├── surat_keluar_edit.php     # Edit surat keluar
│   ├── surat_keluar_hapus.php    # Hapus surat keluar
│   ├── disposisi.php             # Daftar disposisi
│   ├── disposisi_edit.php        # Edit disposisi
│   ├── disposisi_hapus.php       # Hapus disposisi
│   ├── laporan.php               # Laporan + cetak (admin & pimpinan)
│   ├── export.php                # Ekspor data ke CSV
│   ├── users.php                 # Manajemen user
│   ├── user_tambah.php           # Tambah user
│   ├── user_edit.php             # Edit user
│   └── profil.php                # Halaman profil
├── pimpinan/                     # Halaman khusus role Pimpinan
│   ├── index.php                 # Dashboard pimpinan
│   ├── surat_masuk.php           # Daftar surat masuk (verifikasi)
│   ├── surat_keluar.php          # Daftar surat keluar (verifikasi)
│   ├── disposisi.php             # Daftar disposisi yang diberikan
│   ├── disposisi_tambah.php      # Buat disposisi baru
│   ├── pimpinan_export.php       # Ekspor data pimpinan
│   └── profil.php                # Halaman profil
├── tata_usaha/                   # Halaman khusus role Tata Usaha
│   ├── index.php                 # Dashboard tata usaha
│   ├── surat_masuk.php           # Daftar surat masuk
│   ├── surat_masuk_tambah.php    # Tambah surat masuk
│   ├── surat_masuk_edit.php      # Edit surat masuk
│   ├── surat_keluar.php          # Daftar surat keluar
│   ├── surat_keluar_tambah.php   # Tambah surat keluar
│   ├── surat_keluar_edit.php     # Edit surat keluar
│   ├── disposisi.php             # Disposisi yang diterima
│   ├── disposisi_update.php      # Update status disposisi
│   └── profil.php                # Halaman profil
├── includes/                     # File bersama (include)
│   ├── db.php                    # Koneksi database
│   ├── auth.php                  # Autentikasi & cek role
│   ├── tracking.php              # Helper tracking status
│   ├── header.php                # Navbar admin
│   ├── header_pimpinan.php       # Navbar pimpinan
│   ├── header_tata_usaha.php     # Navbar tata usaha
│   └── footer.php                # Footer bersama
├── assets/
│   ├── css/style.css             # Style tambahan
│   └── uploads/                  # File surat yang diupload
│       ├── surat_masuk/
│       └── surat_keluar/
└── database/
    ├── arsip_surat.sql           # Dump lengkap database
    ├── fix_user_delete.sql       # Fix penghapusan user
    ├── role_restructure.sql      # Migrasi role & status
    └── tracking_update.sql       # Migrasi fitur tracking
```

---

## Role Pengguna

Sistem menggunakan 3 role dengan akses berbeda:

### 1. Admin
- Akses penuh ke semua modul (dashboard, surat masuk/keluar, disposisi, laporan, user).
- Mengelola user (tambah/edit/hapus).
- Finalisasi status menjadi `selesai` (pengarsipan) untuk surat masuk.
- Melihat & melacak seluruh data.

### 2. Pimpinan
- Dashboard ringkasan surat yang menunggu verifikasi.
- Memverifikasi surat (`menunggu_verifikasi` → `terverifikasi`).
- Membuat disposisi untuk surat masuk.
- Melihat laporan.

### 3. Tata Usaha
- Mencatat/mengelola surat masuk dan surat keluar.
- Mengajukan surat untuk verifikasi (`draft` → `menunggu_verifikasi`).
- Menerima dan memproses disposisi dari pimpinan.
- Menyelesaikan surat keluar & disposisi.

---

## Alur Status Surat

Alur status berlaku untuk surat masuk, surat keluar, dan disposisi:

```
Draft → Menunggu Verifikasi → Terverifikasi → Diproses Tata Usaha → Selesai
```

### Aturan transisi status per role

| Jenis | Role | Dari → Ke |
|---|---|---|
| **Surat Masuk** | Tata Usaha | `draft` → `menunggu_verifikasi`, `terverifikasi` → `diproses_tata_usaha` |
| | Pimpinan | `menunggu_verifikasi` → `terverifikasi` |
| | Admin | `diproses_tata_usaha` → `selesai` |
| **Surat Keluar** | Tata Usaha | `draft` → `menunggu_verifikasi`, `terverifikasi` → `diproses_tata_usaha`, `diproses_tata_usaha` → `selesai` |
| | Pimpinan | `menunggu_verifikasi` → `terverifikasi` |
| **Disposisi** | Tata Usaha | `draft` → `diproses_tata_usaha`, `diproses_tata_usaha` → `selesai` |

Aturan ini dikelola pada `includes/tracking.php` (fungsi `getAllowedTransitions()`).

Setiap perubahan status dicatat ke tabel `status_history` dan ditampilkan sebagai timeline di halaman tracking.

---

## Halaman / Modul

| Halaman | Deskripsi |
|---|---|
| `login.php` | Autentikasi user; redirect sesuai role. |
| `admin/index.php` | Dashboard: total user, surat masuk, surat keluar, disposisi + grafik. |
| `pimpinan/index.php` | Dashboard: statistik verifikasi & disposisi yang diberikan. |
| `tata_usaha/index.php` | Dashboard tata usaha. |
| `tracking.php` | Detail surat/disposisi, progress bar status, riwayat, dan form ubah status (sesuai izin role). |
| `admin/laporan.php` | Laporan dengan filter tanggal/status/kategori + cetak. |
| `admin/export.php` | Ekspor data ke CSV. |
| `pimpinan/pimpinan_export.php` | Ekspor data khusus pimpinan. |
| `admin/users.php` | CRUD user (khusus admin). |

---

## Struktur Database

Database: **`arsip_surat`**

| Tabel | Keterangan |
|---|---|
| `users` | Data pengguna (`id`, `nama`, `email`, `password`, `role`, `created_at`). |
| `surat_masuk` | Data surat masuk (`no_agenda`, `no_surat`, `tgl_surat`, `tgl_diterima`, `pengirim`, `instansi`, `perihal`, `kategori`, `file_surat`, `status`). |
| `surat_keluar` | Data surat keluar (`no_agenda`, `no_surat`, `tgl_surat`, `tujuan`, `instansi`, `kategori`, `perihal`, `isi_surat`, `file_surat`, `pembuat_id`, `penyetuju_id`, `status`). |
| `disposisi` | Data disposisi (`surat_masuk_id`, `pengirim_id`, `penerima_id`, `tgl_disposisi`, `instruksi`, `catatan_pimpinan`, `status`). |
| `status_history` | Riwayat pergerakan status (`ref_type`, `ref_id`, `status`, `keterangan`, `updated_by`, `created_at`). |

Nilai `role`: `admin`, `pimpinan`, `tata_usaha`.

Nilai `status` (enum): `draft`, `menunggu_verifikasi`, `terverifikasi`, `diproses_tata_usaha`, `selesai`.

---

## Instalasi

### Prasyarat
- XAMPP (Apache + PHP 8.2+ + MySQL/MariaDB) atau server web sejenis.

### Langkah Instalasi

1. **Salin project** ke direktori web server:
   ```
   /Applications/XAMPP/xamppfiles/htdocs/arsip_surat/
   ```
   (atau `C:\xampp\htdocs\arsip_surat\` di Windows)

2. **Buat database:**
   - Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Buat database baru bernama `arsip_surat`.

3. **Import database:**
   - Import file `database/arsip_surat.sql` ke database `arsip_surat`.

4. **Konfigurasi koneksi database:**
   - Sesuaikan `includes/db.php` jika user/password MySQL berbeda:
     ```php
     $host = "localhost";
     $user = "root";
     $pass = "";
     $db   = "arsip_surat";
     ```

5. **Buat folder upload (jika belum ada):**
   ```
   assets/uploads/surat_masuk/
   assets/uploads/surat_keluar/
   ```

6. **Akses aplikasi:**
   Buka browser dan kunjungi:
   ```
   http://localhost/arsip_surat/login.php
   ```

---

## Akun Default

| Role | Email | Password |
|---|---|---|
| Admin | `admin@mail.com` | `admin123` |
| Pimpinan | `fajar@gmail.com` | (lihat keterangan di bawah) |
| Tata Usaha | `tatausaha@mail.com` | `tatausaha123` |

> **Catatan:** Password tersimpan dalam bentuk hash bcrypt di database. Gunakan `generate_password.php` untuk membuat hash password baru.

---

## Migrations / SQL

File tambahan di folder `database/` untuk update struktur tanpa harus import ulang dump lengkap:

| File | Fungsi |
|---|---|
| `tracking_update.sql` | Menambahkan tabel `status_history` dan memetakan status lama ke skema baru. |
| `role_restructure.sql` | Mengubah role lama (`staf` → `pimpinan`, `user` → `tata_usaha`) dan status (`diproses_kasi_pais` → `diproses_tata_usaha`). |
| `fix_user_delete.sql` | Perbaikan relasi saat user dihapus. |

---

## Keamanan

- Password di-hash menggunakan `password_hash()` (bcrypt) dan diverifikasi dengan `password_verify()`.
- Setiap halaman terproteksi melalui `includes/auth.php` (harus login) dan `requireRole()` (pembatasan per role).
- Session digunakan untuk autentikasi; logout melalui `logout.php`.

> **Catatan pengembangan:** Beberapa query masih menggunakan interpolasi langsung nilai `$_GET`/`$_POST`. Disarankan menggunakan **prepared statements** (`mysqli_prepare`/`PDO`) untuk mencegah SQL injection pada pengembangan selanjutnya.
