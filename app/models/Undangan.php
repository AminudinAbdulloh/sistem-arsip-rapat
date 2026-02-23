<?php
class Undangan {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll() {
        $result = $this->db->query("
            SELECT u.*, us.nama as pembuat 
            FROM undangan_rapat u 
            JOIN users us ON u.dibuat_oleh = us.id 
            ORDER BY u.waktu DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, us.nama as pembuat 
            FROM undangan_rapat u 
            JOIN users us ON u.dibuat_oleh = us.id 
            WHERE u.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO undangan_rapat (hari, waktu, tempat, acara, dibuat_oleh) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('ssssi', $data['hari'], $data['waktu'], $data['tempat'], $data['acara'], $data['dibuat_oleh']);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE undangan_rapat SET hari=?, waktu=?, tempat=?, acara=? WHERE id=?
        ");
        $stmt->bind_param('ssssi', $data['hari'], $data['waktu'], $data['tempat'], $data['acara'], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM undangan_rapat WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function count() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM undangan_rapat");
        return $result->fetch_assoc()['total'];
    }

    public function getByMonth($year, $month) {
        $stmt = $this->db->prepare("
            SELECT u.*, us.nama as pembuat 
            FROM undangan_rapat u 
            JOIN users us ON u.dibuat_oleh = us.id 
            WHERE YEAR(u.waktu)=? AND MONTH(u.waktu)=?
            ORDER BY u.waktu ASC
        ");
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByYear($year) {
        $stmt = $this->db->prepare("
            SELECT u.*, us.nama as pembuat 
            FROM undangan_rapat u 
            JOIN users us ON u.dibuat_oleh = us.id 
            WHERE YEAR(u.waktu)=?
            ORDER BY u.waktu ASC
        ");
        $stmt->bind_param('i', $year);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getMonthlyStats($year) {
        $stmt = $this->db->prepare("
            SELECT MONTH(waktu) as bulan, COUNT(*) as total 
            FROM undangan_rapat 
            WHERE YEAR(waktu)=? 
            GROUP BY MONTH(waktu) 
            ORDER BY bulan
        ");
        $stmt->bind_param('i', $year);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAvailableYears() {
        $result = $this->db->query("SELECT DISTINCT YEAR(waktu) as tahun FROM undangan_rapat ORDER BY tahun DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}