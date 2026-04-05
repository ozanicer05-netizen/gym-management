<?php
// ============================================
// index.php — Dashboard
// Sorumlu: Kişi 5
// ============================================
$pageTitle = "Dashboard";
include 'includes/db.php';
include 'includes/header.php';

// İstatistikler
$totalMembers     = $conn->query("SELECT COUNT(*) as c FROM members WHERE status='active'")->fetch_assoc()['c'];
$totalTrainers    = $conn->query("SELECT COUNT(*) as c FROM trainers WHERE availability_status='active'")->fetch_assoc()['c'];
$totalClasses     = $conn->query("SELECT COUNT(*) as c FROM classes")->fetch_assoc()['c'];
$totalEquipment   = $conn->query("SELECT COUNT(*) as c FROM equipment WHERE status='active'")->fetch_assoc()['c'];
$expiringSoon     = $conn->query("SELECT COUNT(*) as c FROM subscriptions WHERE status='active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['c'];
$maintenanceDue   = $conn->query("SELECT COUNT(*) as c FROM equipment WHERE status='maintenance'")->fetch_assoc()['c'];

// Son eklenen üyeler
$recentMembers = $conn->query("
    SELECT u.name, u.surname, b.branch_name, m.join_date, m.status
    FROM members m
    JOIN users u ON m.user_id = u.user_id
    JOIN branches b ON m.branch_id = b.branch_id
    ORDER BY m.join_date DESC
    LIMIT 5
");
?>

<div class="page-header">
    <h4><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
    <small class="text-muted"><?= date('d M Y, H:i') ?></small>
</div>

<!-- Stat Kartları -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card text-white bg-primary">
            <div class="card-body">
                <div class="stat-number"><?= $totalMembers ?></div>
                <div class="stat-label">Aktif Üye</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card text-white bg-success">
            <div class="card-body">
                <div class="stat-number"><?= $totalTrainers ?></div>
                <div class="stat-label">Antrenör</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card text-white bg-info">
            <div class="card-body">
                <div class="stat-number"><?= $totalClasses ?></div>
                <div class="stat-label">Aktif Ders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card text-white bg-secondary">
            <div class="card-body">
                <div class="stat-number"><?= $totalEquipment ?></div>
                <div class="stat-label">Ekipman</div>
            </div>
        </div>
    </div>
</div>

<!-- Uyarı Kartları -->
<?php if ($expiringSoon > 0 || $maintenanceDue > 0): ?>
<div class="row g-3 mb-4">
    <?php if ($expiringSoon > 0): ?>
    <div class="col-md-6">
        <div class="alert alert-warning d-flex align-items-center mb-0">
            <i class="bi bi-exclamation-triangle me-2 fs-5"></i>
            <div><strong><?= $expiringSoon ?> üyenin</strong> aboneliği 7 gün içinde bitiyor.</div>
            <a href="subscriptions.php" class="ms-auto btn btn-sm btn-warning">Görüntüle</a>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($maintenanceDue > 0): ?>
    <div class="col-md-6">
        <div class="alert alert-danger d-flex align-items-center mb-0">
            <i class="bi bi-tools me-2 fs-5"></i>
            <div><strong><?= $maintenanceDue ?> ekipman</strong> bakımda / arızalı.</div>
            <a href="equipment.php" class="ms-auto btn btn-sm btn-danger">Görüntüle</a>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Son Üyeler Tablosu -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-1"></i> Son Eklenen Üyeler</span>
        <a href="members.php" class="btn btn-sm btn-outline-primary">Tümü</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>Şube</th>
                    <th>Katılım</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $recentMembers->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name'].' '.$row['surname']) ?></td>
                    <td><?= htmlspecialchars($row['branch_name']) ?></td>
                    <td><?= date('d M Y', strtotime($row['join_date'])) ?></td>
                    <td>
                        <span class="badge <?= $row['status']=='active' ? 'badge-aktif' : 'badge-pasif' ?>">
                            <?= $row['status']=='active' ? 'Aktif' : 'Pasif' ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
