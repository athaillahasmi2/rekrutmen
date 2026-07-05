-- =====================================================================
-- VIEW
-- Sistem Informasi Rekrutmen & Seleksi Karyawan
-- Jalankan SETELAH ddl_rekrutmen.sql dan dml_rekrutmen.sql
-- Checklist wajib: minimal 3 VIEW yang aktif digunakan aplikasi/laporan
-- =====================================================================

USE db_rekrutmen;

-- -----------------------------------------------------------------------
-- VIEW 1: view_pipeline_rekrutmen
-- Kegunaan: dashboard utama HRD - ringkasan tiap lowongan (berapa pelamar,
-- berapa masih proses, diterima, tidak lolos, dan offering yang terkirim)
-- -----------------------------------------------------------------------
CREATE OR REPLACE VIEW view_pipeline_rekrutmen AS
SELECT
    low.id_lowongan,
    low.posisi,
    dep.nama_departemen,
    low.status                                                     AS status_lowongan,
    low.jumlah_dibutuhkan,
    COUNT(DISTINCT lam.id_lamaran)                                 AS total_pelamar,
    SUM(CASE WHEN lam.status_terkini LIKE 'Proses%' THEN 1 ELSE 0 END)      AS masih_proses,
    SUM(CASE WHEN lam.status_terkini = 'Diterima' THEN 1 ELSE 0 END)       AS diterima,
    SUM(CASE WHEN lam.status_terkini LIKE 'Tidak Lolos%' THEN 1 ELSE 0 END) AS tidak_lolos,
    COUNT(DISTINCT pk.id_penawaran)                                AS offering_terkirim
FROM lowongan low
JOIN departemen dep      ON dep.id_departemen = low.id_departemen
LEFT JOIN lamaran lam    ON lam.id_lowongan = low.id_lowongan
LEFT JOIN penawaran_kerja pk ON pk.id_lamaran = lam.id_lamaran
GROUP BY low.id_lowongan, low.posisi, dep.nama_departemen, low.status, low.jumlah_dibutuhkan;

-- -----------------------------------------------------------------------
-- VIEW 2: view_jadwal_wawancara
-- Kegunaan: menampilkan seluruh jadwal wawancara lengkap dengan nama
-- pelamar, posisi yang dilamar, tahap, dan pewawancara yang ditugaskan
-- -----------------------------------------------------------------------
CREATE OR REPLACE VIEW view_jadwal_wawancara AS
SELECT
    jw.id_jadwal,
    p.nama          AS nama_pelamar,
    low.posisi,
    ts.nama_tahap,
    u.nama          AS nama_pewawancara,
    jw.waktu,
    jw.lokasi,
    jw.status       AS status_jadwal
FROM jadwal_wawancara jw
JOIN lamaran_tahap lt ON lt.id_lamaran_tahap = jw.id_lamaran_tahap
JOIN lamaran lam      ON lam.id_lamaran = lt.id_lamaran
JOIN pelamar p        ON p.id_pelamar = lam.id_pelamar
JOIN lowongan low     ON low.id_lowongan = lam.id_lowongan
JOIN tahap_seleksi ts ON ts.id_tahap = lt.id_tahap
JOIN users u          ON u.id_user = jw.id_user;

-- -----------------------------------------------------------------------
-- VIEW 3: view_skor_tahap
-- Kegunaan: skor akhir tertimbang (nilai x bobot) tiap pelamar per tahap
-- yang sudah dinilai - dipakai untuk ranking dan keputusan lolos/tidak
-- -----------------------------------------------------------------------
CREATE OR REPLACE VIEW view_skor_tahap AS
SELECT
    lt.id_lamaran_tahap,
    lam.id_lamaran,
    p.nama       AS nama_pelamar,
    low.posisi,
    ts.nama_tahap,
    lt.status    AS status_tahap,
    ROUND(SUM(pn.nilai * kp.bobot) / SUM(kp.bobot), 2) AS skor_akhir
FROM lamaran_tahap lt
JOIN lamaran lam       ON lam.id_lamaran = lt.id_lamaran
JOIN pelamar p         ON p.id_pelamar = lam.id_pelamar
JOIN lowongan low      ON low.id_lowongan = lam.id_lowongan
JOIN tahap_seleksi ts  ON ts.id_tahap = lt.id_tahap
JOIN penilaian pn      ON pn.id_lamaran_tahap = lt.id_lamaran_tahap
JOIN kriteria_penilaian kp ON kp.id_kriteria = pn.id_kriteria
GROUP BY lt.id_lamaran_tahap, lam.id_lamaran, p.nama, low.posisi, ts.nama_tahap, lt.status;

-- -----------------------------------------------------------------------
-- VIEW 4: view_funnel_rekrutmen  (tambahan - melebihi minimal 3)
-- Kegunaan: funnel jumlah pelamar yang masuk & lolos di tiap tahap,
-- per lowongan - dipakai untuk grafik funnel di dashboard
-- -----------------------------------------------------------------------
CREATE OR REPLACE VIEW view_funnel_rekrutmen AS
SELECT
    low.id_lowongan,
    low.posisi,
    ts.id_tahap,
    ts.nama_tahap,
    ts.urutan,
    COUNT(DISTINCT lt.id_lamaran)                          AS jumlah_masuk_tahap,
    SUM(CASE WHEN lt.status = 'Lolos' THEN 1 ELSE 0 END)   AS jumlah_lolos
FROM lowongan low
JOIN lamaran lam        ON lam.id_lowongan = low.id_lowongan
JOIN lamaran_tahap lt   ON lt.id_lamaran = lam.id_lamaran
JOIN tahap_seleksi ts   ON ts.id_tahap = lt.id_tahap
GROUP BY low.id_lowongan, low.posisi, ts.id_tahap, ts.nama_tahap, ts.urutan
ORDER BY low.id_lowongan, ts.urutan;
