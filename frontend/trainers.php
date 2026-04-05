<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Antrenörler');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Antrenör Listesi</h4>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form id="filter-form" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label form-label-sm">Ara</label>
                <input id="search" class="form-control form-control-sm" placeholder="Ad, soyad, email">
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm">Durum</label>
                <select id="status" class="form-select form-select-sm">
                    <option value="">Tümü</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Pasif</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filtrele</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Telefon</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody id="trainers-body">
                    <tr><td colspan="5" class="text-center py-4 text-muted">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function loadTrainers() {
        const tbody = document.getElementById('trainers-body');

        const search = document.getElementById('search').value.trim();
        const status = document.getElementById('status').value;

        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Yükleniyor...</td></tr>';

        try {
            const payload = await apiGet('/gym/backend/api/trainers.php', {
                search,
                status,
                limit: 100
            });

            const rows = payload.data;

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Antrenör bulunamadı.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map((row) => `
                <tr>
                    <td>${Number(row.trainer_id)}</td>
                    <td>${escapeHtml(row.name)} ${escapeHtml(row.surname)}</td>
                    <td>${escapeHtml(row.email)}</td>
                    <td>${escapeHtml(row.phone ?? '')}</td>
                    <td>
                        <span class="badge ${row.availability_status === 'active' ? 'badge-aktif' : 'badge-pasif'}">
                            ${row.availability_status === 'active' ? 'Aktif' : 'Pasif'}
                        </span>
                    </td>
                </tr>
            `).join('');
        } catch (error) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger py-4">
                        Liste yüklenemedi: ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;
        }
    }

    document.getElementById('filter-form').addEventListener('submit', (event) => {
        event.preventDefault();
        loadTrainers();
    });

    loadTrainers();
</script>

<?php renderLayoutEnd(); ?>
