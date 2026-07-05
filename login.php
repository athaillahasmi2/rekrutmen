<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Kalau sudah login, langsung lempar ke dashboard
if (isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem Rekrutmen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card shadow-sm" style="width:380px;">
        <div class="card-body p-4">
            <h4 class="mb-1 text-center">Sistem Rekrutmen</h4>
            <p class="text-muted text-center mb-4">PT Nusantara Digital Kreasi</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>

            <hr>
            <p class="small text-muted mb-1">Akun demo (password sama untuk semua: <code>password123</code>):</p>
            <ul class="small text-muted mb-0 ps-3">
                <li>admin@ptndk.co.id &mdash; Admin</li>
                <li>rani.kusuma@ptndk.co.id &mdash; HRD</li>
                <li>bagus.wirawan@ptndk.co.id &mdash; Pewawancara</li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
