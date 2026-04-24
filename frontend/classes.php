<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Classes');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-calendar3 me-2"></i>Classes</h4>
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
<thead><tr><th>#</th><th>Class</th><th>Trainer</th><th>Branch</th><th>Capacity</th><th>Duration (min)</th><th>Level</th></tr></thead>
<tbody id="classes-body"><tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr></tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between align-items-center mt-3">
  <small id="classes-pagination-info" class="text-muted">Page 1</small>
  <div class="btn-group btn-group-sm">
    <button id="classes-prev-page" class="btn btn-outline-secondary" type="button">Previous</button>
    <button id="classes-next-page" class="btn btn-outline-secondary" type="button">Next</button>
  </div>
</div>

<script>
let classesPage = 1;
let classesMeta = { totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 };

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

  tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Loading...</td></tr>';

  try {
  const payload = await apiGet('/gym/backend/api/classes.php', { search, level, limit: 50, page });
    const rows = payload.data;
  classesMeta = payload.meta || classesMeta;
  classesPage = Number(classesMeta.page ?? page);
  renderClassesPagination(classesMeta);

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No records found.</td></tr>';
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
      </tr>
    `).join('');
  } catch (error) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Failed to load: ${escapeHtml(error.message)}</td></tr>`;
    renderClassesPagination({ totalPages: 1, hasPrev: false, hasNext: false, total: 0, page: 1 });
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  classesPage = 1;
  loadClasses(1);
});

document.getElementById('classes-prev-page').addEventListener('click', () => {
  if (classesMeta.hasPrev) {
    loadClasses(classesPage - 1);
  }
});

document.getElementById('classes-next-page').addEventListener('click', () => {
  if (classesMeta.hasNext) {
    loadClasses(classesPage + 1);
  }
});

loadClasses(1);
</script>

<?php renderLayoutEnd(); ?>
