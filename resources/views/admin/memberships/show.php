<?php $pageTitle = 'Subscription: ' . e($subscription['subscription_number']); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/memberships') ?>" class="text-decoration-none text-muted">Subscriptions</a></li>
        <li class="breadcrumb-item active font-monospace"><?= e($subscription['subscription_number']) ?></li>
    </ol>
</nav>

<!-- Hero Banner -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-4 bg-dark text-warning d-flex align-items-center justify-content-center p-3 shadow-sm" style="width:64px;height:64px;">
                <i class="bi bi-person-badge-fill fs-2"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="font-heading fw-bold mb-0 text-dark font-monospace"><?= e($subscription['subscription_number']) ?></h3>
                    <?php
                    $statusBadge = match ($subscription['status']) {
                        'active' => 'bg-success',
                        'trialing' => 'bg-info text-dark',
                        'grace_period' => 'bg-warning text-dark',
                        'expired' => 'bg-secondary',
                        'cancelled' => 'bg-danger',
                        'paused' => 'bg-dark',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?= $statusBadge ?> text-uppercase px-2 py-1"><?= e($subscription['status']) ?></span>
                    <span class="badge bg-primary-subtle text-primary border"><?= e($subscription['plan_name']) ?></span>
                </div>
                <p class="text-muted small mb-0">
                    Student Member: <strong><?= e($subscription['user_name']) ?></strong> (<?= e($subscription['user_email']) ?>)
                </p>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#extendModal">
                <i class="bi bi-calendar-plus"></i> Extend Period
            </button>
            <?php if ($subscription['status'] !== 'cancelled'): ?>
                <button type="button" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle"></i> Cancel Subscription
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Details Grid -->
<div class="row g-4 mb-4">
    <!-- Left Column: Subscription & Plan Attributes -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-card-checklist text-primary me-2"></i> Subscription Parameters</h5>

            <table class="table table-borderless small mb-0">
                <tr><td class="text-muted">Membership Tier:</td><td class="fw-bold text-dark"><?= e($subscription['plan_name']) ?></td></tr>
                <tr><td class="text-muted">Billing Interval:</td><td class="text-capitalize fw-bold"><?= e($subscription['billing_interval']) ?> (<?= format_rwf($subscription['plan_price']) ?>)</td></tr>
                <tr><td class="text-muted">Auto-Renew:</td><td><?= $subscription['auto_renew'] ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>' ?></td></tr>
                <tr><td class="text-muted">Start Date:</td><td><?= date('M d, Y', strtotime($subscription['start_date'])) ?></td></tr>
                <tr><td class="text-muted">End / Renewal Date:</td><td class="fw-bold text-primary"><?= date('M d, Y', strtotime($subscription['end_date'])) ?></td></tr>
                <tr>
                    <td class="text-muted">Days Remaining:</td>
                    <td>
                        <strong class="<?= $subscription['days_remaining'] <= 3 ? 'text-danger' : 'text-success' ?>">
                            <?= $subscription['days_remaining'] ?> day(s)
                        </strong>
                    </td>
                </tr>
                <tr><td class="text-muted">Course Gating Mode:</td><td class="text-capitalize"><?= str_replace('_', ' ', $subscription['course_access_type']) ?></td></tr>
                <tr><td class="text-muted">Certificate Access:</td><td><?= $subscription['has_certificate_access'] ? '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Included</span>' : '<span class="text-muted">No</span>' ?></td></tr>
                <tr><td class="text-muted">Live Workshops:</td><td><?= $subscription['has_live_workshops'] ? '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Included</span>' : '<span class="text-muted">No</span>' ?></td></tr>
                <?php if (!empty($subscription['cancelled_at'])): ?>
                    <tr><td class="text-danger">Cancelled At:</td><td class="text-danger"><?= date('M d, Y H:i', strtotime($subscription['cancelled_at'])) ?> (<?= e($subscription['cancellation_reason'] ?: 'No reason given') ?>)</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Right Column: Associated Order & Customer Information -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-receipt text-warning me-2"></i> Initial Purchase Order</h5>

            <?php if (!empty($subscription['order'])): ?>
                <?php $ord = $subscription['order']; ?>
                <table class="table table-borderless small mb-0">
                    <tr><td class="text-muted">Order Reference:</td><td><a href="<?= url('admin/orders/' . $ord['id']) ?>" class="font-monospace fw-bold text-decoration-none"><code><?= e($ord['order_number']) ?></code></a></td></tr>
                    <tr><td class="text-muted">Total Paid:</td><td class="fw-bold text-success"><?= format_rwf($ord['total_amount']) ?></td></tr>
                    <tr><td class="text-muted">Payment Method:</td><td class="text-uppercase"><?= e($ord['payment_method']) ?></td></tr>
                    <tr><td class="text-muted">Payment Status:</td><td><span class="badge bg-success text-uppercase"><?= e($ord['payment_status']) ?></span></td></tr>
                    <tr><td class="text-muted">Customer Name:</td><td class="fw-bold"><?= e($ord['billing_name']) ?></td></tr>
                    <tr><td class="text-muted">Customer Email:</td><td><?= e($ord['billing_email']) ?></td></tr>
                    <tr><td class="text-muted">Customer Phone:</td><td><?= e($ord['billing_phone'] ?: 'N/A') ?></td></tr>
                </table>
            <?php else: ?>
                <p class="text-muted small">Subscription was provisioned directly by an administrator or complimentary access.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Renewals History Table -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-clock-history text-secondary me-2"></i> Recurring Billing & Renewal History</h5>

    <?php if (empty($subscription['renewals'])): ?>
        <p class="text-muted small mb-0">No recurring renewal records logged yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Billing Date</th>
                        <th>Period Covered</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscription['renewals'] as $ren): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($ren['billing_date'])) ?></td>
                            <td><?= date('M d, Y', strtotime($ren['period_start'])) ?> &rarr; <?= date('M d, Y', strtotime($ren['period_end'])) ?></td>
                            <td class="fw-bold text-success"><?= format_money($ren['amount'], $ren['currency']) ?></td>
                            <td><span class="badge <?= $ren['status'] === 'success' ? 'bg-success' : 'bg-danger' ?>"><?= strtoupper($ren['status']) ?></span></td>
                            <td class="text-muted"><?= e($ren['notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Activity Audit Trail -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-journal-text text-info me-2"></i> Subscription Audit Trail</h5>

    <?php if (empty($subscription['activity_logs'])): ?>
        <p class="text-muted small mb-0">No activity recorded for this subscription yet.</p>
    <?php else: ?>
        <div class="timeline position-relative ps-4" style="border-left: 2px solid #E5E7EB;">
            <?php foreach ($subscription['activity_logs'] as $log): ?>
                <div class="mb-3 position-relative">
                    <span class="position-absolute bg-primary rounded-circle" style="width:10px;height:10px;left:-21px;top:5px;"></span>
                    <div class="fw-bold small text-dark text-capitalize"><?= str_replace('_', ' ', $log['action']) ?></div>
                    <small class="text-muted"><?= e($log['admin_name'] ?? 'System') ?> • <?= date('M d, Y H:i', strtotime($log['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Extend Subscription -->
<div class="modal fade" id="extendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/memberships/' . $subscription['id'] . '/extend') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold text-primary-dark"><i class="bi bi-calendar-plus me-2"></i> Extend Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Number of Days to Add <span class="text-danger">*</span></label>
                        <input type="number" name="days" class="form-control" value="30" min="1" max="365" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason for Extension</label>
                        <textarea name="reason" rows="2" class="form-control" placeholder="e.g. Complimentary extension for festival delay..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Apply Extension</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cancel Subscription -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/memberships/' . $subscription['id'] . '/cancel') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold text-danger"><i class="bi bi-x-circle me-2"></i> Cancel Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Cancellation Timing</label>
                        <select name="immediate" class="form-select">
                            <option value="0">Cancel Auto-Renew (Student retains access until <?= date('M d, Y', strtotime($subscription['end_date'])) ?>)</option>
                            <option value="1">Cancel Immediately (Revoke access now)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Cancellation Reason</label>
                        <textarea name="reason" rows="2" class="form-control" placeholder="e.g. Student requested cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
