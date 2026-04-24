<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
renderLayoutStart('Dashboard');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
    <small id="last-updated" class="text-muted"></small>
</div>

<div class="dashboard-hero mb-4">
  <div>
    <h5 class="mb-1">GymTrack Network Overview</h5>
    <p class="mb-0">Live summary across Istanbul districts and major city branches.</p>
  </div>
  <span class="hero-pill"><i class="bi bi-activity me-1"></i>Live</span>
</div>

<div class="row g-3" id="stats-row">
  <div class="col-12 text-muted">Loading data...</div>
</div>

<div class="row g-3 mt-1" id="insights-row">
  <div class="col-12 text-muted">Building insights...</div>
</div>

<div class="row g-3 mt-1" id="analytics-row">
  <div class="col-12 text-muted">Loading analytics...</div>
</div>

<script>
const statItems = [
  { key: 'totalMembers', label: 'Active Members', className: 'stat-card stat-members', icon: 'bi-people-fill' },
  { key: 'inactiveMembers', label: 'Passive Members', className: 'stat-card stat-passive', icon: 'bi-person-x-fill' },
  { key: 'totalTrainers', label: 'Active Trainers', className: 'stat-card stat-trainers', icon: 'bi-person-badge-fill' },
  { key: 'totalClasses', label: 'Classes', className: 'stat-card stat-classes', icon: 'bi-calendar2-week-fill' },
  { key: 'totalEquipment', label: 'Active Equipment', className: 'stat-card stat-equipment', icon: 'bi-tools' },
  { key: 'expiringSoon', label: 'Expiring in 7 Days', className: 'stat-card stat-expiring', icon: 'bi-hourglass-split' },
  { key: 'maintenanceDue', label: 'In Maintenance', className: 'stat-card stat-maintenance', icon: 'bi-cone-striped' },
];

async function loadDashboard() {
  const row = document.getElementById('stats-row');
  const insightsRow = document.getElementById('insights-row');
  const analyticsRow = document.getElementById('analytics-row');
  try {
    const payload = await apiGet('/gym/backend/api/dashboard.php');
    const data = payload.data;

    const totalMembers = Number(data.totalMembers ?? 0);
  const inactiveMembers = Number(data.inactiveMembers ?? 0);
    const totalTrainers = Number(data.totalTrainers ?? 0);
    const totalClasses = Number(data.totalClasses ?? 0);
    const totalEquipment = Number(data.totalEquipment ?? 0);
    const expiringSoon = Number(data.expiringSoon ?? 0);
    const maintenanceDue = Number(data.maintenanceDue ?? 0);
  const cityDistribution = Array.isArray(data.cityDistribution) ? data.cityDistribution : [];
  const topBranches = Array.isArray(data.topBranches) ? data.topBranches : [];

  const membersPerTrainer = totalTrainers > 0 ? (totalMembers / totalTrainers).toFixed(1) : '0.0';
    const maintenanceRate = totalEquipment > 0 ? Math.round((maintenanceDue / totalEquipment) * 100) : 0;
  const expiryRate = totalMembers > 0 ? Math.round((expiringSoon / totalMembers) * 100) : 0;
  const passiveRate = (totalMembers + inactiveMembers) > 0 ? Math.round((inactiveMembers / (totalMembers + inactiveMembers)) * 100) : 0;

    row.innerHTML = statItems.map(item => `
      <div class="col-6 col-lg-4">
        <div class="card ${item.className}">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div class="h3 mb-0">${Number(data[item.key] ?? 0)}</div>
              <i class="bi ${item.icon} stat-icon"></i>
            </div>
            <div class="stat-label">${item.label}</div>
          </div>
        </div>
      </div>
    `).join('');

    insightsRow.innerHTML = `
      <div class="col-12 col-xl-4">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Member / Trainer Ratio</div>
            <div class="insight-value">${membersPerTrainer}</div>
            <div class="insight-sub">Balanced staffing improves retention and class quality.</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Maintenance Pressure</div>
            <div class="insight-value">${maintenanceRate}%</div>
            <div class="progress mt-2" role="progressbar" aria-valuenow="${maintenanceRate}" aria-valuemin="0" aria-valuemax="100">
              <div class="progress-bar bg-danger" style="width: ${maintenanceRate}%"></div>
            </div>
            <div class="insight-sub mt-2">${maintenanceDue} equipment unit(s) are currently under maintenance.</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Renewal Window</div>
            <div class="insight-value">${expiringSoon}</div>
            <div class="progress mt-2" role="progressbar" aria-valuenow="${expiryRate}" aria-valuemin="0" aria-valuemax="100">
              <div class="progress-bar bg-warning" style="width: ${expiryRate}%"></div>
            </div>
            <div class="insight-sub mt-2">${expiryRate}% of active members are near renewal in 7 days.</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-4">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Passive Member Share</div>
            <div class="insight-value">${passiveRate}%</div>
            <div class="insight-sub">${inactiveMembers} passive members are currently inactive or suspended.</div>
          </div>
        </div>
      </div>
    `;

    const cityList = cityDistribution.length
      ? cityDistribution.map(item => `<li class="d-flex justify-content-between"><span>${escapeHtml(item.label)}</span><strong>${Number(item.value)}</strong></li>`).join('')
      : '<li class="text-muted">No data</li>';

    const branchList = topBranches.length
      ? topBranches.map(item => `<li class="d-flex justify-content-between"><span class="text-truncate pe-2">${escapeHtml(item.label)}</span><strong>${Number(item.value)}</strong></li>`).join('')
      : '<li class="text-muted">No data</li>';

    analyticsRow.innerHTML = `
      <div class="col-12 col-xl-6">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Top Cities by Active Members</div>
            <ul class="list-unstyled small mt-3 mb-0 d-grid gap-2">${cityList}</ul>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-6">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Top Branches by Active Members</div>
            <ul class="list-unstyled small mt-3 mb-0 d-grid gap-2">${branchList}</ul>
          </div>
        </div>
      </div>
    `;

    document.getElementById('last-updated').textContent =
      'Updated: ' + new Date().toLocaleString('en-GB');
  } catch (error) {
    row.innerHTML = `<div class="col-12"><div class="alert alert-danger">Failed to load dashboard: ${escapeHtml(error.message)}</div></div>`;
    insightsRow.innerHTML = '';
    analyticsRow.innerHTML = '';
  }
}

loadDashboard();
</script>

<?php renderLayoutEnd(); ?>
