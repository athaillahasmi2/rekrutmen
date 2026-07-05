<?php
/**
 * Helper autentikasi & session
 * Semua halaman yang butuh login harus require_once file ini.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Tolak akses jika belum login, redirect ke halaman login */
function require_login(): void
{
    if (!isset($_SESSION['id_user'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Tolak akses jika role user tidak termasuk yang diizinkan.
 * Panggil SETELAH require_login(), atau langsung - fungsi ini otomatis
 * memanggil require_login() dulu.
 * Contoh: require_role(['admin', 'hrd']);
 */
function require_role(array $allowed_roles): void
{
    require_login();
    if (!in_array($_SESSION['role'], $allowed_roles, true)) {
        http_response_code(403);
        echo '<div style="font-family:sans-serif;max-width:500px;margin:80px auto;text-align:center;">
                <h3>Akses Ditolak</h3>
                <p>Role kamu (' . htmlspecialchars($_SESSION['role']) . ') tidak memiliki izin untuk membuka halaman ini.</p>
                <a href="index.php">&larr; Kembali ke Dashboard</a>
              </div>';
        exit;
    }
}

/** Ambil data user yang sedang login */
function current_user(): array
{
    return [
        'id_user' => $_SESSION['id_user'] ?? null,
        'nama'    => $_SESSION['nama'] ?? null,
        'role'    => $_SESSION['role'] ?? null,
    ];
}
