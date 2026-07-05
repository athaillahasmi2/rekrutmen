<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_role(['admin', 'hrd']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lowongan.php');
    exit;
}

$id = (int) ($_POST['id_lowongan'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM lowongan WHERE id_lowongan = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lowongan berhasil dihapus.'];
} catch (PDOException $e) {
    // Kode 23000 = integrity constraint violation (FK RESTRICT di tabel lamaran)
    if ($e->getCode() === '23000') {
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Lowongan ini tidak bisa dihapus karena sudah memiliki data pelamar. '
                       . 'Gunakan tombol Edit lalu ubah status menjadi "Ditutup" sebagai gantinya.',
        ];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Gagal menghapus lowongan.'];
    }
}

header('Location: lowongan.php');
exit;
