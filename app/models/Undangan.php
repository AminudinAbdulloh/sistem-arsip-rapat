<?php
class Undangan {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll() {
        $result = $this->db->query("
            SELECT u.*
            FROM undangan_rapat u 
            ORDER BY u.waktu DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*
            FROM undangan_rapat u 
            WHERE u.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO undangan_rapat (waktu, tempat, acara, tgl_surat, dibuat_oleh) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $tglSurat = !empty($data['tgl_surat']) ? $data['tgl_surat'] : date('Y-m-d');
        $stmt->bind_param('ssssi', $data['waktu'], $data['tempat'], $data['acara'], $tglSurat, $data['dibuat_oleh']);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE undangan_rapat SET waktu=?, tempat=?, acara=?, tgl_surat=? WHERE id=?
        ");
        $tglSurat = !empty($data['tgl_surat']) ? $data['tgl_surat'] : date('Y-m-d');
        $stmt->bind_param('ssssi', $data['waktu'], $data['tempat'], $data['acara'], $tglSurat, $id);
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
            SELECT u.*
            FROM undangan_rapat u
            WHERE YEAR(u.waktu)=? AND MONTH(u.waktu)=?
            ORDER BY u.waktu ASC
        ");
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByYear($year) {
        $stmt = $this->db->prepare("
            SELECT u.*
            FROM undangan_rapat u 
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

    public function getUndanganTanpaNotulensi() {
        $result = $this->db->query("
            SELECT u.*
            FROM undangan_rapat u 
            LEFT JOIN notulensi_rapat n ON n.undangan_id = u.id
            WHERE n.id IS NULL
            ORDER BY u.waktu DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}