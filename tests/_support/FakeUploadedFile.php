<?php

namespace Tests\Support;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Pengganti (test double) untuk CodeIgniter\HTTP\Files\UploadedFile.
 *
 * Pengujian unit murni (dijalankan lewat CLI oleh PHPUnit) tidak melalui
 * transaksi HTTP sungguhan, sehingga is_uploaded_file()/move_uploaded_file()
 * bawaan PHP selalu bernilai false untuk berkas apa pun. Kelas ini meng-override
 * method move() agar jalur "berkas valid -> berhasil dipindahkan" pada
 * NotulensiController::uploadFoto() tetap dapat diuji secara terisolasi
 * tanpa operasi berkas fisik yang sesungguhnya.
 */
class FakeUploadedFile extends UploadedFile
{
    public function __construct(
        string $path,
        string $originalName,
        ?string $mimeType = null,
        ?int $size = null,
        ?int $error = null,
        ?string $clientPath = null,
    ) {
        parent::__construct(
            $path,
            $originalName,
            $mimeType,
            $size ?? (filesize($path) ?: null),
            $error ?? UPLOAD_ERR_OK,
            $clientPath,
        );
    }

    public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
    {
        return true;
    }
}
