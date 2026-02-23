<?php

namespace ArsipRapat\Model;

use ArsipRapat\Config\Database;

class NotulensiRapat
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("
            SELECT n.*, u.acara as nama_undangan, u.hari, u.waktu as waktu_undangan, u.tempat,
                   us.nama as created_by_nama
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            JOIN users us ON n.created_by = us.id
            ORDER BY n.tgl_rapat DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, u.acara as nama_undangan, u.hari, u.waktu as waktu_undangan, u.tempat,
                   us.nama as created_by_nama
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            JOIN users us ON n.created_by = us.id
            WHERE n.id = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO notulensi_rapat (undangan_id, tgl_rapat, tema_rapat, deskripsi_rapat, catatan, dokumentasi, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['undangan_id'],
            $data['tgl_rapat'],
            $data['tema_rapat'],
            $data['deskripsi_rapat'],
            $data['catatan'],
            $data['dokumentasi'],
            $data['created_by']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE notulensi_rapat 
            SET undangan_id = ?, tgl_rapat = ?, tema_rapat = ?, deskripsi_rapat = ?, catatan = ?, dokumentasi = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['undangan_id'],
            $data['tgl_rapat'],
            $data['tema_rapat'],
            $data['deskripsi_rapat'],
            $data['catatan'],
            $data['dokumentasi'],
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM notulensi_rapat WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function countByMonth(int $month, int $year): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notulensi_rapat WHERE MONTH(tgl_rapat) = ? AND YEAR(tgl_rapat) = ?");
        $stmt->execute([$month, $year]);
        return (int) $stmt->fetchColumn();
    }

    public function countByYear(int $year): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notulensi_rapat WHERE YEAR(tgl_rapat) = ?");
        $stmt->execute([$year]);
        return (int) $stmt->fetchColumn();
    }

    public function findByMonth(int $month, int $year): array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, u.acara as nama_undangan, u.tempat, us.nama as created_by_nama
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            JOIN users us ON n.created_by = us.id
            WHERE MONTH(n.tgl_rapat) = ? AND YEAR(n.tgl_rapat) = ?
            ORDER BY n.tgl_rapat ASC
        ");
        $stmt->execute([$month, $year]);
        return $stmt->fetchAll();
    }

    public function findByYear(int $year): array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, u.acara as nama_undangan, u.tempat, us.nama as created_by_nama
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            JOIN users us ON n.created_by = us.id
            WHERE YEAR(n.tgl_rapat) = ?
            ORDER BY n.tgl_rapat ASC
        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }
}
