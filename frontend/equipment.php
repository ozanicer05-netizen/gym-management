<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Equipment');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-tools me-2"></i>Equipment</h4>
    <button id="equipment-add-btn" type="button" class="btn btn-sm btn-success"><i class="bi bi-plus-lg me-1"></i>New Equipment</button>
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
<thead><tr><th>#</th><th>Equipment</th><th>Category</th><th>Brand</th><th>Branch</th><th>Purchase Date</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
<tbody id="equipment-body"><tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="equipment-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="equipment-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="equipment-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<div class="modal fade" id="equipment-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="equipment-form">
        <div class="modal-header">
          <h5 class="modal-title" id="equipment-modal-title">New Equipment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="equipment-id">
          <div class="mb-2"><label class="form-label form-label-sm">Equipment Name *</label><input id="equipment-name" class="form-control form-control-sm" required></div>
          <div class="mb-2"><label class="form-label form-label-sm">Category *</label><select id="equipment-category" class="form-select form-select-sm" required></select></div>
          <div class="mb-2"><label class="form-label form-label-sm">Branch *</label><select id="equipment-branch" class="form-select form-select-sm" required></select></div>
          <div class="mb-2"><label class="form-label form-label-sm">Brand</label><input id="equipment-brand" class="form-control form-control-sm"></div>
          <div class="mb-2"><label class="form-label form-label-sm">Purchase Date</label><input id="equipment-purchase-date" type="date" class="form-control form-control-sm"></div>
          <div class="mb-2"><label class="form-label form-label-sm">Status</label>
            <select id="equipment-status" class="form-select form-select-sm"><option value="active">Active</option><option value="maintenance">Maintenance</option><option value="out_of_order">Out of Order</option></select>
          </div>
          <div id="equipment-form-error" class="alert alert-danger d-none mb-0"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let equipmentPage = 1;
let equipmentMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };
let equipmentModal;

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

  tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/equipment.php', { search, status, limit: 50, page });
    const rows = payload.data;
    equipmentMeta = payload.meta || equipmentMeta;
    equipmentPage = Number(equipmentMeta.page ?? page);
    renderEquipmentPagination(equipmentMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No records found.</td></tr>';
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
        <td><span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${escapeHtml(row.status)}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${Number(row.equipment_id)}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${Number(row.equipment_id)}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderEquipmentPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

async function populateEquipmentCategories(selectedId = '') {
  const cats = await loadLookup('equipment_categories');
  document.getElementById('equipment-category').innerHTML = '<option value="">-- Select Category --</option>' + cats.map(c =>
    `<option value="${Number(c.category_id)}" ${Number(selectedId) === Number(c.category_id) ? 'selected' : ''}>${escapeHtml(c.category_name)}</option>`
  ).join('');
}

async function populateEquipmentBranches(selectedId = '') {
  const branches = await loadLookup('branches');
  document.getElementById('equipment-branch').innerHTML = '<option value="">-- Select Branch --</option>' + branches.map(b =>
    `<option value="${Number(b.branch_id)}" ${Number(selectedId) === Number(b.branch_id) ? 'selected' : ''}>${escapeHtml(b.branch_name)}</option>`
  ).join('');
}

async function openEquipmentModal(row = null) {
  document.getElementById('equipment-form-error').classList.add('d-none');
  document.getElementById('equipment-form').reset();
  document.getElementById('equipment-id').value = row ? row.equipment_id : '';
  document.getElementById('equipment-modal-title').textContent = row ? 'Edit Equipment' : 'New Equipment';

  await Promise.all([
    populateEquipmentCategories(row ? row.category_id : ''),
    populateEquipmentBranches(row ? row.branch_id : ''),
  ]);

  if (row) {
    document.getElementById('equipment-name').value = row.equipment_name ?? '';
    document.getElementById('equipment-brand').value = row.brand ?? '';
    document.getElementById('equipment-purchase-date').value = row.purchase_date ?? '';
    document.getElementById('equipment-status').value = row.status ?? 'active';
  }
  equipmentModal.show();
}

async function editEquipment(id) {
  try {
    const payload = await apiGet('/gym/backend/api/equipment.php', { id });
    await openEquipmentModal(payload.data);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  equipmentPage = 1;
  loadEquipment(1);
});

document.getElementById('equipment-prev-page').addEventListener('click', () => {
  if (equipmentMeta.hasPrev) loadEquipment(equipmentPage - 1);
});
document.getElementById('equipment-next-page').addEventListener('click', () => {
  if (equipmentMeta.hasNext) loadEquipment(equipmentPage + 1);
});

document.getElementById('equipment-add-btn').addEventListener('click', () => openEquipmentModal());

document.getElementById('equipment-body').addEventListener('click', async (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  if (btn.dataset.action === 'edit') {
    await editEquipment(id);
  } else if (btn.dataset.action === 'delete') {
    if (!confirmAction('Delete this equipment? This cannot be undone.')) return;
    try {
      await apiDelete('/gym/backend/api/equipment.php', id);
      showToast('Equipment deleted.');
      loadEquipment(equipmentPage);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
});

document.getElementById('equipment-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('equipment-form-error');
  errorBox.classList.add('d-none');

  const id = document.getElementById('equipment-id').value;
  const body = {
    equipment_name: document.getElementById('equipment-name').value.trim(),
    category_id: Number(document.getElementById('equipment-category').value),
    branch_id: Number(document.getElementById('equipment-branch').value),
    brand: document.getElementById('equipment-brand').value.trim(),
    purchase_date: document.getElementById('equipment-purchase-date').value,
    status: document.getElementById('equipment-status').value,
  };

  try {
    if (id) {
      await apiPut('/gym/backend/api/equipment.php', Number(id), body);
      showToast('Equipment updated.');
    } else {
      await apiPost('/gym/backend/api/equipment.php', body);
      showToast('Equipment created.');
    }
    equipmentModal.hide();
    loadEquipment(equipmentPage);
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('d-none');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  equipmentModal = new bootstrap.Modal(document.getElementById('equipment-modal'));
  loadEquipment(1);
});
</script>

<?php renderLayoutEnd(); ?>
