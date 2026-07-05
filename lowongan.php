<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$user = current_user();
$can_edit = in_array($user['role'], ['admin', 'hrd'], true);

$lowongan = $pdo->query("
    SELECT low.*, dep.nama_departemen
    FROM lowongan low
    JOIN departemen dep ON dep.id_departemen = low.id_departemen
    ORDER BY low.id_lowongan DESC
")->fetchAll();

function badge_status_lowongan(string $status): string
{
    if ($status === 'Buka')      return 'success';
    if ($status === 'Ditutup')   return 'secondary';
    if ($status === 'Terpenuhi') return 'primary';
    return 'light';
}

// Pesan sukses/gagal dari redirect form/delete
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Daftar Lowongan</h3>
    <?php if ($can_edit): ?>
        <a href="lowongan_form.php" class="btn btn-primary">+ Tambah Lowongan</a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Posisi</th>
                    <th>Departemen</th>
                    <th class="text-center">Dibutuhkan</th>
                    <th>Tgl Posting</th>
                    <th>Batas Lamaran</th>
                    <th>Status</th>
                    <?php if ($can_edit): ?><th class="text-end">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowongan as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['posisi']) ?></td>
                    <td><?= htmlspecialchars($row['nama_departemen']) ?></td>
                    <td class="text-center"><?= (int) $row['jumlah_dibutuhkan'] ?></td>
                    <td><?= htmlspecialchars($row['tanggal_posting']) ?></td>
                    <td><?= htmlspecialchars($row['batas_lamaran']) ?></td>
                    <td>
                        <span class="badge bg-<?= badge_status_lowongan($row['status']) ?>">
                            <?= htmlspecialchars($row['status']) ?>
                        </span>
                    </td>
                    <?php if ($can_edit): ?>
                    <td class="text-end">
                        <a href="lowongan_form.php?id=<?= $row['id_lowongan'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="lowongan_delete.php" method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin hapus lowongan &quot;<?= htmlspecialchars($row['posisi']) ?>&quot;?');">
                            <input type="hidden" name="id_lowongan" value="<?= $row['id_lowongan'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($lowongan)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada lowongan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
