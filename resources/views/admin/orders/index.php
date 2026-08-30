<?php $pageTitle = 'Orders Management'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Customer Orders</h2>
        <p class="text-muted small mb-0">Manage course purchases, payment confirmations, manual bank approvals, and refunds.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/finance') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-graph-up"></i> Finance Dashboard</a>
        <a href="<?= url('admin/orders/export') ?>" class="btn btn-primary btn-sm"><i class="bi bi-download me-1"></i> Export Orders CSV</a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-surface">
    <form action="<?= url('admin/orders') ?>" method="GET" id="ordersFilterForm">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm border-start-0" placeholder="Search order #, customer name, email..." value="<?= e($filters['q']) ?>">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <select name="payment_status" class="form-select form-select-sm" onchange="document.getElementById('ordersFilterForm').submit()">
                    <option value="all">All Payment Statuses</option>
                    <option value="paid" <?= $filters['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="pending" <?= $filters['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending / Unpaid</option>
                    <option value="refunded" <?= $filters['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    <option value="failed" <?= $filters['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="payment_method" class="form-select form-select-sm" onchange="document.getElementById('ordersFilterForm').submit()">
                    <option value="all">All Gateways</option>
                    <option value="momo" <?= $filters['payment_method'] === 'momo' ? 'selected' : '' ?>>MTN/Airtel MoMo</option>
                    <option value="stripe" <?= $filters['payment_method'] === 'stripe' ? 'selected' : '' ?>>Credit Card</option>
                    <option value="bank_transfer" <?= $filters['payment_method'] === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="sandbox" <?= $filters['payment_method'] === 'sandbox' ? 'selected' : '' ?>>Sandbox Test</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="document.getElementById('ordersFilterForm').submit()">
                    <option value="all">All Order Statuses</option>
                    <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="refunded" <?= $filters['status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>

            <div class="col-6 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="<?= url('admin/orders') ?>" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-surface">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Course Items</th>
                    <th>Total Paid</th>
                    <th>Gateway</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-2 mb-2 d-block"></i>
                            No orders found matching your search.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <a href="<?= url('admin/orders/' . $o['id']) ?>" class="fw-bold font-monospace text-decoration-none hover-primary">
                                    <?= e($o['order_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($o['customer_name']) ?></div>
                                <small class="text-muted"><?= e($o['customer_email']) ?></small>
                            </td>
                            <td>
                                <div class="small fw-bold text-truncate" style="max-width: 220px;">
                                    <?= e($o['first_item_title'] ?: 'Course Enrollment') ?>
                                </div>
                                <?php if ($o['items_count'] > 1): ?>
                                    <small class="text-muted">+<?= $o['items_count'] - 1 ?> more item(s)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= format_money($o['final_amount'], $o['currency']) ?></div>
                                <?php if ($o['discount_amount'] > 0): ?>
                                    <small class="text-success d-block" style="font-size:0.7rem;">Discount: -<?= format_money($o['discount_amount'], $o['currency']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size:0.72rem;">
                                    <?= e($o['payment_method'] ?: 'sandbox') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $o['payment_status'] === 'paid' ? 'bg-success-subtle text-success border border-success' : ($o['payment_status'] === 'pending' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-danger-subtle text-danger border border-danger') ?> text-capitalize" style="font-size:0.72rem;">
                                    <?= e($o['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $o['status'] === 'completed' ? 'bg-success' : ($o['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') ?> text-capitalize" style="font-size:0.72rem;">
                                    <?= e($o['status']) ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="<?= url('admin/orders/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary px-3">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
