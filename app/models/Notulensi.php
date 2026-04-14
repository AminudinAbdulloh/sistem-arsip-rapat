<?php
require_once BASE_PATH . '/app/models/Model.php';
require_once BASE_PATH . '/app/helpers/FileUploadHelper.php';

class Notulensi extends Model
{
    protected string $table = 'notulensi_rapat';

    public const PER_PAGE = 8;

    private const DIR_FOTO    = '/public/uploads/dokumentasi/';
    private const DIR_DOKUMEN = '/public/uploads/dokumen/';

    // ----------------------------------------------------------------
    // Read — notulensi
    // ----------------------------------------------------------------

    public function getAll(): array
    {
        $rows = $this->fetchAll(
            "SELECT n.*,
                    u.acara       AS nama_undangan,
                    u.acara       AS tema_rapat,
                    u.waktu       AS waktu_undangan,
                    DATE(u.waktu) AS tgl_rapat
             FROM `{$this->table}` n
             JOIN undangan_rapat u ON n.undangan_id = u.id
             ORDER BY u.waktu DESC"
        );

        foreach ($rows as &$row) {
            $this->attachPreview($row);
        }

        return $rows;
    }

    /**
     * Ambil data dengan paginasi.
     *
     * @return array{ data: array, total: int, totalPages: int, page: int }
     */
    public function getPaginated(int $page = 1, int $perPage = self::PER_PAGE): array
    {
        $total      = $this->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $perPage;

        $rows = $this->fetchAll(
            "SELECT n.*,
                    u.acara       AS nama_undangan,
                    u.acara       AS tema_rapat,
                    u.waktu       AS waktu_undangan,
                    DATE(u.waktu) AS tgl_rapat
             FROM `{$this->table}` n
             JOIN undangan_rapat u ON n.undangan_id = u.id
             ORDER BY u.waktu DESC
             LIMIT ? OFFSET ?",
            'ii', $perPage, $offset
        );

        foreach ($rows as &$row) {
            $this->attachPreview($row);
        }

        return ['data' => $rows, 'total' => $total, 'totalPages' => $totalPages, 'page' => $page];
    }

    public function getById(int $id): ?array
    {
        $row = $this->fetchOne(
            "SELECT n.*,
                    u.acara       AS nama_undangan,
                    u.acara       AS tema_rapat,
                    u.waktu       AS waktu_undangan,
                    DATE(u.waktu) AS tgl_rapat,
                    u.tempat
             FROM `{$this->table}` n
             JOIN undangan_rapat u ON n.undangan_id = u.id
             WHERE n.id = ?",
            'i', $id
        );

        if ($row) {
            $row['dokumentasi_list'] = $this->getDokumentasi($id);
            $row['dokumen_list']     = $this->getDokumen($id);
        }

        return $row;
    }

    public function existsByUndanganId(int $undanganId): bool
    {
        return $this->fetchOne(
            "SELECT id FROM `{$this->table}` WHERE undangan_id = ? LIMIT 1",
            'i', $undanganId
        ) !== null;
    }

    public function getByMonth(int $year, int $month): array
    {
        return $this->fetchAll(
            "SELECT n.*,
                    u.acara       AS nama_undangan,
                    u.acara       AS tema_rapat,
                    DATE(u.waktu) AS tgl_rapat
             FROM `{$this->table}` n
             JOIN undangan_rapat u ON n.undangan_id = u.id
             WHERE YEAR(u.waktu) = ? AND MONTH(u.waktu) = ?
             ORDER BY u.waktu ASC",
            'ii', $year, $month
        );
    }

    public function getByYear(int $year): array
    {
        return $this->fetchAll(
            "SELECT n.*,
                    u.acara       AS nama_undangan,
                    u.acara       AS tema_rapat,
                    DATE(u.waktu) AS tgl_rapat
             FROM `{$this->table}` n
             JOIN undangan_rapat u ON n.undangan_id = u.id
             WHERE YEAR(u.waktu) = ?
             ORDER BY u.waktu ASC",
            'i', $year
        );
    }

    // ----------------------------------------------------------------
    // Write — notulensi
    // ----------------------------------------------------------------

    /** Simpan notulensi baru; kembalikan ID baru atau false. */
    public function create(array $data): int|false
    {
        return $this->insertGetId(
            "INSERT INTO `{$this->table}` (undangan_id, deskripsi_rapat, catatan, dibuat_oleh)
             VALUES (?, ?, ?, ?)",
            'issi',
            $data['undangan_id'],
            $data['deskripsi_rapat'],
            $data['catatan'],
            $data['dibuat_oleh']
        );
    }

    public function update(int $id, array $data): bool
    {
        // affected_rows bisa 0 jika data tidak berubah — tetap dianggap sukses
        return $this->execute(
            "UPDATE `{$this->table}` SET undangan_id = ?, deskripsi_rapat = ?, catatan = ?
             WHERE id = ?",
            'issi',
            $data['undangan_id'],
            $data['deskripsi_rapat'],
            $data['catatan'],
            $id
        ) >= 0;
    }

    // ----------------------------------------------------------------
    // Dokumentasi foto
    // ----------------------------------------------------------------

    public function getDokumentasi(int $notulensiId): array
    {
        return $this->fetchAll(
            "SELECT * FROM notulensi_dokumentasi WHERE notulensi_id = ? ORDER BY id ASC",
            'i', $notulensiId
        );
    }

    public function countDokumentasi(int $notulensiId): int
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM notulensi_dokumentasi WHERE notulensi_id = ?",
            'i', $notulensiId
        );
        return (int) ($row['total'] ?? 0);
    }

    public function addDokumentasi(int $notulensiId, string $filename): bool
    {
        return $this->execute(
            "INSERT INTO notulensi_dokumentasi (notulensi_id, filename) VALUES (?, ?)",
            'is', $notulensiId, $filename
        ) > 0;
    }

    public function deleteDokumentasi(int $id): bool
    {
        $row = $this->fetchOne("SELECT filename FROM notulensi_dokumentasi WHERE id = ?", 'i', $id);
        if ($row) {
            FileUploadHelper::deleteFile(BASE_PATH . self::DIR_FOTO . $row['filename']);
        }
        return $this->execute("DELETE FROM notulensi_dokumentasi WHERE id = ?", 'i', $id) > 0;
    }

    public function deleteAllDokumentasi(int $notulensiId): void
    {
        foreach ($this->getDokumentasi($notulensiId) as $d) {
            FileUploadHelper::deleteFile(BASE_PATH . self::DIR_FOTO . $d['filename']);
        }
        $this->execute("DELETE FROM notulensi_dokumentasi WHERE notulensi_id = ?", 'i', $notulensiId);
    }

    // ----------------------------------------------------------------
    // Dokumen pendukung
    // ----------------------------------------------------------------

    public function getDokumen(int $notulensiId): array
    {
        return $this->fetchAll(
            "SELECT * FROM notulensi_dokumen WHERE notulensi_id = ? ORDER BY id ASC",
            'i', $notulensiId
        );
    }

    public function addDokumen(int $notulensiId, string $filename, string $originalName, string $mimeType): bool
    {
        return $this->execute(
            "INSERT INTO notulensi_dokumen (notulensi_id, filename, original_name, mime_type) VALUES (?, ?, ?, ?)",
            'isss', $notulensiId, $filename, $originalName, $mimeType
        ) > 0;
    }

    public function deleteDokumen(int $id): bool
    {
        $row = $this->fetchOne("SELECT filename FROM notulensi_dokumen WHERE id = ?", 'i', $id);
        if ($row) {
            FileUploadHelper::deleteFile(BASE_PATH . self::DIR_DOKUMEN . $row['filename']);
        }
        return $this->execute("DELETE FROM notulensi_dokumen WHERE id = ?", 'i', $id) > 0;
    }

    public function deleteAllDokumen(int $notulensiId): void
    {
        foreach ($this->getDokumen($notulensiId) as $d) {
            FileUploadHelper::deleteFile(BASE_PATH . self::DIR_DOKUMEN . $d['filename']);
        }
        $this->execute("DELETE FROM notulensi_dokumen WHERE notulensi_id = ?", 'i', $notulensiId);
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    /** Tambahkan data preview foto ke baris notulensi. */
    private function attachPreview(array &$row): void
    {
        $foto = $this->fetchOne(
            "SELECT filename FROM notulensi_dokumentasi WHERE notulensi_id = ? ORDER BY id ASC LIMIT 1",
            'i', $row['id']
        );
        $row['dokumentasi_preview'] = $foto['filename'] ?? null;
        $row['dokumentasi_count']   = $this->countDokumentasi($row['id']);
    }
}