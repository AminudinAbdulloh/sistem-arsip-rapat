<?php
class User {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByNip($nip) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE nip = ?");
        $stmt->bind_param('s', $nip);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}