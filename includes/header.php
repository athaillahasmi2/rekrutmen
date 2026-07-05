<?php
require_once __DIR__ . '/auth.php';
require_login();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Rekrutmen - PT Nusantara Digital Kreasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Sistem Rekrutmen</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="lowongan.php">Lowongan</a></li>
                <li class="nav-item"><a class="nav-link" href="lamaran.php">Lamaran</a></li>
                <li class="nav-item"><a class="nav-link" href="wawancara.php">Wawancara</a></li>
                <li class="nav-item"><a class="nav-link" href="penawaran.php">Penawaran</a></li>
            </ul>
            <span class="navbar-text text-white me-3">
                <?= htmlspecialchars($user['nama']) ?>
                <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($user['role']) ?></span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Keluar</a>
        </div>
    </div>
</nav>

<div class="container my-4">
