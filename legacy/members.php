<?php
// ============================================
// members.php — Üye Listesi
// Sorumlu: Kişi 1
// ============================================
$pageTitle = "Üyeler";
include 'includes/db.php';
include 'includes/header.php';

// Arama filtresi
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$where = "WHERE 1=1";
if ($search)       $where .= " AND (u.name LIKE '%$search%' OR u.surname LIKE '%$search%' OR u.email LIKE '%$search%')";
if ($statusFilter) $where .= " AND m.status = '$statusFilter'";

$members = $conn->query("
    SELECT m.member_id, u.name, u.surname, u.email, u.phone,
           b.branch_name, m.join_date, m.status
    FROM members m
    JOIN users u ON m.user_id = u.user_id
    JOIN branches b ON m.branch_id = b.branch_id
    $where
    ORDER BY m.join_date DESC
");
?>

<div class="page-header">
    <h4><i class="bi bi-people me-2"></i>Üyeler</h4>
    <a href="members_add.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Yeni Üye
    </a>
</div>

<!-- Filtre -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Ad, soyad veya e-posta ara..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tüm Durumlar</option>
                    <option value="active"   <?= $statusFilter=='active'   ?'selected':'' ?>>Aktif</option>
                    <option value="inactive" <?= $statusFilter=='inactive' ?'selected':'' ?>>Pasif</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filtrele</button>
                <a href="members.php" class="btn btn-sm btn-outline-secondary">Temizle</a>
            </div>
        </form>
    </div>
</div>

<!-- Tablo -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Şube</th>
                    <th>Katılım</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($members->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Üye bulunamadı.</td></tr>
                <?php endif; ?>
                <?php while($row = $members->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted"><?= $row['member_id'] ?></td>
                    <td><?= htmlspecialchars($row['name'].' '.$row['surname']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['branch_name']) ?></td>
                    <td><?= date('d M Y', strtotime($row['join_date'])) ?></td>
                    <td>
                        <span class="badge <?= $row['status']=='active' ? 'badge-aktif' : 'badge-pasif' ?>">
                            <?= $row['status']=='active' ? 'Aktif' : 'Pasif' ?>
                        </span>
                    </td>
                    <td>
                        <a href="members_edit.php?id=<?= $row['member_id'] ?>"
                           class="btn btn-xs btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="members_delete.php?id=<?= $row['member_id'] ?>"
                           class="btn btn-xs btn-outline-danger btn-sm"
                           onclick="return confirm('Bu üyeyi silmek istediğinizden emin misiniz?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
