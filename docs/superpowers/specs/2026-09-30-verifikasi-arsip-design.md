# Desain: Verifikasi Notulensi & Arsip Dosen (Bagian 2)

Tanggal: 2026-09-30
Status: Dikerjakan langsung atas permintaan pengguna (asumsi di bawah dapat diubah)
Bergantung pada: `2026-09-30-role-based-access-design.md` (bagian 1)

## Asumsi yang dipilih

1. **Dokumen yang diverifikasi = notulensi rapat** (satu-satunya dokumen saat ini; daftar hadir/berita acara menyusul di bagian 3).
2. **Status:** `menunggu` (default), `terverifikasi`, `ditolak`. Penolakan wajib disertai alasan.
3. **Yang memverifikasi hanya Kaprodi.** Admin tidak dapat menyetujui/menolak, agar persetujuan tidak terlewati.
4. **Mengubah notulensi** (oleh Admin/Sekretaris) mengembalikan statusnya ke `menunggu` dan menghapus data verifikasi, karena isinya berubah.
5. **Notulensi lama** (sebelum migrasi) berstatus `menunggu`; Kaprodi perlu memverifikasinya.
6. **Arsip** = notulensi berstatus `terverifikasi`. Judul dan tanggal diambil dari undangan terkait (kolom `tgl_rapat`/`tema_rapat` notulensi tidak lagi diisi form).

## Data

Migrasi `AddVerifikasiToNotulensi` pada `notulensi_rapat`:

| Kolom | Tipe |
|---|---|
| `status_verifikasi` | ENUM(`menunggu`,`terverifikasi`,`ditolak`) NOT NULL DEFAULT `menunggu` |
| `catatan_verifikasi` | TEXT NULL |
| `verified_by` | INT UNSIGNED NULL, FK `users.id` ON DELETE SET NULL |
| `verified_at` | DATETIME NULL |

## Alur & Akses

| Route | Role | Fungsi |
|---|---|---|
| `POST /notulensi/{id}/verifikasi` | kaprodi | `aksi=setujui` atau `aksi=tolak` (+`catatan` wajib) |
| `GET /notulensi?status=...` | admin, sekretaris, kaprodi | daftar dengan kolom status dan filter status |
| `GET /notulensi/{id}/show` | admin, sekretaris, kaprodi | menampilkan status, pemverifikasi, alasan penolakan; form verifikasi hanya untuk kaprodi |
| `GET /arsip` | semua yang login | daftar + pencarian arsip terverifikasi: kata kunci (`q`: acara, tempat, deskripsi, catatan), rentang tanggal (`dari`, `sampai`, tanggal undangan) |
| `GET /arsip/{id}` | semua yang login | detail arsip; bukan `terverifikasi` -> redirect `/arsip` dengan pesan error |

## Tampilan

- Sidebar: menu **Arsip Rapat** untuk semua role.
- Dashboard Kaprodi: kartu "Menunggu Verifikasi" (tautan ke `/notulensi?status=menunggu`).
- Laporan: kolom Status pada tabel notulensi dan ringkasan jumlah terverifikasi.

## Pengujian

Verifikasi (setuju, tolak tanpa/dengan alasan, aksi tidak valid, role lain ditolak), reset status saat edit,
pencarian arsip (hanya terverifikasi, kata kunci, rentang tanggal), detail arsip, tampilan per role, migrasi.

## Keterbatasan

Foto dokumentasi disimpan di `public/uploads/dokumentasi/`, sehingga URL langsung ke berkas tetap dapat dibuka
oleh siapa pun yang tahu nama berkasnya; pembatasan hanya berlaku pada halaman aplikasi.

## Di luar cakupan

Riwayat/audit verifikasi, notifikasi ke Sekretaris, verifikasi undangan, paginasi hasil pencarian.
