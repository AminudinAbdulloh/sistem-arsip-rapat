<?php
require_once BASE_PATH . '/app/models/Model.php';

class Undangan extends Model
{
    protected string $table = 'undangan_rapat';

    // ----------------------------------------------------------------
    // Read
    // ----------------------------------------------------------------

    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY waktu DESC"
        );
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE id = ?",
            'i', $id
        );
    }

    /** Undangan yang belum memiliki notulensi. */
    public function getUndanganTanpaNotulensi(): array
    {
        return $this->fetchAll(
            "SELECT u.*
             FROM `{$this->table}` u
             LEFT JOIN notulensi_rapat n ON n.undangan_id = u.id
             WHERE n.id IS NULL
             ORDER BY u.waktu DESC"
        );
    }

    public function getByMonth(int $year, int $month): array
    {
        return $this->fetchAll(
            "SELECT * FROM `{$this->table}`
             WHERE YEAR(waktu) = ? AND MONTH(waktu) = ?
             ORDER BY waktu ASC",
            'ii', $year, $month
        );
    }

    public function getByYear(int $year): array
    {
        return $this->fetchAll(
            "SELECT * FROM `{$this->table}`
             WHERE YEAR(waktu) = ?
             ORDER BY waktu ASC",
            'i', $year
        );
    }

    public function getMonthlyStats(int $year): array
    {
        return $this->fetchAll(
            "SELECT MONTH(waktu) AS bulan, COUNT(*) AS total
             FROM `{$this->table}`
             WHERE YEAR(waktu) = ?
             GROUP BY MONTH(waktu)
             ORDER BY bulan",
            'i', $year
        );
    }

    public function getAvailableYears(): array
    {
        return $this->fetchAll(
            "SELECT DISTINCT YEAR(waktu) AS tahun
             FROM `{$this->table}`
             ORDER BY tahun DESC"
        );
    }

    // ----------------------------------------------------------------
    // Write
    // ----------------------------------------------------------------

    public function create(array $data): bool
    {
        $tglSurat = $data['tgl_surat'] ?: date('Y-m-d');
        return $this->execute(
            "INSERT INTO `{$this->table}` (waktu, tempat, acara, tgl_surat, dibuat_oleh)
             VALUES (?, ?, ?, ?, ?)",
            'ssssi',
            $data['waktu'],
            $data['tempat'],
            $data['acara'],
            $tglSurat,
            $data['dibuat_oleh']
        ) > 0;
    }

    public function update(int $id, array $data): bool
    {
        $tglSurat = $data['tgl_surat'] ?: date('Y-m-d');
        return $this->execute(
            "UPDATE `{$this->table}` SET waktu = ?, tempat = ?, acara = ?, tgl_surat = ?
             WHERE id = ?",
            'ssssi',
            $data['waktu'],
            $data['tempat'],
            $data['acara'],
            $tglSurat,
            $id
        ) > 0;
    }
}