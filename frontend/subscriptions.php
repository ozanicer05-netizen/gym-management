<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Abonelikler');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-card-checklist me-2"></i>Abonelikler</h4>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Ara</label><input id="search" class="form-control form-control-sm" placeholder="Üye veya paket"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Durum</label><select id="status" class="form-select form-select-sm"><option value="">Tümü</option><option value="active">Aktif</option><option value="expired">Expired</option><option value="cancelled">Cancelled</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filtrele</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Üye</th><th>Paket</th><th>Başlangıç</th><th>Bitiş</th><th>Durum</th></tr></thead>
<tbody id="subscriptions-body"><tr><td colspan="6" class="text-center py-4 text-muted">Yükleniyor...</td></tr></tbody>
</table>
</div></div></div>

<script>
async function loadSubscriptions() {
  const tbody = document.getElementById('subscriptions-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Yükleniyor...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/subscriptions.php', { search, status, limit: 100 });
    const rows = payload.data;

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.subscription_id)}</td>
        <td>${escapeHtml(row.member_name)}</td>
        <td>${escapeHtml(row.package_name)}</td>
        <td>${escapeHtml(row.start_date)}</td>
        <td>${escapeHtml(row.end_date)}</td>
        <td>${escapeHtml(row.status)}</td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Yüklenemedi: ${escapeHtml(error.message)}</td></tr>`;
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  loadSubscriptions();
});

loadSubscriptions();
</script>

<?php renderLayoutEnd(); ?>
