<?php
require_once BASE_PATH . '/app/models/Model.php';

class Undangan extends Model
{
    protected string $table = 'undangan_rapat';

    public const PER_PAGE = 8;

    // ----------------------------------------------------------------
    // Read
    // ----------------------------------------------------------------

    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY waktu DESC"
        );
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

        $data = $this->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY waktu DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        return compact('data', 'total', 'totalPages', 'page');
    }

    public function getById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE id = ?",
            [$id]
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
        // YEAR()/MONTH() = MySQL; EXTRACT() = PostgreSQL (keduanya SQL standard)
        $sql = $this->db->getDriver() === 'pgsql'
            ? "SELECT * FROM `{$this->table}`
               WHERE EXTRACT(YEAR FROM waktu) = ? AND EXTRACT(MONTH FROM waktu) = ?
               ORDER BY waktu ASC"
            : "SELECT * FROM `{$this->table}`
               WHERE YEAR(waktu) = ? AND MONTH(waktu) = ?
               ORDER BY waktu ASC";

        return $this->fetchAll($sql, [$year, $month]);
    }

    public function getByYear(int $year): array
    {
        $sql = $this->db->getDriver() === 'pgsql'
            ? "SELECT * FROM `{$this->table}` WHERE EXTRACT(YEAR FROM waktu) = ? ORDER BY waktu ASC"
            : "SELECT * FROM `{$this->table}` WHERE YEAR(waktu) = ? ORDER BY waktu ASC";

        return $this->fetchAll($sql, [$year]);
    }

    public function getMonthlyStats(int $year): array
    {
        $sql = $this->db->getDriver() === 'pgsql'
            ? "SELECT EXTRACT(MONTH FROM waktu)::int AS bulan, COUNT(*) AS total
               FROM `{$this->table}`
               WHERE EXTRACT(YEAR FROM waktu) = ?
               GROUP BY EXTRACT(MONTH FROM waktu)
               ORDER BY bulan"
            : "SELECT MONTH(waktu) AS bulan, COUNT(*) AS total
               FROM `{$this->table}`
               WHERE YEAR(waktu) = ?
               GROUP BY MONTH(waktu)
               ORDER BY bulan";

        return $this->fetchAll($sql, [$year]);
    }

    public function getAvailableYears(): array
    {
        $sql = $this->db->getDriver() === 'pgsql'
            ? "SELECT DISTINCT EXTRACT(YEAR FROM waktu)::int AS tahun FROM `{$this->table}` ORDER BY tahun DESC"
            : "SELECT DISTINCT YEAR(waktu) AS tahun FROM `{$this->table}` ORDER BY tahun DESC";

        return $this->fetchAll($sql);
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
            [$data['waktu'], $data['tempat'], $data['acara'], $tglSurat, $data['dibuat_oleh']]
        ) > 0;
    }

    public function update(int $id, array $data): bool
    {
        $tglSurat = $data['tgl_surat'] ?: date('Y-m-d');
        return $this->execute(
            "UPDATE `{$this->table}` SET waktu = ?, tempat = ?, acara = ?, tgl_surat = ?
             WHERE id = ?",
            [$data['waktu'], $data['tempat'], $data['acara'], $tglSurat, $id]
        ) > 0;
    }
}