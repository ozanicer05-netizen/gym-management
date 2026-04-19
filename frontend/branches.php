<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Şubeler');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-building me-2"></i>Şubeler</h4>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Ara</label><input id="search" class="form-control form-control-sm" placeholder="Şube adı veya şehir"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Durum</label><select id="status" class="form-select form-select-sm"><option value="">Tümü</option><option value="active">Aktif</option><option value="inactive">Pasif</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filtrele</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Şube</th><th>Şehir</th><th>Telefon</th><th>Durum</th></tr></thead>
<tbody id="branches-body"><tr><td colspan="5" class="text-center py-4 text-muted">Yükleniyor...</td></tr></tbody>
</table>
</div></div></div>

<script>
async function loadBranches() {
  const tbody = document.getElementById('branches-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Yükleniyor...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/branches.php', { search, status, limit: 100 });
    const rows = payload.data;

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.branch_id)}</td>
        <td>${escapeHtml(row.branch_name)}</td>
        <td>${escapeHtml(row.city)}</td>
        <td>${escapeHtml(row.phone)}</td>
        <td><span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${row.status === 'active' ? 'Aktif' : 'Pasif'}</span></td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Yüklenemedi: ${escapeHtml(error.message)}</td></tr>`;
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  loadBranches();
});

loadBranches();
</script>

<?php renderLayoutEnd(); ?>
