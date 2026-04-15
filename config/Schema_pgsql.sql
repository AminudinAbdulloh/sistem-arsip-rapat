-- =============================================================
-- Schema PostgreSQL
-- Sistem Informasi Pengelolaan Arsip Rapat — ITDA
-- =============================================================

-- Buat database (jalankan sebagai superuser jika belum ada):
-- CREATE DATABASE arsip_rapat_itd ENCODING 'UTF8';
-- \c arsip_rapat_itd

-- ---------------------------------------------------------------
-- Tabel users
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          SERIAL PRIMARY KEY,
    nip         VARCHAR(30)  NOT NULL UNIQUE,
    nama        VARCHAR(100) NOT NULL,
    password    VARCHAR(255) NOT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- Tabel undangan_rapat
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS undangan_rapat (
    id          SERIAL PRIMARY KEY,
    waktu       TIMESTAMP    NOT NULL,
    tempat      VARCHAR(200) NOT NULL,
    acara       TEXT         NOT NULL,
    tgl_surat   DATE         NOT NULL DEFAULT CURRENT_DATE,
    dibuat_oleh INT          NOT NULL REFERENCES users(id),
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- Trigger untuk auto-update updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER undangan_rapat_updated_at
    BEFORE UPDATE ON undangan_rapat
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ---------------------------------------------------------------
-- Tabel notulensi_rapat
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notulensi_rapat (
    id               SERIAL PRIMARY KEY,
    undangan_id      INT  NOT NULL REFERENCES undangan_rapat(id) ON DELETE CASCADE,
    deskripsi_rapat  TEXT NOT NULL,
    catatan          TEXT DEFAULT NULL,
    dibuat_oleh      INT  NOT NULL REFERENCES users(id),
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE TRIGGER notulensi_rapat_updated_at
    BEFORE UPDATE ON notulensi_rapat
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ---------------------------------------------------------------
-- Tabel notulensi_dokumentasi (foto)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notulensi_dokumentasi (
    id           SERIAL PRIMARY KEY,
    notulensi_id INT          NOT NULL REFERENCES notulensi_rapat(id) ON DELETE CASCADE,
    filename     VARCHAR(255) NOT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- Tabel notulensi_dokumen (file pendukung)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notulensi_dokumen (
    id            SERIAL PRIMARY KEY,
    notulensi_id  INT          NOT NULL REFERENCES notulensi_rapat(id) ON DELETE CASCADE,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type     VARCHAR(100) DEFAULT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);