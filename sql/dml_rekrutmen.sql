-- =====================================================================
-- DATA DUMMY (DML)
-- Sistem Informasi Rekrutmen & Seleksi Karyawan
-- Studi kasus: PT Nusantara Digital Kreasi (perusahaan fiktif)
-- Jalankan SETELAH ddl_rekrutmen.sql
-- =====================================================================

USE db_rekrutmen;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE penawaran_kerja;
TRUNCATE TABLE jadwal_wawancara;
TRUNCATE TABLE penilaian;
TRUNCATE TABLE kriteria_penilaian;
TRUNCATE TABLE lamaran_tahap;
TRUNCATE TABLE lamaran;
TRUNCATE TABLE pelamar;
TRUNCATE TABLE lowongan;
TRUNCATE TABLE tahap_seleksi;
TRUNCATE TABLE sumber_rekrutmen;
TRUNCATE TABLE users;
TRUNCATE TABLE departemen;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. DEPARTEMEN
-- ---------------------------------------------------------------------
INSERT INTO departemen (id_departemen, nama_departemen, keterangan) VALUES
(1, 'Teknologi Informasi', 'Pengembangan produk digital dan infrastruktur IT'),
(2, 'Pemasaran', 'Marketing, branding, dan pertumbuhan bisnis'),
(3, 'Keuangan', 'Akuntansi, keuangan, dan pengendalian anggaran'),
(4, 'Sumber Daya Manusia', 'Rekrutmen, pelatihan, dan administrasi karyawan');

-- ---------------------------------------------------------------------
-- 2. USERS (password dummy - ganti dengan hash asli saat implementasi)
-- ---------------------------------------------------------------------
INSERT INTO users (id_user, nama, email, password, role, is_active) VALUES
(1, 'Admin Sistem', 'admin@ptndk.co.id', '$2y$10$dummyhash0000000000000000000000000000000000', 'admin', TRUE),
(2, 'Rani Kusuma', 'rani.kusuma@ptndk.co.id', '$2y$10$dummyhash0000000000000000000000000000000000', 'hrd', TRUE),
(3, 'Dedi Prasetyo', 'dedi.prasetyo@ptndk.co.id', '$2y$10$dummyhash0000000000000000000000000000000000', 'hrd', TRUE),
(4, 'Bagus Wirawan', 'bagus.wirawan@ptndk.co.id', '$2y$10$dummyhash0000000000000000000000000000000000', 'pewawancara', TRUE),
(5, 'Siti Amalia', 'siti.amalia@ptndk.co.id', '$2y$10$dummyhash0000000000000000000000000000000000', 'pewawancara', TRUE),
(6, 'Fajar Nugroho', 'fajar.nugroho@ptndk.co.id', '$2y$10$dummyhash0000000000000000000000000000000000', 'pewawancara', TRUE);

-- ---------------------------------------------------------------------
-- 3. SUMBER_REKRUTMEN
-- ---------------------------------------------------------------------
INSERT INTO sumber_rekrutmen (id_sumber, nama_sumber) VALUES
(1, 'LinkedIn'),
(2, 'Jobstreet'),
(3, 'Referral Karyawan'),
(4, 'Kampus (Job Fair)'),
(5, 'Website Perusahaan');

-- ---------------------------------------------------------------------
-- 4. TAHAP_SELEKSI
-- ---------------------------------------------------------------------
INSERT INTO tahap_seleksi (id_tahap, nama_tahap, urutan, keterangan) VALUES
(1, 'Screening CV', 1, 'Seleksi administrasi berkas lamaran'),
(2, 'Psikotes', 2, 'Tes logika dan kepribadian'),
(3, 'Interview HRD', 3, 'Wawancara oleh tim HRD'),
(4, 'Interview User', 4, 'Wawancara oleh calon atasan/departemen terkait'),
(5, 'Offering', 5, 'Penerbitan dan negosiasi surat penawaran kerja');

-- ---------------------------------------------------------------------
-- 5. LOWONGAN
-- ---------------------------------------------------------------------
INSERT INTO lowongan (id_lowongan, id_departemen, posisi, kualifikasi, jumlah_dibutuhkan, tanggal_posting, batas_lamaran, status) VALUES
(1, 1, 'Backend Developer', 'Min. S1 Informatika, menguasai PHP/MySQL, pengalaman 1 tahun', 2, '2026-04-01', '2026-05-15', 'Buka'),
(2, 1, 'UI/UX Designer', 'Menguasai Figma, wajib melampirkan portfolio, pengalaman 1 tahun', 1, '2026-04-01', '2026-05-15', 'Buka'),
(3, 2, 'Digital Marketing Specialist', 'Menguasai SEO/SEM dan media sosial, S1 semua jurusan', 2, '2026-04-05', '2026-05-20', 'Buka'),
(4, 3, 'Staff Finance', 'S1 Akuntansi, menguasai Excel, teliti dan disiplin', 1, '2026-04-05', '2026-05-20', 'Ditutup'),
(5, 4, 'HR Recruiter', 'S1 Psikologi/Manajemen, pengalaman rekrutmen min. 1 tahun', 1, '2026-04-10', '2026-05-25', 'Buka'),
(6, 1, 'Data Analyst', 'S1 Statistika/Informatika, menguasai SQL dan Python', 1, '2026-04-10', '2026-05-25', 'Terpenuhi');

-- ---------------------------------------------------------------------
-- 6. PELAMAR
-- ---------------------------------------------------------------------
INSERT INTO pelamar (id_pelamar, nama, email, telepon, tanggal_lahir, alamat, cv_path) VALUES
(1, 'Ahmad Fauzi Rahman', 'ahmad.fauzi.r@gmail.com', '081234567801', '1998-03-12', 'Jember, Jawa Timur', 'uploads/cv/ahmad_fauzi.pdf'),
(2, 'Dewi Puspitasari', 'dewi.puspita@gmail.com', '081234567802', '1999-07-22', 'Surabaya, Jawa Timur', 'uploads/cv/dewi_puspita.pdf'),
(3, 'Bayu Setiawan', 'bayu.setiawan99@gmail.com', '081234567803', '1997-11-05', 'Malang, Jawa Timur', 'uploads/cv/bayu_setiawan.pdf'),
(4, 'Citra Amelia', 'citra.amelia@gmail.com', '081234567804', '2000-01-18', 'Jember, Jawa Timur', 'uploads/cv/citra_amelia.pdf'),
(5, 'Fajar Ramadhan', 'fajar.ramadhan21@gmail.com', '081234567805', '1998-09-30', 'Banyuwangi, Jawa Timur', 'uploads/cv/fajar_ramadhan.pdf'),
(6, 'Gita Lestari', 'gita.lestari@gmail.com', '081234567806', '1999-05-14', 'Jember, Jawa Timur', 'uploads/cv/gita_lestari.pdf'),
(7, 'Hendra Wijaya', 'hendra.wijaya88@gmail.com', '081234567807', '1996-12-25', 'Surabaya, Jawa Timur', 'uploads/cv/hendra_wijaya.pdf'),
(8, 'Indah Permatasari', 'indah.permata@gmail.com', '081234567808', '2000-04-09', 'Lumajang, Jawa Timur', 'uploads/cv/indah_permata.pdf'),
(9, 'Joko Santoso', 'joko.santoso77@gmail.com', '081234567809', '1997-08-17', 'Jember, Jawa Timur', 'uploads/cv/joko_santoso.pdf'),
(10, 'Kartika Sari', 'kartika.sari@gmail.com', '081234567810', '1999-02-28', 'Probolinggo, Jawa Timur', 'uploads/cv/kartika_sari.pdf'),
(11, 'Lukman Hakim', 'lukman.hakim@gmail.com', '081234567811', '1998-06-06', 'Jember, Jawa Timur', 'uploads/cv/lukman_hakim.pdf'),
(12, 'Maya Anggraini', 'maya.anggraini@gmail.com', '081234567812', '1999-10-11', 'Situbondo, Jawa Timur', 'uploads/cv/maya_anggraini.pdf'),
(13, 'Nanda Pratama', 'nanda.pratama@gmail.com', '081234567813', '1997-01-23', 'Bondowoso, Jawa Timur', 'uploads/cv/nanda_pratama.pdf'),
(14, 'Oktavia Rahayu', 'oktavia.rahayu@gmail.com', '081234567814', '2000-03-03', 'Jember, Jawa Timur', 'uploads/cv/oktavia_rahayu.pdf'),
(15, 'Panji Kusuma', 'panji.kusuma@gmail.com', '081234567815', '1998-11-19', 'Surabaya, Jawa Timur', 'uploads/cv/panji_kusuma.pdf'),
(16, 'Qonita Zahra', 'qonita.zahra@gmail.com', '081234567816', '1999-08-08', 'Jember, Jawa Timur', 'uploads/cv/qonita_zahra.pdf'),
(17, 'Rudi Hartono', 'rudi.hartono@gmail.com', '081234567817', '1996-05-27', 'Malang, Jawa Timur', 'uploads/cv/rudi_hartono.pdf'),
(18, 'Sinta Dewi', 'sinta.dewi@gmail.com', '081234567818', '2000-07-15', 'Jember, Jawa Timur', 'uploads/cv/sinta_dewi.pdf'),
(19, 'Taufik Ilham', 'taufik.ilham@gmail.com', '081234567819', '1997-09-09', 'Banyuwangi, Jawa Timur', 'uploads/cv/taufik_ilham.pdf'),
(20, 'Umi Kalsum', 'umi.kalsum@gmail.com', '081234567820', '1998-12-30', 'Jember, Jawa Timur', 'uploads/cv/umi_kalsum.pdf');

-- ---------------------------------------------------------------------
-- 7. LAMARAN
-- ---------------------------------------------------------------------
INSERT INTO lamaran (id_lamaran, id_pelamar, id_lowongan, id_sumber, tanggal_lamar, status_terkini) VALUES
(1, 1, 1, 1, '2026-04-05', 'Proses - Offering'),
(2, 2, 1, 2, '2026-04-06', 'Tidak Lolos - Psikotes'),
(3, 3, 1, 3, '2026-04-07', 'Diterima'),
(4, 7, 1, 1, '2026-04-10', 'Tidak Lolos - Screening CV'),
(5, 11, 1, 4, '2026-04-12', 'Proses - Interview HRD'),
(6, 17, 1, 2, '2026-04-14', 'Tidak Lolos - Screening CV'),
(7, 4, 2, 5, '2026-04-08', 'Proses - Offering'),
(8, 8, 2, 1, '2026-04-09', 'Tidak Lolos - Psikotes'),
(9, 14, 2, 3, '2026-04-11', 'Proses - Psikotes'),
(10, 5, 3, 2, '2026-04-12', 'Tidak Lolos - Screening CV'),
(11, 9, 3, 1, '2026-04-13', 'Diterima'),
(12, 15, 3, 4, '2026-04-15', 'Tidak Lolos - Interview HRD'),
(13, 19, 3, 5, '2026-04-16', 'Proses - Screening CV'),
(14, 6, 4, 3, '2026-04-09', 'Tidak Lolos - Psikotes'),
(15, 10, 4, 1, '2026-04-10', 'Proses - Offering'),
(16, 12, 5, 2, '2026-04-14', 'Tidak Lolos - Screening CV'),
(17, 16, 5, 4, '2026-04-16', 'Proses - Interview HRD'),
(18, 20, 5, 1, '2026-04-18', 'Tidak Lolos - Psikotes'),
(19, 3, 6, 3, '2026-04-15', 'Tidak Lolos - Screening CV'),
(20, 13, 6, 1, '2026-04-16', 'Diterima'),
(21, 18, 6, 5, '2026-04-17', 'Proses - Psikotes'),
(22, 7, 6, 2, '2026-04-19', 'Tidak Lolos - Screening CV');

-- ---------------------------------------------------------------------
-- 8. LAMARAN_TAHAP (histori pergerakan tiap lamaran antar tahap)
-- ---------------------------------------------------------------------
INSERT INTO lamaran_tahap (id_lamaran_tahap, id_lamaran, id_tahap, tanggal_masuk, tanggal_selesai, status) VALUES
(1, 1, 1, '2026-04-05', '2026-04-08', 'Lolos'),
(2, 1, 2, '2026-04-09', '2026-04-13', 'Lolos'),
(3, 1, 3, '2026-04-14', '2026-04-19', 'Lolos'),
(4, 1, 4, '2026-04-20', '2026-04-23', 'Lolos'),
(5, 1, 5, '2026-04-24', NULL, 'Proses'),

(6, 2, 1, '2026-04-06', '2026-04-09', 'Lolos'),
(7, 2, 2, '2026-04-10', '2026-04-14', 'Tidak Lolos'),

(8, 3, 1, '2026-04-07', '2026-04-10', 'Lolos'),
(9, 3, 2, '2026-04-11', '2026-04-15', 'Lolos'),
(10, 3, 3, '2026-04-16', '2026-04-21', 'Lolos'),
(11, 3, 4, '2026-04-22', '2026-04-25', 'Lolos'),
(12, 3, 5, '2026-04-26', '2026-04-28', 'Lolos'),

(13, 4, 1, '2026-04-10', '2026-04-13', 'Tidak Lolos'),

(14, 5, 1, '2026-04-12', '2026-04-15', 'Lolos'),
(15, 5, 2, '2026-04-16', '2026-04-20', 'Lolos'),
(16, 5, 3, '2026-04-21', NULL, 'Proses'),

(17, 6, 1, '2026-04-14', '2026-04-17', 'Tidak Lolos'),

(18, 7, 1, '2026-04-08', '2026-04-11', 'Lolos'),
(19, 7, 2, '2026-04-12', '2026-04-16', 'Lolos'),
(20, 7, 3, '2026-04-17', '2026-04-22', 'Lolos'),
(21, 7, 4, '2026-04-23', '2026-04-26', 'Lolos'),
(22, 7, 5, '2026-04-27', NULL, 'Proses'),

(23, 8, 1, '2026-04-09', '2026-04-12', 'Lolos'),
(24, 8, 2, '2026-04-13', '2026-04-17', 'Tidak Lolos'),

(25, 9, 1, '2026-04-11', '2026-04-14', 'Lolos'),
(26, 9, 2, '2026-04-15', NULL, 'Proses'),

(27, 10, 1, '2026-04-12', '2026-04-15', 'Tidak Lolos'),

(28, 11, 1, '2026-04-13', '2026-04-16', 'Lolos'),
(29, 11, 2, '2026-04-17', '2026-04-21', 'Lolos'),
(30, 11, 3, '2026-04-22', '2026-04-27', 'Lolos'),
(31, 11, 4, '2026-04-28', '2026-05-01', 'Lolos'),
(32, 11, 5, '2026-05-02', '2026-05-04', 'Lolos'),

(33, 12, 1, '2026-04-15', '2026-04-18', 'Lolos'),
(34, 12, 2, '2026-04-19', '2026-04-23', 'Lolos'),
(35, 12, 3, '2026-04-24', '2026-04-29', 'Tidak Lolos'),

(36, 13, 1, '2026-04-16', NULL, 'Proses'),

(37, 14, 1, '2026-04-09', '2026-04-12', 'Lolos'),
(38, 14, 2, '2026-04-13', '2026-04-17', 'Tidak Lolos'),

(39, 15, 1, '2026-04-10', '2026-04-13', 'Lolos'),
(40, 15, 2, '2026-04-14', '2026-04-18', 'Lolos'),
(41, 15, 3, '2026-04-19', '2026-04-24', 'Lolos'),
(42, 15, 4, '2026-04-25', '2026-04-28', 'Lolos'),
(43, 15, 5, '2026-04-29', NULL, 'Proses'),

(44, 16, 1, '2026-04-14', '2026-04-17', 'Tidak Lolos'),

(45, 17, 1, '2026-04-16', '2026-04-19', 'Lolos'),
(46, 17, 2, '2026-04-20', '2026-04-24', 'Lolos'),
(47, 17, 3, '2026-04-25', NULL, 'Proses'),

(48, 18, 1, '2026-04-18', '2026-04-21', 'Lolos'),
(49, 18, 2, '2026-04-22', '2026-04-26', 'Tidak Lolos'),

(50, 19, 1, '2026-04-15', NULL, 'Proses'),

(51, 20, 1, '2026-04-16', '2026-04-19', 'Lolos'),
(52, 20, 2, '2026-04-20', '2026-04-24', 'Lolos'),
(53, 20, 3, '2026-04-25', '2026-04-30', 'Lolos'),
(54, 20, 4, '2026-05-01', '2026-05-04', 'Lolos'),
(55, 20, 5, '2026-05-05', '2026-05-07', 'Lolos'),

(56, 21, 1, '2026-04-17', '2026-04-20', 'Lolos'),
(57, 21, 2, '2026-04-21', NULL, 'Proses'),

(58, 22, 1, '2026-04-19', '2026-04-22', 'Tidak Lolos');

-- ---------------------------------------------------------------------
-- 9. KRITERIA_PENILAIAN
-- ---------------------------------------------------------------------
INSERT INTO kriteria_penilaian (id_kriteria, id_tahap, nama_kriteria, bobot) VALUES
(1, 2, 'Skor Tes Logika', 50.00),
(2, 2, 'Skor Tes Kepribadian', 50.00),
(3, 3, 'Komunikasi', 40.00),
(4, 3, 'Motivasi & Sikap Kerja', 60.00),
(5, 4, 'Kompetensi Teknis', 70.00),
(6, 4, 'Kesesuaian Budaya Kerja', 30.00);

-- ---------------------------------------------------------------------
-- 10. PENILAIAN (hanya untuk tahap yang statusnya sudah final)
-- ---------------------------------------------------------------------
INSERT INTO penilaian (id_lamaran_tahap, id_kriteria, nilai, id_penilai, tanggal_nilai) VALUES
-- Tahap 2: Psikotes
(2, 1, 85.00, 2, '2026-04-13'), (2, 2, 88.00, 2, '2026-04-13'),
(7, 1, 55.00, 2, '2026-04-14'), (7, 2, 50.00, 2, '2026-04-14'),
(9, 1, 90.00, 2, '2026-04-15'), (9, 2, 92.00, 2, '2026-04-15'),
(15, 1, 78.00, 2, '2026-04-20'), (15, 2, 80.00, 2, '2026-04-20'),
(19, 1, 82.00, 2, '2026-04-16'), (19, 2, 85.00, 2, '2026-04-16'),
(24, 1, 48.00, 2, '2026-04-17'), (24, 2, 52.00, 2, '2026-04-17'),
(29, 1, 88.00, 2, '2026-04-21'), (29, 2, 90.00, 2, '2026-04-21'),
(34, 1, 76.00, 2, '2026-04-23'), (34, 2, 79.00, 2, '2026-04-23'),
(38, 1, 45.00, 2, '2026-04-17'), (38, 2, 49.00, 2, '2026-04-17'),
(40, 1, 84.00, 2, '2026-04-18'), (40, 2, 86.00, 2, '2026-04-18'),
(46, 1, 79.00, 2, '2026-04-24'), (46, 2, 81.00, 2, '2026-04-24'),
(49, 1, 58.00, 2, '2026-04-26'), (49, 2, 54.00, 2, '2026-04-26'),
(52, 1, 91.00, 2, '2026-04-24'), (52, 2, 93.00, 2, '2026-04-24'),
-- Tahap 3: Interview HRD
(3, 3, 80.00, 2, '2026-04-19'), (3, 4, 85.00, 2, '2026-04-19'),
(10, 3, 88.00, 2, '2026-04-21'), (10, 4, 90.00, 2, '2026-04-21'),
(20, 3, 82.00, 2, '2026-04-22'), (20, 4, 84.00, 2, '2026-04-22'),
(30, 3, 85.00, 2, '2026-04-27'), (30, 4, 87.00, 2, '2026-04-27'),
(35, 3, 55.00, 2, '2026-04-29'), (35, 4, 50.00, 2, '2026-04-29'),
(41, 3, 79.00, 2, '2026-04-24'), (41, 4, 83.00, 2, '2026-04-24'),
(53, 3, 90.00, 2, '2026-04-30'), (53, 4, 92.00, 2, '2026-04-30'),
-- Tahap 4: Interview User
(4, 5, 85.00, 4, '2026-04-23'), (4, 6, 88.00, 4, '2026-04-23'),
(11, 5, 90.00, 5, '2026-04-25'), (11, 6, 92.00, 5, '2026-04-25'),
(21, 5, 83.00, 4, '2026-04-26'), (21, 6, 86.00, 4, '2026-04-26'),
(31, 5, 87.00, 5, '2026-05-01'), (31, 6, 89.00, 5, '2026-05-01'),
(42, 5, 81.00, 6, '2026-04-28'), (42, 6, 85.00, 6, '2026-04-28'),
(54, 5, 92.00, 4, '2026-05-04'), (54, 6, 94.00, 4, '2026-05-04');

-- ---------------------------------------------------------------------
-- 11. JADWAL_WAWANCARA
-- ---------------------------------------------------------------------
INSERT INTO jadwal_wawancara (id_lamaran_tahap, id_user, waktu, lokasi, status) VALUES
(3, 2, '2026-04-14 10:00:00', 'Ruang Meeting HRD Lt.2', 'Selesai'),
(10, 2, '2026-04-16 10:00:00', 'Ruang Meeting HRD Lt.2', 'Selesai'),
(16, 2, '2026-04-21 13:00:00', 'Ruang Meeting HRD Lt.2', 'Terjadwal'),
(20, 2, '2026-04-17 10:00:00', 'Ruang Meeting HRD Lt.2', 'Selesai'),
(30, 2, '2026-04-22 10:00:00', 'Ruang Meeting HRD Lt.2', 'Selesai'),
(35, 2, '2026-04-24 14:00:00', 'Ruang Meeting HRD Lt.2', 'Selesai'),
(41, 2, '2026-04-19 10:00:00', 'Ruang Meeting HRD Lt.2', 'Selesai'),
(47, 2, '2026-04-25 13:00:00', 'Ruang Meeting HRD Lt.2', 'Terjadwal'),
(53, 2, '2026-04-25 10:00:00', 'Ruang Meeting HRD Lt.2', 'Selesai'),
(4, 4, '2026-04-20 09:00:00', 'Ruang Meeting IT Lt.3', 'Selesai'),
(11, 4, '2026-04-22 09:00:00', 'Ruang Meeting IT Lt.3', 'Selesai'),
(21, 4, '2026-04-23 09:00:00', 'Ruang Meeting IT Lt.3', 'Selesai'),
(31, 5, '2026-04-28 09:00:00', 'Online - Google Meet', 'Selesai'),
(42, 6, '2026-04-25 09:00:00', 'Ruang Meeting Finance Lt.1', 'Selesai'),
(54, 4, '2026-05-01 09:00:00', 'Ruang Meeting IT Lt.3', 'Selesai');

-- ---------------------------------------------------------------------
-- 12. PENAWARAN_KERJA
-- ---------------------------------------------------------------------
INSERT INTO penawaran_kerja (id_lamaran, gaji_ditawarkan, tanggal_mulai, tanggal_penawaran, status_respon) VALUES
(1, 8500000.00, '2026-05-15', '2026-04-24', 'Menunggu'),
(3, 8700000.00, '2026-05-10', '2026-04-26', 'Diterima'),
(7, 7200000.00, '2026-05-12', '2026-04-27', 'Menunggu'),
(11, 6500000.00, '2026-05-15', '2026-05-02', 'Diterima'),
(15, 6800000.00, '2026-05-15', '2026-04-29', 'Menunggu'),
(20, 9500000.00, '2026-05-20', '2026-05-05', 'Diterima');
