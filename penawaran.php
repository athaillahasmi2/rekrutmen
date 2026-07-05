<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_login();

$user       = current_user();
$can_manage = in_array($user['role'], ['admin', 'hrd'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!$can_manage) { http_response_code(403); die('Akses ditolak.'); }
    $id     = (int) $_POST['id_penawaran'];
    $status = $_POST['status_baru'];
    if (in_array($status, ['Menunggu', 'Diterima', 'Ditolak'], true)) {
        $pdo->prepare("UPDATE penawaran_kerja SET status_respon = ? WHERE id_penawaran = ?")
            ->execute([$status, $id]);
    }
    header('Location: penawaran.php');
    exit;
}

$daftar = $pdo->query("
    SELECT pk.*, p.nama AS nama_pelamar, low.posisi
    FROM penawaran_kerja pk
    JOIN lamaran lam ON lam.id_lamaran = pk.id_lamaran
    JOIN pelamar p ON p.id_pelamar = lam.id_pelamar
    JOIN lowongan low ON low.id_lowongan = lam.id_lowongan
    ORDER BY pk.tanggal_penawaran DESC
")->fetchAll();

function badge_status_penawaran(string $s): string
{
    if ($s === 'Diterima') return 'success';
    if ($s === 'Ditolak')  return 'danger';
    return 'warning';
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Penawaran Kerja</h3>
    <?php if ($can_manage): ?>
        <a href="penawaran_tambah.php" class="btn btn-primary">+ Buat Penawaran</a>
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
                    <th>Pelamar</th><th>Posisi</th><th>Gaji Ditawarkan</th>
                    <th>Tgl Mulai</th><th>Status</th>
                    <?php if ($can_manage): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['nama_pelamar']) ?></td>
                    <td><?= htmlspecialchars($r['posisi']) ?></td>
                    <td>Rp <?= number_format((float) $r['gaji_ditawarkan'], 0, ',', '.') ?></td>
                    <td><?= date('d M Y', strtotime($r['tanggal_mulai'])) ?></td>
                    <td><span class="badge bg-<?= badge_status_penawaran($r['status_respon']) ?>"><?= htmlspecialchars($r['status_respon']) ?></span></td>
                    <?php if ($can_manage): ?>
                    <td class="text-nowrap">
                        <?php if ($r['status_respon'] === 'Menunggu'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Tandai penawaran ini DITERIMA pelamar?');">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="id_penawaran" value="<?= $r['id_penawaran'] ?>">
                                <input type="hidden" name="status_baru" value="Diterima">
                                <button class="btn btn-sm btn-outline-success">Diterima</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="id_penawaran" value="<?= $r['id_penawaran'] ?>">
                                <input type="hidden" name="status_baru" value="Ditolak">
                                <button class="btn btn-sm btn-outline-danger">Ditolak</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($daftar)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penawaran kerja.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
