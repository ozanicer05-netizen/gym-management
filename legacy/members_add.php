<?php
// ============================================
// members_add.php — Yeni Üye Ekle
// Sorumlu: Kişi 1
// ============================================
$pageTitle = "Üyeler";
include 'includes/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = $conn->real_escape_string(trim($_POST['name']));
    $surname   = $conn->real_escape_string(trim($_POST['surname']));
    $email     = $conn->real_escape_string(trim($_POST['email']));
    $phone     = $conn->real_escape_string(trim($_POST['phone']));
    $branch_id = (int)$_POST['branch_id'];
    $join_date = $conn->real_escape_string($_POST['join_date']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Önce users tablosuna ekle
    $conn->query("INSERT INTO users (name, surname, email, phone, password_hash, status)
                  VALUES ('$name','$surname','$email','$phone','$password','active')");

    if ($conn->error) {
        $error = "E-posta adresi zaten kayıtlı olabilir: " . $conn->error;
    } else {
        $user_id = $conn->insert_id;
        // Sonra members tablosuna ekle
        $conn->query("INSERT INTO members (user_id, branch_id, join_date, status)
                      VALUES ($user_id, $branch_id, '$join_date', 'active')");
        $success = "Üye başarıyla eklendi!";
    }
}

$branches = $conn->query("SELECT branch_id, branch_name FROM branches WHERE status='active'");
include 'includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-person-plus me-2"></i>Yeni Üye Ekle</h4>
    <a href="members.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Geri
    </a>
</div>

<?php if ($error):   echo "<div class='alert alert-danger'>$error</div>";   endif; ?>
<?php if ($success): echo "<div class='alert alert-success'>$success</div>"; endif; ?>

<div class="card" style="max-width:640px">
    <div class="card-body">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Ad</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Soyad</label>
                    <input type="text" name="surname" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-control" placeholder="05xx xxx xx xx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Şube</label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">Şube seçin...</option>
                        <?php while($b = $branches->fetch_assoc()): ?>
                        <option value="<?= $b['branch_id'] ?>"><?= htmlspecialchars($b['branch_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Katılım Tarihi</label>
                    <input type="date" name="join_date" class="form-control"
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Üyeyi Kaydet
                    </button>
                    <a href="members.php" class="btn btn-outline-secondary ms-2">İptal</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
