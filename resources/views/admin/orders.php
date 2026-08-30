<?php $pageTitle = 'Orders & Payments'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="font-heading fw-bold mb-0">Orders & Payments</h2>
    <a href="<?= url('admin/orders/export') ?>" class="btn btn-outline-success btn-sm fw-bold">
        <i class="bi bi-download me-1"></i> Export CSV
    </a>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card p-3 border-0 shadow-sm rounded-4 text-center">
            <h5 class="font-heading fw-bold mb-0 text-success"><?= format_rwf($summary['total_revenue']) ?></h5>
            <p class="text-muted small mb-0">Total Revenue</p>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card p-3 border-0 shadow-sm rounded-4 text-center">
            <h5 class="font-heading fw-bold mb-0"><?= number_format($summary['total_orders']) ?></h5>
            <p class="text-muted small mb-0">Total Orders</p>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card p-3 border-0 shadow-sm rounded-4 text-center">
            <h5 class="font-heading fw-bold mb-0 text-warning"><?= number_format($summary['pending_orders']) ?></h5>
            <p class="text-muted small mb-0">Pending Orders</p>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="font-monospace small"><?= e($order['order_number']) ?></td>
                        <td class="small fw-bold"><?= e($order['student_name']) ?></td>
                        <td class="small text-muted"><?= e(substr($order['course_title'], 0, 30)) ?>...</td>
                        <td class="fw-bold small"><?= format_rwf($order['total_amount']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= strtoupper($order['payment_method'] ?? 'momo') ?></span></td>
                        <td class="text-muted small"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td>
                            <span class="badge <?= $order['status'] === 'completed' ? 'bg-success' : ($order['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                <?= strtoupper($order['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
