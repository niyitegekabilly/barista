<?php $pageTitle = 'Admin Dashboard'; ?>
<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <i class="bi bi-people-fill fs-1 text-primary mb-2"></i>
            <h3 class="font-heading fw-bold mb-0"><?= number_format($stats['total_users']) ?></h3>
            <p class="text-muted small mb-0">Total Students</p>
            <span class="badge bg-success-subtle text-success mt-1">+<?= e($stats['new_users_week']) ?> this week</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <i class="bi bi-play-circle-fill fs-1 text-success mb-2"></i>
            <h3 class="font-heading fw-bold mb-0"><?= number_format($stats['total_courses']) ?></h3>
            <p class="text-muted small mb-0">Active Courses</p>
            <span class="badge bg-warning-subtle text-warning mt-1"><?= e($stats['pending_courses']) ?> pending review</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <i class="bi bi-cash-stack fs-1 text-accent mb-2"></i>
            <h3 class="font-heading fw-bold mb-0"><?= format_rwf($stats['revenue_month']) ?></h3>
            <p class="text-muted small mb-0">Revenue This Month</p>
            <span class="badge bg-info-subtle text-info mt-1"><?= format_rwf($stats['revenue_total']) ?> total</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <i class="bi bi-award-fill fs-1 text-warning mb-2"></i>
            <h3 class="font-heading fw-bold mb-0"><?= number_format($stats['total_certificates']) ?></h3>
            <p class="text-muted small mb-0">Certificates Issued</p>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <h5 class="font-heading fw-bold mb-3">Enrollment Trends (Last 6 Months)</h5>
            <canvas id="enrollmentChart" height="100"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <h5 class="font-heading fw-bold mb-3">Revenue by Category</h5>
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0">Recent Orders</h5>
                <a href="<?= url('admin/orders') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Student</th><th>Course</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td class="small"><?= e($order['student_name']) ?></td>
                                <td class="small text-muted"><?= e(substr($order['course_title'] ?? 'N/A', 0, 22)) ?>...</td>
                                <td class="small fw-bold"><?= format_rwf($order['total_amount']) ?></td>
                                <td><span class="badge <?= ($order['status'] ?? '') === 'completed' ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size:0.65rem;"><?= strtoupper($order['status'] ?? 'pending') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0">Pending Course Approvals</h5>
                <a href="<?= url('admin/courses') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <?php if (empty($pending_courses)): ?>
                <p class="text-muted small text-center py-3"><i class="bi bi-check-circle-fill text-success me-1"></i> All courses reviewed</p>
            <?php else: ?>
                <?php foreach ($pending_courses as $c): ?>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div>
                            <div class="fw-bold small"><?= e($c['title']) ?></div>
                            <small class="text-muted">by <?= e($c['instructor_name']) ?></small>
                        </div>
                        <div class="d-flex gap-1">
                            <form action="<?= url('admin/courses/' . $c['id'] . '/approve') ?>" method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <a href="<?= url('admin/courses/' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary">Review</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Enrollment Chart
    new Chart(document.getElementById('enrollmentChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['enrollment_labels'] ?? []) ?>,
            datasets: [{
                label: 'Enrollments',
                data: <?= json_encode($chart_data['enrollment_data'] ?? []) ?>,
                borderColor: '#4C3103',
                backgroundColor: 'rgba(76,49,3,0.1)',
                tension: 0.4, fill: true, pointRadius: 5
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
    // Revenue Doughnut
    new Chart(document.getElementById('revenueChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chart_data['revenue_labels'] ?? []) ?>,
            datasets: [{ data: <?= json_encode($chart_data['revenue_data'] ?? []) ?>, backgroundColor: ['#4C3103','#E29578','#C0B7C5','#1E293B','#6366F1'] }]
        },
        options: { responsive: true }
    });
});
</script>
