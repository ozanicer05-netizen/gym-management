<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Ekipman');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-tools me-2"></i>Ekipman</h4>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Ara</label><input id="search" class="form-control form-control-sm" placeholder="Ekipman, marka, şube"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Durum</label><select id="status" class="form-select form-select-sm"><option value="">Tümü</option><option value="active">Aktif</option><option value="maintenance">Bakım</option><option value="out_of_order">Arızalı</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filtrele</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Ekipman</th><th>Kategori</th><th>Marka</th><th>Şube</th><th>Satın Alma</th><th>Durum</th></tr></thead>
<tbody id="equipment-body"><tr><td colspan="7" class="text-center py-4 text-muted">Yükleniyor...</td></tr></tbody>
</table>
</div></div></div>

<script>
async function loadEquipment() {
  const tbody = document.getElementById('equipment-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Yükleniyor...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/equipment.php', { search, status, limit: 100 });
    const rows = payload.data;

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.equipment_id)}</td>
        <td>${escapeHtml(row.equipment_name)}</td>
        <td>${escapeHtml(row.category_name)}</td>
        <td>${escapeHtml(row.brand)}</td>
        <td>${escapeHtml(row.branch_name)}</td>
        <td>${escapeHtml(row.purchase_date)}</td>
        <td>${escapeHtml(row.status)}</td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Yüklenemedi: ${escapeHtml(error.message)}</td></tr>`;
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  loadEquipment();
});

loadEquipment();
</script>

<?php renderLayoutEnd(); ?>
