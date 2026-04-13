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
        $rows = $result->fetch_all(MYSQLI_ASSOC);

        // Ambil foto dokumentasi pertama untuk preview di listing
        foreach ($rows as &$row) {
            $stmt = $this->db->prepare("SELECT filename FROM notulensi_dokumentasi WHERE notulensi_id = ? ORDER BY id ASC LIMIT 1");
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            $foto = $stmt->get_result()->fetch_assoc();
            $row['dokumentasi_preview'] = $foto ? $foto['filename'] : null;
            $row['dokumentasi_count'] = $this->countDokumentasi($row['id']);
        }
        return $rows;
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
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $row['dokumentasi_list'] = $this->getDokumentasi($id);
            $row['dokumen_list']     = $this->getDokumen($id);
        }
        return $row;
    }

    public function existsByUndanganId($undanganId) {
        $stmt = $this->db->prepare("SELECT id FROM notulensi_rapat WHERE undangan_id = ? LIMIT 1");
        $stmt->bind_param('i', $undanganId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO notulensi_rapat (undangan_id, deskripsi_rapat, catatan, dibuat_oleh)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('issi',
            $data['undangan_id'],
            $data['deskripsi_rapat'],
            $data['catatan'],
            $data['dibuat_oleh']
        );
        if (!$stmt->execute()) return false;
        return $this->db->insert_id;
    }

    public function update($id, $data) {
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
        return $stmt->execute();
    }

    public function delete($id) {
        // File cleanup dilakukan di controller
        $stmt = $this->db->prepare("DELETE FROM notulensi_rapat WHERE id=?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function count() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM notulensi_rapat");
        return $result->fetch_assoc()['total'];
    }

    // -------------------------------------------------------
    // DOKUMENTASI FOTO (multiple)
    // -------------------------------------------------------

    public function getDokumentasi($notulensiId) {
        $stmt = $this->db->prepare("SELECT * FROM notulensi_dokumentasi WHERE notulensi_id = ? ORDER BY id ASC");
        $stmt->bind_param('i', $notulensiId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countDokumentasi($notulensiId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM notulensi_dokumentasi WHERE notulensi_id = ?");
        $stmt->bind_param('i', $notulensiId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    public function addDokumentasi($notulensiId, $filename) {
        $stmt = $this->db->prepare("INSERT INTO notulensi_dokumentasi (notulensi_id, filename) VALUES (?, ?)");
        $stmt->bind_param('is', $notulensiId, $filename);
        return $stmt->execute();
    }

    public function deleteDokumentasi($id) {
        $stmt = $this->db->prepare("SELECT filename FROM notulensi_dokumentasi WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $file = BASE_PATH . '/public/uploads/dokumentasi/' . $row['filename'];
            if (file_exists($file)) unlink($file);
        }
        $stmt2 = $this->db->prepare("DELETE FROM notulensi_dokumentasi WHERE id = ?");
        $stmt2->bind_param('i', $id);
        return $stmt2->execute();
    }

    public function deleteAllDokumentasi($notulensiId) {
        $list = $this->getDokumentasi($notulensiId);
        foreach ($list as $d) {
            $file = BASE_PATH . '/public/uploads/dokumentasi/' . $d['filename'];
            if (file_exists($file)) unlink($file);
        }
        $stmt = $this->db->prepare("DELETE FROM notulensi_dokumentasi WHERE notulensi_id = ?");
        $stmt->bind_param('i', $notulensiId);
        return $stmt->execute();
    }

    // -------------------------------------------------------
    // DOKUMEN PENDUKUNG (multiple: PDF, DOCX, dll)
    // -------------------------------------------------------

    public function getDokumen($notulensiId) {
        $stmt = $this->db->prepare("SELECT * FROM notulensi_dokumen WHERE notulensi_id = ? ORDER BY id ASC");
        $stmt->bind_param('i', $notulensiId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addDokumen($notulensiId, $filename, $originalName, $mimeType) {
        $stmt = $this->db->prepare("INSERT INTO notulensi_dokumen (notulensi_id, filename, original_name, mime_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $notulensiId, $filename, $originalName, $mimeType);
        return $stmt->execute();
    }

    public function deleteDokumen($id) {
        $stmt = $this->db->prepare("SELECT filename FROM notulensi_dokumen WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $file = BASE_PATH . '/public/uploads/dokumen/' . $row['filename'];
            if (file_exists($file)) unlink($file);
        }
        $stmt2 = $this->db->prepare("DELETE FROM notulensi_dokumen WHERE id = ?");
        $stmt2->bind_param('i', $id);
        return $stmt2->execute();
    }

    public function deleteAllDokumen($notulensiId) {
        $list = $this->getDokumen($notulensiId);
        foreach ($list as $d) {
            $file = BASE_PATH . '/public/uploads/dokumen/' . $d['filename'];
            if (file_exists($file)) unlink($file);
        }
        $stmt = $this->db->prepare("DELETE FROM notulensi_dokumen WHERE notulensi_id = ?");
        $stmt->bind_param('i', $notulensiId);
        return $stmt->execute();
    }

    // -------------------------------------------------------
    // LAPORAN
    // -------------------------------------------------------

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