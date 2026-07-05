<?php
require_once 'config/database.php';
require_once 'includes/header.php';

require_role(['admin', 'hrd']);

$id_lowongan = isset($_GET['id']) ? (int) $_GET['id'] : null;
$is_edit = $id_lowongan !== null;

$data = [
    'id_departemen'     => '',
    'posisi'            => '',
    'kualifikasi'       => '',
    'jumlah_dibutuhkan' => 1,
    'tanggal_posting'   => date('Y-m-d'),
    'batas_lamaran'     => date('Y-m-d', strtotime('+30 days')),
    'status'            => 'Buka',
];
$errors = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM lowongan WHERE id_lowongan = ?");
    $stmt->execute([$id_lowongan]);
    $existing = $stmt->fetch();
    if (!$existing) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Lowongan tidak ditemukan.'];
        header('Location: lowongan.php');
        exit;
    }
    $data = $existing;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['id_departemen']     = $_POST['id_departemen'] ?? '';
    $data['posisi']            = trim($_POST['posisi'] ?? '');
    $data['kualifikasi']       = trim($_POST['kualifikasi'] ?? '');
    $data['jumlah_dibutuhkan'] = (int) ($_POST['jumlah_dibutuhkan'] ?? 1);
    $data['tanggal_posting']   = $_POST['tanggal_posting'] ?? '';
    $data['batas_lamaran']     = $_POST['batas_lamaran'] ?? '';
    $data['status']            = $_POST['status'] ?? 'Buka';

    if ($data['posisi'] === '')          $errors[] = 'Posisi wajib diisi.';
    if ($data['id_departemen'] === '')   $errors[] = 'Departemen wajib dipilih.';
    if ($data['jumlah_dibutuhkan'] < 1)  $errors[] = 'Jumlah dibutuhkan minimal 1.';
    if ($data['batas_lamaran'] < $data['tanggal_posting']) {
        $errors[] = 'Batas lamaran tidak boleh sebelum tanggal posting.';
    }

    if (empty($errors)) {
        try {
            if ($is_edit) {
                $stmt = $pdo->prepare("
                    UPDATE lowongan SET id_departemen=?, posisi=?, kualifikasi=?,
                        jumlah_dibutuhkan=?, tanggal_posting=?, batas_lamaran=?, status=?
                    WHERE id_lowongan=?
                ");
                $stmt->execute([
                    $data['id_departemen'], $data['posisi'], $data['kualifikasi'],
                    $data['jumlah_dibutuhkan'], $data['tanggal_posting'], $data['batas_lamaran'],
                    $data['status'], $id_lowongan,
                ]);
                $message = 'Lowongan berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO lowongan (id_departemen, posisi, kualifikasi, jumlah_dibutuhkan,
                        tanggal_posting, batas_lamaran, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['id_departemen'], $data['posisi'], $data['kualifikasi'],
                    $data['jumlah_dibutuhkan'], $data['tanggal_posting'], $data['batas_lamaran'],
                    $data['status'],
                ]);
                $message = 'Lowongan baru berhasil ditambahkan.';
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => $message];
            header('Location: lowongan.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

$departemen_list = $pdo->query("SELECT * FROM departemen ORDER BY nama_departemen")->fetchAll();
?>

<h3 class="mb-4"><?= $is_edit ? 'Edit Lowongan' : 'Tambah Lowongan Baru' ?></h3>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 700px;">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Posisi</label>
                <input type="text" name="posisi" class="form-control"
                       value="<?= htmlspecialchars($data['posisi']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Departemen</label>
                <select name="id_departemen" class="form-select" required>
                    <option value="">-- Pilih Departemen --</option>
                    <?php foreach ($departemen_list as $dep): ?>
                        <option value="<?= $dep['id_departemen'] ?>"
                            <?= (string) $data['id_departemen'] === (string) $dep['id_departemen'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dep['nama_departemen']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Kualifikasi</label>
                <textarea name="kualifikasi" class="form-control" rows="3"><?= htmlspecialchars($data['kualifikasi']) ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Dibutuhkan</label>
                    <input type="number" name="jumlah_dibutuhkan" class="form-control" min="1"
                           value="<?= htmlspecialchars($data['jumlah_dibutuhkan']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Posting</label>
                    <input type="date" name="tanggal_posting" class="form-control"
                           value="<?= htmlspecialchars($data['tanggal_posting']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Batas Lamaran</label>
                    <input type="date" name="batas_lamaran" class="form-control"
                           value="<?= htmlspecialchars($data['batas_lamaran']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (['Buka', 'Ditutup', 'Terpenuhi'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $data['status'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="lowongan.php" class="btn btn-outline-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
