<?php

declare(strict_types=1);

function renderLayoutStart(string $pageTitle): void
{
    $basePath = '/gym';
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pageTitle) ?> | GymTrack</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <link href="<?= $basePath ?>/assets/css/style.css" rel="stylesheet">
    </head>
    <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?= $basePath ?>/frontend/index.php">
                <i class="bi bi-activity"></i> GymTrack Frontend
            </a>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $pageTitle === 'Dashboard' ? 'active' : '' ?>" href="<?= $basePath ?>/frontend/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $pageTitle === 'Üyeler' ? 'active' : '' ?>" href="<?= $basePath ?>/frontend/members.php">Üyeler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $pageTitle === 'Antrenörler' ? 'active' : '' ?>" href="<?= $basePath ?>/frontend/trainers.php">Antrenörler</a>
                    </li>
                </ul>
                <a class="btn btn-sm btn-outline-light" href="<?= $basePath ?>/legacy/index.php">
                    Eski Arayüz
                </a>
            </div>
        </div>
    </nav>
    <div class="container py-4">
    <?php
}

function renderLayoutEnd(): void
{
    ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/gym/frontend/assets/js/app.js"></script>
    </body>
    </html>
    <?php
}
