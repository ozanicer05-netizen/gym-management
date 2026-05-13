<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function renderLayoutStart(string $pageTitle): void
{
    if (empty($_SESSION['auth_user'])) {
        header('Location: /gym/frontend/login.php');
        exit;
    }

    $basePath = '/gym';
    $authUser = $_SESSION['auth_user'];
    $items = [
        'Dashboard' => '/frontend/index.php',
        'Members' => '/frontend/members.php',
        'Trainers' => '/frontend/trainers.php',
        'Classes' => '/frontend/classes.php',
        'Subscriptions' => '/frontend/subscriptions.php',
        'Equipment' => '/frontend/equipment.php',
        'Branches' => '/frontend/branches.php',
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | GymTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $basePath ?>/assets/css/style.css" rel="stylesheet">
    <script src="/gym/frontend/assets/js/app.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= $basePath ?>/frontend/index.php">GymTrack</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <?php foreach ($items as $name => $path): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $pageTitle === $name ? 'active' : '' ?>" href="<?= $basePath . $path ?>"><?= htmlspecialchars($name) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <?= htmlspecialchars((string) ($authUser['name'] ?? 'User')) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars((string) ($authUser['email'] ?? '')) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= $basePath ?>/frontend/logout.php">Logout</a></li>
                </ul>
            </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
