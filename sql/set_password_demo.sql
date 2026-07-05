-- =====================================================================
-- SET PASSWORD DEMO (WAJIB dijalankan sebelum bisa login ke aplikasi)
-- Password dummy di dml_rekrutmen.sql cuma teks placeholder, bukan hash
-- asli, jadi tidak akan pernah cocok saat login. Jalankan ini sekali
-- supaya semua akun demo bisa dipakai login.
--
-- Password untuk SEMUA akun demo: password123
-- =====================================================================

USE rekrutmen;

UPDATE users
SET password = '$2y$10$X.HBUykACnD96w6GAuBxPO0knQ4Ga/d9ljLRIjbpXa5ypnXtJYV4y'
WHERE id_user IN (1,2,3,4,5,6);
