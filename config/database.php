<?php

/**
 * config/database.php
 *
 * Database Abstraction Layer.
 * Mendukung MySQL (via mysqli) dan PostgreSQL (via pgsql).
 */

// ----------------------------------------------------------------
// Interface — kontrak yang harus dipenuhi semua driver
// ----------------------------------------------------------------

interface DatabaseInterface
{
    /**
     * Jalankan query SELECT dan kembalikan semua baris sebagai array asosiatif.
     *
     * @param string $sql    Query dengan placeholder '?'
     * @param array  $params Parameter nilai
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array;

    /**
     * Jalankan query SELECT dan kembalikan satu baris, atau null jika tidak ada.
     *
     * @param string $sql
     * @param array  $params
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params = []): ?array;

    /**
     * Jalankan query INSERT/UPDATE/DELETE.
     * Kembalikan jumlah baris yang terpengaruh.
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Jalankan INSERT dan kembalikan ID baris baru, atau false jika gagal.
     */
    public function insertGetId(string $sql, array $params = []): int|false;

    /**
     * Hitung total baris pada sebuah tabel.
     */
    public function countTable(string $table): int;

    /**
     * Hapus baris berdasarkan ID.
     */
    public function deleteById(string $table, int $id): bool;

    /**
     * Kembalikan nama driver aktif: 'mysql' atau 'pgsql'.
     */
    public function getDriver(): string;
}


// ----------------------------------------------------------------
// Implementasi MySQL (mysqli)
// ----------------------------------------------------------------

class MySQLDatabase implements DatabaseInterface
{
    private mysqli $conn;

    public function __construct(string $host, string $user, string $pass, string $dbName, int $port = 3306)
    {
        $this->conn = new mysqli($host, $user, $pass, $dbName, $port);

        if ($this->conn->connect_error) {
            die('Koneksi MySQL gagal: ' . $this->conn->connect_error);
        }

        $this->conn->set_charset('utf8mb4');
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        return $stmt->affected_rows;
    }

    public function insertGetId(string $sql, array $params = []): int|false
    {
        $stmt = $this->prepare($sql, $params);
        if (!$stmt->execute()) {
            return false;
        }
        return (int) $this->conn->insert_id;
    }

    public function countTable(string $table): int
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM `{$table}`");
        return (int) $result->fetch_assoc()['total'];
    }

    public function deleteById(string $table, int $id): bool
    {
        return $this->execute("DELETE FROM `{$table}` WHERE id = ?", [$id]) > 0;
    }

    public function getDriver(): string
    {
        return 'mysql';
    }

    // ---- Private ----

    private function prepare(string $sql, array $params): mysqli_stmt
    {
        $stmt = $this->conn->prepare($sql);

        if (!empty($params)) {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p))   $types .= 'i';
                elseif (is_float($p)) $types .= 'd';
                else              $types .= 's';
            }
            $stmt->bind_param($types, ...$params);
        }

        return $stmt;
    }
}


// ----------------------------------------------------------------
// Implementasi PostgreSQL (pgsql)
// ----------------------------------------------------------------

class PostgreSQLDatabase implements DatabaseInterface
{
    /** @var \PgSql\Connection */
    private mixed $conn;

    /** Counter untuk nama prepared statement yang unik */
    private int $stmtCounter = 0;

    public function __construct(string $host, string $user, string $pass, string $dbName, int $port = 5432)
    {
        $dsn = "host={$host} port={$port} dbname={$dbName} user={$user} password={$pass}";
        $this->conn = pg_connect($dsn);

        if ($this->conn === false) {
            die('Koneksi PostgreSQL gagal. Periksa konfigurasi DB_* di .env');
        }

        // Set encoding
        pg_set_client_encoding($this->conn, 'UTF8');
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $result = $this->query($sql, $params);
        if ($result === false) return [];
        $rows = pg_fetch_all($result);
        return $rows ?: [];
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params);
        if ($result === false) return null;
        $row = pg_fetch_assoc($result);
        return $row ?: null;
    }

    public function execute(string $sql, array $params = []): int
    {
        $result = $this->query($sql, $params);
        if ($result === false) return 0;
        return pg_affected_rows($result);
    }

    public function insertGetId(string $sql, array $params = []): int|false
    {
        // PostgreSQL menggunakan RETURNING id — tambahkan jika belum ada
        $sqlReturning = $this->appendReturning($sql);
        $result = $this->query($sqlReturning, $params);
        if ($result === false) return false;
        $row = pg_fetch_assoc($result);
        return $row ? (int) $row['id'] : false;
    }

    public function countTable(string $table): int
    {
        // PostgreSQL menggunakan tanda kutip ganda untuk identifier
        $result = $this->query("SELECT COUNT(*) AS total FROM \"{$table}\"", []);
        if ($result === false) return 0;
        $row = pg_fetch_assoc($result);
        return (int) ($row['total'] ?? 0);
    }

    public function deleteById(string $table, int $id): bool
    {
        return $this->execute("DELETE FROM \"{$table}\" WHERE id = $1", [$id]) > 0;
    }

    public function getDriver(): string
    {
        return 'pgsql';
    }

    // ---- Private ----

    /**
     * Konversi placeholder '?' ke '$1, $2, ...' gaya PostgreSQL,
     * lalu jalankan query.
     */
    private function query(string $sql, array $params): mixed
    {
        $pgSql = $this->convertPlaceholders($sql);

        if (empty($params)) {
            $result = pg_query($this->conn, $pgSql);
        } else {
            $stmtName = 'stmt_' . (++$this->stmtCounter);
            pg_prepare($this->conn, $stmtName, $pgSql);
            $result = pg_execute($this->conn, $stmtName, $params);
        }

        if ($result === false) {
            $err = pg_last_error($this->conn);
            error_log("PostgreSQL query error: {$err}\nSQL: {$sql}");
        }

        return $result;
    }

    /**
     * Ganti '?' dengan '$1', '$2', dst.
     * Juga ganti backtick (MySQL) dengan tanda kutip ganda (PostgreSQL).
     */
    private function convertPlaceholders(string $sql): string
    {
        // Ganti backtick MySQL → kutip ganda PostgreSQL
        $sql = str_replace('`', '"', $sql);

        // Ganti ? → $1, $2, ...
        $i = 0;
        return preg_replace_callback('/\?/', function () use (&$i) {
            return '$' . (++$i);
        }, $sql);
    }

    /**
     * Tambahkan "RETURNING id" ke akhir INSERT jika belum ada.
     */
    private function appendReturning(string $sql): string
    {
        $trimmed = rtrim(trim($sql), ';');
        if (stripos($trimmed, 'RETURNING') === false) {
            $trimmed .= ' RETURNING id';
        }
        return $trimmed;
    }
}


// ----------------------------------------------------------------
// Factory — buat instance berdasarkan DB_DRIVER di .env
// ----------------------------------------------------------------

function getDB(): DatabaseInterface
{
    static $instance = null;

    if ($instance === null) {
        $driver = strtolower(trim($_ENV['DB_DRIVER'] ?? 'mysql'));
        $host   = $_ENV['DB_HOST'] ?? 'localhost';
        $user   = $_ENV['DB_USER'] ?? '';
        $pass   = $_ENV['DB_PASS'] ?? '';
        $name   = $_ENV['DB_NAME'] ?? '';
        $port   = (int) ($_ENV['DB_PORT'] ?? ($driver === 'pgsql' ? 5432 : 3306));

        $instance = match ($driver) {
            'pgsql', 'postgres', 'postgresql' => new PostgreSQLDatabase($host, $user, $pass, $name, $port),
            default                            => new MySQLDatabase($host, $user, $pass, $name, $port),
        };
    }

    return $instance;
}