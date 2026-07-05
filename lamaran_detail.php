<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_login();

$user       = current_user();
$can_manage = in_array($user['role'], ['admin', 'hrd'], true);

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT lam.*, p.nama AS nama_pelamar, p.email, p.telepon, p.alamat, p.cv_path,
           low.posisi, dep.nama_departemen, sr.nama_sumber
    FROM lamaran lam
    JOIN pelamar p ON p.id_pelamar = lam.id_pelamar
    JOIN lowongan low ON low.id_lowongan = lam.id_lowongan
    JOIN departemen dep ON dep.id_departemen = low.id_departemen
    LEFT JOIN sumber_rekrutmen sr ON sr.id_sumber = lam.id_sumber
    WHERE lam.id_lamaran = ?
");
$stmt->execute([$id]);
$lamaran = $stmt->fetch();

if (!$lamaran) {
    header('Location: lamaran.php');
    exit;
}

// Cari tahap yang sedang aktif (status masih 'Proses')
function ambil_tahap_aktif(PDO $pdo, int $id_lamaran): ?array
{
    $stmt = $pdo->prepare("
        SELECT lt.*, ts.nama_tahap, ts.urutan
        FROM lamaran_tahap lt
        JOIN tahap_seleksi ts ON ts.id_tahap = lt.id_tahap
        WHERE lt.id_lamaran = ? AND lt.status = 'Proses'
        ORDER BY ts.urutan DESC LIMIT 1
    ");
    $stmt->execute([$id_lamaran]);
    return $stmt->fetch() ?: null;
}

$tahap_aktif = ambil_tahap_aktif($pdo, $id);

// -------------------- Proses aksi (POST) --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tahap_aktif) {
    $action = $_POST['action'] ?? '';

    if ($action === 'lolos_tahap' && $can_manage) {
        $stmt = $pdo->prepare("SELECT id_tahap FROM tahap_seleksi WHERE urutan = ?");
        $stmt->execute([$tahap_aktif['urutan'] + 1]);
        $next = $stmt->fetch();

        if ($next) {
            $pdo->prepare("CALL sp_pindah_tahap(?, ?, CURDATE())")
                ->execute([$id, $next['id_tahap']]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pelamar dilanjutkan ke tahap ' . $next['id_tahap'] . '.'];
        }
        header("Location: lamaran_detail.php?id=$id");
        exit;
    }

    if ($action === 'tolak_tahap' && $can_manage) {
        $pdo->prepare("UPDATE lamaran_tahap SET status = 'Tidak Lolos', tanggal_selesai = CURDATE() WHERE id_lamaran_tahap = ?")
            ->execute([$tahap_aktif['id_lamaran_tahap']]);
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Pelamar ditandai tidak lolos di tahap ' . $tahap_aktif['nama_tahap'] . '.'];
        header("Location: lamaran_detail.php?id=$id");
        exit;
    }

    if ($action === 'input_nilai') {
        $stmt = $pdo->prepare("SELECT id_kriteria FROM kriteria_penilaian WHERE id_tahap = ?");
        $stmt->execute([$tahap_aktif['id_tahap']]);
        $kriteria_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($kriteria_ids as $kid) {
            $nilai = $_POST['nilai'][$kid] ?? '';
            if ($nilai !== '') {
                $cek = $pdo->prepare("SELECT id_penilaian FROM penilaian WHERE id_lamaran_tahap = ? AND id_kriteria = ?");
                $cek->execute([$tahap_aktif['id_lamaran_tahap'], $kid]);
                if (!$cek->fetch()) {
                    $pdo->prepare("INSERT INTO penilaian (id_lamaran_tahap, id_kriteria, nilai, id_penilai) VALUES (?, ?, ?, ?)")
                        ->execute([$tahap_aktif['id_lamaran_tahap'], $kid, (float) $nilai, $user['id_user']]);
                }
            }
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Nilai berhasil disimpan.'];
        header("Location: lamaran_detail.php?id=$id");
        exit;
    }
}

// Refresh data setelah kemungkinan aksi di atas
$tahap_aktif = ambil_tahap_aktif($pdo, $id);

$stmt = $pdo->prepare("
    SELECT lt.*, ts.nama_tahap, ts.urutan
    FROM lamaran_tahap lt
    JOIN tahap_seleksi ts ON ts.id_tahap = lt.id_tahap
    WHERE lt.id_lamaran = ?
    ORDER BY ts.urutan
");
$stmt->execute([$id]);
$riwayat_tahap = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT nama_tahap, skor_akhir FROM view_skor_tahap WHERE id_lamaran = ?");
$stmt->execute([$id]);
$skor_by_tahap = [];
foreach ($stmt->fetchAll() as $s) {
    $skor_by_tahap[$s['nama_tahap']] = $s['skor_akhir'];
}

// Kriteria untuk tahap yang sedang aktif (kalau ada)
$kriteria_aktif = [];
$nilai_terisi   = [];
if ($tahap_aktif) {
    $stmt = $pdo->prepare("SELECT * FROM kriteria_penilaian WHERE id_tahap = ?");
    $stmt->execute([$tahap_aktif['id_tahap']]);
    $kriteria_aktif = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT id_kriteria, nilai FROM penilaian WHERE id_lamaran_tahap = ?");
    $stmt->execute([$tahap_aktif['id_lamaran_tahap']]);
    foreach ($stmt->fetchAll() as $n) {
        $nilai_terisi[$n['id_kriteria']] = $n['nilai'];
    }
}
$semua_kriteria_dinilai = $kriteria_aktif && count($nilai_terisi) >= count($kriteria_aktif);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function badge_status_tahap(string $status): string
{
    if ($status === 'Lolos') return 'success';
    if ($status === 'Tidak Lolos') return 'danger';
    return 'warning';
}

require_once 'includes/header.php';
?>

<a href="lamaran.php" class="text-decoration-none">&larr; Kembali ke Daftar Lamaran</a>

<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> mt-3"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="row mt-3 g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-1"><?= htmlspecialchars($lamaran['nama_pelamar']) ?></h5>
                <p class="text-muted mb-3">melamar sebagai <strong><?= htmlspecialchars($lamaran['posisi']) ?></strong></p>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Email</td><td><?= htmlspecialchars($lamaran['email']) ?></td></tr>
                    <tr><td class="text-muted">Telepon</td><td><?= htmlspecialchars($lamaran['telepon'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Departemen</td><td><?= htmlspecialchars($lamaran['nama_departemen']) ?></td></tr>
                    <tr><td class="text-muted">Sumber</td><td><?= htmlspecialchars($lamaran['nama_sumber'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">Tgl Lamar</td><td><?= date('d M Y', strtotime($lamaran['tanggal_lamar'])) ?></td></tr>
                    <tr><td class="text-muted">CV</td><td>
                        <?php if ($lamaran['cv_path']): ?>
                            <a href="<?= htmlspecialchars($lamaran['cv_path']) ?>" target="_blank">Lihat CV</a>
                        <?php else: ?>
                            <span class="text-muted">Tidak ada</span>
                        <?php endif; ?>
                    </td></tr>
                </table>
            </div>
        </div>

        <?php if ($tahap_aktif): ?>
        <div class="card mt-3">
            <div class="card-header fw-semibold">Tahap Aktif: <?= htmlspecialchars($tahap_aktif['nama_tahap']) ?></div>
            <div class="card-body">

                <?php if (!empty($kriteria_aktif)): ?>
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="action" value="input_nilai">
                        <?php foreach ($kriteria_aktif as $k): ?>
                            <div class="mb-2">
                                <label class="form-label small mb-1">
                                    <?= htmlspecialchars($k['nama_kriteria']) ?> (bobot <?= $k['bobot'] ?>%)
                                </label>
                                <?php if (isset($nilai_terisi[$k['id_kriteria']])): ?>
                                    <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($nilai_terisi[$k['id_kriteria']]) ?>" disabled>
                                <?php else: ?>
                                    <input type="number" step="0.01" min="0" max="100" name="nilai[<?= $k['id_kriteria'] ?>]" class="form-control form-control-sm" placeholder="0 - 100">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!$semua_kriteria_dinilai): ?>
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100 mt-2">Simpan Nilai</button>
                        <?php else: ?>
                            <div class="alert alert-light border py-2 mt-2 mb-0 small">
                                Skor akhir tahap ini: <strong><?= $skor_by_tahap[$tahap_aktif['nama_tahap']] ?? '-' ?></strong>
                            </div>
                        <?php endif; ?>
                    </form>
                    <hr>
                <?php endif; ?>

                <?php if ($can_manage): ?>
                    <?php if ($tahap_aktif['urutan'] < 5): ?>
                        <form method="POST" onsubmit="return confirm('Loloskan pelamar ke tahap berikutnya?');" class="mb-2">
                            <input type="hidden" name="action" value="lolos_tahap">
                            <button type="submit" class="btn btn-success btn-sm w-100">Loloskan ke Tahap Berikutnya</button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Tandai pelamar TIDAK LOLOS di tahap ini?');">
                            <input type="hidden" name="action" value="tolak_tahap">
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Tidak Lolos</button>
                        </form>
                    <?php else: ?>
                        <p class="small text-muted">Tahap akhir (Offering). Lanjutkan ke modul Penawaran untuk menerbitkan surat penawaran kerja.</p>
                        <a href="penawaran_tambah.php?id_lamaran=<?= $id ?>" class="btn btn-primary btn-sm w-100">Buat Penawaran &rarr;</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="small text-muted mb-0">Hanya Admin/HRD yang bisa memindahkan tahap.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-secondary mt-3 mb-0">
            Proses seleksi untuk lamaran ini sudah selesai. Status akhir: <strong><?= htmlspecialchars($lamaran['status_terkini']) ?></strong>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header fw-semibold">Riwayat Tahap Seleksi</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tahap</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat_tahap as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['nama_tahap']) ?></td>
                            <td><?= date('d M Y', strtotime($t['tanggal_masuk'])) ?></td>
                            <td><?= $t['tanggal_selesai'] ? date('d M Y', strtotime($t['tanggal_selesai'])) : '-' ?></td>
                            <td><span class="badge bg-<?= badge_status_tahap($t['status']) ?>"><?= htmlspecialchars($t['status']) ?></span></td>
                            <td><?= $skor_by_tahap[$t['nama_tahap']] ?? '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

