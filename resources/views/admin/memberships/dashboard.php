<?php $pageTitle = 'Memberships & Recurring Revenue Hub'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark"><i class="bi bi-repeat text-primary me-2"></i> Memberships & Recurring Revenue</h2>
        <p class="text-muted small mb-0">Monitor Monthly Recurring Revenue (MRR), subscriber retention, renewals, and tier distribution.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('admin/memberships') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-people"></i> Subscriptions List
        </a>
        <a href="<?= url('admin/membership-plans') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-layers"></i> Membership Plans
        </a>
        <a href="<?= url('admin/memberships/export') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <a href="<?= url('admin/membership-plans/create') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> Create Plan
        </a>
    </div>
</div>

<!-- 10 Executive MRR & Subscription KPI Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Monthly Recurring Revenue (MRR) -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface" style="border-left: 4px solid #10B981 !important;">
            <span class="text-muted small fw-semibold d-block mb-1">Monthly Recurring (MRR)</span>
            <h3 class="fw-bold mb-0 text-success"><?= format_rwf($kpis['mrr']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Normalized monthly run-rate</small>
        </div>
    </div>

    <!-- 2. Annual Recurring Revenue (ARR) -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface" style="border-left: 4px solid #4C3103 !important;">
            <span class="text-muted small fw-semibold d-block mb-1">Annual Recurring (ARR)</span>
            <h3 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['arr']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">MRR × 12 annualized</small>
        </div>
    </div>

    <!-- 3. Active Subscribers -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface" style="border-left: 4px solid #3B82F6 !important;">
            <span class="text-muted small fw-semibold d-block mb-1">Active Subscribers</span>
            <h4 class="fw-bold mb-0 text-primary"><?= number_format($kpis['active_subscribers']) ?></h4>
            <small class="text-primary" style="font-size:0.72rem;"><i class="bi bi-person-check-fill"></i> Current members</small>
        </div>
    </div>

    <!-- 4. Average Revenue Per User (ARPU) -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Avg Revenue (ARPU)</span>
            <h4 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['arpu']) ?></h4>
            <small class="text-muted" style="font-size:0.72rem;">Per active member/mo</small>
        </div>
    </div>

    <!-- 5. Churn Rate -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Churn Rate</span>
            <h4 class="fw-bold mb-0 <?= $kpis['churn_rate'] > 5 ? 'text-danger' : 'text-success' ?>"><?= $kpis['churn_rate'] ?>%</h4>
            <small class="text-muted" style="font-size:0.72rem;">Historical cancellations</small>
        </div>
    </div>

    <!-- 6. Trialing Subscribers -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">In Free Trial</span>
            <h5 class="fw-bold mb-0 text-info"><?= number_format($kpis['trialing_subscribers']) ?></h5>
            <small class="text-info" style="font-size:0.72rem;">Trial period</small>
        </div>
    </div>

    <!-- 7. Expired Subscriptions -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Expired</span>
            <h5 class="fw-bold mb-0 text-secondary"><?= number_format($kpis['expired_subscribers']) ?></h5>
            <small class="text-muted" style="font-size:0.72rem;">Past grace period</small>
        </div>
    </div>

    <!-- 8. Cancelled -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Cancelled</span>
            <h5 class="fw-bold mb-0 text-danger"><?= number_format($kpis['cancelled_subscribers']) ?></h5>
            <small class="text-danger" style="font-size:0.72rem;">Churned users</small>
        </div>
    </div>

    <!-- 9. Total Ever -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Total Subscriptions Ever</span>
            <h5 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total_subscriptions']) ?></h5>
            <small class="text-muted" style="font-size:0.72rem;">All recorded subscriptions</small>
        </div>
    </div>

    <!-- 10. Active Plans -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Active Membership Plans</span>
            <h5 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total_plans']) ?></h5>
            <small class="text-muted" style="font-size:0.72rem;">Published tiers</small>
        </div>
    </div>
</div>

<!-- Interactive Chart.js Visualizations -->
<div class="row g-4 mb-4">
    <!-- Subscriber Growth & Cashflow Trends -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-graph-up text-primary me-2"></i> Subscriber Growth & Recurring Cashflow</h5>
                    <small class="text-muted">Active members and renewal payments (Last 14 Days)</small>
                </div>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="mrrChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tier Breakdown -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-pie-chart-fill text-warning me-2"></i> Plan Distribution</h5>
                <a href="<?= url('admin/membership-plans') ?>" class="btn btn-link btn-sm text-decoration-none fw-bold p-0">Manage</a>
            </div>

            <?php if (empty($chartData['plan_breakdown'])): ?>
                <p class="text-muted small">No active subscribers yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($chartData['plan_breakdown'] as $pb): ?>
                        <div class="p-3 bg-light rounded-4 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-dark d-block"><?= e($pb['name']) ?></span>
                                <small class="text-muted text-capitalize"><?= $pb['billing_interval'] ?> Plan</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-primary d-block small"><?= $pb['subscriber_count'] ?> member(s)</span>
                                <small class="text-muted" style="font-size:0.7rem;"><?= format_rwf($pb['total_value']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Subscriptions Feed -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Recent Student Subscriptions</h5>
        <a href="<?= url('admin/memberships') ?>" class="btn btn-link btn-sm text-decoration-none fw-bold p-0">Full Subscriptions Hub &rarr;</a>
    </div>

    <?php if (empty($recentSubscriptions)): ?>
        <p class="text-muted small mb-0">No subscriptions recorded yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Subscription #</th>
                        <th>Student</th>
                        <th>Plan</th>
                        <th>Interval</th>
                        <th>Status</th>
                        <th>End Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSubscriptions as $sub): ?>
                        <tr>
                            <td><code><?= e($sub['subscription_number']) ?></code></td>
                            <td>
                                <div class="fw-bold"><?= e($sub['user_name']) ?></div>
                                <small class="text-muted"><?= e($sub['user_email']) ?></small>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary border"><?= e($sub['plan_name']) ?></span></td>
                            <td class="text-capitalize"><?= e($sub['billing_interval']) ?></td>
                            <td>
                                <span class="badge <?= $sub['status'] === 'active' ? 'bg-success' : ($sub['status'] === 'trialing' ? 'bg-info text-dark' : 'bg-secondary') ?>">
                                    <?= e($sub['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($sub['end_date'])) ?></td>
                            <td class="text-end">
                                <a href="<?= url('admin/memberships/' . $sub['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2">Workspace</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('mrrChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartData['labels']) ?>,
            datasets: [
                {
                    type: 'line',
                    label: 'Active Subscribers Count',
                    data: <?= json_encode($chartData['subscribers_series']) ?>,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    yAxisID: 'ySubs',
                    tension: 0.3
                },
                {
                    type: 'bar',
                    label: 'Recurring Revenue (RWF)',
                    data: <?= json_encode($chartData['revenue_series']) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderRadius: 6,
                    yAxisID: 'yRev'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                ySubs: {
                    type: 'linear',
                    position: 'left',
                    ticks: { precision: 0 }
                },
                yRev: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { callback: function(v) { return v.toLocaleString() + ' RWF'; } }
                }
            }
        }
    });
});
</script>
