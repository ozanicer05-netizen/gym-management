<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'GymTrack' ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Özel CSS -->
    <link href="/gym/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="/gym/legacy/index.php">
            <i class="bi bi-activity"></i> GymTrack
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($pageTitle=='Dashboard')?'active':'' ?>" href="/gym/legacy/index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pageTitle=='Üyeler')?'active':'' ?>" href="/gym/legacy/members.php">
                        <i class="bi bi-people"></i> Üyeler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pageTitle=='Abonelikler')?'active':'' ?>" href="/gym/legacy/subscriptions.php">
                        <i class="bi bi-card-checklist"></i> Abonelikler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pageTitle=='Antrenörler')?'active':'' ?>" href="/gym/legacy/trainers.php">
                        <i class="bi bi-person-badge"></i> Antrenörler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pageTitle=='Dersler')?'active':'' ?>" href="/gym/legacy/classes.php">
                        <i class="bi bi-calendar3"></i> Dersler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pageTitle=='Ekipman')?'active':'' ?>" href="/gym/legacy/equipment.php">
                        <i class="bi bi-tools"></i> Ekipman
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($pageTitle=='Şubeler')?'active':'' ?>" href="/gym/legacy/branches.php">
                        <i class="bi bi-building"></i> Şubeler
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/gym/legacy/notifications.php">
                        <i class="bi bi-bell"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/gym/legacy/feedback.php"><i class="bi bi-star"></i> Değerlendirmeler</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-box-arrow-right"></i> Çıkış</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sayfa içeriği buradan başlar -->
<div class="container-fluid py-4">
