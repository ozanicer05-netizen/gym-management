<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Trainers');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-person-badge me-2"></i>Trainers</h4>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Search</label><input id="search" class="form-control form-control-sm" placeholder="Name, surname, email"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Status</label><select id="status" class="form-select form-select-sm"><option value="">All</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filter</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Specialization</th><th>Branch</th><th>Status</th></tr></thead>
<tbody id="trainers-body"><tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="trainers-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="trainers-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="trainers-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<script>
let trainersPage = 1;
let trainersMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };

function renderTrainersPagination(meta) {
  const info = document.getElementById('trainers-pagination-info');
  const prevBtn = document.getElementById('trainers-prev-page');
  const nextBtn = document.getElementById('trainers-next-page');
  const page = Number(meta.page ?? 1);
  const totalPages = Number(meta.totalPages ?? 1);
  const total = Number(meta.total ?? 0);

  info.textContent = `Page ${page} / ${totalPages} • Total ${total} trainer(s)`;
  prevBtn.disabled = !meta.hasPrev;
  nextBtn.disabled = !meta.hasNext;
}

async function loadTrainers(page = 1) {
  const tbody = document.getElementById('trainers-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/trainers.php', { search, status, limit: 50, page });
    const rows = payload.data;
    trainersMeta = payload.meta || trainersMeta;
    trainersPage = Number(trainersMeta.page ?? page);
    renderTrainersPagination(trainersMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No records found.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.trainer_id)}</td>
        <td>${escapeHtml(row.name)} ${escapeHtml(row.surname)}</td>
        <td>${escapeHtml(row.email)}</td>
        <td>${escapeHtml(row.phone)}</td>
        <td>${escapeHtml(row.specialization)}</td>
        <td>${escapeHtml(row.branch_name)}</td>
        <td><span class="badge ${row.availability_status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${row.availability_status === 'active' ? 'Active' : 'Inactive'}</span></td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderTrainersPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  trainersPage = 1;
  loadTrainers(1);
});

document.getElementById('trainers-prev-page').addEventListener('click', () => {
  if (trainersMeta.hasPrev) {
    loadTrainers(trainersPage - 1);
  }
});

document.getElementById('trainers-next-page').addEventListener('click', () => {
  if (trainersMeta.hasNext) {
    loadTrainers(trainersPage + 1);
  }
});

loadTrainers(1);
</script>

<?php renderLayoutEnd(); ?>
