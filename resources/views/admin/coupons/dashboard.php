<?php $pageTitle = 'Promotions & Discounts Dashboard'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Promotions & Discounts Hub</h2>
        <p class="text-muted small mb-0">Manage promotional campaigns, coupon redemptions, student discount codes, and revenue impact.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('admin/coupons') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-ticket-perforated"></i> Coupons List
        </a>
        <a href="<?= url('admin/campaigns') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-megaphone"></i> Campaigns
        </a>
        <a href="<?= url('admin/coupons/redemptions') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-journal-check"></i> Redemptions Ledger
        </a>
        <a href="<?= url('admin/coupons/bulk-generate') ?>" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-magic"></i> Bulk Generator
        </a>
        <a href="<?= url('admin/coupons/create') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> Create Coupon
        </a>
    </div>
</div>

<!-- Date Filter Toolbar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-surface">
    <form action="<?= url('admin/coupons/dashboard') ?>" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap align-items-center gap-1">
            <span class="small fw-bold text-muted me-2"><i class="bi bi-calendar-range me-1"></i> Period:</span>
            <a href="<?= url('admin/coupons/dashboard?range=today') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'today' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">Today</a>
            <a href="<?= url('admin/coupons/dashboard?range=yesterday') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'yesterday' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">Yesterday</a>
            <a href="<?= url('admin/coupons/dashboard?range=last_7_days') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'last_7_days' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">Last 7 Days</a>
            <a href="<?= url('admin/coupons/dashboard?range=this_month') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">This Month</a>
            <a href="<?= url('admin/coupons/dashboard?range=last_month') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'last_month' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">Last Month</a>
            <a href="<?= url('admin/coupons/dashboard?range=this_year') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'this_year' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">This Year</a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($filters['start_date'] ?? '') ?>">
            <span class="text-muted small">to</span>
            <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($filters['end_date'] ?? '') ?>">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">Apply</button>
        </div>
    </form>
</div>

<!-- 10 Executive Promotion KPI Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Total Redemptions -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface" style="border-left: 4px solid #10B981 !important;">
            <span class="text-muted small fw-semibold d-block mb-1">Total Redemptions</span>
            <h4 class="fw-bold mb-0 text-success"><?= number_format($kpis['total_redemptions']) ?></h4>
            <small class="text-success" style="font-size:0.72rem;"><i class="bi bi-check-circle-fill"></i> Completed Sales</small>
        </div>
    </div>

    <!-- 2. Revenue Generated -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface" style="border-left: 4px solid #4C3103 !important;">
            <span class="text-muted small fw-semibold d-block mb-1">Revenue via Coupons</span>
            <h3 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['revenue_generated']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Gross order payments</small>
        </div>
    </div>

    <!-- 3. Total Discount Given -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface" style="border-left: 4px solid #F59E0B !important;">
            <span class="text-muted small fw-semibold d-block mb-1">Total Discount Given</span>
            <h3 class="fw-bold mb-0 text-warning"><?= format_rwf($kpis['total_discount_given']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Promotional savings</small>
        </div>
    </div>

    <!-- 4. Average Discount -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Avg Discount / Order</span>
            <h4 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['avg_discount']) ?></h4>
            <small class="text-muted" style="font-size:0.72rem;">Per redemption</small>
        </div>
    </div>

    <!-- 5. Active Coupons -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Active Coupons</span>
            <h4 class="fw-bold mb-0 text-primary"><?= number_format($kpis['active_coupons']) ?></h4>
            <small class="text-primary" style="font-size:0.72rem;">Ready for checkout</small>
        </div>
    </div>

    <!-- 6. Total Coupons -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Total Codes</span>
            <h5 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total_coupons']) ?></h5>
            <small class="text-muted" style="font-size:0.72rem;">All promotional codes</small>
        </div>
    </div>

    <!-- 7. Scheduled -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Upcoming Scheduled</span>
            <h5 class="fw-bold mb-0 text-info"><?= number_format($kpis['scheduled_coupons']) ?></h5>
            <small class="text-info" style="font-size:0.72rem;">Future start date</small>
        </div>
    </div>

    <!-- 8. Expired -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Expired</span>
            <h5 class="fw-bold mb-0 text-secondary"><?= number_format($kpis['expired_coupons']) ?></h5>
            <small class="text-muted" style="font-size:0.72rem;">Past expiry date</small>
        </div>
    </div>

    <!-- 9. Disabled -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Disabled</span>
            <h5 class="fw-bold mb-0 text-danger"><?= number_format($kpis['disabled_coupons']) ?></h5>
            <small class="text-danger" style="font-size:0.72rem;">Turned off</small>
        </div>
    </div>

    <!-- 10. Campaigns -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Marketing Campaigns</span>
            <h5 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total_campaigns']) ?></h5>
            <small class="text-muted" style="font-size:0.72rem;">Active campaigns</small>
        </div>
    </div>
</div>

<!-- Interactive Chart.js Analytics Rows -->
<div class="row g-4 mb-4">
    <!-- Redemptions & Revenue Trend -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-graph-up text-primary me-2"></i> Promotional Redemptions & Revenue Trend</h5>
                    <small class="text-muted">Daily coupon redemptions and gross sales generated (Last 14 Days)</small>
                </div>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="couponTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Performing Coupons -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-trophy-fill text-warning me-2"></i> Top Coupons</h5>
                <a href="<?= url('admin/coupons') ?>" class="btn btn-link btn-sm text-decoration-none fw-bold p-0">View All</a>
            </div>

            <?php if (empty($chartData['top_coupons'])): ?>
                <p class="text-muted small">No redemptions recorded yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($chartData['top_coupons'] as $tc): ?>
                        <div class="p-3 bg-light rounded-4 d-flex justify-content-between align-items-center">
                            <div>
                                <a href="<?= url('admin/coupons/' . $tc['id']) ?>" class="fw-bold font-monospace text-decoration-none text-dark d-block">
                                    <?= e($tc['code']) ?>
                                </a>
                                <small class="text-muted"><?= $tc['redemptions_count'] ?> use(s) • <?= $tc['discount_type'] === 'percentage' ? (float)$tc['discount_value'] . '%' : format_money($tc['discount_value'], $tc['currency']) ?></small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-success d-block small"><?= format_rwf($tc['total_revenue']) ?></span>
                                <small class="text-muted" style="font-size:0.7rem;">Saved: <?= format_rwf($tc['total_discount']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Redemptions Feed -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Recent Promotion Redemptions</h5>
        <a href="<?= url('admin/coupons/redemptions') ?>" class="btn btn-link btn-sm text-decoration-none fw-bold p-0">Full Redemptions Ledger &rarr;</a>
    </div>

    <?php if (empty($recentRedemptions)): ?>
        <p class="text-muted small mb-0">No promotion redemptions recorded yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Student</th>
                        <th>Order #</th>
                        <th>Course</th>
                        <th>Original Price</th>
                        <th>Discount</th>
                        <th>Final Paid</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentRedemptions as $r): ?>
                        <tr>
                            <td><span class="badge bg-primary-subtle text-primary border font-monospace"><?= e($r['coupon_code']) ?></span></td>
                            <td class="fw-bold"><?= e($r['user_name']) ?></td>
                            <td><code><?= e($r['order_number']) ?></code></td>
                            <td><?= e($r['course_title'] ?: 'Full Curriculum') ?></td>
                            <td><?= format_money($r['original_amount'], $r['currency']) ?></td>
                            <td class="text-success fw-bold">-<?= format_money($r['discount_amount'], $r['currency']) ?></td>
                            <td class="fw-bold text-dark"><?= format_money($r['final_amount'], $r['currency']) ?></td>
                            <td class="text-muted"><?= date('M d, Y H:i', strtotime($r['redeemed_at'])) ?></td>
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
    const ctxTrend = document.getElementById('couponTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartData['labels']) ?>,
            datasets: [
                {
                    type: 'line',
                    label: 'Revenue Generated (RWF)',
                    data: <?= json_encode($chartData['revenue_series']) ?>,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    yAxisID: 'yRev',
                    tension: 0.35
                },
                {
                    type: 'bar',
                    label: 'Redemptions Count',
                    data: <?= json_encode($chartData['redemptions_series']) ?>,
                    backgroundColor: 'rgba(76, 49, 3, 0.7)',
                    borderRadius: 6,
                    yAxisID: 'yCount'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yRev: {
                    type: 'linear',
                    position: 'left',
                    ticks: { callback: function(v) { return v.toLocaleString() + ' RWF'; } }
                },
                yCount: {
                    type: 'linear',
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { precision: 0 }
                }
            }
        }
    });
});
</script>
