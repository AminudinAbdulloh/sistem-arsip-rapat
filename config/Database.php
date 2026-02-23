<?php

namespace ArsipRapat\Config;

class Database
{
    private static ?\PDO $pdo = null;

    public static function getConnection(): \PDO
    {
        if (self::$pdo === null) {
            $host = 'localhost';
            $dbname = 'arsip_rapat';
            $username = 'root';
            $password = 'root';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$pdo = new \PDO($dsn, $username, $password, $options);
            } catch (\PDOException $e) {
                die('Koneksi database gagal: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
