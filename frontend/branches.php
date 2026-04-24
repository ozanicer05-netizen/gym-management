<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Branches');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-building me-2"></i>Branches</h4>
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
<thead><tr><th>#</th><th>Branch</th><th>City</th><th>Phone</th><th>Status</th></tr></thead>
<tbody id="branches-body"><tr><td colspan="5" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="branches-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="branches-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="branches-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<script>
let branchesPage = 1;
let branchesMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };

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

  tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
  const payload = await apiGet('/gym/backend/api/branches.php', { search, status, limit: 50, page });
    const rows = payload.data;
  branchesMeta = payload.meta || branchesMeta;
  branchesPage = Number(branchesMeta.page ?? page);
  renderBranchesPagination(branchesMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No records found.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.branch_id)}</td>
        <td>${escapeHtml(row.branch_name)}</td>
        <td>${escapeHtml(row.city)}</td>
        <td>${escapeHtml(row.phone)}</td>
        <td><span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${row.status === 'active' ? 'Active' : 'Inactive'}</span></td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderBranchesPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  branchesPage = 1;
  loadBranches(1);
});

document.getElementById('branches-prev-page').addEventListener('click', () => {
  if (branchesMeta.hasPrev) {
    loadBranches(branchesPage - 1);
  }
});

document.getElementById('branches-next-page').addEventListener('click', () => {
  if (branchesMeta.hasNext) {
    loadBranches(branchesPage + 1);
  }
});

loadBranches(1);
</script>

<?php renderLayoutEnd(); ?>
