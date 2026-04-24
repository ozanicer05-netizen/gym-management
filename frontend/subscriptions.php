<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Subscriptions');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-card-checklist me-2"></i>Subscriptions</h4>
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
<thead><tr><th>#</th><th>Member</th><th>Package</th><th>Start Date</th><th>End Date</th><th>Status</th></tr></thead>
<tbody id="subscriptions-body"><tr><td colspan="6" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="subscriptions-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="subscriptions-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="subscriptions-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<script>
let subscriptionsPage = 1;
let subscriptionsMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };

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

  tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
  const payload = await apiGet('/gym/backend/api/subscriptions.php', { search, status, limit: 50, page });
    const rows = payload.data;
  subscriptionsMeta = payload.meta || subscriptionsMeta;
  subscriptionsPage = Number(subscriptionsMeta.page ?? page);
  renderSubscriptionsPagination(subscriptionsMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No records found.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(row => `
      <tr>
        <td>${Number(row.subscription_id)}</td>
        <td>${escapeHtml(row.member_name)}</td>
        <td>${escapeHtml(row.package_name)}</td>
        <td>${escapeHtml(row.start_date)}</td>
        <td>${escapeHtml(row.end_date)}</td>
        <td>${escapeHtml(row.status)}</td>
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderSubscriptionsPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  subscriptionsPage = 1;
  loadSubscriptions(1);
});

document.getElementById('subscriptions-prev-page').addEventListener('click', () => {
  if (subscriptionsMeta.hasPrev) {
    loadSubscriptions(subscriptionsPage - 1);
  }
});

document.getElementById('subscriptions-next-page').addEventListener('click', () => {
  if (subscriptionsMeta.hasNext) {
    loadSubscriptions(subscriptionsPage + 1);
  }
});

loadSubscriptions(1);
</script>

<?php renderLayoutEnd(); ?>
