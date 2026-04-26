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

<!-- Stat Cards -->
<div class="row g-3" id="stats-row">
  <div class="col-12 text-muted">Loading data...</div>
</div>

<!-- Revenue + Subscription Breakdown -->
<div class="row g-3 mt-1" id="revenue-row">
  <div class="col-12 text-muted">Loading revenue data...</div>
</div>

<!-- KPI Insights -->
<div class="row g-3 mt-1" id="insights-row">
  <div class="col-12 text-muted">Building insights...</div>
</div>

<!-- Charts Row -->
<div class="row g-3 mt-1" id="charts-row">
  <div class="col-12 text-muted">Loading charts...</div>
</div>

<!-- Analytics Lists + Recent Activity -->
<div class="row g-3 mt-1" id="analytics-row">
  <div class="col-12 text-muted">Loading analytics...</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const statItems = [
  { key: 'totalMembers',   label: 'Active Members',    className: 'stat-card stat-members',     icon: 'bi-people-fill' },
  { key: 'inactiveMembers',label: 'Passive Members',   className: 'stat-card stat-passive',     icon: 'bi-person-x-fill' },
  { key: 'totalTrainers',  label: 'Active Trainers',   className: 'stat-card stat-trainers',    icon: 'bi-person-badge-fill' },
  { key: 'totalClasses',   label: 'Classes',           className: 'stat-card stat-classes',     icon: 'bi-calendar2-week-fill' },
  { key: 'totalEquipment', label: 'Active Equipment',  className: 'stat-card stat-equipment',   icon: 'bi-tools' },
  { key: 'expiringSoon',   label: 'Expiring in 7 Days',className: 'stat-card stat-expiring',    icon: 'bi-hourglass-split' },
  { key: 'maintenanceDue', label: 'In Maintenance',    className: 'stat-card stat-maintenance', icon: 'bi-cone-striped' },
];

const activityIcons = {
  'New Member':       { icon: 'bi-person-plus-fill',    color: '#22c55e' },
  'New Subscription': { icon: 'bi-credit-card-fill',    color: '#3b82f6' },
  'Payment Received': { icon: 'bi-cash-coin',           color: '#f59e0b' },
};

let revenueChart = null;
let branchChart  = null;

async function loadDashboard() {
  const statsRow    = document.getElementById('stats-row');
  const revenueRow  = document.getElementById('revenue-row');
  const insightsRow = document.getElementById('insights-row');
  const chartsRow   = document.getElementById('charts-row');
  const analyticsRow= document.getElementById('analytics-row');

  try {
    const payload = await apiGet('/gym/backend/api/dashboard.php');
    const data = payload.data;

    const totalMembers   = Number(data.totalMembers   ?? 0);
    const inactiveMembers= Number(data.inactiveMembers?? 0);
    const totalTrainers  = Number(data.totalTrainers  ?? 0);
    const totalEquipment = Number(data.totalEquipment ?? 0);
    const expiringSoon   = Number(data.expiringSoon   ?? 0);
    const maintenanceDue = Number(data.maintenanceDue ?? 0);
    const monthlyRevenue = Number(data.monthlyRevenue ?? 0);
    const breakdown      = Array.isArray(data.subscriptionBreakdown) ? data.subscriptionBreakdown : [];
    const recentActivity = Array.isArray(data.recentActivity) ? data.recentActivity : [];
    const cityDistrib        = Array.isArray(data.cityDistribution) ? data.cityDistribution : [];
    const topBranches        = Array.isArray(data.topBranches) ? data.topBranches : [];
    const revenueChart_      = Array.isArray(data.monthlyRevenueChart) ? data.monthlyRevenueChart : [];
    const branchRevenue      = Array.isArray(data.branchRevenue) ? data.branchRevenue : [];
    const packageProfit      = Array.isArray(data.packageProfitability) ? data.packageProfitability : [];

    const membersPerTrainer = totalTrainers > 0 ? (totalMembers / totalTrainers).toFixed(1) : '0.0';
    const maintenanceRate   = totalEquipment > 0 ? Math.round((maintenanceDue / totalEquipment) * 100) : 0;
    const expiryRate        = totalMembers > 0 ? Math.round((expiringSoon / totalMembers) * 100) : 0;
    const passiveRate       = (totalMembers + inactiveMembers) > 0
      ? Math.round((inactiveMembers / (totalMembers + inactiveMembers)) * 100) : 0;

    // ── Stat Cards ──────────────────────────────────────────────────────────
    statsRow.innerHTML = statItems.map(item => `
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

    // ── Revenue + Subscription Breakdown ────────────────────────────────────
    const breakdownColors = { active: 'bg-success', expired: 'bg-secondary', cancelled: 'bg-danger' };
    const breakdownTotal  = breakdown.reduce((s, r) => s + Number(r.value), 0);
    const breakdownBars   = breakdown.map(r => {
      const pct = breakdownTotal > 0 ? Math.round((Number(r.value) / breakdownTotal) * 100) : 0;
      const cls = breakdownColors[r.label] ?? 'bg-secondary';
      return `<div class="progress-bar ${cls}" style="width:${pct}%" title="${r.label}: ${r.value}">${pct > 8 ? r.label : ''}</div>`;
    }).join('');

    const breakdownList = breakdown.map(r => {
      const pct = breakdownTotal > 0 ? Math.round((Number(r.value) / breakdownTotal) * 100) : 0;
      const cls = breakdownColors[r.label] ?? 'bg-secondary';
      return `<div class="d-flex justify-content-between align-items-center small">
        <span><span class="badge ${cls} me-1">&nbsp;</span>${escapeHtml(r.label)}</span>
        <strong>${Number(r.value)} <span class="text-muted">(${pct}%)</span></strong>
      </div>`;
    }).join('');

    revenueRow.innerHTML = `
      <div class="col-12 col-xl-4">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title"><i class="bi bi-cash-stack me-1 text-success"></i>Revenue This Month</div>
            <div class="insight-value text-success">$${monthlyRevenue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
            <div class="insight-sub">Total confirmed payments in the current calendar month.</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-8">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title"><i class="bi bi-pie-chart me-1 text-primary"></i>Subscription Status Breakdown</div>
            <div class="progress mt-3 mb-2" style="height:24px;border-radius:6px;">${breakdownBars || '<div class="progress-bar bg-secondary" style="width:100%">No data</div>'}</div>
            <div class="d-grid gap-1 mt-2">${breakdownList || '<span class="text-muted small">No subscription data.</span>'}</div>
          </div>
        </div>
      </div>
    `;

    // ── KPI Insights ─────────────────────────────────────────────────────────
    insightsRow.innerHTML = `
      <div class="col-12 col-xl-3">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Member / Trainer Ratio</div>
            <div class="insight-value">${membersPerTrainer}</div>
            <div class="insight-sub">members per trainer on average.</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Maintenance Pressure</div>
            <div class="insight-value">${maintenanceRate}%</div>
            <div class="progress mt-2" style="height:6px;">
              <div class="progress-bar bg-danger" style="width:${maintenanceRate}%"></div>
            </div>
            <div class="insight-sub mt-2">${maintenanceDue} unit(s) under maintenance.</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Renewal Window</div>
            <div class="insight-value">${expiringSoon}</div>
            <div class="progress mt-2" style="height:6px;">
              <div class="progress-bar bg-warning" style="width:${expiryRate}%"></div>
            </div>
            <div class="insight-sub mt-2">${expiryRate}% of members renew in 7 days.</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-3">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title">Passive Member Share</div>
            <div class="insight-value">${passiveRate}%</div>
            <div class="progress mt-2" style="height:6px;">
              <div class="progress-bar bg-secondary" style="width:${passiveRate}%"></div>
            </div>
            <div class="insight-sub mt-2">${inactiveMembers} inactive or suspended.</div>
          </div>
        </div>
      </div>
    `;

    // ── Charts ───────────────────────────────────────────────────────────────
    chartsRow.innerHTML = `
      <div class="col-12 col-xl-6">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title"><i class="bi bi-bar-chart-fill me-1 text-primary"></i>Monthly Revenue (Last 6 Months)</div>
            <div style="position:relative;height:220px;margin-top:12px;">
              <canvas id="revenueChartCanvas"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-6">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title"><i class="bi bi-building me-1 text-info"></i>Branch Revenue This Month</div>
            <div style="position:relative;height:220px;margin-top:12px;">
              <canvas id="branchRevenueChartCanvas"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-6">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title"><i class="bi bi-trophy-fill me-1 text-warning"></i>Most Profitable Packages</div>
            <div style="position:relative;height:220px;margin-top:12px;">
              <canvas id="packageChartCanvas"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-6">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title"><i class="bi bi-people-fill me-1 text-success"></i>Top Branches by Members</div>
            <div style="position:relative;height:220px;margin-top:12px;">
              <canvas id="branchChartCanvas"></canvas>
            </div>
          </div>
        </div>
      </div>
    `;

    // Revenue chart
    if (revenueChart) revenueChart.destroy();
    revenueChart = new Chart(document.getElementById('revenueChartCanvas'), {
      type: 'bar',
      data: {
        labels: revenueChart_.map(r => r.label),
        datasets: [{
          label: 'Revenue ($)',
          data: revenueChart_.map(r => Number(r.value)),
          backgroundColor: 'rgba(59,130,246,0.7)',
          borderRadius: 5,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } },
          x: { grid: { display: false } }
        }
      }
    });

    // Branch revenue chart
    if (branchChart) branchChart.destroy();
    branchChart = new Chart(document.getElementById('branchRevenueChartCanvas'), {
      type: 'bar',
      data: {
        labels: branchRevenue.map(r => r.label),
        datasets: [{
          label: 'Revenue ($)',
          data: branchRevenue.map(r => Number(r.value)),
          backgroundColor: 'rgba(99,102,241,0.7)',
          borderRadius: 5,
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } },
          y: { grid: { display: false } }
        }
      }
    });

    // Package profitability chart
    new Chart(document.getElementById('packageChartCanvas'), {
      type: 'bar',
      data: {
        labels: packageProfit.map(r => r.label),
        datasets: [
          {
            label: 'Total Revenue ($)',
            data: packageProfit.map(r => Number(r.total_revenue)),
            backgroundColor: 'rgba(245,158,11,0.7)',
            borderRadius: 5,
            yAxisID: 'y',
          },
          {
            label: 'Subscriptions',
            data: packageProfit.map(r => Number(r.subscription_count)),
            backgroundColor: 'rgba(239,68,68,0.5)',
            borderRadius: 5,
            yAxisID: 'y1',
          }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: {
          y:  { beginAtZero: true, position: 'left',  ticks: { callback: v => '$' + v.toLocaleString() } },
          y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } },
          x:  { grid: { display: false } }
        }
      }
    });

    // Top branches by member count chart
    new Chart(document.getElementById('branchChartCanvas'), {
      type: 'bar',
      data: {
        labels: topBranches.map(r => r.label),
        datasets: [{
          label: 'Active Members',
          data: topBranches.map(r => Number(r.value)),
          backgroundColor: 'rgba(16,185,129,0.7)',
          borderRadius: 5,
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { beginAtZero: true },
          y: { grid: { display: false } }
        }
      }
    });

    // ── Analytics + Recent Activity ──────────────────────────────────────────
    const cityList = cityDistrib.length
      ? cityDistrib.map(r => `
          <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
            <span><i class="bi bi-geo-alt text-muted me-1"></i>${escapeHtml(r.label)}</span>
            <strong>${Number(r.value)}</strong>
          </li>`).join('')
      : '<li class="text-muted small">No data</li>';

    const activityList = recentActivity.length
      ? recentActivity.map(r => {
          const meta  = activityIcons[r.type] ?? { icon: 'bi-dot', color: '#94a3b8' };
          let date = '';
          if (r.happened_at) {
            const iso = String(r.happened_at).replace(' ', 'T');
            const d   = new Date(iso);
            date = isNaN(d.getTime()) ? String(r.happened_at) : d.toLocaleString('en-GB', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' });
          }
          return `
            <li class="d-flex align-items-start gap-2 py-2 border-bottom">
              <i class="bi ${meta.icon} mt-1 flex-shrink-0" style="color:${meta.color};font-size:1rem;"></i>
              <div class="flex-grow-1 min-width-0">
                <div class="small fw-semibold">${escapeHtml(r.type)}</div>
                <div class="small text-truncate">${escapeHtml(r.subject)} — <span class="text-muted">${escapeHtml(r.detail)}</span></div>
              </div>
              <small class="text-muted flex-shrink-0">${escapeHtml(date)}</small>
            </li>`;
        }).join('')
      : '<li class="text-muted small">No recent activity.</li>';

    analyticsRow.innerHTML = `
      <div class="col-12 col-xl-4">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title"><i class="bi bi-geo me-1 text-warning"></i>Top Cities by Active Members</div>
            <ul class="list-unstyled mb-0 mt-2">${cityList}</ul>
          </div>
        </div>
      </div>
      <div class="col-12 col-xl-8">
        <div class="card insight-card h-100">
          <div class="card-body">
            <div class="insight-title"><i class="bi bi-clock-history me-1 text-secondary"></i>Recent Activity</div>
            <ul class="list-unstyled mb-0 mt-1">${activityList}</ul>
          </div>
        </div>
      </div>
    `;

    document.getElementById('last-updated').textContent =
      'Updated: ' + new Date().toLocaleString('en-GB');

  } catch (error) {
    statsRow.innerHTML = `<div class="col-12"><div class="alert alert-danger">Failed to load dashboard: ${escapeHtml(error.message)}</div></div>`;
    revenueRow.innerHTML = insightsRow.innerHTML = chartsRow.innerHTML = analyticsRow.innerHTML = '';
  }
}

loadDashboard();
</script>

<?php renderLayoutEnd(); ?>
