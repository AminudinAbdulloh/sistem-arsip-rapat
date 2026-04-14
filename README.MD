# Dokumentasi Sistem Informasi Pengelolaan Arsip Rapat
## Program Studi Informatika — Institut Teknologi Dirgantara Adisutjipto

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Teknologi yang Digunakan](#2-teknologi-yang-digunakan)
3. [Struktur Direktori](#3-struktur-direktori)
4. [Instalasi & Konfigurasi](#4-instalasi--konfigurasi)
5. [Skema Database](#5-skema-database)
6. [Arsitektur Aplikasi](#6-arsitektur-aplikasi)
7. [Fitur & Alur Penggunaan](#7-fitur--alur-penggunaan)
8. [Referensi API Internal (Routing)](#8-referensi-api-internal-routing)
9. [Keamanan](#9-keamanan)
10. [Panduan Pengembangan Lanjutan](#10-panduan-pengembangan-lanjutan)

---

## 1. Gambaran Umum

Sistem Informasi Pengelolaan Arsip Rapat adalah aplikasi web berbasis PHP yang dibangun untuk mengelola arsip kegiatan rapat di Program Studi Informatika, Institut Teknologi Dirgantara Adisutjipto (ITDA). Aplikasi ini memungkinkan pengguna untuk:

- Membuat dan mengelola **undangan rapat** beserta surat undangan resmi dalam format `.docx`
- Mencatat dan mengelola **notulensi rapat** lengkap dengan dokumentasi foto dan dokumen pendukung
- Melihat **statistik** jumlah rapat per bulan dalam bentuk grafik batang
- Mengunduh **laporan** bulanan maupun tahunan dalam format HTML yang siap cetak (PDF melalui browser)

Aplikasi dibangun menggunakan pola **MVC (Model-View-Controller)** sederhana tanpa framework, dengan router berbasis query string (`?url=controller/action/param`).

---

## 2. Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| Bahasa pemrograman | PHP 8.1+ |
| Database | MySQL 8.0+ |
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| Ikon | Font Awesome 6.4.0 (CDN) |
| Library PHP | `vlucas/phpdotenv` (manajemen environment) |
| Generate DOCX | Native PHP `ZipArchive` + template `.docx` |
| Web Server | Apache (dengan mod_headers, mod_rewrite) |
| Format Tanggal | PHP `IntlDateFormatter` (Bahasa Indonesia) |

---

## 3. Struktur Direktori

```
Sistem-Arsip-Rapat/
├── index.php                        # Entry point & router utama
├── .env                             # Konfigurasi environment (tidak di-commit)
├── .htaccess                        # Konfigurasi Apache (keamanan & header)
├── .gitignore
│
├── config/
│   ├── database.php                 # Koneksi database (singleton)
│   └── schema.sql                   # DDL skema database
│
├── app/
│   ├── controllers/
│   │   ├── Controller.php           # Base Controller (helper umum)
│   │   ├── AuthController.php       # Login & logout
│   │   ├── DashboardController.php  # Dashboard & unduh laporan
│   │   ├── UndanganController.php   # CRUD undangan + generate DOCX
│   │   └── NotulensiController.php  # CRUD notulensi + upload file
│   │
│   ├── models/
│   │   ├── Model.php                # Base Model (query helper)
│   │   ├── User.php                 # Model pengguna
│   │   ├── Undangan.php             # Model undangan rapat
│   │   └── Notulensi.php            # Model notulensi + file
│   │
│   ├── views/
│   │   ├── layouts/
│   │   │   └── main.php             # Layout utama (sidebar, navbar, modal)
│   │   ├── auth/
│   │   │   └── login.php            # Halaman login
│   │   ├── dashboard/
│   │   │   └── index.php            # Halaman dashboard & grafik
│   │   ├── undangan/
│   │   │   ├── index.php            # Daftar undangan
│   │   │   └── form.php             # Form tambah/edit undangan
│   │   ├── notulensi/
│   │   │   ├── index.php            # Daftar notulensi
│   │   │   ├── form.php             # Form tambah/edit notulensi
│   │   │   ├── detail.php           # Detail notulensi + lightbox foto
│   │   │   └── _dokumen_item.php    # Partial: item dokumen pendukung
│   │   ├── partials/
│   │   │   └── _pagination.php      # Partial: navigasi halaman
│   │   └── laporan/
│   │       └── cetak.php            # Template laporan printable
│   │
│   └── helpers/
│       ├── DateHelper.php           # Pemformatan tanggal Bahasa Indonesia
│       └── FileUploadHelper.php     # Validasi & penyimpanan file upload
│
├── public/
│   ├── images/
│   │   └── Logo_ITDA_HD.png
│   ├── templates/
│   │   └── undangan_template.docx   # Template surat undangan
│   └── uploads/
│       ├── dokumentasi/             # Foto dokumentasi rapat (auto-generated)
│       └── dokumen/                 # Dokumen pendukung rapat (auto-generated)
│
└── vendor/                          # Composer dependencies (tidak di-commit)
```

---

## 4. Instalasi & Konfigurasi

### Prasyarat

- PHP 8.1 atau lebih baru dengan ekstensi: `mysqli`, `intl`, `zip`, `fileinfo`
- MySQL 8.0+
- Apache dengan `mod_headers` aktif
- Composer

### Langkah Instalasi

**1. Clone / salin project ke direktori web server**

```bash
# Contoh untuk XAMPP di Windows
cp -r Sistem-Arsip-Rapat/ D:/xampp/htdocs/
```

**2. Install dependensi PHP**

```bash
cd Sistem-Arsip-Rapat
composer install
```

**3. Buat database dan jalankan skema**

```sql
-- Di MySQL client atau phpMyAdmin
SOURCE config/schema.sql;
```

**4. Buat file `.env` di root project**

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=password_anda
DB_NAME=arsip_rapat_itd
```

**5. Buat direktori upload (jika belum ada)**

```bash
mkdir -p public/uploads/dokumentasi
mkdir -p public/uploads/dokumen
chmod 755 public/uploads/dokumentasi
chmod 755 public/uploads/dokumen
```

**6. Buat akun pengguna pertama**

Jalankan query berikut di MySQL untuk membuat akun admin:

```sql
INSERT INTO users (nip, nama, password)
VALUES (
  '198001012010011001',
  'Nama Administrator',
  '$2y$10$...'  -- Hash dari password_hash('password_anda', PASSWORD_BCRYPT)
);
```

Atau gunakan script PHP sementara untuk generate hash:

```php
<?php echo password_hash('password_anda', PASSWORD_BCRYPT); ?>
```

**7. Letakkan template surat undangan**

Tempatkan file `undangan_template.docx` di `public/templates/`. Template harus mengandung placeholder berikut:

| Placeholder | Keterangan |
|---|---|
| `{{PERIHAL}}` | Acara/perihal rapat |
| `{{HARI_TGL}}` | Hari dan tanggal rapat (contoh: Senin / 12 Januari 2025) |
| `{{WAKTU}}` | Jam mulai rapat (contoh: 09.00 WIB - Selesai) |
| `{{TEMPAT}}` | Lokasi rapat |
| `{{TGL_SURAT}}` | Tanggal pembuatan surat |

### Konfigurasi `index.php`

Pastikan urutan pemanggilan di `index.php` adalah sebagai berikut:

```php
// 1. Muat vendor (paling atas)
require_once __DIR__ . '/vendor/autoload.php';

// 2. Muat .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 3. Baru muat database
require_once __DIR__ . '/config/database.php';
```

---

## 5. Skema Database

### Tabel `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT (PK, AUTO_INCREMENT) | ID pengguna |
| `nip` | VARCHAR(30) UNIQUE | Nomor Induk Pegawai |
| `nama` | VARCHAR(100) | Nama lengkap |
| `password` | VARCHAR(255) | Hash bcrypt |
| `foto_profil` | VARCHAR(255) | Nama file foto (opsional) |
| `created_at` | TIMESTAMP | Waktu dibuat |

### Tabel `undangan_rapat`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT (PK) | ID undangan |
| `waktu` | DATETIME | Waktu rapat |
| `tempat` | VARCHAR(200) | Lokasi rapat |
| `acara` | TEXT | Agenda/perihal rapat |
| `tgl_surat` | DATE | Tanggal pembuatan surat |
| `dibuat_oleh` | INT (FK → users.id) | ID pembuat |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### Tabel `notulensi_rapat`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT (PK) | ID notulensi |
| `undangan_id` | INT (FK → undangan_rapat.id) | Relasi ke undangan (CASCADE DELETE) |
| `deskripsi_rapat` | TEXT | Isi/hasil rapat |
| `catatan` | TEXT (nullable) | Catatan tambahan |
| `dibuat_oleh` | INT (FK → users.id) | ID pembuat |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### Tabel `notulensi_dokumentasi`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT (PK) | ID foto |
| `notulensi_id` | INT (FK → notulensi_rapat.id) | Relasi (CASCADE DELETE) |
| `filename` | VARCHAR(255) | Nama file tersimpan di disk |
| `created_at` | TIMESTAMP | Waktu diupload |

### Tabel `notulensi_dokumen`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | INT (PK) | ID dokumen |
| `notulensi_id` | INT (FK → notulensi_rapat.id) | Relasi (CASCADE DELETE) |
| `filename` | VARCHAR(255) | Nama file tersimpan di disk |
| `original_name` | VARCHAR(255) | Nama file asli |
| `mime_type` | VARCHAR(100) | Tipe MIME file |
| `created_at` | TIMESTAMP | Waktu diupload |

### Diagram Relasi

```
users ──────────────────┐
  │ (dibuat_oleh)       │
  ├─> undangan_rapat    │
  │     │               │
  │     └─> notulensi_rapat ──(dibuat_oleh)
  │               │
  │               ├─> notulensi_dokumentasi
  │               └─> notulensi_dokumen
  │
  └──────────────────────┘
```

---

## 6. Arsitektur Aplikasi

### Pola MVC

Aplikasi menggunakan pola MVC sederhana tanpa framework:

```
Request (HTTP)
    │
    ▼
index.php  ──→  Router  ──→  Controller  ──→  Model  ──→  Database
                                  │
                                  └──→  View (PHP template)
                                              │
                                              └──→  Layout (main.php)
```

### Router

Router membaca parameter `?url=` dan memecahnya menjadi tiga segmen:

```
?url=notulensi/edit/5
      │         │    └── $param  = "5"
      │         └──────── $action = "edit"
      └────────────────── controller = NotulensiController
```

### Base Controller (`Controller.php`)

Menyediakan method helper yang diwarisi semua controller:

| Method | Keterangan |
|---|---|
| `view($path, $data)` | Render file view dengan data |
| `renderMain($title, $content, $data)` | Render halaman dengan layout utama |
| `redirect($path)` | Redirect ke URL relatif |
| `requireLogin()` | Paksa login, redirect jika belum |
| `flash($type, $msg)` | Simpan flash message ke session |
| `isPost()` | Cek apakah request adalah POST |
| `input($key)` | Ambil nilai dari `$_POST` |
| `trimInput($key)` | Ambil dan trim nilai dari `$_POST` |
| `generateCsrfToken()` | Buat/ambil CSRF token dari session |
| `verifyCsrfToken()` | Verifikasi CSRF token, die jika gagal |

### Base Model (`Model.php`)

Menyediakan query helper berbasis `mysqli` prepared statements:

| Method | Keterangan |
|---|---|
| `count()` | Hitung total baris di tabel |
| `delete($id)` | Hapus baris berdasarkan ID |
| `fetchAll($sql, $types, ...$params)` | Eksekusi query, kembalikan semua baris |
| `fetchOne($sql, $types, ...$params)` | Eksekusi query, kembalikan satu baris |
| `execute($sql, $types, ...$params)` | Eksekusi INSERT/UPDATE/DELETE |
| `insertGetId($sql, $types, ...$params)` | INSERT dan kembalikan last insert ID |

---

## 7. Fitur & Alur Penggunaan

### 7.1 Autentikasi

**Login** (`?url=auth/login`)

- Pengguna memasukkan NIP dan kata sandi
- Sistem memverifikasi dengan `password_verify()` terhadap hash bcrypt di database
- Tersedia mekanisme **rate limiting**: maksimal 10 percobaan gagal, lalu akun terkunci 5 menit
- Setelah login berhasil, session di-regenerate untuk mencegah session fixation
- Session otomatis kadaluarsa setelah **1 jam** tidak aktif

**Logout** (`?url=auth/logout`)

- Menghancurkan seluruh session dengan `session_destroy()`

---

### 7.2 Dashboard

**URL:** `?url=dashboard`

Menampilkan:
- Statistik total undangan dan notulensi
- Grafik batang horizontal jumlah undangan per bulan
- Panel unduh laporan bulanan dan tahunan

**Unduh Laporan Bulanan** (`?url=dashboard/downloadBulanan&year=Y&month=M`)

- Menghasilkan halaman HTML printable yang memuat tabel undangan dan notulensi di bulan yang dipilih
- Browser akan langsung membuka dialog cetak (`window.print()`)

**Unduh Laporan Tahunan** (`?url=dashboard/downloadTahunan&year=Y`)

- Sama seperti laporan bulanan namun mencakup seluruh data dalam satu tahun

---

### 7.3 Undangan Rapat

**Daftar** (`?url=undangan`)

- Menampilkan semua undangan dengan paginasi 8 data per halaman
- Kolom: No, Hari/Tanggal Rapat, Waktu, Tempat, Agenda, Tanggal Surat, Aksi

**Tambah** (`?url=undangan/create`)

- Field: Waktu Rapat, Tanggal Surat, Tempat, Acara/Perihal
- Validasi: semua field wajib diisi

**Edit** (`?url=undangan/edit/{id}`)

- Form yang sama dengan tambah, data di-pre-fill dari database

**Hapus** (`?url=undangan/delete/{id}`)

- Hanya bisa diproses via POST (dari modal konfirmasi)
- Karena ada relasi CASCADE DELETE, menghapus undangan akan otomatis menghapus notulensi terkait beserta semua file-nya

**Generate Surat Undangan** (`?url=undangan/doc/{id}`)

- Membaca `public/templates/undangan_template.docx`
- Mengganti placeholder dengan data undangan menggunakan `ZipArchive` + manipulasi XML
- File `.docx` langsung didownload oleh browser

---

### 7.4 Notulensi Rapat

**Daftar** (`?url=notulensi`)

- Menampilkan notulensi dengan paginasi 8 data per halaman
- Menampilkan thumbnail foto pertama jika ada

**Tambah** (`?url=notulensi/create`)

Aturan tambah notulensi:
- Hanya undangan yang **belum memiliki notulensi** yang bisa dipilih
- Notulensi **tidak bisa dibuat** sebelum waktu rapat tiba
- Bisa upload banyak foto (JPG, PNG, GIF, WEBP) sekaligus
- Bisa upload banyak dokumen (PDF, Word, Excel, PowerPoint) sekaligus
- Upload dilakukan via `fetch()` (AJAX), tidak reload halaman

**Edit** (`?url=notulensi/edit/{id}`)

- Bisa mengubah deskripsi, catatan, dan undangan yang dituju
- Bisa menghapus foto/dokumen lama (centang checkbox)
- Bisa menambah foto/dokumen baru
- Semua undangan (bukan hanya yang belum punya notulensi) tersedia sebagai pilihan

**Detail** (`?url=notulensi/detail/{id}`)

- Menampilkan informasi rapat lengkap
- **Galeri foto** dengan lightbox (navigasi keyboard: ← →, tutup: Esc)
- Daftar dokumen pendukung dengan tombol unduh

**Hapus** (`?url=notulensi/delete/{id}`)

- Menghapus notulensi beserta semua foto dan dokumen dari disk

---

### 7.5 Upload File

Konfigurasi upload (di `FileUploadHelper.php`):

| Parameter | Nilai |
|---|---|
| Ukuran maksimum per file | 10 MB |
| Format foto yang diterima | JPG, PNG, GIF, WEBP |
| Format dokumen yang diterima | PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX |

Proses validasi upload:
1. Cek error upload PHP
2. Cek ukuran file
3. Deteksi MIME type sebenarnya menggunakan `finfo` (bukan dari header browser)
4. Validasi ekstensi harus sesuai dengan MIME type yang terdeteksi
5. Simpan dengan nama acak (`prefix_timestamp_random.ext`)

---

## 8. Referensi API Internal (Routing)

Semua URL menggunakan format `index.php?url=...`.

### Auth

| URL | Method | Keterangan |
|---|---|---|
| `auth/login` | GET | Tampilkan form login |
| `auth/login` | POST | Proses login |
| `auth/logout` | GET | Logout & hancurkan session |

### Dashboard

| URL | Method | Keterangan |
|---|---|---|
| `dashboard` | GET | Halaman dashboard |
| `dashboard/downloadBulanan?year=Y&month=M` | GET | Laporan bulanan printable |
| `dashboard/downloadTahunan?year=Y` | GET | Laporan tahunan printable |

### Undangan

| URL | Method | Keterangan |
|---|---|---|
| `undangan` | GET | Daftar undangan (+ `?page=N`) |
| `undangan/create` | GET | Form tambah |
| `undangan/create` | POST | Simpan undangan baru |
| `undangan/edit/{id}` | GET | Form edit |
| `undangan/edit/{id}` | POST | Perbarui undangan |
| `undangan/delete/{id}` | POST | Hapus undangan |
| `undangan/doc/{id}` | GET | Download surat `.docx` |

### Notulensi

| URL | Method | Keterangan |
|---|---|---|
| `notulensi` | GET | Daftar notulensi (+ `?page=N`) |
| `notulensi/create` | GET | Form tambah |
| `notulensi/create` | POST | Simpan notulensi baru + upload file |
| `notulensi/edit/{id}` | GET | Form edit |
| `notulensi/edit/{id}` | POST | Perbarui notulensi + kelola file |
| `notulensi/delete/{id}` | POST | Hapus notulensi + semua file |
| `notulensi/detail/{id}` | GET | Halaman detail |
| `notulensi/deleteFoto/{id}` | POST | Hapus satu foto |
| `notulensi/deleteDokumen/{id}` | POST | Hapus satu dokumen |

---

## 9. Keamanan

### Mekanisme yang Sudah Diterapkan

**CSRF Protection**
- Setiap form POST menyertakan hidden input `csrf_token`
- Token disimpan di session dan diverifikasi dengan `hash_equals()` (mencegah timing attack)
- Termasuk modal hapus di layout utama

**SQL Injection Prevention**
- Semua query menggunakan **prepared statements** `mysqli` dengan `bind_param()`
- Tidak ada query yang menggunakan string concatenation dengan input pengguna

**File Upload Security**
- MIME type dideteksi dari isi file (`finfo`), bukan dari ekstensi atau header browser
- Ekstensi divalidasi silang dengan MIME type
- File disimpan dengan nama acak (tidak bisa ditebak)
- Akses langsung ke file PHP di folder upload diblokir via `.htaccess`

**Session Security**
- Session ID di-regenerate setelah login berhasil (`session_regenerate_id(true)`)
- Cookie session: `httponly=true`, `samesite=Lax`
- Session kadaluarsa otomatis setelah 1 jam tidak aktif

**Rate Limiting Login**
- Maksimal 10 percobaan login gagal
- Terkunci otomatis selama 5 menit setelah batas tercapai

**HTTP Security Headers** (via `.htaccess`)

```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
X-XSS-Protection: 1; mode=block
```

**Directory Listing**
- Dinonaktifkan dengan `Options -Indexes`

**File `.env`**
- Diblokir akses publik via `.htaccess`
- Ditambahkan ke `.gitignore`

### Rekomendasi Tambahan untuk Production

- Aktifkan HTTPS dan uncomment redirect HTTP→HTTPS di `.htaccess`
- Ubah `session_set_cookie_params` agar `secure=true` benar-benar aktif di HTTPS
- Batasi ukuran upload di `php.ini`: `upload_max_filesize` dan `post_max_size`
- Pertimbangkan menambahkan logging untuk percobaan login gagal

---

## 10. Panduan Pengembangan Lanjutan

### Menambahkan Controller Baru

1. Buat file di `app/controllers/NamaController.php`
2. Extend class `Controller`
3. Panggil `$this->requireLogin()` di setiap method yang membutuhkan autentikasi
4. File controller akan otomatis ditemukan oleh router selama penamaan konsisten

```php
<?php
require_once BASE_PATH . '/app/controllers/Controller.php';

class ContohController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->renderMain('Judul Halaman', 'contoh/index', [
            'data' => 'nilai',
        ]);
    }
}
```

### Menambahkan Model Baru

1. Buat file di `app/models/NamaModel.php`
2. Extend class `Model`
3. Definisikan properti `$table`

```php
<?php
require_once BASE_PATH . '/app/models/Model.php';

class Contoh extends Model
{
    protected string $table = 'nama_tabel';

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE id = ?",
            'i', $id
        );
    }
}
```

### Menambahkan View Baru

1. Buat file di `app/views/namacontroller/namaview.php`
2. View dapat langsung menggunakan variabel yang di-pass dari controller
3. Gunakan `$this->generateCsrfToken()` di dalam form

### Helper yang Tersedia di View

**DateHelper** — pemformatan tanggal Bahasa Indonesia:

```php
DateHelper::tanggal('2025-01-12')         // "12 Januari 2025"
DateHelper::tanggalLengkap('2025-01-12')  // "Senin, 12 Januari 2025"
DateHelper::tanggalSurat('2025-01-12')    // "Senin / 12 Januari 2025"
DateHelper::jam('2025-01-12 09:00:00')    // "09.00 WIB"
DateHelper::jamSelesai('2025-01-12 09:00:00') // "09.00 WIB - Selesai"
```

**FileUploadHelper** — ikon dan warna untuk dokumen:

```php
FileUploadHelper::iconClass('application/pdf')  // "fa-file-pdf"
FileUploadHelper::iconColor('application/pdf')  // "#ef4444"
```

### Konvensi Kode

- Nama controller: `PascalCase` + suffix `Controller` (contoh: `UndanganController`)
- Nama model: `PascalCase` tanpa suffix (contoh: `Undangan`)
- Nama view: `snake_case` sesuai folder controller (contoh: `undangan/form.php`)
- Method controller: `camelCase`, nama sesuai aksi (contoh: `create`, `edit`, `delete`)
- Semua input pengguna yang ditampilkan ke HTML wajib melalui `htmlspecialchars()`

---

*Dokumentasi ini dibuat berdasarkan analisis source code versi April 2026.*