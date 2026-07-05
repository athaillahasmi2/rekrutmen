<?php
/**
 * Koneksi Database - Sistem Informasi Rekrutmen & Seleksi Karyawan
 *
 * Otomatis pakai environment variable dari Railway kalau tersedia
 * (MYSQLHOST, MYSQLPORT, dst - diisi otomatis oleh Railway saat deploy).
 * Kalau tidak ada (misal saat jalan di XAMPP lokal), pakai default lokal.
 */

$host    = getenv('MYSQLHOST') ?: 'localhost';
$port    = getenv('MYSQLPORT') ?: '3306';
$dbname  = getenv('MYSQLDATABASE') ?: 'rekrutmen';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $db_user,
        $db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

