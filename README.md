# Sistem Informasi Pengelolaan Arsip Rapat ITD Adisutjipto

Aplikasi web berbasis **CodeIgniter 4** untuk mengelola dokumentasi rapat Program Studi ITD Adisutjipto.

## Fitur
- **Login** menggunakan NIP dan kata sandi
- **Dashboard** dengan laporan bulanan & tahunan + grafik interaktif
- **Undangan Rapat**: CRUD + download surat undangan (HTML/Print)
- **Notulensi Rapat**: CRUD + upload foto dokumentasi + detail view
- **Laporan**: Download laporan bulanan atau tahunan

## Struktur Aplikasi (CodeIgniter 4)
```
arsip-rapat/
├── app/
│   ├── Config/       # Konfigurasi aplikasi (Routes, Database, Filters, etc)
│   ├── Controllers/  # AuthController, DashboardController, UndanganController, NotulensiController
│   ├── Database/
│   │   ├── Migrations/  # Database migrations
│   │   └── Seeds/       # Database seeders
│   ├── Filters/      # AuthFilter, GuestFilter (middleware)
│   ├── Models/       # UserModel, UndanganRapatModel, NotulensiRapatModel
│   └── Views/        # Template PHP (Auth, Dashboard, Undangan, Notulensi, Layouts)
├── public/           # DocumentRoot - index.php & .htaccess
│   └── uploads/dokumentasi/  # Foto dokumentasi rapat
├── vendor/           # Composer dependencies
├── writable/         # CI4 writable folder (cache, logs, session, uploads)
└── composer.json
```

## Instalasi

### 1. Persyaratan
- PHP >= 8.2
- MySQL / MariaDB
- Apache (dengan mod_rewrite)
- Composer

### 2. Install Dependencies
```bash
composer install
```

### 3. Setup Database
```bash
# Jalankan migration untuk membuat tabel
php spark migrate

# Jalankan seeder untuk data awal
php spark db:seed UserSeeder
```

### 4. Konfigurasi Database (Opsional)
Edit `app/Config/Database.php` jika perlu mengubah kredensial database.

### 5. Konfigurasi Web Server

**Apache** — arahkan DocumentRoot ke folder `public/`:
```apache
DocumentRoot /path/to/arsip-rapat/public
<Directory /path/to/arsip-rapat/public>
    AllowOverride All
    Require all granted
</Directory>
```

**Atau gunakan PHP Built-in Server (development):**
```bash
php spark serve
# Akses: http://localhost:8080
```

### 6. Permissions Writable Folder
```bash
chmod -R 777 writable/
chmod -R 777 public/uploads/
```

## Akun Default
| NIP | Kata Sandi | Jabatan |
|-----|-----------|---------|
| 198001012005011001 | password | Kepala Program Studi |
| 198502152010012002 | password | Sekretaris Prodi |

## Teknologi
- **Backend**: CodeIgniter 4 (PHP 8.2+)
- **Database**: MySQL dengan Query Builder & Migrations
- **Frontend**: HTML5, Tailwind CSS, Chart.js
- **Icons**: Font Awesome 6
- **Routing**: CI4 Routing dengan Filters

## Command Line (Spark)
```bash
# Jalankan development server
php spark serve

# Jalankan migration
php spark migrate

# Jalankan seeder
php spark db:seed UserSeeder

# Buat controller baru
php spark make:controller NamaController

# Buat model baru
php spark make:model NamaModel
```

## Aturan Bisnis
- Notulensi **hanya dapat dibuat** jika sudah ada undangan rapat
- Undangan **tidak dapat dihapus** jika sudah memiliki notulensi terkait
- Download undangan menghasilkan surat undangan resmi dalam format HTML (print-ready)
- Laporan bulanan/tahunan dapat diunduh dan dicetak

## Perubahan dari Versi Sebelumnya
- **Framework**: Custom PHP → CodeIgniter 4
- **Database**: PDO Manual → CI4 Query Builder & Migrations
- **Routing**: Custom Router → CI4 Routes dengan Filters
- **Views**: Plain PHP → CI4 Views dengan Layouts
- **Session**: PHP Native → CI4 Session Library
