# Sistem Informasi Pengelolaan Arsip Rapat ITD Adisutjipto

Aplikasi web berbasis **CodeIgniter 4** untuk mengelola arsip rapat (undangan & notulensi) di Program Studi ITD Adisutjipto — mulai dari penjadwalan undangan, pencatatan notulensi beserta dokumentasi foto, hingga rekap laporan bulanan/tahunan.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Struktur Proyek](#struktur-proyek)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi dan Menjalankan di Lokal](#instalasi-dan-menjalankan-di-lokal)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Akun Default](#akun-default)
- [Peran dan Hak Akses](#peran-dan-hak-akses)
- [Verifikasi dan Arsip](#verifikasi-dan-arsip)
- [Skema Database](#skema-database)
- [Daftar Endpoint (Routing)](#daftar-endpoint-routing)
- [Alur dan Aturan Bisnis](#alur-dan-aturan-bisnis)
- [Menjalankan Test](#menjalankan-test)
- [Troubleshooting](#troubleshooting)
- [Catatan dan Keterbatasan](#catatan-dan-keterbatasan)
- [Riwayat Perubahan](#riwayat-perubahan)
- [Lisensi](#lisensi)

## Fitur Utama

- **Login** menggunakan NIP dan kata sandi (session-based, tanpa registrasi mandiri).
- **Hak akses berbasis peran** (Admin, Ketua Program Studi, Sekretaris/Staff, Dosen) — lihat [Peran dan Hak Akses](#peran-dan-hak-akses).
- **Manajemen pengguna** (khusus Admin): tambah, ubah, hapus pengguna, ganti role dan kata sandi.
- **Verifikasi notulensi** oleh Ketua Program Studi (setujui / tolak dengan alasan) — lihat [Verifikasi dan Arsip](#verifikasi-dan-arsip).
- **Arsip Rapat**: pencarian notulensi yang sudah terverifikasi (kata kunci dan rentang tanggal), dapat dibuka semua pengguna yang login.
- **Dashboard** dengan rekap jumlah undangan & notulensi per bulan/tahun serta grafik interaktif (Chart.js).
- **Undangan Rapat**: CRUD lengkap + download surat undangan resmi dalam format **.docx** (digenerate dari template Word).
- **Notulensi Rapat**: CRUD + upload foto dokumentasi + halaman detail. Satu undangan hanya bisa memiliki satu notulensi.
- **Laporan**: Download laporan bulanan atau tahunan dalam format HTML siap cetak (print-ready).

## Teknologi yang Digunakan

| Kategori | Teknologi |
|---|---|
| Backend | PHP 8.2+, CodeIgniter 4.7 |
| Database | MySQL/MariaDB — CI4 Query Builder & Migrations |
| Generate dokumen | [PHPWord](https://github.com/PHPOffice/PHPWord) (`phpoffice/phpword`) untuk mengisi template `.docx` |
| Frontend | HTML5 + Tailwind CSS (via CDN), Chart.js (via CDN) |
| Ikon & Font | Font Awesome 6 (CDN), Google Fonts "Inter" (CDN) |
| Testing | PHPUnit 10 |

> Karena Tailwind CSS, Font Awesome, Google Fonts, dan Chart.js dimuat lewat CDN (lihat [app/Views/Layouts/main.php](app/Views/Layouts/main.php)), **koneksi internet dibutuhkan** agar tampilan aplikasi tampil sempurna saat diakses, meskipun aplikasinya sendiri berjalan lokal.

## Struktur Proyek

```
sistem-arsip-rapat/
├── app/
│   ├── Config/          # Konfigurasi aplikasi (Routes, Database, Filters, App, dll)
│   ├── Controllers/     # AuthController, DashboardController, UndanganController, NotulensiController
│   ├── Database/
│   │   ├── Migrations/  # Skema tabel: users, undangan_rapat, notulensi_rapat
│   │   └── Seeds/       # UserSeeder (akun default)
│   ├── Filters/         # AuthFilter (wajib login), GuestFilter (khusus tamu/belum login)
│   ├── Models/          # UserModel, UndanganRapatModel, NotulensiRapatModel
│   └── Views/           # Auth, Dashboard, Undangan, Notulensi, Layouts/main.php
├── public/              # Document root — index.php, .htaccess, favicon
│   ├── template_undangan_rapat.docx  # Template Word untuk surat undangan
│   └── uploads/dokumentasi/          # Foto dokumentasi rapat (dibuat manual, lihat Instalasi)
├── tests/               # PHPUnit (saat ini masih contoh bawaan CodeIgniter 4)
├── vendor/              # Composer dependencies (hasil `composer install`)
├── writable/            # Folder yang wajib bisa ditulis: cache, logs, session, uploads sementara
├── composer.json
├── env                  # Contoh konfigurasi, disalin menjadi `.env`
└── spark                # CLI CodeIgniter (php spark ...)
```

Folder standar CodeIgniter 4 lainnya (`app/Helpers`, `app/Libraries`, `app/Language`, `app/ThirdParty`) masih mengikuti struktur bawaan framework dan belum dipakai secara khusus oleh aplikasi ini.

## Persyaratan Sistem

- **PHP >= 8.2** dengan ekstensi yang umumnya sudah aktif di XAMPP: `intl`, `mbstring`, `mysqli`, `json`, `curl`.
- **Composer 2.x**
- **MySQL / MariaDB** (bawaan XAMPP)
- **Apache** dengan `mod_rewrite` aktif (bawaan XAMPP) — atau cukup pakai server bawaan `php spark serve`.
- Koneksi internet aktif saat mengakses aplikasi (lihat catatan CDN di atas).

## Instalasi dan Menjalankan di Lokal

### 1. Tempatkan proyek di htdocs

Jika belum, salin/clone proyek ini ke dalam folder `htdocs` XAMPP, misalnya:

```bash
C:\xampp\htdocs\sistem-arsip-rapat
```

### 2. Install dependency Composer

```bash
composer install
```

Perintah ini mengunduh CodeIgniter 4 framework, PHPWord, dan dependency lain ke folder `vendor/` (folder ini di-ignore oleh git, jadi wajib dijalankan setelah clone).

### 3. Buat file environment (`.env`)

Salin file `env` menjadi `.env` di root proyek:

```bash
cp env .env
```

Di Windows Command Prompt, gunakan:

```bash
copy env .env
```

Lalu buka `.env` dan aktifkan (hapus tanda `#`) serta sesuaikan minimal bagian berikut:

```dotenv
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = arsip_rapat
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Lihat detail tiap variabel di bagian [Konfigurasi Environment](#konfigurasi-environment).

### 4. Buat database

Buat database kosong (nama harus sama dengan `database.default.database` di `.env`), misalnya lewat phpMyAdmin atau CLI MySQL:

```sql
CREATE DATABASE arsip_rapat;
```

### 5. Jalankan migration

```bash
php spark migrate
```

Ini akan membuat 3 tabel: `users`, `undangan_rapat`, `notulensi_rapat` (detail di [Skema Database](#skema-database)).

### 6. Jalankan seeder akun default

```bash
php spark db:seed UserSeeder
```

Lihat akun yang dibuat di bagian [Akun Default](#akun-default).

### 7. Siapkan folder upload dokumentasi

Folder `public/uploads/dokumentasi/` **tidak ikut ter-clone** (bukan bagian repo) tetapi wajib ada dan bisa ditulis, karena di situlah foto dokumentasi notulensi disimpan:

```bash
mkdir -p public/uploads/dokumentasi
```

Di Linux/Mac, pastikan juga permission-nya:

```bash
chmod -R 777 writable/
chmod -R 777 public/uploads/
```

Tanpa folder ini, upload foto dokumentasi pada form Notulensi akan gagal.

### 8. Jalankan aplikasi

Pilih salah satu:

**Opsi A — PHP built-in server (paling cepat untuk development):**

```bash
php spark serve
```

Akses di `http://localhost:8080`.

**Opsi B — Apache XAMPP langsung dari folder htdocs:**

Akses lewat `http://localhost/sistem-arsip-rapat/public/`, dan sesuaikan `app.baseURL` di `.env` menjadi:

```dotenv
app.baseURL = 'http://localhost/sistem-arsip-rapat/public/'
```

**Opsi C — Virtual Host Apache** (arahkan DocumentRoot ke folder `public/`):

```apache
<VirtualHost *:80>
    ServerName arsip-rapat.test
    DocumentRoot "C:/xampp/htdocs/sistem-arsip-rapat/public"
    <Directory "C:/xampp/htdocs/sistem-arsip-rapat/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Tambahkan `127.0.0.1 arsip-rapat.test` ke file hosts, lalu set `app.baseURL = 'http://arsip-rapat.test/'`.

### 9. Login

Buka halaman `/login` dan masuk menggunakan salah satu [akun default](#akun-default).

## Konfigurasi Environment

Variabel `.env` yang relevan dengan aplikasi ini (selebihnya mengikuti default CodeIgniter 4):

| Variabel | Contoh Nilai | Keterangan |
|---|---|---|
| `CI_ENVIRONMENT` | `development` | Gunakan `development` di lokal agar error PHP tampil jelas (bukan halaman 500 generik). |
| `app.baseURL` | `http://localhost:8080/` | Wajib disesuaikan dengan cara kamu mengakses aplikasi (lihat [langkah 8](#8-jalankan-aplikasi)). |
| `database.default.hostname` | `localhost` | Host database. |
| `database.default.database` | `arsip_rapat` | Nama database (default di `Config\Database` kosong, wajib diisi). |
| `database.default.username` | `root` | User default XAMPP. |
| `database.default.password` | *(kosong)* | Default XAMPP tidak berpassword. |
| `database.default.DBDriver` | `MySQLi` | Driver database. |
| `database.default.port` | `3306` | Port MySQL default. |

## Akun Default

Dibuat oleh [`UserSeeder`](app/Database/Seeds/UserSeeder.php):

| NIP | Kata Sandi | Nama | Jabatan | Role |
|---|---|---|---|---|
| 198001012005011001 | password | Administrator ITD | Administrator | `admin` |
| 197505102003121001 | password | Dr. Budi Santoso | Kepala Program Studi | `kaprodi` |
| 198502152010012002 | password | Dr. Siti Rahayu | Sekretaris Prodi | `sekretaris` |
| 199003202019031003 | password | Ahmad Fauzi, M.Kom. | Dosen | `dosen` |

Login menggunakan **NIP**, bukan email. Kata sandi disimpan ter-hash (`password_hash`/`password_verify`).

> **Upgrade dari versi tanpa role:** migration [`AddRoleToUsers`](app/Database/Migrations/2026-09-30-100000_AddRoleToUsers.php) menjadikan **semua akun yang sudah ada berstatus Admin** agar tidak ada yang terkunci. Setelah `php spark migrate`, Admin sebaiknya menurunkan role pengguna lain lewat menu **Pengguna**. Pengguna yang sedang login sebelum migrasi harus **login ulang** agar role masuk ke session.

## Peran dan Hak Akses

Akses dijaga oleh filter route `role` ([`RoleFilter`](app/Filters/RoleFilter.php)); menu dan tombol di UI hanya menyesuaikan tampilan lewat helper `has_role()`. Role disimpan di session saat login, sehingga perubahan role oleh Admin baru berlaku setelah pengguna tersebut login ulang.

| Modul | Admin | Kaprodi | Sekretaris/Staff | Dosen |
|---|---|---|---|---|
| Kelola pengguna | ✅ CRUD | ❌ | ❌ | ❌ |
| Undangan (CRUD + download .docx) | ✅ | 👁 lihat saja | ✅ CRUD | ❌ |
| Notulensi (CRUD + foto) | ✅ | 👁 lihat saja | ✅ CRUD | ❌ |
| Dashboard | ✅ | ✅ (+ antrean verifikasi) | ✅ | ✅ (ringkas, tanpa grafik/laporan) |
| Laporan bulanan/tahunan | ✅ | ✅ | ❌ | ❌ |
| Verifikasi notulensi | ❌ | ✅ | ❌ | ❌ |
| Arsip Rapat (terverifikasi saja) | ✅ | ✅ | ✅ | ✅ |

Aturan pengelolaan pengguna: Admin tidak dapat menghapus atau mengubah role akunnya sendiri, tidak dapat menghapus/menurunkan Admin terakhir, dan tidak dapat menghapus pengguna yang sudah memiliki undangan/notulensi (FK `created_by` bersifat CASCADE).

Daftar hadir, berita acara, dan upload dokumen oleh Sekretaris/Staff belum tersedia (tahap berikutnya).

## Verifikasi dan Arsip

Dibuat oleh migration [`AddVerifikasiToNotulensi`](app/Database/Migrations/2026-09-30-110000_AddVerifikasiToNotulensi.php).

- Setiap notulensi punya status: **Menunggu Verifikasi** (default), **Terverifikasi**, atau **Ditolak**.
- Hanya **Ketua Program Studi** yang dapat menyetujui/menolak, lewat halaman detail notulensi. Penolakan wajib disertai alasan, yang tampil ke Sekretaris/Admin di halaman detail.
- Jika notulensi **diubah** oleh Admin/Sekretaris, statusnya kembali ke *Menunggu Verifikasi* (data verifikasi dihapus) karena isinya berubah.
- Notulensi yang sudah ada sebelum migrasi berstatus *Menunggu Verifikasi* dan perlu diverifikasi Kaprodi.
- **Arsip Rapat** (`/arsip`) hanya menampilkan notulensi *Terverifikasi*. Pencarian: kata kunci (acara, tempat, isi notulensi, catatan) dan rentang tanggal rapat (tanggal undangan). Notulensi yang belum terverifikasi tidak dapat dibuka lewat `/arsip/{id}`.
- Daftar Notulensi dapat difilter menurut status; laporan bulanan/tahunan memuat kolom status dan jumlah notulensi terverifikasi.

> Foto dokumentasi disimpan di `public/uploads/dokumentasi/`, sehingga URL langsung ke berkas tetap dapat dibuka oleh siapa pun yang mengetahui nama berkasnya; pembatasan verifikasi hanya berlaku pada halaman aplikasi.

## Skema Database

Dibuat oleh migration [`CreateArsipRapatTables`](app/Database/Migrations/2026-05-08-065614_CreateArsipRapatTables.php).

**`users`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED, PK, AUTO_INCREMENT | |
| nip | VARCHAR(20), UNIQUE | Dipakai sebagai username login |
| nama | VARCHAR(100) | |
| kata_sandi | VARCHAR(255) | Hash bcrypt |
| foto_profil | VARCHAR(255), NULL | Kolom tersedia, tapi belum ada form pengelolaan foto profil di UI |
| jabatan | VARCHAR(100), NULL | Label jabatan (teks bebas, bukan hak akses) |
| role | ENUM(`admin`,`kaprodi`,`sekretaris`,`dosen`), default `dosen` | Hak akses; ditambahkan migration `AddRoleToUsers` |
| created_at, updated_at | DATETIME, NULL | |

**`undangan_rapat`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED, PK, AUTO_INCREMENT | |
| hari | ENUM(Senin..Minggu) | |
| waktu | DATETIME | |
| tempat | VARCHAR(255) | |
| acara | TEXT | |
| created_by | INT UNSIGNED, FK → `users.id` (CASCADE) | Pembuat undangan |
| created_at, updated_at | DATETIME, NULL | |

**`notulensi_rapat`**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED, PK, AUTO_INCREMENT | |
| undangan_id | INT UNSIGNED, **UNIQUE**, FK → `undangan_rapat.id` (CASCADE) | Relasi 1:1 — satu undangan maksimal satu notulensi |
| tgl_rapat | DATE | |
| tema_rapat | VARCHAR(255) | |
| deskripsi_rapat | TEXT | |
| catatan | TEXT, NULL | |
| dokumentasi | VARCHAR(255), NULL | Nama file, disimpan fisik di `public/uploads/dokumentasi/` |
| created_by | INT UNSIGNED, FK → `users.id` (CASCADE) | Pencatat notulensi |
| created_at, updated_at | DATETIME, NULL | |

Relasi singkat: `users` 1—N `undangan_rapat`, `users` 1—N `notulensi_rapat`, `undangan_rapat` 1—1 `notulensi_rapat`.

## Daftar Endpoint (Routing)

Didefinisikan di [app/Config/Routes.php](app/Config/Routes.php). Route `auth` mewajibkan sesi login (`AuthFilter`); route `role:<daftar>` mewajibkan login **dan** salah satu role yang disebut (`RoleFilter`; ditolak → redirect `/dashboard`); route `guest` hanya bisa diakses saat *belum* login (`GuestFilter`).

| Method | URI | Aksi | Filter |
|---|---|---|---|
| GET | `/login` | `AuthController::loginPage` | guest |
| POST | `/login` | `AuthController::login` | guest |
| GET | `/logout` | `AuthController::logout` | - |
| GET | `/`, `/dashboard` | `DashboardController::index` | auth |
| GET | `/dashboard/download` | `DashboardController::downloadLaporan` | role:admin,kaprodi |
| GET | `/undangan` | `UndanganController::index` | role:admin,sekretaris,kaprodi |
| GET | `/undangan/create` | `UndanganController::create` | role:admin,sekretaris |
| POST | `/undangan/store` | `UndanganController::store` | role:admin,sekretaris |
| GET | `/undangan/{id}/edit` | `UndanganController::edit` | role:admin,sekretaris |
| POST | `/undangan/{id}/update` | `UndanganController::update` | role:admin,sekretaris |
| POST | `/undangan/{id}/delete` | `UndanganController::delete` | role:admin,sekretaris |
| GET | `/undangan/{id}/download` | `UndanganController::downloadPdf` | role:admin,sekretaris |
| GET | `/notulensi` | `NotulensiController::index` | role:admin,sekretaris,kaprodi |
| GET | `/notulensi/create` | `NotulensiController::create` | role:admin,sekretaris |
| POST | `/notulensi/store` | `NotulensiController::store` | role:admin,sekretaris |
| GET | `/notulensi/{id}/show` | `NotulensiController::show` | role:admin,sekretaris,kaprodi |
| GET | `/notulensi/{id}/edit` | `NotulensiController::edit` | role:admin,sekretaris |
| POST | `/notulensi/{id}/update` | `NotulensiController::update` | role:admin,sekretaris |
| POST | `/notulensi/{id}/delete` | `NotulensiController::delete` | role:admin,sekretaris |
| POST | `/notulensi/{id}/verifikasi` | `NotulensiController::verifikasi` | role:kaprodi |
| GET | `/arsip` | `ArsipController::index` | auth |
| GET | `/arsip/{id}` | `ArsipController::show` | auth |
| GET | `/users` | `UserController::index` | role:admin |
| GET | `/users/create` | `UserController::create` | role:admin |
| POST | `/users/store` | `UserController::store` | role:admin |
| GET | `/users/{id}/edit` | `UserController::edit` | role:admin |
| POST | `/users/{id}/update` | `UserController::update` | role:admin |
| POST | `/users/{id}/delete` | `UserController::delete` | role:admin |

## Alur dan Aturan Bisnis

- Notulensi **hanya dapat dibuat** untuk undangan yang belum memiliki notulensi — form "Tambah Notulensi" otomatis menyembunyikan undangan yang sudah tercatat.
- Satu undangan **maksimal satu notulensi** (dijaga oleh unique key `undangan_id` di database, bukan hanya validasi aplikasi).
- Undangan **tidak dapat dihapus** jika sudah memiliki notulensi terkait (dicek via `UndanganRapatModel::hasNotulensi()`).
- Download undangan menghasilkan file **.docx** dengan mengisi placeholder (`PERIHAL`, `HARI_TANGGAL`, `WAKTU`, `TEMPAT`, `TANGGAL_PEMBUATAN`) pada template [public/template_undangan_rapat.docx](public/template_undangan_rapat.docx) menggunakan PHPWord.
- Download laporan (bulanan/tahunan) menghasilkan halaman **HTML print-ready** (otomatis memicu `window.print()`), bukan PDF asli.
- Upload dokumentasi notulensi hanya menerima `image/jpeg`, `image/png`, `image/gif`, `image/webp`; saat notulensi diperbarui/dihapus, file foto lama ikut dihapus dari server.

## Menjalankan Test

Proyek memakai PHPUnit (lihat [tests/README.md](tests/README.md) untuk detail lengkap bawaan CodeIgniter 4). Ringkasnya:

```bash
composer install
vendor\bin\phpunit
```

> Test membutuhkan MySQL aktif dan database `arsip_rapat_test` (lihat `phpunit.xml.dist`). Skema dibuat otomatis dari migration.

## Troubleshooting

| Masalah | Kemungkinan Penyebab / Solusi |
|---|---|
| Halaman blank / error 500 tanpa detail | Set `CI_ENVIRONMENT = development` di `.env` agar pesan error PHP ditampilkan. |
| `Unable to connect to the database` | Cek `database.default.*` di `.env`, pastikan database sudah dibuat dan MySQL XAMPP menyala. |
| Tampilan CSS/ikon/grafik tidak muncul | Aset dimuat dari CDN (Tailwind, Font Awesome, Chart.js) — pastikan komputer terhubung internet. |
| Upload foto dokumentasi gagal | Pastikan folder `public/uploads/dokumentasi/` sudah dibuat dan bisa ditulis (lihat [langkah 7](#7-siapkan-folder-upload-dokumentasi)). |
| Download undangan gagal "Template tidak ditemukan" | Pastikan file [public/template_undangan_rapat.docx](public/template_undangan_rapat.docx) tidak terhapus. |
| Halaman redirect terus ke `/login` | Sesi belum tersimpan — cek folder `writable/session` bisa ditulis, dan `app.baseURL` sudah sesuai URL akses. |

## Catatan dan Keterbatasan

- Filter `csrf` sudah tersedia sebagai alias ([app/Config/Filters.php](app/Config/Filters.php)) tetapi belum diaktifkan secara global — form belum dilindungi CSRF token.
- Validasi input di `UndanganController` dan `NotulensiController` masih manual (pengecekan `empty()`); hanya manajemen pengguna yang memakai CodeIgniter Validation Library.
- Role dibaca dari session, jadi perubahan role atau penghapusan akun baru berlaku setelah pengguna bersangkutan login ulang.
- `jabatan` pada tabel `users` hanya label tampilan; hak akses ditentukan kolom `role`.

## Riwayat Perubahan

Migrasi dari versi sebelumnya (custom PHP) ke versi saat ini:

- **Framework**: Custom PHP → CodeIgniter 4
- **Database**: PDO Manual → CI4 Query Builder & Migrations
- **Routing**: Custom Router → CI4 Routes dengan Filters
- **Views**: Plain PHP → CI4 Views dengan Layouts
- **Session**: PHP Native → CI4 Session Library

## Lisensi

MIT — mengikuti lisensi starter [CodeIgniter 4](LICENSE) yang menjadi basis proyek ini.
