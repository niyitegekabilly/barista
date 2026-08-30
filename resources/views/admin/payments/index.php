<?php $pageTitle = 'Payment Transactions'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Payment Transactions</h2>
        <p class="text-muted small mb-0">Inspect gateway callbacks, mobile money transactions, and payment statuses.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/finance') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-graph-up"></i> Finance Dashboard</a>
        <a href="<?= url('admin/orders') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-receipt"></i> Orders List</a>
    </div>
</div>

<!-- Payments Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-surface">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Tx Reference</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Gateway</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Verified Date</th>
                    <th class="text-end">Order</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-credit-card fs-2 mb-2 d-block"></i>
                            No payment transactions found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td>
                                <code><?= e($p['transaction_reference']) ?></code>
                            </td>
                            <td>
                                <a href="<?= url('admin/orders/' . $p['order_id']) ?>" class="fw-bold text-decoration-none font-monospace">
                                    <?= e($p['order_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($p['customer_name']) ?></div>
                                <small class="text-muted"><?= e($p['customer_email']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size:0.72rem;">
                                    <?= e($p['gateway'] ?: $p['payment_method']) ?>
                                </span>
                            </td>
                            <td class="fw-bold text-dark">
                                <?= format_money($p['amount'], $p['currency']) ?>
                            </td>
                            <td>
                                <span class="badge <?= $p['status'] === 'successful' ? 'bg-success-subtle text-success border border-success' : ($p['status'] === 'pending' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-danger-subtle text-danger border border-danger') ?> text-capitalize" style="font-size:0.72rem;">
                                    <?= e($p['status']) ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= $p['verified_at'] ? date('M d, Y H:i', strtotime($p['verified_at'])) : date('M d, Y H:i', strtotime($p['created_at'])) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('admin/orders/' . $p['order_id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2">
                                    View Order
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
