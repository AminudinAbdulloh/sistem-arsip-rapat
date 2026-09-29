<?php

namespace App\Libraries;

use App\Models\DokumenRapatModel;
use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Validasi, penyimpanan, dan penghapusan berkas dokumen rapat.
 *
 * Berkas disimpan di writable/uploads/dokumen (di luar web root) dengan nama acak,
 * dan hanya dapat diunduh lewat DokumenRapatController::download() yang mengecek akses.
 */
class DokumenRapatService
{
    public const MAX_BYTES  = 10 * 1024 * 1024;
    public const EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];

    private string $dir;
    private DokumenRapatModel $model;

    public function __construct(?string $dir = null, ?DokumenRapatModel $model = null)
    {
        $this->dir   = rtrim($dir ?? WRITEPATH . 'uploads/dokumen', '/\\') . DIRECTORY_SEPARATOR;
        $this->model = $model ?? new DokumenRapatModel();
    }

    public static function path(string $berkas): string
    {
        return WRITEPATH . 'uploads/dokumen/' . basename($berkas);
    }

    /**
     * @return string|null pesan error, atau null bila berhasil disimpan
     */
    public function simpan(int $undanganId, string $judul, ?UploadedFile $file, int $userId): ?string
    {
        if ($file === null || ! $file->isValid()) {
            return 'Berkas wajib dipilih dan harus berhasil diunggah.';
        }
        if ((int) $file->getSize() > self::MAX_BYTES) {
            return 'Ukuran berkas maksimal 10 MB.';
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, self::EXTENSIONS, true)) {
            return 'Tipe berkas tidak diizinkan. Gunakan: ' . implode(', ', self::EXTENSIONS) . '.';
        }

        $judul = trim($judul);
        if ($judul === '') {
            $judul = pathinfo($file->getClientName(), PATHINFO_FILENAME);
        }
        if (mb_strlen($judul) > 150) {
            return 'Judul maksimal 150 karakter.';
        }

        if (! is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }

        $nama = bin2hex(random_bytes(12)) . '.' . $ext;
        if (! $file->move($this->dir, $nama)) {
            return 'Gagal menyimpan berkas.';
        }

        $this->model->insert([
            'undangan_id' => $undanganId,
            'judul'       => $judul,
            'berkas'      => $nama,
            'nama_asli'   => $file->getClientName(),
            'ukuran'      => (int) $file->getSize(),
            'mime'        => $file->getClientMimeType(),
            'uploaded_by' => $userId,
        ]);

        return null;
    }

    /** Hapus berkas fisik (aman bila berkas sudah tidak ada; hanya menyentuh folder dokumen). */
    public function hapusBerkas(string $berkas): void
    {
        $path = $this->dir . basename($berkas);
        if (is_file($path)) {
            unlink($path);
        }
    }

    /** Hapus berkas fisik semua dokumen sebuah undangan (dipanggil sebelum undangan dihapus). */
    public function hapusBerkasUndangan(int $undanganId): void
    {
        foreach ($this->model->where('undangan_id', $undanganId)->findAll() as $dokumen) {
            $this->hapusBerkas($dokumen['berkas']);
        }
    }
}
