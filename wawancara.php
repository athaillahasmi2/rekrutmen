<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_login();

$user       = current_user();
$can_manage = in_array($user['role'], ['admin', 'hrd'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!$can_manage) { http_response_code(403); die('Akses ditolak.'); }
    $id_jadwal   = (int) $_POST['id_jadwal'];
    $status_baru = $_POST['status_baru'];
    if (in_array($status_baru, ['Terjadwal', 'Selesai', 'Dibatalkan'], true)) {
        $pdo->prepare("UPDATE jadwal_wawancara SET status = ? WHERE id_jadwal = ?")
            ->execute([$status_baru, $id_jadwal]);
    }
    header('Location: wawancara.php');
    exit;
}

$daftar = $pdo->query("SELECT * FROM view_jadwal_wawancara ORDER BY waktu DESC")->fetchAll();

function badge_status_jadwal(string $s): string
{
    if ($s === 'Selesai')     return 'success';
    if ($s === 'Dibatalkan')  return 'secondary';
    return 'warning';
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Jadwal Wawancara</h3>
    <?php if ($can_manage): ?>
        <a href="wawancara_tambah.php" class="btn btn-primary">+ Jadwalkan Wawancara</a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Pelamar</th><th>Posisi</th><th>Tahap</th><th>Pewawancara</th>
                    <th>Waktu</th><th>Lokasi</th><th>Status</th>
                    <?php if ($can_manage): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nama_pelamar']) ?></td>
                    <td><?= htmlspecialchars($r['posisi']) ?></td>
                    <td><?= htmlspecialchars($r['nama_tahap']) ?></td>
                    <td><?= htmlspecialchars($r['nama_pewawancara']) ?></td>
                    <td><?= date('d M Y H:i', strtotime($r['waktu'])) ?></td>
                    <td><?= htmlspecialchars($r['lokasi'] ?: '-') ?></td>
                    <td><span class="badge bg-<?= badge_status_jadwal($r['status_jadwal']) ?>"><?= htmlspecialchars($r['status_jadwal']) ?></span></td>
                    <?php if ($can_manage): ?>
                    <td class="text-nowrap">
                        <?php if ($r['status_jadwal'] === 'Terjadwal'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="id_jadwal" value="<?= $r['id_jadwal'] ?>">
                                <input type="hidden" name="status_baru" value="Selesai">
                                <button class="btn btn-sm btn-outline-success">Selesai</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="id_jadwal" value="<?= $r['id_jadwal'] ?>">
                                <input type="hidden" name="status_baru" value="Dibatalkan">
                                <button class="btn btn-sm btn-outline-danger">Batal</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($daftar)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada jadwal wawancara.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
