-- =====================================================================
-- TRIGGER
-- Sistem Informasi Rekrutmen & Seleksi Karyawan
-- Jalankan SETELAH ddl_rekrutmen.sql, dml_rekrutmen.sql, view_rekrutmen.sql
-- Checklist wajib: minimal 2 TRIGGER dengan logika bisnis jelas
-- =====================================================================

USE rekrutmen;

-- -----------------------------------------------------------------------
-- TRIGGER 1: trg_evaluasi_otomatis
-- Logika bisnis: setiap kali sebuah nilai kriteria dimasukkan (INSERT ke
-- penilaian), trigger mengecek apakah SEMUA kriteria untuk tahap tersebut
-- sudah lengkap dinilai. Jika sudah lengkap DAN skor akhir tertimbang
-- berada di bawah ambang batas (60), status di lamaran_tahap otomatis
-- diubah menjadi 'Tidak Lolos' - HRD tidak perlu mengecek manual satu-satu.
-- Catatan desain: logika "tutup tahap lama saat pindah ke tahap baru"
-- awalnya direncanakan sebagai trigger di sini, tapi MySQL/MariaDB
-- melarang trigger memodifikasi tabel yang sama dengan tabel yang sedang
-- di-INSERT/UPDATE olehnya (mutating table restriction). Karena itu,
-- logika tersebut dipindah ke stored procedure sp_pindah_tahap (bagian
-- berikutnya), yang justru lebih tepat karena "pindah tahap" adalah aksi
-- yang sengaja dijalankan HRD, bukan efek samping otomatis.
-- -----------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_evaluasi_otomatis;

DELIMITER $$
CREATE TRIGGER trg_evaluasi_otomatis
AFTER INSERT ON penilaian
FOR EACH ROW
BEGIN
    DECLARE v_id_tahap        INT;
    DECLARE v_jumlah_kriteria INT;
    DECLARE v_jumlah_dinilai  INT;
    DECLARE v_skor            DECIMAL(5,2);
    DECLARE v_ambang_batas    DECIMAL(5,2) DEFAULT 60.00;

    SELECT id_tahap INTO v_id_tahap
    FROM lamaran_tahap WHERE id_lamaran_tahap = NEW.id_lamaran_tahap;

    SELECT COUNT(*) INTO v_jumlah_kriteria
    FROM kriteria_penilaian WHERE id_tahap = v_id_tahap;

    SELECT COUNT(*) INTO v_jumlah_dinilai
    FROM penilaian WHERE id_lamaran_tahap = NEW.id_lamaran_tahap;

    -- hanya evaluasi kalau semua kriteria tahap ini sudah lengkap dinilai
    IF v_jumlah_dinilai >= v_jumlah_kriteria THEN
        SELECT ROUND(SUM(pn.nilai * kp.bobot) / SUM(kp.bobot), 2) INTO v_skor
        FROM penilaian pn
        JOIN kriteria_penilaian kp ON kp.id_kriteria = pn.id_kriteria
        WHERE pn.id_lamaran_tahap = NEW.id_lamaran_tahap;

        IF v_skor < v_ambang_batas THEN
            UPDATE lamaran_tahap
            SET status = 'Tidak Lolos'
            WHERE id_lamaran_tahap = NEW.id_lamaran_tahap
              AND status = 'Proses';
        END IF;
    END IF;
END$$
DELIMITER ;

-- -----------------------------------------------------------------------
-- TRIGGER 2: trg_update_status_lamaran
-- Logika bisnis: saat status di lamaran_tahap diubah menjadi 'Tidak
-- Lolos', kolom status_terkini di tabel lamaran otomatis ikut diperbarui
-- (contoh: 'Tidak Lolos - Psikotes') tanpa perlu update manual terpisah.
-- -----------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_update_status_lamaran;

DELIMITER $$
CREATE TRIGGER trg_update_status_lamaran
AFTER UPDATE ON lamaran_tahap
FOR EACH ROW
BEGIN
    DECLARE v_nama_tahap VARCHAR(100);

    IF NEW.status = 'Tidak Lolos' AND OLD.status <> 'Tidak Lolos' THEN
        SELECT nama_tahap INTO v_nama_tahap
        FROM tahap_seleksi WHERE id_tahap = NEW.id_tahap;

        UPDATE lamaran
        SET status_terkini = CONCAT('Tidak Lolos - ', v_nama_tahap)
        WHERE id_lamaran = NEW.id_lamaran;
    END IF;
END$$
DELIMITER ;

-- -----------------------------------------------------------------------
-- TRIGGER 3: trg_lowongan_terpenuhi  (tambahan - melebihi minimal 2)
-- Logika bisnis: saat status_respon di penawaran_kerja berubah menjadi
-- 'Diterima', status lamaran ikut jadi 'Diterima'. Jika jumlah pelamar
-- yang sudah diterima untuk lowongan itu mencapai jumlah_dibutuhkan,
-- status lowongan otomatis berubah dari 'Buka' menjadi 'Terpenuhi'.
-- -----------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_lowongan_terpenuhi;

DELIMITER $$
CREATE TRIGGER trg_lowongan_terpenuhi
AFTER UPDATE ON penawaran_kerja
FOR EACH ROW
BEGIN
    DECLARE v_id_lowongan INT;
    DECLARE v_jumlah_dibutuhkan INT;
    DECLARE v_jumlah_diterima INT;

    IF NEW.status_respon = 'Diterima' AND OLD.status_respon <> 'Diterima' THEN

        UPDATE lamaran SET status_terkini = 'Diterima'
        WHERE id_lamaran = NEW.id_lamaran;

        SELECT lam.id_lowongan, low.jumlah_dibutuhkan
        INTO v_id_lowongan, v_jumlah_dibutuhkan
        FROM lamaran lam
        JOIN lowongan low ON low.id_lowongan = lam.id_lowongan
        WHERE lam.id_lamaran = NEW.id_lamaran;

        SELECT COUNT(*) INTO v_jumlah_diterima
        FROM penawaran_kerja pk
        JOIN lamaran lam ON lam.id_lamaran = pk.id_lamaran
        WHERE lam.id_lowongan = v_id_lowongan
          AND pk.status_respon = 'Diterima';

        IF v_jumlah_diterima >= v_jumlah_dibutuhkan THEN
            UPDATE lowongan SET status = 'Terpenuhi' WHERE id_lowongan = v_id_lowongan;
        END IF;
    END IF;
END$$
DELIMITER ;
