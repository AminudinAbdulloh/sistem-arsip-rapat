<?php

namespace ArsipRapat\Model;

use ArsipRapat\Config\Database;

class User
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByNip(string $nip): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE nip = ?");
        $stmt->execute([$nip]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function updateFotoProfil(int $id, string $foto): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET foto_profil = ? WHERE id = ?");
        return $stmt->execute([$foto, $id]);
    }
}
