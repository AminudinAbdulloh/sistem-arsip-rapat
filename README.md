# Sistem Informasi Pengelolaan Arsip Rapat ITD Adisutjipto

Aplikasi web berbasis PHP MVC untuk mengelola dokumentasi rapat Program Studi ITD Adisutjipto.

## Fitur
- **Login** menggunakan NIP dan kata sandi
- **Dashboard** dengan laporan bulanan & tahunan + grafik interaktif
- **Undangan Rapat**: CRUD + download surat undangan (HTML/Print)
- **Notulensi Rapat**: CRUD + upload foto dokumentasi + detail view
- **Laporan**: Download laporan bulanan atau tahunan

## Struktur Aplikasi (MVC)
```
arsip-rapat/
├── app/
│   ├── App/          # Router, View helper
│   ├── Controller/   # AuthController, DashboardController, UndanganController, NotulensiController
│   ├── Middleware/   # AuthMiddleware, GuestMiddleware
│   ├── Model/        # User, UndanganRapat, NotulensiRapat
│   └── View/         # Template PHP (Auth, Dashboard, Undangan, Notulensi, Layouts)
├── config/
│   ├── Database.php  # Koneksi PDO MySQL
│   └── database.sql  # Schema & data awal
├── public/
│   ├── index.php     # Entry point
│   └── .htaccess     # URL rewriting
├── uploads/
│   └── dokumentasi/  # Foto dokumentasi rapat
└── composer.json
```

## Instalasi

### 1. Persyaratan
- PHP >= 8.0
- MySQL / MariaDB
- Apache (dengan mod_rewrite)
- Composer

### 2. Setup Database
```sql
-- Jalankan file config/database.sql di MySQL Anda
mysql -u root -p < config/database.sql
```

### 3. Konfigurasi Database
Edit `config/Database.php`:
```php
$host = 'localhost';
$dbname = 'arsip_rapat_itd';
$username = 'root';  // sesuaikan
$password = '';      // sesuaikan
```

### 4. Install Dependencies
```bash
composer install
```

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
cd public
php -S localhost:8000
# Akses: http://localhost:8000
```

### 6. Permissions Upload Folder
```bash
chmod 777 uploads/dokumentasi
```

## Akun Default
| NIP | Kata Sandi | Jabatan |
|-----|-----------|---------|
| 198001012005011001 | password | Kepala Program Studi |
| 198502152010012002 | password | Sekretaris Prodi |

> **Catatan**: Hash password default di database menggunakan `password_hash('password', PASSWORD_BCRYPT)`
> Untuk generate hash baru: `php -r "echo password_hash('passwordbaru', PASSWORD_BCRYPT);"`

## Teknologi
- **Backend**: PHP 8+ (OOP, MVC pattern)
- **Database**: MySQL dengan PDO
- **Frontend**: HTML5, CSS3 (custom design), Chart.js
- **Icons**: Font Awesome 6
- **URL Routing**: Apache mod_rewrite + custom Router

## Aturan Bisnis
- Notulensi **hanya dapat dibuat** jika sudah ada undangan rapat
- Undangan **tidak dapat dihapus** jika sudah memiliki notulensi terkait
- Download undangan menghasilkan surat undangan resmi dalam format HTML (print-ready)
- Laporan bulanan/tahunan dapat diunduh dan dicetak
