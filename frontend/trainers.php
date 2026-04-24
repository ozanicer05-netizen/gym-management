<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Trainers');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-person-badge me-2"></i>Trainers</h4>
    <button id="trainer-add-btn" type="button" class="btn btn-sm btn-success"><i class="bi bi-plus-lg me-1"></i>New Trainer</button>
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
<thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Specialization</th><th>Branch</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
<tbody id="trainers-body"><tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="trainers-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="trainers-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="trainers-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<div class="modal fade" id="trainer-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="trainer-form">
        <div class="modal-header">
          <h5 class="modal-title" id="trainer-modal-title">New Trainer</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="trainer-id">
          <div class="mb-2" id="trainer-user-group">
            <label class="form-label form-label-sm">User *</label>
            <select id="trainer-user" class="form-select form-select-sm" required></select>
            <small class="text-muted">Only users who aren't already a trainer are listed.</small>
          </div>
          <div class="mb-2"><label class="form-label form-label-sm">Branch *</label><select id="trainer-branch" class="form-select form-select-sm" required></select></div>
          <div class="mb-2"><label class="form-label form-label-sm">Specialization</label><input id="trainer-specialization" class="form-control form-control-sm" placeholder="e.g. Yoga, Strength"></div>
          <div class="mb-2"><label class="form-label form-label-sm">Availability</label>
            <select id="trainer-availability" class="form-select form-select-sm"><option value="active">Active</option><option value="inactive">Inactive</option><option value="on_leave">On Leave</option></select>
          </div>
          <div id="trainer-form-error" class="alert alert-danger d-none mb-0"></div>
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
let trainersPage = 1;
let trainersMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };
let trainerModal;

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

  tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/trainers.php', { search, status, limit: 50, page });
    const rows = payload.data;
    trainersMeta = payload.meta || trainersMeta;
    trainersPage = Number(trainersMeta.page ?? page);
    renderTrainersPagination(trainersMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No records found.</td></tr>';
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
        <td><span class="badge ${row.availability_status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${escapeHtml(row.availability_status)}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${Number(row.trainer_id)}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${Number(row.trainer_id)}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderTrainersPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

async function populateTrainerBranches(selectedId = '') {
  const branches = await loadLookup('branches');
  document.getElementById('trainer-branch').innerHTML = '<option value="">-- Select Branch --</option>' + branches.map(b =>
    `<option value="${Number(b.branch_id)}" ${Number(selectedId) === Number(b.branch_id) ? 'selected' : ''}>${escapeHtml(b.branch_name)}</option>`
  ).join('');
}

async function populateTrainerUsers() {
  const users = await loadLookup('users_without_trainer');
  document.getElementById('trainer-user').innerHTML = '<option value="">-- Select User --</option>' + users.map(u =>
    `<option value="${Number(u.user_id)}">${escapeHtml(u.name)} ${escapeHtml(u.surname)} (${escapeHtml(u.email)})</option>`
  ).join('');
}

async function openTrainerModal(row = null) {
  document.getElementById('trainer-form-error').classList.add('d-none');
  document.getElementById('trainer-form').reset();
  document.getElementById('trainer-id').value = row ? row.trainer_id : '';
  document.getElementById('trainer-modal-title').textContent = row ? 'Edit Trainer' : 'New Trainer';

  const userGroup = document.getElementById('trainer-user-group');
  if (row) {
    userGroup.classList.add('d-none');
    await populateTrainerBranches(row.branch_id);
    document.getElementById('trainer-branch').value = row.branch_id ?? '';
    document.getElementById('trainer-specialization').value = row.specialization ?? '';
    document.getElementById('trainer-availability').value = row.availability_status ?? 'active';
  } else {
    userGroup.classList.remove('d-none');
    await Promise.all([populateTrainerUsers(), populateTrainerBranches()]);
  }
  trainerModal.show();
}

async function editTrainer(id) {
  try {
    const payload = await apiGet('/gym/backend/api/trainers.php', { id });
    await openTrainerModal(payload.data);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  trainersPage = 1;
  loadTrainers(1);
});

document.getElementById('trainers-prev-page').addEventListener('click', () => {
  if (trainersMeta.hasPrev) loadTrainers(trainersPage - 1);
});
document.getElementById('trainers-next-page').addEventListener('click', () => {
  if (trainersMeta.hasNext) loadTrainers(trainersPage + 1);
});

document.getElementById('trainer-add-btn').addEventListener('click', () => openTrainerModal());

document.getElementById('trainers-body').addEventListener('click', async (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  if (btn.dataset.action === 'edit') {
    await editTrainer(id);
  } else if (btn.dataset.action === 'delete') {
    if (!confirmAction('Delete this trainer? This cannot be undone.')) return;
    try {
      await apiDelete('/gym/backend/api/trainers.php', id);
      showToast('Trainer deleted.');
      loadTrainers(trainersPage);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
});

document.getElementById('trainer-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('trainer-form-error');
  errorBox.classList.add('d-none');

  const id = document.getElementById('trainer-id').value;
  const body = {
    branch_id: Number(document.getElementById('trainer-branch').value),
    specialization: document.getElementById('trainer-specialization').value.trim(),
    availability_status: document.getElementById('trainer-availability').value,
  };
  if (!id) {
    body.user_id = Number(document.getElementById('trainer-user').value);
  }

  try {
    if (id) {
      await apiPut('/gym/backend/api/trainers.php', Number(id), body);
      showToast('Trainer updated.');
    } else {
      await apiPost('/gym/backend/api/trainers.php', body);
      showToast('Trainer created.');
    }
    trainerModal.hide();
    loadTrainers(trainersPage);
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('d-none');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  trainerModal = new bootstrap.Modal(document.getElementById('trainer-modal'));
  loadTrainers(1);
});
</script>

<?php renderLayoutEnd(); ?>
