<?php $pageTitle = 'Order #' . e($order['order_number']); ?>

<!-- Breadcrumbs & Header -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/orders') ?>" class="text-decoration-none text-muted">Orders</a></li>
        <li class="breadcrumb-item active">Order #<?= e($order['order_number']) ?></li>
    </ol>
</nav>

<!-- Order Hero Card -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="font-heading fw-bold mb-0 text-dark">Order #<?= e($order['order_number']) ?></h3>
                <span class="badge <?= $order['payment_status'] === 'paid' ? 'bg-success-subtle text-success border border-success' : ($order['payment_status'] === 'pending' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-danger-subtle text-danger border border-danger') ?> text-capitalize px-2 py-1">
                    Payment: <?= e($order['payment_status']) ?>
                </span>
                <span class="badge <?= $order['status'] === 'completed' ? 'bg-success' : 'bg-secondary' ?> text-capitalize px-2 py-1">
                    Order: <?= e($order['status']) ?>
                </span>
            </div>
            <p class="text-muted small mb-0">Placed on <?= date('F d, Y \a\t H:i', strtotime($order['created_at'])) ?> • Gateway: <strong class="text-dark text-uppercase"><?= e($order['payment_method'] ?: 'sandbox') ?></strong></p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if (!empty($order['invoices'][0])): ?>
                <a href="<?= url('invoice/' . $order['invoices'][0]['invoice_number']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-receipt"></i> View Invoice
                </a>
            <?php endif; ?>

            <?php if (!empty($order['receipts'][0])): ?>
                <a href="<?= url('receipt/' . $order['receipts'][0]['receipt_number']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-file-earmark-check"></i> View Receipt
                </a>
            <?php endif; ?>

            <?php if ($order['payment_status'] === 'pending' && $order['payment_method'] === 'bank_transfer'): ?>
                <form action="<?= url('admin/orders/' . $order['id'] . '/verify-manual') ?>" method="POST" class="d-inline" onsubmit="return confirm('Verify manual bank payment and grant immediate course enrollment?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Verify Manual Payment
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($order['remaining_refundable'] > 0): ?>
                <button type="button" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#refundModal">
                    <i class="bi bi-arrow-counterclockwise"></i> Process Refund
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Items, Breakdown, Payment, Enrollment -->
    <div class="col-lg-8">
        
        <!-- 1. Purchased Items Table -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-cart-check-fill text-primary me-2"></i> Purchased Curriculum Items</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Item / Course</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-dark text-warning d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0;">
                                            <i class="bi bi-journal-code"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= e($item['item_title']) ?></div>
                                            <span class="badge bg-light text-secondary border text-capitalize"><?= e($item['item_type']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end fw-bold"><?= format_money($item['unit_price'], $order['currency']) ?></td>
                                <td class="text-end text-success"><?= $item['discount_amount'] > 0 ? ('-' . format_money($item['discount_amount'], $order['currency'])) : '—' ?></td>
                                <td class="text-end fw-bold text-primary"><?= format_money($item['total_amount'], $order['currency']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Financial Summary Breakdown -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-calculator-fill text-secondary me-2"></i> Financial Summary</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <table class="table table-borderless small mb-0">
                        <tr><td class="text-muted">Subtotal:</td><td class="text-end fw-bold"><?= format_money($order['subtotal_amount'] ?: $order['total_amount'], $order['currency']) ?></td></tr>
                        <tr><td class="text-muted">Coupon Discount:</td><td class="text-end text-success fw-bold"><?= $order['discount_amount'] > 0 ? ('-' . format_money($order['discount_amount'], $order['currency'])) : '0 RWF' ?></td></tr>
                        <tr><td class="text-muted">Taxes & VAT:</td><td class="text-end text-muted"><?= format_money($order['tax_amount'] ?? 0, $order['currency']) ?></td></tr>
                        <tr class="border-top"><td class="fw-bold fs-6">Order Total:</td><td class="text-end fw-bold fs-5 text-primary-dark"><?= format_money($order['final_amount'], $order['currency']) ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6 border-start">
                    <table class="table table-borderless small mb-0">
                        <tr><td class="text-muted">Total Paid:</td><td class="text-end text-success fw-bold"><?= $order['payment_status'] === 'paid' ? format_money($order['final_amount'], $order['currency']) : '0 RWF' ?></td></tr>
                        <tr><td class="text-muted">Total Refunded:</td><td class="text-end text-danger fw-bold"><?= count($order['refunds']) > 0 ? format_money(array_sum(array_column($order['refunds'], 'amount')), $order['currency']) : '0 RWF' ?></td></tr>
                        <tr class="border-top"><td class="fw-bold">Remaining Refundable:</td><td class="text-end fw-bold text-dark"><?= format_money($order['remaining_refundable'], $order['currency']) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. Gateway Payment Record -->
        <?php if (!empty($order['latest_payment'])): ?>
            <?php $p = $order['latest_payment']; ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-credit-card-2-front-fill text-success me-2"></i> Payment Transaction</h5>
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <span class="text-muted d-block">Transaction Reference</span>
                        <code><?= e($p['transaction_reference']) ?></code>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Gateway</span>
                        <strong class="text-uppercase"><?= e($p['gateway'] ?: $p['payment_method']) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Status</span>
                        <span class="badge <?= $p['status'] === 'successful' ? 'bg-success' : 'bg-warning text-dark' ?> text-capitalize"><?= e($p['status']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Verified Timestamp</span>
                        <span><?= $p['verified_at'] ? date('M d, Y H:i:s', strtotime($p['verified_at'])) : 'Pending verification' ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4. Course Enrollment Status -->
        <?php if (!empty($order['enrollment'])): ?>
            <?php $en = $order['enrollment']; ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-mortarboard-fill text-warning me-2"></i> Student Classroom Enrollment</h5>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-dark mb-1">Status: <span class="badge bg-success text-capitalize"><?= e($en['status']) ?></span></div>
                        <small class="text-muted">Enrolled on <?= date('M d, Y', strtotime($en['enrolled_at'])) ?> • Progress: <?= $en['progress_percent'] ?>%</small>
                    </div>
                    <a href="<?= url('admin/users/' . $order['user_id']) ?>" class="btn btn-sm btn-outline-primary">View Student Profile</a>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Right Column: Customer Profile, Timeline, Staff Notes -->
    <div class="col-lg-4">

        <!-- 1. Customer Information Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-person-circle text-primary me-2"></i> Customer Profile</h5>
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-5" style="width:48px;height:48px;">
                    <?= strtoupper(substr($order['user']['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <h6 class="fw-bold mb-0"><?= e($order['user']['name'] ?? 'Customer') ?></h6>
                    <small class="text-muted"><?= e($order['user']['email'] ?? '') ?></small>
                </div>
            </div>

            <div class="small text-muted d-flex flex-column gap-1 border-top pt-2">
                <div><i class="bi bi-telephone me-1"></i> <?= e($order['billing_phone'] ?: 'No phone provided') ?></div>
                <div><i class="bi bi-geo-alt me-1"></i> <?= e($order['billing_address'] ?: 'Kigali, Rwanda') ?></div>
                <div class="mt-2">
                    <a href="<?= url('admin/users/' . $order['user_id']) ?>" class="btn btn-outline-secondary btn-sm w-100 fw-bold">
                        <i class="bi bi-person-lines-fill me-1"></i> 360° Student Workspace
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Interactive Order Timeline -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Order Event Timeline</h5>
            <div class="timeline position-relative ps-4" style="border-left: 2px solid #E5E7EB;">
                <div class="mb-3 position-relative">
                    <span class="position-absolute bg-primary rounded-circle" style="width:10px;height:10px;left:-21px;top:5px;"></span>
                    <div class="fw-bold small text-dark">Order Created</div>
                    <small class="text-muted"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></small>
                </div>
                <?php if ($order['payment_status'] === 'paid'): ?>
                    <div class="mb-3 position-relative">
                        <span class="position-absolute bg-success rounded-circle" style="width:10px;height:10px;left:-21px;top:5px;"></span>
                        <div class="fw-bold small text-success">Payment Verified & Paid</div>
                        <small class="text-muted">Enrollment granted to curriculum</small>
                    </div>
                <?php endif; ?>
                <?php foreach ($order['refunds'] as $ref): ?>
                    <div class="mb-3 position-relative">
                        <span class="position-absolute bg-danger rounded-circle" style="width:10px;height:10px;left:-21px;top:5px;"></span>
                        <div class="fw-bold small text-danger">Refund Processed (<?= format_money($ref['amount'], $ref['currency']) ?>)</div>
                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($ref['processed_at'] ?: $ref['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. Internal Staff Notes -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-sticky-fill text-warning me-2"></i> Internal Staff Notes</h5>
            
            <form action="<?= url('admin/orders/' . $order['id'] . '/add-note') ?>" method="POST" class="mb-3">
                <?= csrf_field() ?>
                <textarea name="note" rows="2" class="form-control form-control-sm mb-2" placeholder="Add confidential staff note..." required></textarea>
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Add Note</button>
            </form>

            <?php if (empty($order['notes'])): ?>
                <p class="text-muted small mb-0">No staff notes added yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-2 small">
                    <?php foreach ($order['notes'] as $n): ?>
                        <div class="p-2 bg-light rounded-3">
                            <p class="mb-1"><?= nl2br(e($n['note'])) ?></p>
                            <small class="text-muted" style="font-size:0.7rem;"><?= e($n['author_name'] ?? 'Admin') ?> • <?= date('M d, Y H:i', strtotime($n['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal: Process Refund -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/orders/' . $order['id'] . '/refund') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold text-danger"><i class="bi bi-arrow-counterclockwise me-2"></i> Process Payment Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                        Maximum refundable balance: <strong class="text-dark"><?= format_money($order['remaining_refundable'], $order['currency']) ?></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Refund Amount (<?= e($order['currency']) ?>) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" value="<?= (float)$order['remaining_refundable'] ?>" max="<?= (float)$order['remaining_refundable'] ?>" min="1" step="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Refund Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="2" class="form-control" placeholder="Explain reason for refund..." required></textarea>
                    </div>

                    <div class="form-check p-2 border rounded-3 bg-light">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="cancel_enrollment" value="1" id="cancelEnrollmentCheck" checked>
                        <label class="form-check-label small fw-bold" for="cancelEnrollmentCheck">Revoke student course access / cancel enrollment</label>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">Confirm & Issue Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
