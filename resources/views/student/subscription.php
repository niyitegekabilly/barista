<?php $pageTitle = 'My Membership & Subscription'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark"><i class="bi bi-person-badge-fill text-primary me-2"></i> My Membership & Subscription</h2>
        <p class="text-muted small mb-0">View your active academy tier, manage renewals, or explore upgrade plans.</p>
    </div>
    <a href="<?= url('pricing') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-layers me-1"></i> Browse All Plans</a>
</div>

<?php if (empty($subscription) || !in_array($subscription['status'], ['active', 'trialing', 'grace_period'])): ?>
    <!-- No Active Subscription Card -->
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-surface mb-4">
        <i class="bi bi-award text-warning fs-1 mb-2 d-block"></i>
        <h4 class="font-heading fw-bold text-dark mb-2">No Active Membership</h4>
        <p class="text-muted small max-w-500 mx-auto mb-4">
            Unlock all specialty coffee and hospitality courses, earn recognized certificates, and join live masterclasses with a Beyond Barista Membership.
        </p>
        <a href="<?= url('pricing') ?>" class="btn btn-primary btn-lg fw-bold px-4 mx-auto shadow">
            <i class="bi bi-lightning-charge-fill me-1"></i> Explore Membership Plans
        </a>
    </div>
<?php else: ?>
    <!-- Active Subscription Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-surface mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-success text-uppercase px-3 py-2 fs-6">Active Member</span>
                    <span class="badge bg-primary-subtle text-primary border px-3 py-2 fs-6"><code><?= e($subscription['subscription_number']) ?></code></span>
                </div>

                <h2 class="font-heading fw-bold text-dark mb-1"><?= e($subscription['plan_name']) ?></h2>
                <p class="text-muted small mb-3">
                    Billed <?= e($subscription['billing_interval']) ?> at <?= format_rwf($subscription['plan_price']) ?>. Access valid until <strong><?= date('M d, Y', strtotime($subscription['end_date'])) ?></strong>.
                </p>

                <!-- Days Left Meter -->
                <div class="p-3 bg-light rounded-4 mb-4" style="max-width: 480px;">
                    <div class="d-flex justify-content-between small fw-bold mb-1">
                        <span class="text-muted">Subscription Period:</span>
                        <span class="<?= $subscription['days_remaining'] <= 3 ? 'text-danger' : 'text-success' ?>">
                            <?= $subscription['days_remaining'] ?> day(s) remaining
                        </span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $subscription['days_remaining'] <= 3 ? 'bg-danger' : 'bg-success' ?>" style="width: <?= min(100, $subscription['days_remaining'] * 3.3) ?>%;"></div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('student/courses') ?>" class="btn btn-primary btn-sm fw-bold px-3">
                        <i class="bi bi-play-circle-fill me-1"></i> Start Learning
                    </a>
                    <?php if ($subscription['auto_renew']): ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#cancelSubModal">
                            Cancel Auto-Renew
                        </button>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark align-self-center py-2 px-3">Auto-Renew Disabled (Access expires <?= date('M d, Y', strtotime($subscription['end_date'])) ?>)</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="p-4 bg-light rounded-4 border">
                    <h6 class="font-heading fw-bold text-dark mb-3"><i class="bi bi-check-circle-fill text-success me-1"></i> Plan Inclusions:</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small mb-0">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check text-success fs-5"></i>
                            <span><?= ucfirst(str_replace('_', ' ', $subscription['course_access_type'])) ?> access</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check text-success fs-5"></i>
                            <span><?= $subscription['has_certificate_access'] ? 'Official Certificates included' : 'Certificates not included' ?></span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check text-success fs-5"></i>
                            <span><?= $subscription['has_live_workshops'] ? 'Live Workshops included' : 'Workshops not included' ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Renewal & Invoicing History -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
        <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-receipt text-warning me-2"></i> Billing & Renewal History</h5>

        <?php if (empty($subscription['renewals'])): ?>
            <p class="text-muted small mb-0">No past renewals recorded.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Billing Date</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscription['renewals'] as $ren): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($ren['billing_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($ren['period_start'])) ?> &rarr; <?= date('M d, Y', strtotime($ren['period_end'])) ?></td>
                                <td class="fw-bold text-success"><?= format_money($ren['amount'], $ren['currency']) ?></td>
                                <td><span class="badge bg-success"><?= strtoupper($ren['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal: Cancel Auto-Renew -->
    <div class="modal fade" id="cancelSubModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form action="<?= url('student/subscription/cancel') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-header border-bottom py-3">
                        <h5 class="modal-title font-heading fw-bold text-dark">Cancel Auto-Renewal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3">
                            You will retain full access to all your membership courses until <strong><?= date('M d, Y', strtotime($subscription['end_date'])) ?></strong>. After this date, your subscription will not automatically renew.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Reason for Cancelling (Optional)</label>
                            <textarea name="reason" rows="2" class="form-control" placeholder="Please let us know how we can improve..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Keep My Subscription</button>
                        <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">Disable Auto-Renew</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
