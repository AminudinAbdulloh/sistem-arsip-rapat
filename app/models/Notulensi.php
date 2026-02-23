<?php
class Notulensi {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll() {
        $result = $this->db->query("
            SELECT n.*, u.acara as nama_undangan, u.waktu as waktu_undangan, us.nama as pembuat 
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            JOIN users us ON n.dibuat_oleh = us.id
            ORDER BY n.tgl_rapat DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.acara as nama_undangan, u.hari, u.waktu as waktu_undangan, u.tempat,
                   us.nama as pembuat
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            JOIN users us ON n.dibuat_oleh = us.id
            WHERE n.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO notulensi_rapat (undangan_id, tgl_rapat, tema_rapat, deskripsi_rapat, catatan, dokumentasi, dibuat_oleh)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('isssssi', $data['undangan_id'], $data['tgl_rapat'], $data['tema_rapat'],
            $data['deskripsi_rapat'], $data['catatan'], $data['dokumentasi'], $data['dibuat_oleh']);
        return $stmt->execute();
    }

    public function update($id, $data) {
        if (!empty($data['dokumentasi'])) {
            $stmt = $this->db->prepare("
                UPDATE notulensi_rapat SET undangan_id=?, tgl_rapat=?, tema_rapat=?, deskripsi_rapat=?, catatan=?, dokumentasi=? WHERE id=?
            ");
            $stmt->bind_param('isssssi', $data['undangan_id'], $data['tgl_rapat'], $data['tema_rapat'],
                $data['deskripsi_rapat'], $data['catatan'], $data['dokumentasi'], $id);
        } else {
            $stmt = $this->db->prepare("
                UPDATE notulensi_rapat SET undangan_id=?, tgl_rapat=?, tema_rapat=?, deskripsi_rapat=?, catatan=? WHERE id=?
            ");
            $stmt->bind_param('issssi', $data['undangan_id'], $data['tgl_rapat'], $data['tema_rapat'],
                $data['deskripsi_rapat'], $data['catatan'], $id);
        }
        return $stmt->execute();
    }

    public function delete($id) {
        // Get dokumentasi file before delete
        $n = $this->getById($id);
        if ($n && $n['dokumentasi']) {
            $file = BASE_PATH . '/public/uploads/dokumentasi/' . $n['dokumentasi'];
            if (file_exists($file)) unlink($file);
        }
        $stmt = $this->db->prepare("DELETE FROM notulensi_rapat WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function count() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM notulensi_rapat");
        return $result->fetch_assoc()['total'];
    }

    public function getByMonth($year, $month) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.acara as nama_undangan, us.nama as pembuat 
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            JOIN users us ON n.dibuat_oleh = us.id
            WHERE YEAR(n.tgl_rapat)=? AND MONTH(n.tgl_rapat)=?
            ORDER BY n.tgl_rapat ASC
        ");
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByYear($year) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.acara as nama_undangan, us.nama as pembuat 
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            JOIN users us ON n.dibuat_oleh = us.id
            WHERE YEAR(n.tgl_rapat)=?
            ORDER BY n.tgl_rapat ASC
        ");
        $stmt->bind_param('i', $year);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}