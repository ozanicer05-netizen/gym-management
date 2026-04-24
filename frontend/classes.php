<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Classes');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-calendar3 me-2"></i>Classes</h4>
    <button id="class-add-btn" type="button" class="btn btn-sm btn-success"><i class="bi bi-plus-lg me-1"></i>New Class</button>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Search</label><input id="search" class="form-control form-control-sm" placeholder="Class name, branch, trainer"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Level</label><select id="level" class="form-select form-select-sm"><option value="">All</option><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filter</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Class</th><th>Trainer</th><th>Branch</th><th>Capacity</th><th>Duration (min)</th><th>Level</th><th class="text-end">Actions</th></tr></thead>
<tbody id="classes-body"><tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="classes-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="classes-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="classes-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<div class="modal fade" id="class-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="class-form">
        <div class="modal-header">
          <h5 class="modal-title" id="class-modal-title">New Class</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="class-id">
          <div class="mb-2"><label class="form-label form-label-sm">Class Name *</label><input id="class-name" class="form-control form-control-sm" required></div>
          <div class="mb-2"><label class="form-label form-label-sm">Trainer *</label><select id="class-trainer" class="form-select form-select-sm" required></select></div>
          <div class="mb-2"><label class="form-label form-label-sm">Branch *</label><select id="class-branch" class="form-select form-select-sm" required></select></div>
          <div class="row g-2">
            <div class="col-6 mb-2"><label class="form-label form-label-sm">Capacity</label><input id="class-capacity" type="number" min="1" class="form-control form-control-sm" value="20"></div>
            <div class="col-6 mb-2"><label class="form-label form-label-sm">Duration (min)</label><input id="class-duration" type="number" min="1" class="form-control form-control-sm" value="60"></div>
          </div>
          <div class="mb-2"><label class="form-label form-label-sm">Level</label>
            <select id="class-level" class="form-select form-select-sm"><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select>
          </div>
          <div id="class-form-error" class="alert alert-danger d-none mb-0"></div>
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
let classesPage = 1;
let classesMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };
let classModal;

function renderClassesPagination(meta) {
  const info = document.getElementById('classes-pagination-info');
  const prevBtn = document.getElementById('classes-prev-page');
  const nextBtn = document.getElementById('classes-next-page');
  const page = Number(meta.page ?? 1);
  const totalPages = Number(meta.totalPages ?? 1);
  const total = Number(meta.total ?? 0);

  info.textContent = `Page ${page} / ${totalPages} • Total ${total} class(es)`;
  prevBtn.disabled = !meta.hasPrev;
  nextBtn.disabled = !meta.hasNext;
}

async function loadClasses(page = 1) {
  const tbody = document.getElementById('classes-body');
  const search = document.getElementById('search').value.trim();
  const level = document.getElementById('level').value;

  tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/classes.php', { search, level, limit: 50, page });
    const rows = payload.data;
    classesMeta = payload.meta || classesMeta;
    classesPage = Number(classesMeta.page ?? page);
    renderClassesPagination(classesMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No records found.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.class_id)}</td>
        <td>${escapeHtml(row.class_name)}</td>
        <td>${escapeHtml(row.trainer_name)}</td>
        <td>${escapeHtml(row.branch_name)}</td>
        <td>${Number(row.capacity)}</td>
        <td>${Number(row.duration_min)}</td>
        <td>${escapeHtml(row.level)}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${Number(row.class_id)}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${Number(row.class_id)}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderClassesPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

async function populateClassTrainers(selectedId = '') {
  const trainers = await loadLookup('trainers');
  document.getElementById('class-trainer').innerHTML = '<option value="">-- Select Trainer --</option>' + trainers.map(t =>
    `<option value="${Number(t.trainer_id)}" ${Number(selectedId) === Number(t.trainer_id) ? 'selected' : ''}>${escapeHtml(t.trainer_name)}</option>`
  ).join('');
}

async function populateClassBranches(selectedId = '') {
  const branches = await loadLookup('branches');
  document.getElementById('class-branch').innerHTML = '<option value="">-- Select Branch --</option>' + branches.map(b =>
    `<option value="${Number(b.branch_id)}" ${Number(selectedId) === Number(b.branch_id) ? 'selected' : ''}>${escapeHtml(b.branch_name)}</option>`
  ).join('');
}

async function openClassModal(row = null) {
  document.getElementById('class-form-error').classList.add('d-none');
  document.getElementById('class-form').reset();
  document.getElementById('class-id').value = row ? row.class_id : '';
  document.getElementById('class-modal-title').textContent = row ? 'Edit Class' : 'New Class';

  await Promise.all([
    populateClassTrainers(row ? row.trainer_id : ''),
    populateClassBranches(row ? row.branch_id : ''),
  ]);

  if (row) {
    document.getElementById('class-name').value = row.class_name ?? '';
    document.getElementById('class-capacity').value = row.capacity ?? 20;
    document.getElementById('class-duration').value = row.duration_min ?? 60;
    document.getElementById('class-level').value = row.level ?? 'beginner';
  } else {
    document.getElementById('class-capacity').value = 20;
    document.getElementById('class-duration').value = 60;
    document.getElementById('class-level').value = 'beginner';
  }
  classModal.show();
}

async function editClass(id) {
  try {
    const payload = await apiGet('/gym/backend/api/classes.php', { id });
    await openClassModal(payload.data);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  classesPage = 1;
  loadClasses(1);
});

document.getElementById('classes-prev-page').addEventListener('click', () => {
  if (classesMeta.hasPrev) loadClasses(classesPage - 1);
});
document.getElementById('classes-next-page').addEventListener('click', () => {
  if (classesMeta.hasNext) loadClasses(classesPage + 1);
});

document.getElementById('class-add-btn').addEventListener('click', () => openClassModal());

document.getElementById('classes-body').addEventListener('click', async (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  if (btn.dataset.action === 'edit') {
    await editClass(id);
  } else if (btn.dataset.action === 'delete') {
    if (!confirmAction('Delete this class? This cannot be undone.')) return;
    try {
      await apiDelete('/gym/backend/api/classes.php', id);
      showToast('Class deleted.');
      loadClasses(classesPage);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
});

document.getElementById('class-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('class-form-error');
  errorBox.classList.add('d-none');

  const id = document.getElementById('class-id').value;
  const body = {
    class_name: document.getElementById('class-name').value.trim(),
    trainer_id: Number(document.getElementById('class-trainer').value),
    branch_id: Number(document.getElementById('class-branch').value),
    capacity: Number(document.getElementById('class-capacity').value),
    duration_min: Number(document.getElementById('class-duration').value),
    level: document.getElementById('class-level').value,
  };

  try {
    if (id) {
      await apiPut('/gym/backend/api/classes.php', Number(id), body);
      showToast('Class updated.');
    } else {
      await apiPost('/gym/backend/api/classes.php', body);
      showToast('Class created.');
    }
    classModal.hide();
    loadClasses(classesPage);
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('d-none');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  classModal = new bootstrap.Modal(document.getElementById('class-modal'));
  loadClasses(1);
});
</script>

<?php renderLayoutEnd(); ?>
