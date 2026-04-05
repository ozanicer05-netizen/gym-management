<?php
// ============================================
// ŞABLON — Yeni sayfa eklerken bu dosyayı kopyala
// Dosya adını ve $pageTitle'ı değiştir
// Sorumlu: [Kişi adını yaz]
// ============================================
$pageTitle = "Sayfa Adı";   // ← bunu değiştir
include 'includes/db.php';
include 'includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-circle me-2"></i><?= $pageTitle ?></h4>
    <!-- İstenirse buraya buton ekle -->
</div>

<!-- İçeriğini buraya yaz -->
<div class="card">
    <div class="card-body">
        <p class="text-muted">Bu sayfa henüz yapım aşamasında.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
