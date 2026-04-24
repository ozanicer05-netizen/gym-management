<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Branches');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-building me-2"></i>Branches</h4>
    <button id="branch-add-btn" type="button" class="btn btn-sm btn-success"><i class="bi bi-plus-lg me-1"></i>New Branch</button>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Search</label><input id="search" class="form-control form-control-sm" placeholder="Branch name or city"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Status</label><select id="status" class="form-select form-select-sm"><option value="">All</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filter</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Branch</th><th>City</th><th>Phone</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
<tbody id="branches-body"><tr><td colspan="6" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="branches-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="branches-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="branches-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<div class="modal fade" id="branch-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="branch-form">
        <div class="modal-header">
          <h5 class="modal-title" id="branch-modal-title">New Branch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="branch-id">
          <div class="mb-2"><label class="form-label form-label-sm">Branch Name *</label><input id="branch-name" class="form-control form-control-sm" required></div>
          <div class="mb-2"><label class="form-label form-label-sm">Address</label><input id="branch-address" class="form-control form-control-sm"></div>
          <div class="mb-2"><label class="form-label form-label-sm">City</label><input id="branch-city" class="form-control form-control-sm"></div>
          <div class="mb-2"><label class="form-label form-label-sm">Phone</label><input id="branch-phone" class="form-control form-control-sm"></div>
          <div class="mb-2"><label class="form-label form-label-sm">Status</label>
            <select id="branch-status" class="form-select form-select-sm"><option value="active">Active</option><option value="inactive">Inactive</option></select>
          </div>
          <div id="branch-form-error" class="alert alert-danger d-none mb-0"></div>
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
let branchesPage = 1;
let branchesMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };
let branchModal;

function renderBranchesPagination(meta) {
  const info = document.getElementById('branches-pagination-info');
  const prevBtn = document.getElementById('branches-prev-page');
  const nextBtn = document.getElementById('branches-next-page');
  const page = Number(meta.page ?? 1);
  const totalPages = Number(meta.totalPages ?? 1);
  const total = Number(meta.total ?? 0);

  info.textContent = `Page ${page} / ${totalPages} • Total ${total} branch(es)`;
  prevBtn.disabled = !meta.hasPrev;
  nextBtn.disabled = !meta.hasNext;
}

async function loadBranches(page = 1) {
  const tbody = document.getElementById('branches-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/branches.php', { search, status, limit: 50, page });
    const rows = payload.data;
    branchesMeta = payload.meta || branchesMeta;
    branchesPage = Number(branchesMeta.page ?? page);
    renderBranchesPagination(branchesMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No records found.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.branch_id)}</td>
        <td>${escapeHtml(row.branch_name)}</td>
        <td>${escapeHtml(row.city)}</td>
        <td>${escapeHtml(row.phone)}</td>
        <td><span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${row.status === 'active' ? 'Active' : 'Inactive'}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${Number(row.branch_id)}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${Number(row.branch_id)}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderBranchesPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

function openBranchModal(row = null) {
  document.getElementById('branch-form-error').classList.add('d-none');
  document.getElementById('branch-form').reset();
  document.getElementById('branch-id').value = row ? row.branch_id : '';
  document.getElementById('branch-modal-title').textContent = row ? 'Edit Branch' : 'New Branch';
  if (row) {
    document.getElementById('branch-name').value = row.branch_name ?? '';
    document.getElementById('branch-address').value = row.address ?? '';
    document.getElementById('branch-city').value = row.city ?? '';
    document.getElementById('branch-phone').value = row.phone ?? '';
    document.getElementById('branch-status').value = row.status ?? 'active';
  }
  branchModal.show();
}

async function editBranch(id) {
  try {
    const payload = await apiGet('/gym/backend/api/branches.php', { id });
    openBranchModal(payload.data);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  branchesPage = 1;
  loadBranches(1);
});

document.getElementById('branches-prev-page').addEventListener('click', () => {
  if (branchesMeta.hasPrev) loadBranches(branchesPage - 1);
});
document.getElementById('branches-next-page').addEventListener('click', () => {
  if (branchesMeta.hasNext) loadBranches(branchesPage + 1);
});

document.getElementById('branch-add-btn').addEventListener('click', () => openBranchModal());

document.getElementById('branches-body').addEventListener('click', async (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  const action = btn.dataset.action;

  if (action === 'edit') {
    await editBranch(id);
  } else if (action === 'delete') {
    if (!confirmAction('Delete this branch? This cannot be undone.')) return;
    try {
      await apiDelete('/gym/backend/api/branches.php', id);
      showToast('Branch deleted.');
      loadBranches(branchesPage);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
});

document.getElementById('branch-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('branch-form-error');
  errorBox.classList.add('d-none');

  const id = document.getElementById('branch-id').value;
  const body = {
    branch_name: document.getElementById('branch-name').value.trim(),
    address: document.getElementById('branch-address').value.trim(),
    city: document.getElementById('branch-city').value.trim(),
    phone: document.getElementById('branch-phone').value.trim(),
    status: document.getElementById('branch-status').value,
  };

  try {
    if (id) {
      await apiPut('/gym/backend/api/branches.php', Number(id), body);
      showToast('Branch updated.');
    } else {
      await apiPost('/gym/backend/api/branches.php', body);
      showToast('Branch created.');
    }
    branchModal.hide();
    loadBranches(branchesPage);
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('d-none');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  branchModal = new bootstrap.Modal(document.getElementById('branch-modal'));
  loadBranches(1);
});
</script>

<?php renderLayoutEnd(); ?>
