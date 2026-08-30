<?php $pageTitle = 'Finance & Revenue Dashboard'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Financial & Revenue Analytics</h2>
        <p class="text-muted small mb-0">Executive overview of academy commerce, gross income, student enrollments, and cash flow.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('admin/finance/ledger') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-journal-text"></i> Financial Ledger
        </a>
        <a href="<?= url('admin/finance/reports') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-file-earmark-bar-graph"></i> Sales Reports
        </a>
        <a href="<?= url('admin/orders/export') ?>" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-download"></i> Export Orders CSV
        </a>
    </div>
</div>

<!-- Date Filter Selector Toolbar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-surface">
    <form action="<?= url('admin/finance') ?>" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap align-items-center gap-1">
            <span class="small fw-bold text-muted me-2"><i class="bi bi-calendar-range me-1"></i> Period:</span>
            <a href="<?= url('admin/finance?range=today') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'today' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">Today</a>
            <a href="<?= url('admin/finance?range=yesterday') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'yesterday' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">Yesterday</a>
            <a href="<?= url('admin/finance?range=last_7_days') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'last_7_days' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">Last 7 Days</a>
            <a href="<?= url('admin/finance?range=this_month') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">This Month</a>
            <a href="<?= url('admin/finance?range=last_month') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'last_month' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">Last Month</a>
            <a href="<?= url('admin/finance?range=this_year') ?>" class="btn btn-sm <?= ($filters['range'] ?? '') === 'this_year' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">This Year</a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($filters['start_date'] ?? '') ?>">
            <span class="text-muted small">to</span>
            <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($filters['end_date'] ?? '') ?>">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">Apply</button>
        </div>
    </form>
</div>

<!-- 12 Executive KPI Metric Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Gross Revenue -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface position-relative overflow-hidden" style="border-left: 4px solid #10B981 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Gross Revenue</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(16,185,129,0.1);color:#10B981;">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['gross_revenue']) ?></h3>
            <small class="text-success" style="font-size:0.72rem;"><i class="bi bi-check2-circle"></i> Completed Payments</small>
        </div>
    </div>

    <!-- 2. Revenue This Month -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface position-relative overflow-hidden" style="border-left: 4px solid #2563EB !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Revenue This Month</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(37,99,235,0.1);color:#2563EB;">
                    <i class="bi bi-calendar-month-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['revenue_this_month']) ?></h3>
            <small class="text-primary" style="font-size:0.72rem;"><?= date('F Y') ?></small>
        </div>
    </div>

    <!-- 3. Revenue Today -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface position-relative overflow-hidden" style="border-left: 4px solid #F59E0B !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Revenue Today</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(245,158,11,0.1);color:#F59E0B;">
                    <i class="bi bi-sun-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['revenue_today']) ?></h3>
            <small class="text-warning" style="font-size:0.72rem;"><?= date('M d, Y') ?></small>
        </div>
    </div>

    <!-- 4. Net Revenue -->
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface position-relative overflow-hidden" style="border-left: 4px solid #4C3103 !important;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Net Revenue</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(76,49,3,0.1);color:#4C3103;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['net_revenue']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">After Refunds</small>
        </div>
    </div>

    <!-- 5. Total Orders -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Total Orders</span>
            <h4 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total_orders']) ?></h4>
            <small class="text-muted" style="font-size:0.72rem;">All checkouts</small>
        </div>
    </div>

    <!-- 6. Average Order Value -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Average Order Value</span>
            <h4 class="fw-bold mb-0 text-dark"><?= format_rwf($kpis['avg_order_value']) ?></h4>
            <small class="text-muted" style="font-size:0.72rem;">Per Paid Order</small>
        </div>
    </div>

    <!-- 7. Successful Payments -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Successful Payments</span>
            <h4 class="fw-bold mb-0 text-success"><?= number_format($kpis['successful_payments']) ?></h4>
            <small class="text-success" style="font-size:0.72rem;"><i class="bi bi-check-all"></i> Verified</small>
        </div>
    </div>

    <!-- 8. Pending Payments -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Pending / Offline</span>
            <h4 class="fw-bold mb-0 text-warning"><?= number_format($kpis['pending_payments']) ?></h4>
            <small class="text-warning" style="font-size:0.72rem;">Awaiting verification</small>
        </div>
    </div>

    <!-- 9. Failed Payments -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Failed / Cancelled</span>
            <h4 class="fw-bold mb-0 text-danger"><?= number_format($kpis['failed_payments']) ?></h4>
            <small class="text-danger" style="font-size:0.72rem;">Cancelled attempts</small>
        </div>
    </div>

    <!-- 10. Refunded Amount -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <span class="text-muted small fw-semibold d-block mb-1">Refunded Amount</span>
            <h4 class="fw-bold mb-0 text-secondary"><?= format_rwf($kpis['refunded_amount']) ?></h4>
            <small class="text-muted" style="font-size:0.72rem;">Customer refunds</small>
        </div>
    </div>
</div>

<!-- Interactive Charts Row -->
<div class="row g-4 mb-4">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-graph-up text-primary me-2"></i> Daily Revenue Trend (Last 14 Days)</h5>
                    <small class="text-muted">Real-time daily transaction totals</small>
                </div>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Payment Methods Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <h5 class="font-heading fw-bold mb-1 text-primary-dark"><i class="bi bi-pie-chart text-warning me-2"></i> Payment Gateways</h5>
            <small class="text-muted d-block mb-3">Revenue distribution by channel</small>
            
            <div style="height: 200px; position: relative;" class="mb-3">
                <canvas id="gatewayPieChart"></canvas>
            </div>

            <div class="d-flex flex-column gap-2 small">
                <?php foreach ($chartData['payment_methods'] as $pm): ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                        <span class="fw-bold text-dark text-capitalize"><i class="bi bi-credit-card me-1 text-primary"></i> <?= e($pm['payment_method']) ?></span>
                        <span class="fw-bold text-success"><?= format_rwf($pm['total_amount']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Courses Revenue & Recent Orders Row -->
<div class="row g-4 mb-4">
    <!-- Top Courses by Sales -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-trophy-fill text-warning me-2"></i> Top Revenue Generating Courses</h5>
            <?php if (empty($chartData['courses'])): ?>
                <p class="text-muted small">No course sales recorded yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($chartData['courses'] as $crs): ?>
                        <div class="p-3 bg-light rounded-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= e($crs['title']) ?></h6>
                                <small class="text-muted"><?= $crs['sales_count'] ?> sales</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-success fs-6"><?= format_rwf($crs['total_revenue']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Orders Stream -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Recent Order Transactions</h5>
                <a href="<?= url('admin/orders') ?>" class="btn btn-link btn-sm text-decoration-none fw-bold p-0">View All Orders &rarr;</a>
            </div>

            <?php if (empty($recentOrders)): ?>
                <p class="text-muted small">No recent orders.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $ro): ?>
                                <tr>
                                    <td><code><?= e($ro['order_number']) ?></code></td>
                                    <td class="fw-bold"><?= e($ro['customer_name']) ?></td>
                                    <td class="fw-bold text-dark"><?= format_money($ro['final_amount'], $ro['currency']) ?></td>
                                    <td>
                                        <span class="badge <?= $ro['payment_status'] === 'paid' ? 'bg-success-subtle text-success border border-success' : 'bg-warning-subtle text-warning border border-warning' ?> text-capitalize">
                                            <?= e($ro['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $ro['status'] === 'completed' ? 'bg-success' : 'bg-secondary' ?> text-capitalize">
                                            <?= e($ro['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('admin/orders/' . $ro['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js Library & Initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue Trend Chart
    const ctxTrend = document.getElementById('revenueTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartData['labels']) ?>,
            datasets: [{
                label: 'Revenue (RWF)',
                data: <?= json_encode($chartData['revenue_series']) ?>,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#10B981'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) { return val.toLocaleString() + ' RWF'; }
                    }
                }
            }
        }
    });

    // 2. Gateway Pie Chart
    const ctxGateway = document.getElementById('gatewayPieChart').getContext('2d');
    const gatewayLabels = <?= json_encode(array_column($chartData['payment_methods'], 'payment_method')) ?>;
    const gatewayData = <?= json_encode(array_map('floatval', array_column($chartData['payment_methods'], 'total_amount'))) ?>;

    new Chart(ctxGateway, {
        type: 'doughnut',
        data: {
            labels: gatewayLabels.map(l => l.toUpperCase()),
            datasets: [{
                data: gatewayData.length > 0 ? gatewayData : [1],
                backgroundColor: ['#10B981', '#2563EB', '#F59E0B', '#6366F1', '#EC4899'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
