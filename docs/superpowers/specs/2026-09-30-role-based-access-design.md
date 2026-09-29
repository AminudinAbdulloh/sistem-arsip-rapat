# Desain: Fondasi Peran & Hak Akses (Bagian 1)

Tanggal: 2026-09-30
Status: Menunggu review

## Latar Belakang

Saat ini semua route hanya dijaga filter `auth`, sehingga siapa pun yang login dapat
mengakses semua fitur. Tabel `users` belum punya kolom peran. Permintaan lengkapnya
mencakup empat peran (Admin, Ketua Program Studi, Sekretaris/Staff, Dosen) dan dipecah
menjadi tiga bagian yang dikerjakan berurutan:

1. **Fondasi peran (dokumen ini):** kolom role, filter akses, menu per role, manajemen pengguna.
2. **Verifikasi dan arsip:** status verifikasi oleh Kaprodi, dosen hanya melihat/mencari arsip terverifikasi, laporan Kaprodi.
3. **Dokumen rapat baru:** daftar hadir, berita acara, upload dokumen oleh Sekretaris/Staff.

## Tujuan

Membatasi akses ke modul yang sudah ada berdasarkan peran, dan menyediakan manajemen
pengguna untuk Admin.

## Di Luar Cakupan

Verifikasi dokumen, arsip khusus dosen, daftar hadir, berita acara, upload dokumen
(bagian 2 dan 3), serta foto profil pengguna.

## Peran

| Nilai `role` | Peran |
|---|---|
| `admin` | Admin |
| `kaprodi` | Ketua Program Studi |
| `sekretaris` | Sekretaris/Staff |
| `dosen` | Dosen |

## Matriks Akses

| Modul | Admin | Kaprodi | Sekretaris | Dosen |
|---|---|---|---|---|
| Kelola pengguna | CRUD | - | - | - |
| Undangan (CRUD + download .docx) | CRUD | lihat | CRUD | - |
| Notulensi (CRUD + foto) | CRUD | lihat | CRUD | - |
| Dashboard | ya | ya | ya | ya (ringkas) |
| Laporan bulanan/tahunan | ya | ya | - | - |

Dosen belum punya akses ke notulensi pada bagian 1. Akses "hanya yang terverifikasi"
ditambahkan di bagian 2.

## Desain

### 1. Data

- Migrasi baru menambah `users.role` sebagai ENUM(`admin`,`kaprodi`,`sekretaris`,`dosen`),
  default `dosen`. Semua akun yang sudah ada diubah menjadi `admin` agar tidak ada yang
  terkunci setelah migrasi. `down()` menghapus kolom.
- `UserModel`: tambah `role` ke `allowedFields`; aturan validasi: NIP wajib dan unik,
  nama wajib, kata sandi minimal 8 karakter (saat dibuat / diganti), `role` harus salah
  satu dari empat nilai.
- `AuthController::login`: simpan `role` di session `user`.
- `UserSeeder`: empat akun demo, satu per role, kata sandi `password` (sama seperti
  seeder sekarang).

### 2. Otorisasi

- `App\Filters\RoleFilter`, alias `role` di `Config/Filters.php`. Argumen filter adalah
  daftar role yang diizinkan, contoh `role:admin,sekretaris`.
  - Belum login: redirect ke `/login`.
  - Login tetapi role tidak ada di daftar: redirect ke `/dashboard` dengan flash error
    "Anda tidak memiliki akses ke halaman tersebut."
- Helper `has_role(string ...$roles): bool` (file `app/Helpers/auth_helper.php`) untuk
  view. Hanya untuk tampilan; penjagaan sesungguhnya ada di filter.
- Penerapan di `Routes.php`:
  - `/users/*` -> `role:admin`
  - Undangan dan notulensi, route yang menulis (create, store, edit, update, delete,
    download) -> `role:admin,sekretaris`
  - Undangan dan notulensi, route baca (index, show) -> `role:admin,sekretaris,kaprodi`
  - `/dashboard/download` -> `role:admin,kaprodi`
  - `/` dan `/dashboard` -> `auth` saja; view Dashboard menyembunyikan grafik dan tombol
    laporan untuk role yang tidak berhak.

### 3. Manajemen Pengguna (Admin)

- `UserController` dengan aksi: daftar, tambah, simpan, edit, update, hapus. Reset kata
  sandi dilakukan lewat field kata sandi opsional di form edit (kosong = tidak berubah).
- View di `app/Views/Users/` (`index`, `create`, `edit`) dengan gaya yang sama seperti
  view Undangan.
- Field: NIP, nama, jabatan, role, kata sandi (di-hash dengan `password_hash`).
- Pengaman:
  - Admin tidak dapat menghapus akunnya sendiri.
  - Admin tidak dapat mengubah role akunnya sendiri.
  - Akun `admin` terakhir tidak dapat dihapus atau diturunkan rolenya.
  - Pengguna yang sudah memiliki undangan/notulensi tidak dapat dihapus (FK `created_by`
    bersifat CASCADE, sehingga penghapusan akan ikut menghapus data rapat).
  Pelanggaran menghasilkan redirect dengan flash error, tanpa perubahan data.

### 4. Antarmuka

- Sidebar `Layouts/main.php`: item menu ditampilkan menurut role (Pengguna hanya Admin;
  Undangan/Notulensi untuk Admin, Sekretaris, Kaprodi) dan label role ditampilkan di
  bawah nama pengguna.
- View Undangan dan Notulensi: tombol tambah, edit, hapus, dan download hanya tampil
  untuk Admin dan Sekretaris (memakai `has_role`).

### 5. Pengujian

- `RoleFilterTest`: untuk empat role terhadap route sampel (diizinkan, ditolak, belum login).
- `UserControllerTest` dan `UserModelTest`: validasi, unik NIP, hash kata sandi, tiga
  pengaman admin.
- Test yang ada memakai `withSession` tanpa `role`; diperbarui agar memakai role
  `admin` atau `sekretaris` sesuai route yang diuji.

## Risiko dan Catatan

- Migrasi mengubah semua akun lama menjadi Admin. Admin perlu meninjau dan menurunkan
  role pengguna lain lewat menu Pengguna setelah migrasi.
- Sidebar dan tombol yang disembunyikan bukan kontrol keamanan; filter route adalah
  satu-satunya penjaga akses.
