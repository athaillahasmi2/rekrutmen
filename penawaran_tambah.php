<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_role(['admin', 'hrd']);

$preselect_id = (int) ($_GET['id_lamaran'] ?? 0);
$errors = [];

$kandidat = $pdo->query("
    SELECT lam.id_lamaran, p.nama AS nama_pelamar, low.posisi
    FROM lamaran lam
    JOIN lamaran_tahap lt ON lt.id_lamaran = lam.id_lamaran AND lt.id_tahap = 5 AND lt.status = 'Proses'
    JOIN pelamar p ON p.id_pelamar = lam.id_pelamar
    JOIN lowongan low ON low.id_lowongan = lam.id_lowongan
    WHERE NOT EXISTS (SELECT 1 FROM penawaran_kerja pk WHERE pk.id_lamaran = lam.id_lamaran)
    ORDER BY p.nama
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_lamaran    = (int) ($_POST['id_lamaran'] ?? 0);
    $gaji          = (float) ($_POST['gaji_ditawarkan'] ?? 0);
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';

    if (!$id_lamaran)        $errors[] = 'Pilih pelamar.';
    if ($gaji <= 0)          $errors[] = 'Gaji harus lebih dari 0.';
    if (!$tanggal_mulai)     $errors[] = 'Tanggal mulai wajib diisi.';

    // Validasi ulang di server: jangan hanya percaya pilihan dropdown di sisi
    // client. Pastikan pelamar ini BENAR-BENAR sedang di tahap Offering
    // (id_tahap=5, status Proses) dan belum punya penawaran sebelumnya.
    if (empty($errors)) {
        $cek = $pdo->prepare("
            SELECT lam.id_lamaran
            FROM lamaran lam
            JOIN lamaran_tahap lt ON lt.id_lamaran = lam.id_lamaran AND lt.id_tahap = 5 AND lt.status = 'Proses'
            WHERE lam.id_lamaran = ?
              AND NOT EXISTS (SELECT 1 FROM penawaran_kerja pk WHERE pk.id_lamaran = lam.id_lamaran)
        ");
        $cek->execute([$id_lamaran]);
        if (!$cek->fetch()) {
            $errors[] = 'Pelamar ini tidak sedang berada di tahap Offering, atau sudah memiliki penawaran.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->prepare("
                INSERT INTO penawaran_kerja (id_lamaran, gaji_ditawarkan, tanggal_mulai, status_respon)
                VALUES (?, ?, ?, 'Menunggu')
            ")->execute([$id_lamaran, $gaji, $tanggal_mulai]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Penawaran kerja berhasil dibuat.'];
            header('Location: penawaran.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = ($e->getCode() === '23000')
                ? 'Pelamar ini sudah memiliki penawaran sebelumnya.'
                : 'Gagal menyimpan penawaran.';
        }
    }
}

require_once 'includes/header.php';
?>

<h3 class="mb-4">Buat Penawaran Kerja</h3>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Pelamar</label>
                <select name="id_lamaran" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach ($kandidat as $k): ?>
                        <option value="<?= $k['id_lamaran'] ?>" <?= $preselect_id === (int) $k['id_lamaran'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_pelamar'] . ' - ' . $k['posisi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($kandidat)): ?>
                    <div class="form-text text-warning">Tidak ada pelamar yang siap ditawari (harus berada di tahap Offering dan belum punya penawaran).</div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Gaji Ditawarkan (Rp)</label>
                <input type="number" name="gaji_ditawarkan" class="form-control" step="100000" min="0" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tanggal Mulai Bergabung</label>
                <input type="date" name="tanggal_mulai" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Penawaran</button>
            <a href="penawaran.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
