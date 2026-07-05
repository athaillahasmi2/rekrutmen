<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$pipeline = $pdo->query("SELECT * FROM view_pipeline_rekrutmen ORDER BY id_lowongan")->fetchAll();

$total_lowongan_buka = 0;
$total_pelamar       = 0;
$total_diterima      = 0;

foreach ($pipeline as $row) {
    if ($row['status_lowongan'] === 'Buka') {
        $total_lowongan_buka++;
    }
    $total_pelamar  += (int) $row['total_pelamar'];
    $total_diterima += (int) $row['diterima'];
}

function badge_status(string $status): string
{
    if ($status === 'Buka')      return 'success';
    if ($status === 'Ditutup')   return 'secondary';
    if ($status === 'Terpenuhi') return 'primary';
    return 'light';
}
?>

<h3 class="mb-4">Dashboard Rekrutmen</h3>

<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card text-bg-primary h-100">
            <div class="card-body">
                <h6 class="card-title">Lowongan Dibuka</h6>
                <h2 class="mb-0"><?= $total_lowongan_buka ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-info h-100">
            <div class="card-body">
                <h6 class="card-title">Total Pelamar</h6>
                <h2 class="mb-0"><?= $total_pelamar ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success h-100">
            <div class="card-body">
                <h6 class="card-title">Pelamar Diterima</h6>
                <h2 class="mb-0"><?= $total_diterima ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Ringkasan Pipeline per Lowongan</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Posisi</th>
                    <th>Departemen</th>
                    <th>Status</th>
                    <th class="text-center">Total Pelamar</th>
                    <th class="text-center">Proses</th>
                    <th class="text-center">Diterima</th>
                    <th class="text-center">Tidak Lolos</th>
                    <th class="text-center">Offering Terkirim</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pipeline as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['posisi']) ?></td>
                    <td><?= htmlspecialchars($row['nama_departemen']) ?></td>
                    <td>
                        <span class="badge bg-<?= badge_status($row['status_lowongan']) ?>">
                            <?= htmlspecialchars($row['status_lowongan']) ?>
                        </span>
                    </td>
                    <td class="text-center"><?= $row['total_pelamar'] ?></td>
                    <td class="text-center"><?= $row['masih_proses'] ?></td>
                    <td class="text-center"><?= $row['diterima'] ?></td>
                    <td class="text-center"><?= $row['tidak_lolos'] ?></td>
                    <td class="text-center"><?= $row['offering_terkirim'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
