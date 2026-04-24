<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Equipment');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-tools me-2"></i>Equipment</h4>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Search</label><input id="search" class="form-control form-control-sm" placeholder="Equipment, brand, branch"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Status</label><select id="status" class="form-select form-select-sm"><option value="">All</option><option value="active">Active</option><option value="maintenance">Maintenance</option><option value="out_of_order">Out of Order</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filter</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Equipment</th><th>Category</th><th>Brand</th><th>Branch</th><th>Purchase Date</th><th>Status</th></tr></thead>
<tbody id="equipment-body"><tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="equipment-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="equipment-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="equipment-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<script>
let equipmentPage = 1;
let equipmentMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };

function renderEquipmentPagination(meta) {
  const info = document.getElementById('equipment-pagination-info');
  const prevBtn = document.getElementById('equipment-prev-page');
  const nextBtn = document.getElementById('equipment-next-page');
  const page = Number(meta.page ?? 1);
  const totalPages = Number(meta.totalPages ?? 1);
  const total = Number(meta.total ?? 0);

  info.textContent = `Page ${page} / ${totalPages} • Total ${total} equipment item(s)`;
  prevBtn.disabled = !meta.hasPrev;
  nextBtn.disabled = !meta.hasNext;
}

async function loadEquipment(page = 1) {
  const tbody = document.getElementById('equipment-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
  const payload = await apiGet('/gym/backend/api/equipment.php', { search, status, limit: 50, page });
    const rows = payload.data;
  equipmentMeta = payload.meta || equipmentMeta;
  equipmentPage = Number(equipmentMeta.page ?? page);
  renderEquipmentPagination(equipmentMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No records found.</td></tr>';
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
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderEquipmentPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  equipmentPage = 1;
  loadEquipment(1);
});

document.getElementById('equipment-prev-page').addEventListener('click', () => {
  if (equipmentMeta.hasPrev) {
    loadEquipment(equipmentPage - 1);
  }
});

document.getElementById('equipment-next-page').addEventListener('click', () => {
  if (equipmentMeta.hasNext) {
    loadEquipment(equipmentPage + 1);
  }
});

loadEquipment(1);
</script>

<?php renderLayoutEnd(); ?>
