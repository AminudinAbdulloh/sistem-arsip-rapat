-- Database: arsip_rapat
CREATE DATABASE IF NOT EXISTS arsip_rapat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE arsip_rapat;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kata_sandi VARCHAR(255) NOT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    jabatan VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: undangan_rapat
CREATE TABLE IF NOT EXISTS undangan_rapat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
    waktu DATETIME NOT NULL,
    tempat VARCHAR(255) NOT NULL,
    acara TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: notulensi_rapat
CREATE TABLE IF NOT EXISTS notulensi_rapat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    undangan_id INT NOT NULL,
    tgl_rapat DATE NOT NULL,
    tema_rapat VARCHAR(255) NOT NULL,
    deskripsi_rapat TEXT NOT NULL,
    catatan TEXT,
    dokumentasi VARCHAR(255) DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (undangan_id) REFERENCES undangan_rapat(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Default user (password: password)
INSERT INTO users (nip, nama, kata_sandi, jabatan) VALUES 
('198001012005011001', 'Administrator ITD', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kepala Program Studi'),
('198502152010012002', 'Dr. Siti Rahayu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sekretaris Prodi');
