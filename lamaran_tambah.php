<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_role(['admin', 'hrd']);

$errors = [];
$data = [
    'nama' => '', 'email' => '', 'telepon' => '', 'tanggal_lahir' => '', 'alamat' => '',
    'id_lowongan' => '', 'id_sumber' => '',
];

$lowongan_list = $pdo->query("SELECT id_lowongan, posisi FROM lowongan WHERE status = 'Buka' ORDER BY posisi")->fetchAll();
$sumber_list   = $pdo->query("SELECT id_sumber, nama_sumber FROM sumber_rekrutmen ORDER BY nama_sumber")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama']          = trim($_POST['nama'] ?? '');
    $data['email']         = trim($_POST['email'] ?? '');
    $data['telepon']       = trim($_POST['telepon'] ?? '');
    $data['tanggal_lahir'] = $_POST['tanggal_lahir'] ?? '';
    $data['alamat']        = trim($_POST['alamat'] ?? '');
    $data['id_lowongan']   = (int) ($_POST['id_lowongan'] ?? 0);
    $id_sumber_input       = $_POST['id_sumber'] ?? '';
    $data['id_sumber']     = $id_sumber_input !== '' ? (int) $id_sumber_input : null;

    if ($data['nama'] === '')       $errors[] = 'Nama wajib diisi.';
    if ($data['email'] === '')      $errors[] = 'Email wajib diisi.';
    if ($data['id_lowongan'] === 0) $errors[] = 'Lowongan wajib dipilih.';

    // Upload CV (opsional)
    $cv_path = null;
    if (!empty($_FILES['cv']['name'])) {
        $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
            $errors[] = 'CV harus berformat PDF/DOC/DOCX.';
        } elseif ($_FILES['cv']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran CV maksimal 2MB.';
        } else {
            $upload_dir = __DIR__ . '/uploads/cv/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $filename = uniqid('cv_') . '.' . $ext;
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $upload_dir . $filename)) {
                $cv_path = 'uploads/cv/' . $filename;
            } else {
                $errors[] = 'Gagal mengunggah CV.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Cek apakah pelamar dengan email ini sudah pernah tercatat
            $stmt = $pdo->prepare("SELECT id_pelamar FROM pelamar WHERE email = ?");
            $stmt->execute([$data['email']]);
            $existing = $stmt->fetch();

            if ($existing) {
                $id_pelamar = $existing['id_pelamar'];
                if ($cv_path) {
                    $pdo->prepare("UPDATE pelamar SET cv_path = ? WHERE id_pelamar = ?")
                        ->execute([$cv_path, $id_pelamar]);
                }
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO pelamar (nama, email, telepon, tanggal_lahir, alamat, cv_path)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['nama'], $data['email'], $data['telepon'] ?: null,
                    $data['tanggal_lahir'] ?: null, $data['alamat'] ?: null, $cv_path,
                ]);
                $id_pelamar = $pdo->lastInsertId();
            }

            // Cegah pelamar yang sama melamar 2x ke lowongan yang sama
            $stmt = $pdo->prepare("SELECT id_lamaran FROM lamaran WHERE id_pelamar = ? AND id_lowongan = ?");
            $stmt->execute([$id_pelamar, $data['id_lowongan']]);
            if ($stmt->fetch()) {
                throw new Exception('Pelamar ini sudah pernah melamar ke lowongan yang sama.');
            }

            $stmt = $pdo->prepare("
                INSERT INTO lamaran (id_pelamar, id_lowongan, id_sumber, tanggal_lamar, status_terkini)
                VALUES (?, ?, ?, CURDATE(), 'Proses - Screening CV')
            ");
            $stmt->execute([$id_pelamar, $data['id_lowongan'], $data['id_sumber']]);
            $id_lamaran = $pdo->lastInsertId();

            // Buka tahap pertama otomatis: Screening CV (id_tahap = 1)
            $stmt = $pdo->prepare("
                INSERT INTO lamaran_tahap (id_lamaran, id_tahap, tanggal_masuk, status)
                VALUES (?, 1, CURDATE(), 'Proses')
            ");
            $stmt->execute([$id_lamaran]);

            $pdo->commit();

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lamaran baru berhasil disimpan.'];
            header('Location: lamaran.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<h3 class="mb-4">Tambah Lamaran Baru</h3>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width:700px;">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <h6 class="text-muted mb-3">Data Pelamar</h6>
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($data['telepon']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($data['tanggal_lahir']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">CV (PDF/DOC, maks 2MB)</label>
                    <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($data['alamat']) ?></textarea>
            </div>

            <hr>
            <h6 class="text-muted mb-3">Detail Lamaran</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lowongan yang Dilamar</label>
                    <select name="id_lowongan" class="form-select" required>
                        <option value="">-- Pilih Lowongan --</option>
                        <?php foreach ($lowongan_list as $low): ?>
                            <option value="<?= $low['id_lowongan'] ?>" <?= (string) $data['id_lowongan'] === (string) $low['id_lowongan'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($low['posisi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Hanya lowongan berstatus "Buka" yang muncul di sini.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sumber Rekrutmen</label>
                    <select name="id_sumber" class="form-select">
                        <option value="">-- Tidak diketahui --</option>
                        <?php foreach ($sumber_list as $src): ?>
                            <option value="<?= $src['id_sumber'] ?>" <?= (string) $data['id_sumber'] === (string) $src['id_sumber'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($src['nama_sumber']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Lamaran</button>
            <a href="lamaran.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
