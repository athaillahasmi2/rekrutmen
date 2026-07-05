<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_role(['admin', 'hrd']);

$errors = [];

$kandidat = $pdo->query("
    SELECT lt.id_lamaran_tahap, p.nama AS nama_pelamar, low.posisi, ts.nama_tahap
    FROM lamaran_tahap lt
    JOIN tahap_seleksi ts ON ts.id_tahap = lt.id_tahap
    JOIN lamaran lam ON lam.id_lamaran = lt.id_lamaran
    JOIN pelamar p ON p.id_pelamar = lam.id_pelamar
    JOIN lowongan low ON low.id_lowongan = lam.id_lowongan
    WHERE lt.status = 'Proses' AND lt.id_tahap IN (3, 4)
      AND NOT EXISTS (SELECT 1 FROM jadwal_wawancara jw WHERE jw.id_lamaran_tahap = lt.id_lamaran_tahap)
    ORDER BY p.nama
")->fetchAll();

$pewawancara_list = $pdo->query("
    SELECT id_user, nama, role FROM users WHERE role IN ('hrd', 'pewawancara') AND is_active = 1 ORDER BY nama
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_lamaran_tahap = (int) ($_POST['id_lamaran_tahap'] ?? 0);
    $id_user          = (int) ($_POST['id_user'] ?? 0);
    $waktu            = $_POST['waktu'] ?? '';
    $lokasi           = trim($_POST['lokasi'] ?? '');

    if (!$id_lamaran_tahap) $errors[] = 'Pilih pelamar & tahap.';
    if (!$id_user)          $errors[] = 'Pilih pewawancara.';
    if (!$waktu)            $errors[] = 'Waktu wajib diisi.';

    if (empty($errors)) {
        try {
            $pdo->prepare("
                INSERT INTO jadwal_wawancara (id_lamaran_tahap, id_user, waktu, lokasi, status)
                VALUES (?, ?, ?, ?, 'Terjadwal')
            ")->execute([$id_lamaran_tahap, $id_user, $waktu, $lokasi]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Jadwal wawancara berhasil dibuat.'];
            header('Location: wawancara.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = ($e->getCode() === '23000')
                ? 'Tahap ini sudah memiliki jadwal wawancara.'
                : 'Gagal menyimpan jadwal.';
        }
    }
}

require_once 'includes/header.php';
?>

<h3 class="mb-4">Jadwalkan Wawancara</h3>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Pelamar &amp; Tahap</label>
                <select name="id_lamaran_tahap" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach ($kandidat as $k): ?>
                        <option value="<?= $k['id_lamaran_tahap'] ?>">
                            <?= htmlspecialchars($k['nama_pelamar'] . ' - ' . $k['posisi'] . ' (' . $k['nama_tahap'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($kandidat)): ?>
                    <div class="form-text text-warning">Belum ada pelamar yang butuh dijadwalkan wawancara (harus sedang di tahap Interview HRD/User).</div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Pewawancara</label>
                <select name="id_user" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach ($pewawancara_list as $u): ?>
                        <option value="<?= $u['id_user'] ?>"><?= htmlspecialchars($u['nama'] . ' (' . $u['role'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Waktu</label>
                <input type="datetime-local" name="waktu" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Lokasi / Link</label>
                <input type="text" name="lokasi" class="form-control" placeholder="Ruang Meeting Lt.2 / link Zoom">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="wawancara.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
