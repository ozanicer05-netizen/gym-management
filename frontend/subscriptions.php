<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Subscriptions');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-card-checklist me-2"></i>Subscriptions</h4>
    <button id="subscription-add-btn" type="button" class="btn btn-sm btn-success"><i class="bi bi-plus-lg me-1"></i>New Subscription</button>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Search</label><input id="search" class="form-control form-control-sm" placeholder="Member or package"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Status</label><select id="status" class="form-select form-select-sm"><option value="">All</option><option value="active">Active</option><option value="expired">Expired</option><option value="cancelled">Cancelled</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filter</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Member</th><th>Package</th><th>Start Date</th><th>End Date</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
<tbody id="subscriptions-body"><tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="subscriptions-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="subscriptions-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="subscriptions-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<div class="modal fade" id="subscription-modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="subscription-form">
        <div class="modal-header">
          <h5 class="modal-title" id="subscription-modal-title">New Subscription</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="subscription-id">
          <div class="mb-2"><label class="form-label form-label-sm">Member *</label><select id="subscription-member" class="form-select form-select-sm" required></select></div>
          <div class="mb-2"><label class="form-label form-label-sm">Package *</label><select id="subscription-package" class="form-select form-select-sm" required></select></div>
          <div class="row g-2">
            <div class="col-6 mb-2"><label class="form-label form-label-sm">Start Date *</label><input id="subscription-start" type="date" class="form-control form-control-sm" required></div>
            <div class="col-6 mb-2"><label class="form-label form-label-sm">End Date *</label><input id="subscription-end" type="date" class="form-control form-control-sm" required></div>
          </div>
          <div class="mb-2"><label class="form-label form-label-sm">Status</label>
            <select id="subscription-status" class="form-select form-select-sm"><option value="active">Active</option><option value="expired">Expired</option><option value="cancelled">Cancelled</option></select>
          </div>
          <div id="subscription-form-error" class="alert alert-danger d-none mb-0"></div>
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
let subscriptionsPage = 1;
let subscriptionsMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };
let subscriptionModal;

function renderSubscriptionsPagination(meta) {
  const info = document.getElementById('subscriptions-pagination-info');
  const prevBtn = document.getElementById('subscriptions-prev-page');
  const nextBtn = document.getElementById('subscriptions-next-page');
  const page = Number(meta.page ?? 1);
  const totalPages = Number(meta.totalPages ?? 1);
  const total = Number(meta.total ?? 0);

  info.textContent = `Page ${page} / ${totalPages} • Total ${total} subscription(s)`;
  prevBtn.disabled = !meta.hasPrev;
  nextBtn.disabled = !meta.hasNext;
}

async function loadSubscriptions(page = 1) {
  const tbody = document.getElementById('subscriptions-body');
  const search = document.getElementById('search').value.trim();
  const status = document.getElementById('status').value;

  tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/subscriptions.php', { search, status, limit: 50, page });
    const rows = payload.data;
    subscriptionsMeta = payload.meta || subscriptionsMeta;
    subscriptionsPage = Number(subscriptionsMeta.page ?? page);
    renderSubscriptionsPagination(subscriptionsMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No records found.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.subscription_id)}</td>
        <td>${escapeHtml(row.member_name)}</td>
        <td>${escapeHtml(row.package_name)}</td>
        <td>${escapeHtml(row.start_date)}</td>
        <td>${escapeHtml(row.end_date)}</td>
        <td><span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">${escapeHtml(row.status)}</span></td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${Number(row.subscription_id)}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${Number(row.subscription_id)}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderSubscriptionsPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

async function populateSubscriptionMembers(selectedId = '') {
  const members = await loadLookup('members');
  document.getElementById('subscription-member').innerHTML = '<option value="">-- Select Member --</option>' + members.map(m =>
    `<option value="${Number(m.member_id)}" ${Number(selectedId) === Number(m.member_id) ? 'selected' : ''}>${escapeHtml(m.member_name)}</option>`
  ).join('');
}

async function populateSubscriptionPackages(selectedId = '') {
  const packages = await loadLookup('packages');
  document.getElementById('subscription-package').innerHTML = '<option value="">-- Select Package --</option>' + packages.map(p =>
    `<option value="${Number(p.package_id)}" ${Number(selectedId) === Number(p.package_id) ? 'selected' : ''}>${escapeHtml(p.package_name)} (${Number(p.duration_days)} days)</option>`
  ).join('');
}

async function openSubscriptionModal(row = null) {
  document.getElementById('subscription-form-error').classList.add('d-none');
  document.getElementById('subscription-form').reset();
  document.getElementById('subscription-id').value = row ? row.subscription_id : '';
  document.getElementById('subscription-modal-title').textContent = row ? 'Edit Subscription' : 'New Subscription';

  await Promise.all([
    populateSubscriptionMembers(row ? row.member_id : ''),
    populateSubscriptionPackages(row ? row.package_id : ''),
  ]);

  if (row) {
    document.getElementById('subscription-start').value = row.start_date ?? '';
    document.getElementById('subscription-end').value = row.end_date ?? '';
    document.getElementById('subscription-status').value = row.status ?? 'active';
  }
  subscriptionModal.show();
}

async function editSubscription(id) {
  try {
    const payload = await apiGet('/gym/backend/api/subscriptions.php', { id });
    await openSubscriptionModal(payload.data);
  } catch (err) {
    showToast(err.message, 'error');
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  subscriptionsPage = 1;
  loadSubscriptions(1);
});

document.getElementById('subscriptions-prev-page').addEventListener('click', () => {
  if (subscriptionsMeta.hasPrev) loadSubscriptions(subscriptionsPage - 1);
});
document.getElementById('subscriptions-next-page').addEventListener('click', () => {
  if (subscriptionsMeta.hasNext) loadSubscriptions(subscriptionsPage + 1);
});

document.getElementById('subscription-add-btn').addEventListener('click', () => openSubscriptionModal());

document.getElementById('subscriptions-body').addEventListener('click', async (e) => {
  const btn = e.target.closest('button[data-action]');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  if (btn.dataset.action === 'edit') {
    await editSubscription(id);
  } else if (btn.dataset.action === 'delete') {
    if (!confirmAction('Delete this subscription? This cannot be undone.')) return;
    try {
      await apiDelete('/gym/backend/api/subscriptions.php', id);
      showToast('Subscription deleted.');
      loadSubscriptions(subscriptionsPage);
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
});

document.getElementById('subscription-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('subscription-form-error');
  errorBox.classList.add('d-none');

  const id = document.getElementById('subscription-id').value;
  const body = {
    member_id: Number(document.getElementById('subscription-member').value),
    package_id: Number(document.getElementById('subscription-package').value),
    start_date: document.getElementById('subscription-start').value,
    end_date: document.getElementById('subscription-end').value,
    status: document.getElementById('subscription-status').value,
  };

  try {
    if (id) {
      await apiPut('/gym/backend/api/subscriptions.php', Number(id), body);
      showToast('Subscription updated.');
    } else {
      await apiPost('/gym/backend/api/subscriptions.php', body);
      showToast('Subscription created.');
    }
    subscriptionModal.hide();
    loadSubscriptions(subscriptionsPage);
  } catch (err) {
    errorBox.textContent = err.message;
    errorBox.classList.remove('d-none');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  subscriptionModal = new bootstrap.Modal(document.getElementById('subscription-modal'));
  loadSubscriptions(1);
});
</script>

<?php renderLayoutEnd(); ?>
