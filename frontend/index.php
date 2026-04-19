<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Dashboard');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
    <small id="last-updated" class="text-muted"></small>
</div>

<div class="row g-3" id="stats-row">
    <div class="col-12 text-muted">Veriler yükleniyor...</div>
</div>

<script>
const statItems = [
  { key: 'totalMembers', label: 'Aktif Üye', className: 'bg-primary' },
  { key: 'totalTrainers', label: 'Aktif Antrenör', className: 'bg-success' },
  { key: 'totalClasses', label: 'Ders', className: 'bg-info' },
  { key: 'totalEquipment', label: 'Aktif Ekipman', className: 'bg-secondary' },
  { key: 'expiringSoon', label: '7 Günde Bitecek', className: 'bg-warning text-dark' },
  { key: 'maintenanceDue', label: 'Bakımda Ekipman', className: 'bg-danger' },
];

async function loadDashboard() {
  const row = document.getElementById('stats-row');
  try {
    const payload = await apiGet('/gym/backend/api/dashboard.php');
    const data = payload.data;

    row.innerHTML = statItems.map(item => `
      <div class="col-6 col-lg-4">
        <div class="card ${item.className} text-white">
          <div class="card-body">
            <div class="h3 mb-1">${Number(data[item.key] ?? 0)}</div>
            <div>${item.label}</div>
          </div>
        </div>
      </div>
    `).join('');

    document.getElementById('last-updated').textContent =
      'Güncellendi: ' + new Date().toLocaleString('tr-TR');
  } catch (error) {
    row.innerHTML = `<div class="col-12"><div class="alert alert-danger">Dashboard yüklenemedi: ${escapeHtml(error.message)}</div></div>`;
  }
}

loadDashboard();
</script>

<?php renderLayoutEnd(); ?>
