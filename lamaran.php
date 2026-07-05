<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_login();

$user       = current_user();
$can_manage = in_array($user['role'], ['admin', 'hrd'], true);

$filter_lowongan = isset($_GET['id_lowongan']) ? (int) $_GET['id_lowongan'] : 0;

$sql = "
    SELECT lam.*, p.nama AS nama_pelamar, p.email,
           low.posisi, sr.nama_sumber
    FROM lamaran lam
    JOIN pelamar p ON p.id_pelamar = lam.id_pelamar
    JOIN lowongan low ON low.id_lowongan = lam.id_lowongan
    LEFT JOIN sumber_rekrutmen sr ON sr.id_sumber = lam.id_sumber
";
$params = [];
if ($filter_lowongan > 0) {
    $sql .= " WHERE lam.id_lowongan = ? ";
    $params[] = $filter_lowongan;
}
$sql .= " ORDER BY lam.tanggal_lamar DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_lamaran = $stmt->fetchAll();

$lowongan_list = $pdo->query("SELECT id_lowongan, posisi FROM lowongan ORDER BY posisi")->fetchAll();

function badge_status_lamaran(string $status): string
{
    if ($status === 'Diterima') return 'success';
    if (str_starts_with($status, 'Tidak Lolos')) return 'danger';
    if (str_starts_with($status, 'Proses')) return 'warning';
    return 'secondary';
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Daftar Lamaran</h3>
    <?php if ($can_manage): ?>
        <a href="lamaran_tambah.php" class="btn btn-primary">+ Tambah Lamaran</a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<form method="GET" class="mb-3" style="max-width:320px;">
    <select name="id_lowongan" class="form-select" onchange="this.form.submit()">
        <option value="0">-- Semua Lowongan --</option>
        <?php foreach ($lowongan_list as $low): ?>
            <option value="<?= $low['id_lowongan'] ?>" <?= $filter_lowongan === (int) $low['id_lowongan'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($low['posisi']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama Pelamar</th>
                    <th>Posisi Dilamar</th>
                    <th>Sumber</th>
                    <th>Tanggal Lamar</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar_lamaran as $row): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($row['nama_pelamar']) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($row['posisi']) ?></td>
                    <td><?= htmlspecialchars($row['nama_sumber'] ?? '-') ?></td>
                    <td><?= date('d M Y', strtotime($row['tanggal_lamar'])) ?></td>
                    <td><span class="badge bg-<?= badge_status_lamaran($row['status_terkini']) ?>"><?= htmlspecialchars($row['status_terkini']) ?></span></td>
                    <td><a href="lamaran_detail.php?id=<?= $row['id_lamaran'] ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($daftar_lamaran)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada lamaran.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
