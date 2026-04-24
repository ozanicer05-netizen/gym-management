<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Members');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="page-title mb-0"><i class="bi bi-people me-2"></i>Members</h4>
  <div class="d-flex gap-2">
    <a id="members-export" class="btn btn-sm btn-outline-primary" href="/gym/backend/api/members_export.php">Export CSV</a>
    <label class="btn btn-sm btn-outline-success mb-0" for="members-import-file">Import CSV</label>
    <input id="members-import-file" type="file" accept=".csv,text/csv" class="d-none">
  </div>
</div>

<div id="members-import-result" class="alert alert-info d-none"></div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5">
    <label class="form-label form-label-sm">Search</label>
    <input id="search" class="form-control form-control-sm" placeholder="Name, surname, email">
  </div>
  <div class="col-md-3">
    <label class="form-label form-label-sm">Status</label>
    <select id="status" class="form-select form-select-sm">
      <option value="">All</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
    </select>
  </div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filter</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Branch</th><th>Join Date</th><th>Status</th></tr></thead>
<tbody id="members-body"><tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="members-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<script>
let currentPage = 1;
let paginationMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, limit: 50, page: 1 };

function renderPagination(meta) {
  const info = document.getElementById('members-pagination-info');
  const prevBtn = document.getElementById('prev-page');
  const nextBtn = document.getElementById('next-page');

  const totalPages = Number(meta.totalPages ?? 1);
  const page = Number(meta.page ?? 1);
  const total = Number(meta.total ?? 0);

  info.textContent = `Page ${page} / ${totalPages} • Total ${total} member(s)`;
  prevBtn.disabled = !meta.hasPrev;
  nextBtn.disabled = !meta.hasNext;
}

async function loadMembers(page = 1) {
  const tbody = document.getElementById('members-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  document.getElementById('members-export').href = `/gym/backend/api/members_export.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`;

  tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/members.php', { search, status, limit: 50, page });
    const rows = payload.data;
    paginationMeta = payload.meta || paginationMeta;
    currentPage = Number(paginationMeta.page ?? page);
    renderPagination(paginationMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No records found.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.member_id)}</td>
        <td>${escapeHtml(row.name)} ${escapeHtml(row.surname)}</td>
        <td>${escapeHtml(row.email)}</td>
        <td>${escapeHtml(row.phone)}</td>
        <td>${escapeHtml(row.branch_name)}</td>
        <td>${escapeHtml(row.join_date)}</td>
        <td><span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${row.status === 'active' ? 'Active' : 'Inactive'}</span></td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  currentPage = 1;
  loadMembers(1);
});

document.getElementById('prev-page').addEventListener('click', () => {
  if (paginationMeta.hasPrev) {
    loadMembers(currentPage - 1);
  }
});

document.getElementById('next-page').addEventListener('click', () => {
  if (paginationMeta.hasNext) {
    loadMembers(currentPage + 1);
  }
});

loadMembers(1);

document.getElementById('members-import-file').addEventListener('change', async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  const resultBox = document.getElementById('members-import-result');
  resultBox.className = 'alert alert-info';
  resultBox.textContent = 'Importing CSV...';
  resultBox.classList.remove('d-none');

  try {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch('/gym/backend/api/members_import.php', {
      method: 'POST',
      body: formData,
    });

    const payload = await response.json();
    if (!response.ok || !payload.ok) {
      throw new Error(payload.error || 'Import failed');
    }

    const data = payload.data || {};
    resultBox.className = 'alert alert-success';
    resultBox.textContent = `Import completed. Inserted: ${Number(data.inserted ?? 0)}, Skipped: ${Number(data.skipped ?? 0)}. Default password: ${data.defaultPassword ?? '-'}`;
    currentPage = 1;
    await loadMembers(1);
  } catch (error) {
    resultBox.className = 'alert alert-danger';
    resultBox.textContent = `Import failed: ${error.message}`;
  } finally {
    event.target.value = '';
  }
});
</script>

<?php renderLayoutEnd(); ?>
