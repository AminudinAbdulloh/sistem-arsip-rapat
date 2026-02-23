-- Database: arsip_rapat_itd
CREATE DATABASE IF NOT EXISTS arsip_rapat_itd CHARACTER SET utf8 COLLATE utf8_general_ci;
USE arsip_rapat_itd;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(30) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS undangan_rapat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hari VARCHAR(20) NOT NULL,
    waktu DATETIME NOT NULL,
    tempat VARCHAR(200) NOT NULL,
    acara TEXT NOT NULL,
    dibuat_oleh INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS notulensi_rapat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    undangan_id INT NOT NULL,
    tgl_rapat DATE NOT NULL,
    tema_rapat VARCHAR(200) NOT NULL,
    deskripsi_rapat TEXT NOT NULL,
    catatan TEXT,
    dokumentasi VARCHAR(255) DEFAULT NULL,
    dibuat_oleh INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (undangan_id) REFERENCES undangan_rapat(id) ON DELETE CASCADE,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
);

-- Default user: NIP=123456789, Password=admin123
INSERT INTO users (nip, nama, password) VALUES 
('123456789', 'Admin ITD Adisutjipto', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- password: password