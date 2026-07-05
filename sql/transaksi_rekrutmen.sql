-- =====================================================================
-- TRANSAKSI (BEGIN - COMMIT - ROLLBACK)
-- Sistem Informasi Rekrutmen & Seleksi Karyawan
-- Jalankan SETELAH ddl, dml, view, trigger, sp_rekrutmen.sql
-- Checklist wajib: minimal 1 skenario transaksi dengan BEGIN-COMMIT-ROLLBACK
--
-- Cara pakai: jalankan blok per blok (SEBELUM -> transaksi -> SESUDAH),
-- lalu screenshot tiap hasil SELECT untuk dilampirkan di laporan BAB 5
-- (Pengujian) sebagai bukti skenario sukses dan skenario gagal.
-- =====================================================================

USE rekrutmen;

-- #######################################################################
-- SKENARIO 1: TRANSAKSI SUKSES (COMMIT)
-- Konteks: id_lamaran=9 (Joko Santoso, melamar Digital Marketing
-- Specialist) sedang Proses di tahap Psikotes. HRD memutuskan
-- meloloskannya ke tahap Interview HRD. Prosedur sp_pindah_tahap
-- melakukan 3 operasi sekaligus (tutup tahap lama, buka tahap baru,
-- sinkronkan status lamaran) - transaksi memastikan ketiganya
-- berhasil bersama, tidak boleh cuma sebagian yang tersimpan.
-- #######################################################################

-- --- SEBELUM: tahap 2 (Psikotes) masih 'Proses', belum ada tahap 3 ---
SELECT id_lamaran_tahap, id_tahap, status, tanggal_selesai
FROM lamaran_tahap WHERE id_lamaran = 9;

SELECT status_terkini FROM lamaran WHERE id_lamaran = 9;

START TRANSACTION;

CALL sp_pindah_tahap(9, 3, '2026-04-20');

COMMIT;

-- --- SESUDAH COMMIT: tahap 2 harus 'Lolos', tahap 3 baru muncul 'Proses' ---
SELECT id_lamaran_tahap, id_tahap, status, tanggal_selesai
FROM lamaran_tahap WHERE id_lamaran = 9;

SELECT status_terkini FROM lamaran WHERE id_lamaran = 9;


-- #######################################################################
-- SKENARIO 2: TRANSAKSI GAGAL (ROLLBACK)
-- Konteks: HRD tidak sengaja mencoba menerbitkan penawaran kerja KEDUA
-- untuk id_lamaran=3 (Bayu Setiawan) yang sebenarnya sudah pernah
-- ditawari sebelumnya (lihat dml_rekrutmen.sql). Constraint UNIQUE
-- (uq_penawaran_lamaran) menolak percobaan ini. Karena itu transaksi
-- dibatalkan SELURUHNYA - termasuk UPDATE status yang sudah sempat
-- berjalan sebelumnya - supaya data tidak "nyangkut" setengah tersimpan.
-- #######################################################################

-- --- SEBELUM: status lamaran & jumlah penawaran yang sudah ada ---
SELECT status_terkini FROM lamaran WHERE id_lamaran = 3;

SELECT COUNT(*) AS jumlah_penawaran_lamaran_3
FROM penawaran_kerja WHERE id_lamaran = 3;

START TRANSACTION;

UPDATE lamaran
SET status_terkini = 'Proses - Offering (revisi)'
WHERE id_lamaran = 3;

-- Baris berikut akan GAGAL: id_lamaran=3 sudah memiliki penawaran,
-- melanggar UNIQUE KEY uq_penawaran_lamaran di tabel penawaran_kerja
INSERT INTO penawaran_kerja (id_lamaran, gaji_ditawarkan, tanggal_mulai, status_respon)
VALUES (3, 9200000.00, '2026-06-01', 'Menunggu');

-- Karena INSERT di atas gagal, batalkan seluruh transaksi (termasuk
-- UPDATE yang sudah sempat berjalan) agar data konsisten:
ROLLBACK;

-- --- SESUDAH ROLLBACK: harus KEMBALI seperti semula (UPDATE dibatalkan) ---
SELECT status_terkini FROM lamaran WHERE id_lamaran = 3;

SELECT COUNT(*) AS jumlah_penawaran_lamaran_3
FROM penawaran_kerja WHERE id_lamaran = 3;
