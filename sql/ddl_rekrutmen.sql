-- =====================================================================
-- SKEMA RELASIONAL (DDL)
-- Sistem Informasi Rekrutmen & Seleksi Karyawan
-- Proyek Akhir Basis Data - Sistem Informasi, Universitas Muhammadiyah Jember
-- DBMS   : MySQL 8+
-- Normalisasi: seluruh tabel sudah 3NF
--   - Tidak ada atribut multi-nilai (1NF)
--   - Tidak ada atribut non-key yang bergantung pada sebagian primary key
--     komposit (2NF) - semua tabel pakai surrogate key tunggal (id_xxx)
--   - Tidak ada atribut non-key yang bergantung pada atribut non-key lain
--     (3NF), misal: nama_departemen tidak disimpan ulang di lowongan,
--     cukup id_departemen (FK)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_rekrutmen
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_rekrutmen;

-- ---------------------------------------------------------------------
-- 1. DEPARTEMEN
-- ---------------------------------------------------------------------
CREATE TABLE departemen (
    id_departemen   INT AUTO_INCREMENT PRIMARY KEY,
    nama_departemen VARCHAR(100) NOT NULL,
    keterangan      VARCHAR(255),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. USERS (akun login: admin, hrd, pewawancara)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id_user     INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','hrd','pewawancara') NOT NULL DEFAULT 'hrd',
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. SUMBER_REKRUTMEN (master: LinkedIn, Kampus, Referral, dll.)
-- ---------------------------------------------------------------------
CREATE TABLE sumber_rekrutmen (
    id_sumber   INT AUTO_INCREMENT PRIMARY KEY,
    nama_sumber VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. TAHAP_SELEKSI (master urutan tahap: Screening -> Psikotes -> ... )
-- ---------------------------------------------------------------------
CREATE TABLE tahap_seleksi (
    id_tahap    INT AUTO_INCREMENT PRIMARY KEY,
    nama_tahap  VARCHAR(100) NOT NULL,
    urutan      INT NOT NULL UNIQUE,
    keterangan  VARCHAR(255)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. LOWONGAN
-- ---------------------------------------------------------------------
CREATE TABLE lowongan (
    id_lowongan        INT AUTO_INCREMENT PRIMARY KEY,
    id_departemen      INT NOT NULL,
    posisi             VARCHAR(100) NOT NULL,
    kualifikasi        TEXT,
    jumlah_dibutuhkan  INT NOT NULL DEFAULT 1,
    tanggal_posting    DATE NOT NULL,
    batas_lamaran      DATE NOT NULL,
    status             ENUM('Buka','Ditutup','Terpenuhi') NOT NULL DEFAULT 'Buka',
    CONSTRAINT fk_lowongan_departemen FOREIGN KEY (id_departemen)
        REFERENCES departemen(id_departemen) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_batas_lamaran CHECK (batas_lamaran >= tanggal_posting)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. PELAMAR
-- ---------------------------------------------------------------------
CREATE TABLE pelamar (
    id_pelamar     INT AUTO_INCREMENT PRIMARY KEY,
    nama           VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL,
    telepon        VARCHAR(20),
    tanggal_lahir  DATE,
    alamat         VARCHAR(255),
    cv_path        VARCHAR(255),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pelamar_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. LAMARAN (pelamar melamar ke satu lowongan)
-- ---------------------------------------------------------------------
CREATE TABLE lamaran (
    id_lamaran      INT AUTO_INCREMENT PRIMARY KEY,
    id_pelamar      INT NOT NULL,
    id_lowongan     INT NOT NULL,
    id_sumber       INT,
    tanggal_lamar   DATE NOT NULL,
    status_terkini  VARCHAR(50) NOT NULL DEFAULT 'Screening CV',
    CONSTRAINT fk_lamaran_pelamar FOREIGN KEY (id_pelamar)
        REFERENCES pelamar(id_pelamar) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_lamaran_lowongan FOREIGN KEY (id_lowongan)
        REFERENCES lowongan(id_lowongan) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_lamaran_sumber FOREIGN KEY (id_sumber)
        REFERENCES sumber_rekrutmen(id_sumber) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT uq_lamaran_unik UNIQUE (id_pelamar, id_lowongan)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. LAMARAN_TAHAP (histori pergerakan pelamar antar tahap seleksi)
-- ---------------------------------------------------------------------
CREATE TABLE lamaran_tahap (
    id_lamaran_tahap  INT AUTO_INCREMENT PRIMARY KEY,
    id_lamaran        INT NOT NULL,
    id_tahap          INT NOT NULL,
    tanggal_masuk     DATE NOT NULL,
    tanggal_selesai   DATE,
    status            ENUM('Proses','Lolos','Tidak Lolos') NOT NULL DEFAULT 'Proses',
    catatan           VARCHAR(255),
    CONSTRAINT fk_lt_lamaran FOREIGN KEY (id_lamaran)
        REFERENCES lamaran(id_lamaran) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_lt_tahap FOREIGN KEY (id_tahap)
        REFERENCES tahap_seleksi(id_tahap) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_tanggal_selesai CHECK (tanggal_selesai IS NULL OR tanggal_selesai >= tanggal_masuk)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. KRITERIA_PENILAIAN (per tahap, punya bobot)
-- ---------------------------------------------------------------------
CREATE TABLE kriteria_penilaian (
    id_kriteria    INT AUTO_INCREMENT PRIMARY KEY,
    id_tahap       INT NOT NULL,
    nama_kriteria  VARCHAR(100) NOT NULL,
    bobot          DECIMAL(5,2) NOT NULL,
    CONSTRAINT fk_kriteria_tahap FOREIGN KEY (id_tahap)
        REFERENCES tahap_seleksi(id_tahap) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_bobot CHECK (bobot > 0 AND bobot <= 100)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. PENILAIAN (nilai pelamar per kriteria per tahap yang dijalani)
-- ---------------------------------------------------------------------
CREATE TABLE penilaian (
    id_penilaian      INT AUTO_INCREMENT PRIMARY KEY,
    id_lamaran_tahap  INT NOT NULL,
    id_kriteria       INT NOT NULL,
    nilai             DECIMAL(5,2) NOT NULL,
    id_penilai        INT,
    tanggal_nilai     DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT fk_penilaian_lt FOREIGN KEY (id_lamaran_tahap)
        REFERENCES lamaran_tahap(id_lamaran_tahap) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_penilaian_kriteria FOREIGN KEY (id_kriteria)
        REFERENCES kriteria_penilaian(id_kriteria) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_penilaian_user FOREIGN KEY (id_penilai)
        REFERENCES users(id_user) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT chk_nilai CHECK (nilai >= 0 AND nilai <= 100),
    CONSTRAINT uq_penilaian_unik UNIQUE (id_lamaran_tahap, id_kriteria)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 11. JADWAL_WAWANCARA
-- ---------------------------------------------------------------------
CREATE TABLE jadwal_wawancara (
    id_jadwal         INT AUTO_INCREMENT PRIMARY KEY,
    id_lamaran_tahap  INT NOT NULL,
    id_user           INT NOT NULL,
    waktu             DATETIME NOT NULL,
    lokasi            VARCHAR(150),
    status            ENUM('Terjadwal','Selesai','Dibatalkan') NOT NULL DEFAULT 'Terjadwal',
    CONSTRAINT fk_jadwal_lt FOREIGN KEY (id_lamaran_tahap)
        REFERENCES lamaran_tahap(id_lamaran_tahap) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_jadwal_user FOREIGN KEY (id_user)
        REFERENCES users(id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_jadwal_lt UNIQUE (id_lamaran_tahap)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 12. PENAWARAN_KERJA (offering letter)
-- ---------------------------------------------------------------------
CREATE TABLE penawaran_kerja (
    id_penawaran       INT AUTO_INCREMENT PRIMARY KEY,
    id_lamaran         INT NOT NULL,
    gaji_ditawarkan    DECIMAL(12,2) NOT NULL,
    tanggal_mulai      DATE NOT NULL,
    tanggal_penawaran  DATE NOT NULL DEFAULT (CURRENT_DATE),
    status_respon      ENUM('Menunggu','Diterima','Ditolak') NOT NULL DEFAULT 'Menunggu',
    CONSTRAINT fk_penawaran_lamaran FOREIGN KEY (id_lamaran)
        REFERENCES lamaran(id_lamaran) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT uq_penawaran_lamaran UNIQUE (id_lamaran)
) ENGINE=InnoDB;

-- =====================================================================
-- INDEX tambahan untuk kolom yang sering dipakai di WHERE / JOIN
-- (PK, FK, dan UNIQUE sudah otomatis terindeks oleh MySQL/InnoDB)
-- =====================================================================
CREATE INDEX idx_lowongan_status      ON lowongan(status);
CREATE INDEX idx_lamaran_tanggal      ON lamaran(tanggal_lamar);
CREATE INDEX idx_lamaran_status       ON lamaran(status_terkini);
CREATE INDEX idx_lt_tanggal_masuk     ON lamaran_tahap(tanggal_masuk);
CREATE INDEX idx_jadwal_waktu         ON jadwal_wawancara(waktu);
CREATE INDEX idx_penawaran_status     ON penawaran_kerja(status_respon);
