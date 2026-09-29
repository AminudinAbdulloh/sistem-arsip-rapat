# Desain: Kelengkapan Rapat — Daftar Hadir, Berita Acara, Dokumen (Bagian 3)

Tanggal: 2026-09-30
Status: Dikerjakan langsung atas permintaan pengguna (asumsi di bawah dapat diubah)
Bergantung pada: bagian 1 (peran) dan bagian 2 (verifikasi & arsip)

## Asumsi yang dipilih

1. Daftar hadir, berita acara, dan dokumen **melekat pada undangan (rapat)**, bukan pada notulensi, karena
   ada sebelum notulensi dibuat. Dikelola di halaman "Kelengkapan Rapat" per undangan.
2. **Daftar hadir:** banyak peserta per undangan; nama bebas (bukan harus pengguna sistem), jabatan opsional,
   status `hadir` / `izin` / `tidak_hadir`, keterangan opsional. Tambah dan hapus (mengubah = hapus lalu tambah).
3. **Berita acara:** satu per undangan (unique key), berisi nomor (opsional), uraian (wajib), keputusan (opsional).
   Menyimpan lagi memperbarui yang ada. Ada halaman cetak (window.print()) yang memuat daftar hadir.
4. **Dokumen:** unggah pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png; maksimal 10 MB; judul opsional
   (default nama berkas). Berkas disimpan di `writable/uploads/dokumen/` dengan nama acak (di luar web root)
   dan hanya bisa diunduh lewat controller.
5. **Akses:** Admin dan Sekretaris menulis; Kaprodi hanya membaca (halaman kelengkapan, cetak berita acara,
   unduh dokumen). Dosen tidak membuka halaman kelengkapan, tetapi melihat daftar hadir, berita acara, dan
   dokumen pada **detail arsip** rapat yang notulensinya *terverifikasi*, dan hanya rapat tersebut yang
   dokumennya dapat diunduh.
6. Menghapus undangan menghapus daftar hadir, berita acara, dan dokumennya (CASCADE), dan berkas fisik ikut
   dibersihkan.
7. Mengubah kelengkapan **tidak** mengembalikan status verifikasi notulensi (verifikasi hanya untuk notulensi).

## Data (migrasi `CreateKelengkapanRapatTables`)

| Tabel | Kolom utama |
|---|---|
| `daftar_hadir` | undangan_id (FK CASCADE), nama, jabatan, status ENUM(hadir,izin,tidak_hadir), keterangan |
| `berita_acara` | undangan_id (UNIQUE, FK CASCADE), nomor, uraian, keputusan, created_by (FK users) |
| `dokumen_rapat` | undangan_id (FK CASCADE), judul, berkas (nama acak), nama_asli, ukuran, mime, uploaded_by (FK users) |

## Route

| Route | Role |
|---|---|
| `GET /undangan/{id}/kelengkapan` | admin, sekretaris, kaprodi |
| `POST /undangan/{id}/hadir/store`, `POST /hadir/{id}/delete` | admin, sekretaris |
| `POST /undangan/{id}/berita-acara/save` | admin, sekretaris |
| `GET /undangan/{id}/berita-acara/cetak` | admin, sekretaris, kaprodi |
| `POST /undangan/{id}/dokumen/store`, `POST /dokumen/{id}/delete` | admin, sekretaris |
| `GET /dokumen/{id}/download` | login; dicek di controller: staf selalu, dosen hanya bila notulensi rapat terverifikasi |

## Keamanan unggahan

Whitelist ekstensi, batas ukuran, nama berkas acak (nama asli hanya disimpan sebagai metadata), penyimpanan di
luar web root, unduhan selalu `attachment`, dan pengecekan akses di controller.

## Keterbatasan

- Jalur sukses unggahan HTTP tidak dapat diuji lewat CLI; logika unggah diuji lewat `DokumenRapatService`
  dengan `FakeUploadedFile`, dan controller diuji untuk jalur gagal.
- Berkas hanya divalidasi menurut ekstensi (bukan isi/antivirus).
- Tidak ada riwayat perubahan dan tidak ada edit peserta di tempat.
