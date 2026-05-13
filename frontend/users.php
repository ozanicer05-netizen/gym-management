<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Users');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="page-title mb-0"><i class="bi bi-person-plus me-2"></i>Users</h4>
  <div class="d-flex gap-2">
    <button id="user-add-btn" type="button" class="btn btn-sm btn-success"><i class="bi bi-plus-lg me-1"></i>New User</button>
  </div>
</div>

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
<thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Roles</th><th>Profile</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
<tbody id="users-body"><tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="users-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<div class="modal fade" id="user-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="user-form">
        <div class="modal-header">
          <h5 class="modal-title" id="user-modal-title">New User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="user-id">
          <div class="row g-2">
            <div class="col-6 mb-2"><label class="form-label form-label-sm">First Name *</label><input id="user-name" class="form-control form-control-sm"></div>
            <div class="col-6 mb-2"><label class="form-label form-label-sm">Last Name *</label><input id="user-surname" class="form-control form-control-sm"></div>
          </div>
          <div class="mb-2" id="user-email-group">
            <label class="form-label form-label-sm">Email *</label>
            <input id="user-email" type="email" class="form-control form-control-sm">
          </div>
          <div class="row g-2">
            <div class="col-6 mb-2"><label class="form-label form-label-sm">Phone</label><input id="user-phone" class="form-control form-control-sm"></div>
            <div class="col-6 mb-2" id="user-password-group"><label class="form-label form-label-sm">Password</label><input id="user-password" type="text" class="form-control form-control-sm" placeholder="Default: Welcome123!"></div>
          </div>
          <div class="mb-2"><label class="form-label form-label-sm">Status</label>
            <select id="user-status" class="form-select form-select-sm">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div id="user-form-error" class="alert alert-danger d-none mb-0"></div>
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
let currentPage = 1;
let paginationMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, limit: 50, page: 1 };
let userModal;

function renderPagination(meta) {
  const info = document.getElementById('users-pagination-info');
  const prevBtn = document.getElementById('prev-page');
  const nextBtn = document.getElementById('next-page');
  const totalPages = Number(meta.totalPages ?? 1);
  const page = Number(meta.page ?? 1);
  const total = Number(meta.total ?? 0);
  info.textContent = `Page ${page} / ${totalPages} • Total ${total} user(s)`;
  prevBtn.disabled = !meta.hasPrev;
  nextBtn.disabled = !meta.hasNext;
}

function profileBadges(row) {
  const parts = [];
  if (Number(row.is_member) === 1) parts.push('<span class="badge bg-info">Member</span>');
  if (Number(row.is_trainer) === 1) parts.push('<span class="badge bg-warning text-dark">Trainer</span>');
  if (!parts.length) parts.push('<span class="text-muted small">—</span>');
  return parts.join(' ');
}

async function loadUsers(page = 1) {
  const tbody = document.getElementById('users-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/users.php', { search, status, limit: 50, page });
    const rows = payload.data;
    paginationMeta = payload.meta || paginationMeta;
    currentPage = Number(paginationMeta.page ?? page);
    renderPagination(paginationMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No records found.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.user_id)}</td>
        <td>${escapeHtml(row.name)} ${escapeHtml(row.surname)}</td>
        <td>${escapeHtml(row.email)}</td>
        <td>${escapeHtml(row.phone || '')}</td>
        <td>${row.roles ? escapeHtml(row.roles) : '<span class="text-muted small">—</span>'}</td>
        <td>${profileBadges(row)}</td>
        <td><span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${row.status === 'active' ? 'Active' : 'Inactive'}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${Number(row.user_id)}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${Number(row.user_id)}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

function openUserModal(row = null) {
  document.getElementById('user-form-error').classList.add('d-none');
  document.getElementById('user-form').reset();
  document.getElementById('user-id').value = row ? row.user_id : '';
  document.getElementById('user-modal-title').textContent = row ? 'Edit User' : 'New User';

  const emailInput = document.getElementById('user-email');
  const passwordGroup = document.getElementById('user-password-group');

  if (row) {
    document.getElementById('user-name').value = row.name ?? '';
    document.getElementById('user-surname').value = row.surname ?? '';
    emailInput.value = row.email ?? '';
    emailInput.disabled = true;
    document.getElementById('user-phone').value = row.phone ?? '';
    document.getElementById('user-status').value = row.status ?? 'active';
    passwordGroup.classList.add('d-none');
  } else {
    emailInput.disabled = false;
    passwordGroup.classList.remove('d-none');
  }
  userModal.show();
}

async function editUser(id) {
  try {
    const payload = await apiGet('/gym/backend/api/users.php', { id });
    openUserModal(payload.data);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  currentPage = 1;
  loadUsers(1);
});

document.getElementById('prev-page').addEventListener('click', () => {
  if (paginationMeta.hasPrev) loadUsers(currentPage - 1);
});
document.getElementById('next-page').addEventListener('click', () => {
  if (paginationMeta.hasNext) loadUsers(currentPage + 1);
});

document.getElementById('user-add-btn').addEventListener('click', () => openUserModal());

document.getElementById('users-body').addEventListener('click', async (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  if (btn.dataset.action === 'edit') {
    await editUser(id);
  } else if (btn.dataset.action === 'delete') {
    if (!confirmAction('Delete this user? Linked member/trainer profiles will also be removed.')) return;
    try {
      await apiDelete('/gym/backend/api/users.php', id);
      showToast('User deleted.');
      loadUsers(currentPage);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
});

document.getElementById('user-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('user-form-error');
  errorBox.classList.add('d-none');

  const id = document.getElementById('user-id').value;
  const name = document.getElementById('user-name').value.trim();
  const surname = document.getElementById('user-surname').value.trim();
  const phone = document.getElementById('user-phone').value.trim();
  const status = document.getElementById('user-status').value;

  if (!name || !surname) {
    errorBox.textContent = 'First name and last name are required.';
    errorBox.classList.remove('d-none');
    return;
  }

  try {
    if (id) {
      await apiPut('/gym/backend/api/users.php', Number(id), { name, surname, phone, status });
      showToast('User updated.');
    } else {
      const email = document.getElementById('user-email').value.trim();
      const password = document.getElementById('user-password').value;
      if (!email) {
        errorBox.textContent = 'Email is required.';
        errorBox.classList.remove('d-none');
        return;
      }
      await apiPost('/gym/backend/api/users.php', { name, surname, email, phone, password, status });
      showToast('User created.');
    }
    userModal.hide();
    loadUsers(currentPage);
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('d-none');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  userModal = new bootstrap.Modal(document.getElementById('user-modal'));
  loadUsers(1);
});
</script>

<?php renderLayoutEnd(); ?>
