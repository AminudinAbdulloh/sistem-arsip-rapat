<?php

namespace ArsipRapat\Model;

use ArsipRapat\Config\Database;

class UndanganRapat
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, us.nama as created_by_nama 
            FROM undangan_rapat u 
            JOIN users us ON u.created_by = us.id 
            ORDER BY u.waktu DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, us.nama as created_by_nama 
            FROM undangan_rapat u 
            JOIN users us ON u.created_by = us.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO undangan_rapat (hari, waktu, tempat, acara, created_by) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['hari'],
            $data['waktu'],
            $data['tempat'],
            $data['acara'],
            $data['created_by']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE undangan_rapat 
            SET hari = ?, waktu = ?, tempat = ?, acara = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['hari'],
            $data['waktu'],
            $data['tempat'],
            $data['acara'],
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM undangan_rapat WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findByMonth(int $month, int $year): array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, us.nama as created_by_nama,
                (SELECT COUNT(*) FROM notulensi_rapat n WHERE n.undangan_id = u.id) as has_notulensi
            FROM undangan_rapat u 
            JOIN users us ON u.created_by = us.id 
            WHERE MONTH(u.waktu) = ? AND YEAR(u.waktu) = ?
            ORDER BY u.waktu ASC
        ");
        $stmt->execute([$month, $year]);
        return $stmt->fetchAll();
    }

    public function findByYear(int $year): array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, us.nama as created_by_nama,
                (SELECT COUNT(*) FROM notulensi_rapat n WHERE n.undangan_id = u.id) as has_notulensi
            FROM undangan_rapat u 
            JOIN users us ON u.created_by = us.id 
            WHERE YEAR(u.waktu) = ?
            ORDER BY u.waktu ASC
        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }

    public function countByMonth(int $month, int $year): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM undangan_rapat WHERE MONTH(waktu) = ? AND YEAR(waktu) = ?");
        $stmt->execute([$month, $year]);
        return (int) $stmt->fetchColumn();
    }

    public function countByYear(int $year): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM undangan_rapat WHERE YEAR(waktu) = ?");
        $stmt->execute([$year]);
        return (int) $stmt->fetchColumn();
    }

    public function getAvailableYears(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT YEAR(waktu) as year FROM undangan_rapat ORDER BY year DESC");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getLastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    public function hasNotulensi(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notulensi_rapat WHERE undangan_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
