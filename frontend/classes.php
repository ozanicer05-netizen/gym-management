<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Dersler');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-calendar3 me-2"></i>Dersler</h4>
</div>

<div class="card mb-3"><div class="card-body">
<form id="filter-form" class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label form-label-sm">Ara</label><input id="search" class="form-control form-control-sm" placeholder="Ders adı, şube, eğitmen"></div>
  <div class="col-md-3"><label class="form-label form-label-sm">Seviye</label><select id="level" class="form-select form-select-sm"><option value="">Tümü</option><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select></div>
  <div class="col-auto"><button class="btn btn-sm btn-primary" type="submit">Filtrele</button></div>
</form>
</div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Ders</th><th>Eğitmen</th><th>Şube</th><th>Kapasite</th><th>Süre (dk)</th><th>Seviye</th></tr></thead>
<tbody id="classes-body"><tr><td colspan="7" class="text-center py-4 text-muted">Yükleniyor...</td></tr></tbody>
</table>
</div></div></div>

<script>
async function loadClasses() {
  const tbody = document.getElementById('classes-body');
  const search = document.getElementById('search').value.trim();
  const level = document.getElementById('level').value;

  tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Yükleniyor...</td></tr>';

  try {
    const payload = await apiGet('/gym/backend/api/classes.php', { search, level, limit: 100 });
    const rows = payload.data;

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>';
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
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Yüklenemedi: ${escapeHtml(error.message)}</td></tr>`;
  }
}

document.getElementById('filter-form').addEventListener('submit', e => {
  e.preventDefault();
  loadClasses();
});

loadClasses();
</script>

<?php renderLayoutEnd(); ?>
