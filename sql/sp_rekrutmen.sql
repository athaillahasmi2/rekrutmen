-- =====================================================================
-- STORED PROCEDURE
-- Sistem Informasi Rekrutmen & Seleksi Karyawan
-- Jalankan SETELAH ddl_rekrutmen.sql, dml_rekrutmen.sql, view_rekrutmen.sql,
-- trigger_rekrutmen.sql
-- Checklist wajib: minimal 2 SP/Function yang dipanggil dari aplikasi
-- =====================================================================

USE rekrutmen;

-- -----------------------------------------------------------------------
-- SP 1: sp_hitung_skor_pelamar
-- Kegunaan: dipanggil aplikasi saat HRD membuka detail pelamar untuk
-- melihat skor akhir tertimbang di tahap tertentu. OUT parameter berisi
-- hasil skor (0 jika belum ada nilai sama sekali).
-- -----------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_hitung_skor_pelamar;

DELIMITER $$
CREATE PROCEDURE sp_hitung_skor_pelamar(
    IN  p_id_lamaran INT,
    IN  p_id_tahap   INT,
    OUT p_skor       DECIMAL(5,2)
)
BEGIN
    DECLARE v_id_lamaran_tahap INT;

    SELECT id_lamaran_tahap INTO v_id_lamaran_tahap
    FROM lamaran_tahap
    WHERE id_lamaran = p_id_lamaran AND id_tahap = p_id_tahap
    LIMIT 1;

    SELECT ROUND(SUM(pn.nilai * kp.bobot) / SUM(kp.bobot), 2)
    INTO p_skor
    FROM penilaian pn
    JOIN kriteria_penilaian kp ON kp.id_kriteria = pn.id_kriteria
    WHERE pn.id_lamaran_tahap = v_id_lamaran_tahap;

    IF p_skor IS NULL THEN
        SET p_skor = 0;
    END IF;
END$$
DELIMITER ;

-- -----------------------------------------------------------------------
-- SP 2: sp_pindah_tahap
-- Kegunaan: dipanggil aplikasi lewat tombol "Loloskan ke tahap berikutnya".
-- Menutup tahap lama yang masih 'Proses' (logika ini dipindah ke sini
-- karena tidak bisa jadi trigger - lihat catatan di trigger_rekrutmen.sql),
-- lalu membuka tahap baru, dan menyinkronkan status_terkini di lamaran.
-- -----------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_pindah_tahap;

DELIMITER $$
CREATE PROCEDURE sp_pindah_tahap(
    IN p_id_lamaran    INT,
    IN p_id_tahap_baru INT,
    IN p_tanggal       DATE
)
BEGIN
    DECLARE v_nama_tahap VARCHAR(100);

    -- Tutup tahap lama yang masih berjalan untuk lamaran ini
    UPDATE lamaran_tahap
    SET status = 'Lolos',
        tanggal_selesai = p_tanggal
    WHERE id_lamaran = p_id_lamaran
      AND status = 'Proses';

    -- Buka tahap baru
    INSERT INTO lamaran_tahap (id_lamaran, id_tahap, tanggal_masuk, status)
    VALUES (p_id_lamaran, p_id_tahap_baru, p_tanggal, 'Proses');

    -- Sinkronkan status ringkas di tabel lamaran
    SELECT nama_tahap INTO v_nama_tahap
    FROM tahap_seleksi WHERE id_tahap = p_id_tahap_baru;

    UPDATE lamaran
    SET status_terkini = CONCAT('Proses - ', v_nama_tahap)
    WHERE id_lamaran = p_id_lamaran;
END$$
DELIMITER ;

-- -----------------------------------------------------------------------
-- SP 3: sp_funnel_lowongan  (tambahan - melebihi minimal 2)
-- Kegunaan: dipanggil aplikasi di halaman detail lowongan untuk
-- menampilkan funnel (corong) jumlah pelamar yang masuk & lolos di
-- setiap tahap seleksi, khusus untuk satu lowongan.
-- -----------------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_funnel_lowongan;

DELIMITER $$
CREATE PROCEDURE sp_funnel_lowongan(IN p_id_lowongan INT)
BEGIN
    SELECT
        ts.urutan,
        ts.nama_tahap,
        COUNT(DISTINCT lt.id_lamaran)                        AS jumlah_masuk,
        SUM(CASE WHEN lt.status = 'Lolos' THEN 1 ELSE 0 END) AS jumlah_lolos
    FROM tahap_seleksi ts
    LEFT JOIN lamaran_tahap lt
        ON lt.id_tahap = ts.id_tahap
       AND lt.id_lamaran IN (SELECT id_lamaran FROM lamaran WHERE id_lowongan = p_id_lowongan)
    GROUP BY ts.urutan, ts.nama_tahap
    ORDER BY ts.urutan;
END$$
DELIMITER ;
