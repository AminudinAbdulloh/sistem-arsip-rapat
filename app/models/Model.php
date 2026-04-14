<?php

/**
 * Base Model
 * Menyediakan operasi CRUD dasar dan query helper yang dapat digunakan ulang.
 */
abstract class Model
{
    protected mysqli $db;

    /** Nama tabel utama — wajib didefinisikan di subclass */
    protected string $table = '';

    public function __construct()
    {
        $this->db = getDB();
    }

    // ----------------------------------------------------------------
    // CRUD Generik
    // ----------------------------------------------------------------

    public function count(): int
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM `{$this->table}`");
        return (int) $result->fetch_assoc()['total'];
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // ----------------------------------------------------------------
    // Query helpers
    // ----------------------------------------------------------------

    /**
     * Jalankan prepared statement dan kembalikan semua baris.
     *
     * @param string $sql    Query dengan placeholder '?'
     * @param string $types  String tipe parameter (mis. 'ii', 'ss')
     * @param mixed  ...$params  Nilai parameter
     */
    protected function fetchAll(string $sql, string $types = '', mixed ...$params): array
    {
        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Jalankan prepared statement dan kembalikan satu baris.
     */
    protected function fetchOne(string $sql, string $types = '', mixed ...$params): ?array
    {
        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Jalankan prepared statement non-SELECT (INSERT/UPDATE/DELETE).
     * Mengembalikan jumlah baris yang terpengaruh.
     */
    protected function execute(string $sql, string $types = '', mixed ...$params): int
    {
        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->affected_rows;
    }

    /**
     * Jalankan INSERT dan kembalikan last insert ID, atau false jika gagal.
     */
    protected function insertGetId(string $sql, string $types = '', mixed ...$params): int|false
    {
        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            return false;
        }
        return $this->db->insert_id;
    }
}