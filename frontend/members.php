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
    <button id="member-add-btn" type="button" class="btn btn-sm btn-success"><i class="bi bi-plus-lg me-1"></i>New Member</button>
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
<thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Branch</th><th>Join Date</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
<tbody id="members-body"><tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="members-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<div class="modal fade" id="member-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="member-form">
        <div class="modal-header">
          <h5 class="modal-title" id="member-modal-title">New Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="member-id">
          <div class="mb-2" id="member-user-group">
            <label class="form-label form-label-sm">User *</label>
            <select id="member-user" class="form-select form-select-sm" required></select>
            <small class="text-muted">Only users who aren't already a member are listed.</small>
          </div>
          <div class="mb-2"><label class="form-label form-label-sm">Branch *</label><select id="member-branch" class="form-select form-select-sm" required></select></div>
          <div class="row g-2">
            <div class="col-6 mb-2"><label class="form-label form-label-sm">Birth Date</label><input id="member-birth-date" type="date" class="form-control form-control-sm"></div>
            <div class="col-6 mb-2"><label class="form-label form-label-sm">Gender</label>
              <select id="member-gender" class="form-select form-select-sm"><option value="">-</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
            </div>
          </div>
          <div class="mb-2"><label class="form-label form-label-sm">Emergency Contact</label><input id="member-emergency" class="form-control form-control-sm"></div>
          <div class="mb-2"><label class="form-label form-label-sm">Status</label>
            <select id="member-status" class="form-select form-select-sm"><option value="active">Active</option><option value="inactive">Inactive</option><option value="suspended">Suspended</option></select>
          </div>
          <div id="member-form-error" class="alert alert-danger d-none mb-0"></div>
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
let memberModal;

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

  tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/members.php', { search, status, limit: 50, page });
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
        <td>${Number(row.member_id)}</td>
        <td>${escapeHtml(row.name)} ${escapeHtml(row.surname)}</td>
        <td>${escapeHtml(row.email)}</td>
        <td>${escapeHtml(row.phone)}</td>
        <td>${escapeHtml(row.branch_name)}</td>
        <td>${escapeHtml(row.join_date)}</td>
        <td><span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${row.status === 'active' ? 'Active' : 'Inactive'}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${Number(row.member_id)}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${Number(row.member_id)}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

async function populateBranchOptions(selectedId = '') {
  const branchSel = document.getElementById('member-branch');
  const branches = await loadLookup('branches');
  branchSel.innerHTML = '<option value="">-- Select Branch --</option>' + branches.map(b =>
    `<option value="${Number(b.branch_id)}" ${Number(selectedId) === Number(b.branch_id) ? 'selected' : ''}>${escapeHtml(b.branch_name)}</option>`
  ).join('');
}

async function populateUserOptions() {
  const userSel = document.getElementById('member-user');
  const users = await loadLookup('users_without_member');
  userSel.innerHTML = '<option value="">-- Select User --</option>' + users.map(u =>
    `<option value="${Number(u.user_id)}">${escapeHtml(u.name)} ${escapeHtml(u.surname)} (${escapeHtml(u.email)})</option>`
  ).join('');
}

async function openMemberModal(row = null) {
  document.getElementById('member-form-error').classList.add('d-none');
  document.getElementById('member-form').reset();
  document.getElementById('member-id').value = row ? row.member_id : '';
  document.getElementById('member-modal-title').textContent = row ? 'Edit Member' : 'New Member';

  const userGroup = document.getElementById('member-user-group');
  if (row) {
    userGroup.classList.add('d-none');
    await populateBranchOptions(row.branch_id);
    document.getElementById('member-branch').value = row.branch_id ?? '';
    document.getElementById('member-birth-date').value = row.birth_date ?? '';
    document.getElementById('member-gender').value = row.gender ?? '';
    document.getElementById('member-emergency').value = row.emergency_contact ?? '';
    document.getElementById('member-status').value = row.status ?? 'active';
  } else {
    userGroup.classList.remove('d-none');
    await Promise.all([populateUserOptions(), populateBranchOptions()]);
  }
  memberModal.show();
}

async function editMember(id) {
  try {
    const payload = await apiGet('/gym/backend/api/members.php', { id });
    await openMemberModal(payload.data);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  currentPage = 1;
  loadMembers(1);
});

document.getElementById('prev-page').addEventListener('click', () => {
  if (paginationMeta.hasPrev) loadMembers(currentPage - 1);
});
document.getElementById('next-page').addEventListener('click', () => {
  if (paginationMeta.hasNext) loadMembers(currentPage + 1);
});

document.getElementById('member-add-btn').addEventListener('click', () => openMemberModal());

document.getElementById('members-body').addEventListener('click', async (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  if (btn.dataset.action === 'edit') {
    await editMember(id);
  } else if (btn.dataset.action === 'delete') {
    if (!confirmAction('Delete this member? This cannot be undone.')) return;
    try {
      await apiDelete('/gym/backend/api/members.php', id);
      showToast('Member deleted.');
      loadMembers(currentPage);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
});

document.getElementById('member-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('member-form-error');
  errorBox.classList.add('d-none');

  const id = document.getElementById('member-id').value;
  const body = {
    branch_id: Number(document.getElementById('member-branch').value),
    birth_date: document.getElementById('member-birth-date').value,
    gender: document.getElementById('member-gender').value,
    emergency_contact: document.getElementById('member-emergency').value.trim(),
    status: document.getElementById('member-status').value,
  };

  if (!id) {
    body.user_id = Number(document.getElementById('member-user').value);
  }

  try {
    if (id) {
      await apiPut('/gym/backend/api/members.php', Number(id), body);
      showToast('Member updated.');
    } else {
      await apiPost('/gym/backend/api/members.php', body);
      showToast('Member created.');
    }
    memberModal.hide();
    loadMembers(currentPage);
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('d-none');
  }
});

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

document.addEventListener('DOMContentLoaded', () => {
  memberModal = new bootstrap.Modal(document.getElementById('member-modal'));
  loadMembers(1);
});
</script>

<?php renderLayoutEnd(); ?>
