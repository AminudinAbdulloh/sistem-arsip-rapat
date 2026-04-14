<?php

/**
 * FileUploadHelper
 *
 * Mengenkapsulasi logika validasi dan penyimpanan file upload.
 * Dapat digunakan oleh controller manapun tanpa duplikasi kode.
 */
class FileUploadHelper
{
    // ----------------------------------------------------------------
    // Konfigurasi default
    // ----------------------------------------------------------------

    public const MAX_SIZE = 10 * 1024 * 1024; // 10 MB

    public const ALLOWED_IMAGE = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public const ALLOWED_DOCUMENT = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    private static array $ALLOWED_EXT = [
        'image/jpeg'   => ['jpg', 'jpeg'],
        'image/png'    => ['png'],
        'image/gif'    => ['gif'],
        'image/webp'   => ['webp'],
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
    ];

    // ----------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------

    /**
     * Upload banyak file dari satu input[type=file multiple].
     *
     * @param string   $inputName     Nama atribut `name` pada input file
     * @param string   $destDir       Direktori tujuan (path absolut)
     * @param string   $prefix        Prefix nama file yang disimpan
     * @param array    $allowedMimes  Daftar MIME type yang diizinkan
     * @param int      $maxSize       Ukuran maksimum per file (byte)
     * @param callable $onSuccess     Callback(filename, originalName, mimeType) dipanggil tiap file sukses
     *
     * @return string[]  Array pesan error; kosong jika semua sukses
     */
    public static function uploadMultiple(
        string $inputName,
        string $destDir,
        string $prefix,
        array  $allowedMimes,
        int    $maxSize,
        callable $onSuccess
    ): array {
        if (empty($_FILES[$inputName]['name'][0])) {
            return [];
        }

        self::ensureDir($destDir);

        $files  = $_FILES[$inputName];
        $errors = [];
        $count  = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($files['name'][$i])) {
                continue;
            }

            $error = self::validateSingleFile($files, $i, $allowedMimes, $maxSize);
            if ($error !== null) {
                $errors[] = $error;
                continue;
            }

            $ext      = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $filename = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $dest     = rtrim($destDir, '/') . '/' . $filename;

            if (!move_uploaded_file($files['tmp_name'][$i], $dest)) {
                $errors[] = $files['name'][$i] . ' (gagal disimpan)';
                continue;
            }

            $onSuccess($filename, $files['name'][$i], $files['type'][$i]);
        }

        return $errors;
    }

    /**
     * Hapus file fisik dari disk jika ada.
     */
    public static function deleteFile(string $fullPath): void
    {
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    /**
     * Kembalikan class ikon Font Awesome berdasarkan MIME type.
     */
    public static function iconClass(string $mimeType): string
    {
        return match (true) {
            str_contains($mimeType, 'pdf')          => 'fa-file-pdf',
            str_contains($mimeType, 'word')         => 'fa-file-word',
            str_contains($mimeType, 'excel'),
            str_contains($mimeType, 'spreadsheet')  => 'fa-file-excel',
            str_contains($mimeType, 'presentation'),
            str_contains($mimeType, 'powerpoint')   => 'fa-file-powerpoint',
            default                                  => 'fa-file-alt',
        };
    }

    /**
     * Kembalikan warna hex ikon berdasarkan MIME type.
     */
    public static function iconColor(string $mimeType): string
    {
        return match (true) {
            str_contains($mimeType, 'pdf')          => '#ef4444',
            str_contains($mimeType, 'word')         => '#2563eb',
            str_contains($mimeType, 'excel'),
            str_contains($mimeType, 'spreadsheet')  => '#10b981',
            str_contains($mimeType, 'presentation'),
            str_contains($mimeType, 'powerpoint')   => '#f59e0b',
            default                                  => '#6b7280',
        };
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    private static function validateSingleFile(array $files, int $i, array $allowedMimes, int $maxSize): ?string
    {
        $name = $files['name'][$i];

        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            return "{$name} (error upload)";
        }
        if ($files['size'][$i] > $maxSize) {
            return "{$name} (ukuran melebihi " . self::formatSize($maxSize) . ")";
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($files['tmp_name'][$i]);

        if (!in_array($realMime, $allowedMimes, true)) {
            return "{$name} (format tidak didukung)";
        }

        $ext         = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        $allowedExts = self::$ALLOWED_EXT[$realMime] ?? [];
        if (!empty($allowedExts) && !in_array($ext, $allowedExts, true)) {
            return "{$name} (ekstensi tidak sesuai tipe file)";
        }

        return null;
    }

    private static function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private static function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576) . 'MB';
        }
        return round($bytes / 1024) . 'KB';
    }
}