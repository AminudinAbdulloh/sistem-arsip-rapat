<?php
class Notulensi {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll() {
        $result = $this->db->query("
            SELECT n.*, 
                   u.acara as nama_undangan, 
                   u.acara as tema_rapat,
                   u.waktu as waktu_undangan,
                   DATE(u.waktu) as tgl_rapat
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            ORDER BY u.waktu DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT n.*, 
                   u.acara as nama_undangan,
                   u.acara as tema_rapat,
                   u.waktu as waktu_undangan,
                   DATE(u.waktu) as tgl_rapat,
                   u.tempat
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            WHERE n.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function existsByUndanganId($undanganId) {
        $stmt = $this->db->prepare("SELECT id FROM notulensi_rapat WHERE undangan_id = ? LIMIT 1");
        $stmt->bind_param('i', $undanganId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO notulensi_rapat (undangan_id, deskripsi_rapat, catatan, dokumentasi, dibuat_oleh)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('isssi',
            $data['undangan_id'],
            $data['deskripsi_rapat'],
            $data['catatan'],
            $data['dokumentasi'],
            $data['dibuat_oleh']
        );
        return $stmt->execute();
    }

    public function update($id, $data) {
        if (!empty($data['dokumentasi'])) {
            $stmt = $this->db->prepare("
                UPDATE notulensi_rapat 
                SET undangan_id=?, deskripsi_rapat=?, catatan=?, dokumentasi=? 
                WHERE id=?
            ");
            $stmt->bind_param('isssi',
                $data['undangan_id'],
                $data['deskripsi_rapat'],
                $data['catatan'],
                $data['dokumentasi'],
                $id
            );
        } else {
            $stmt = $this->db->prepare("
                UPDATE notulensi_rapat 
                SET undangan_id=?, deskripsi_rapat=?, catatan=? 
                WHERE id=?
            ");
            $stmt->bind_param('issi',
                $data['undangan_id'],
                $data['deskripsi_rapat'],
                $data['catatan'],
                $id
            );
        }
        return $stmt->execute();
    }

    public function delete($id) {
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
            SELECT n.*, 
                   u.acara as nama_undangan,
                   u.acara as tema_rapat,
                   DATE(u.waktu) as tgl_rapat
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            WHERE YEAR(u.waktu)=? AND MONTH(u.waktu)=?
            ORDER BY u.waktu ASC
        ");
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByYear($year) {
        $stmt = $this->db->prepare("
            SELECT n.*, 
                   u.acara as nama_undangan,
                   u.acara as tema_rapat,
                   DATE(u.waktu) as tgl_rapat
            FROM notulensi_rapat n
            JOIN undangan_rapat u ON n.undangan_id = u.id
            WHERE YEAR(u.waktu)=?
            ORDER BY u.waktu ASC
        ");
        $stmt->bind_param('i', $year);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}