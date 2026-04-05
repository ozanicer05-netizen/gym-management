<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Üyeler');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-people me-2"></i>Üye Listesi</h4>
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
                        <th>Şube</th>
                        <th>Katılım</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody id="members-body">
                    <tr><td colspan="7" class="text-center py-4 text-muted">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function loadMembers() {
        const tbody = document.getElementById('members-body');

        const search = document.getElementById('search').value.trim();
        const status = document.getElementById('status').value;

        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Yükleniyor...</td></tr>';

        try {
            const payload = await apiGet('/gym/backend/api/members.php', {
                search,
                status,
                limit: 100
            });

            const rows = payload.data;

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Üye bulunamadı.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map((row) => `
                <tr>
                    <td>${Number(row.member_id)}</td>
                    <td>${escapeHtml(row.name)} ${escapeHtml(row.surname)}</td>
                    <td>${escapeHtml(row.email)}</td>
                    <td>${escapeHtml(row.phone ?? '')}</td>
                    <td>${escapeHtml(row.branch_name ?? '')}</td>
                    <td>${escapeHtml(row.join_date ?? '')}</td>
                    <td>
                        <span class="badge ${row.status === 'active' ? 'badge-aktif' : 'badge-pasif'}">
                            ${row.status === 'active' ? 'Aktif' : 'Pasif'}
                        </span>
                    </td>
                </tr>
            `).join('');
        } catch (error) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger py-4">
                        Liste yüklenemedi: ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;
        }
    }

    document.getElementById('filter-form').addEventListener('submit', (event) => {
        event.preventDefault();
        loadMembers();
    });

    loadMembers();
</script>

<?php renderLayoutEnd(); ?>
