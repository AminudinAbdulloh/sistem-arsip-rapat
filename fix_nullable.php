<?php
/**
 * Fix migration: mark new migration as run + ALTER columns
 * Hapus file ini setelah dijalankan sekali!
 */

$conn = new mysqli('localhost', 'root', 'root', 'arsip_rapat', 3306);
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error . PHP_EOL);
echo "Koneksi berhasil." . PHP_EOL;

// Buat tgl_rapat dan tema_rapat nullable
$r1 = $conn->query("ALTER TABLE `notulensi_rapat` MODIFY COLUMN `tgl_rapat` DATE NULL");
echo "tgl_rapat nullable: " . ($r1 ? "OK" : $conn->error) . PHP_EOL;

$r2 = $conn->query("ALTER TABLE `notulensi_rapat` MODIFY COLUMN `tema_rapat` VARCHAR(255) NULL");
echo "tema_rapat nullable: " . ($r2 ? "OK" : $conn->error) . PHP_EOL;

// Catat migration baru ke tabel migrations
$res = $conn->query("SELECT id FROM migrations WHERE version = '2026-06-19-162400'");
if ($res->num_rows === 0) {
    $conn->query("INSERT INTO migrations (version, class, `group`, namespace, time, batch) VALUES (
        '2026-06-19-162400',
        'App\\\\Database\\\\Migrations\\\\NullableTglRapatTemaRapat',
        'default', 'App', " . time() . ", 3
    )");
    echo "Migration 'NullableTglRapatTemaRapat' dicatat." . PHP_EOL;
} else {
    echo "Migration sudah tercatat, skip." . PHP_EOL;
}

$conn->close();
echo "Selesai! Hapus file ini." . PHP_EOL;
