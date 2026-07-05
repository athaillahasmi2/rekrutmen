# Sistem Informasi Rekrutmen & Seleksi Karyawan

Proyek Akhir Mata Kuliah Basis Data — Program Studi Sistem Informasi, Universitas Muhammadiyah Jember.

Studi kasus: PT Nusantara Digital Kreasi (perusahaan fiktif).

## Tech Stack

- **Backend:** PHP Native (PDO)
- **Database:** MySQL 8+ / MariaDB
- **Frontend:** Bootstrap 5

## Fitur Utama

- Autentikasi & manajemen akun (Admin, HRD, Pewawancara)
- CRUD Lowongan Kerja
- Penerimaan Lamaran (dengan upload CV)
- Pipeline Seleksi bertahap (Screening CV → Psikotes → Interview HRD → Interview User → Offering)
- Input Penilaian per kriteria dengan bobot, dan evaluasi otomatis via trigger
- Penjadwalan Wawancara
- Penerbitan Penawaran Kerja (offering letter)
- Dashboard ringkasan pipeline rekrutmen

## Objek Database

- 12 tabel (ternormalisasi 3NF)
- 4 VIEW
- 3 TRIGGER
- 3 Stored Procedure
- 2 skenario transaksi (COMMIT & ROLLBACK)

Lihat folder `sql/` untuk script lengkap (`ddl_rekrutmen.sql`, `dml_rekrutmen.sql`, `view_rekrutmen.sql`, `trigger_rekrutmen.sql`, `sp_rekrutmen.sql`, `transaksi_rekrutmen.sql`).

## Instalasi Lokal (XAMPP)

1. Clone/download repository ini ke folder `htdocs` XAMPP kamu
2. Buat database `rekrutmen` di phpMyAdmin
3. Import script SQL secara berurutan lewat tab Import:
   - `ddl_rekrutmen.sql`
   - `dml_rekrutmen.sql`
   - `view_rekrutmen.sql`
   - `trigger_rekrutmen.sql`
   - `sp_rekrutmen.sql`
   - `set_password_demo.sql`
4. Jalankan Apache & MySQL di XAMPP Control Panel
5. Buka `http://localhost/rekrutmen/`

### Akun Demo (password sama untuk semua: `password123`)

| Email | Role |
|---|---|
| admin@ptndk.co.id | Admin |
| rani.kusuma@ptndk.co.id | HRD |
| bagus.wirawan@ptndk.co.id | Pewawancara |

## Akses Online

*(akan ditambahkan setelah deployment)*

## Struktur Database

Lihat ER Diagram dan kamus data lengkap di folder `docs/` (Lampiran laporan).
