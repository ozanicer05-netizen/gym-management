<?php<?php



declare(strict_types=1);declare(strict_types=1);



function renderLayoutStart(string $pageTitle): voidfunction renderLayoutStart(string $pageTitle): void

{{

    $basePath = '/gym';    $basePath = '/gym';

    $items = [    ?>

        'Dashboard' => '/frontend/index.php',    <!DOCTYPE html>

        'Üyeler' => '/frontend/members.php',    <html lang="tr">

        'Antrenörler' => '/frontend/trainers.php',    <head>

        'Dersler' => '/frontend/classes.php',        <meta charset="UTF-8">

        'Abonelikler' => '/frontend/subscriptions.php',        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        'Ekipman' => '/frontend/equipment.php',        <title><?= htmlspecialchars($pageTitle) ?> | GymTrack</title>

        'Şubeler' => '/frontend/branches.php',        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    ];        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    ?>        <link href="<?= $basePath ?>/assets/css/style.css" rel="stylesheet">

<!DOCTYPE html>    </head>

<html lang="tr">    <body>

<head>    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">

    <meta charset="UTF-8">        <div class="container-fluid">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">            <a class="navbar-brand fw-bold" href="<?= $basePath ?>/frontend/index.php">

    <title><?= htmlspecialchars($pageTitle) ?> | GymTrack</title>                <i class="bi bi-activity"></i> GymTrack Frontend

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">            </a>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">            <div class="collapse navbar-collapse show">

    <link href="<?= $basePath ?>/assets/css/style.css" rel="stylesheet">                <ul class="navbar-nav me-auto">

</head>                    <li class="nav-item">

<body>                        <a class="nav-link <?= $pageTitle === 'Dashboard' ? 'active' : '' ?>" href="<?= $basePath ?>/frontend/index.php">Dashboard</a>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">                    </li>

    <div class="container-fluid">                    <li class="nav-item">

        <a class="navbar-brand fw-bold" href="<?= $basePath ?>/frontend/index.php">GymTrack</a>                        <a class="nav-link <?= $pageTitle === 'Üyeler' ? 'active' : '' ?>" href="<?= $basePath ?>/frontend/members.php">Üyeler</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">                    </li>

            <span class="navbar-toggler-icon"></span>                    <li class="nav-item">

        </button>                        <a class="nav-link <?= $pageTitle === 'Antrenörler' ? 'active' : '' ?>" href="<?= $basePath ?>/frontend/trainers.php">Antrenörler</a>

        <div class="collapse navbar-collapse" id="navMenu">                    </li>

            <ul class="navbar-nav me-auto">                </ul>

                <?php foreach ($items as $name => $path): ?>                <a class="btn btn-sm btn-outline-light" href="<?= $basePath ?>/legacy/index.php">

                    <li class="nav-item">                    Eski Arayüz

                        <a class="nav-link <?= $pageTitle === $name ? 'active' : '' ?>" href="<?= $basePath . $path ?>"><?= $name ?></a>                </a>

                    </li>            </div>

                <?php endforeach; ?>        </div>

            </ul>    </nav>

        </div>    <div class="container py-4">

    </div>    <?php

</nav>}

<div class="container py-4">

<?phpfunction renderLayoutEnd(): void

}{

    ?>

function renderLayoutEnd(): void    </div>

{    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    ?>    <script src="/gym/frontend/assets/js/app.js"></script>

</div>    </body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>    </html>

<script src="/gym/frontend/assets/js/app.js"></script>    <?php

</body>}

</html>
<?php
}
