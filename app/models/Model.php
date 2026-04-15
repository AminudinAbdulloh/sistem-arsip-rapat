<?php

/**
 * Base Model
 *
 * Menggunakan DatabaseInterface — bekerja dengan MySQL maupun PostgreSQL.
 * Semua query helper meneruskan panggilan ke driver yang aktif.
 */
abstract class Model
{
    protected DatabaseInterface $db;

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
        return $this->db->countTable($this->table);
    }

    public function delete(int $id): bool
    {
        return $this->db->deleteById($this->table, $id);
    }

    // ----------------------------------------------------------------
    // Query helpers — wrapper tipis ke DatabaseInterface
    // ----------------------------------------------------------------

    /**
     * Jalankan SELECT dan kembalikan semua baris.
     *
     * Perubahan dari versi lama: parameter tidak lagi pakai string $types.
     * Cukup kirim array nilai langsung.
     *
     * Contoh:
     *   $this->fetchAll("SELECT * FROM tabel WHERE year = ? AND month = ?", [$year, $month]);
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        // Kompatibilitas mundur: jika dipanggil dengan gaya lama (string types + variadic),
        // abaikan argumen ketiga ke atas dan tangani di sini.
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Jalankan SELECT dan kembalikan satu baris (atau null).
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Jalankan INSERT/UPDATE/DELETE.
     * Kembalikan jumlah baris yang terpengaruh.
     */
    protected function execute(string $sql, array $params = []): int
    {
        return $this->db->execute($sql, $params);
    }

    /**
     * Jalankan INSERT dan kembalikan last insert ID, atau false jika gagal.
     */
    protected function insertGetId(string $sql, array $params = []): int|false
    {
        return $this->db->insertGetId($sql, $params);
    }
}