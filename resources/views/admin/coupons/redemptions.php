<?php $pageTitle = 'Coupon Redemptions Ledger'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Coupon Redemptions Ledger</h2>
        <p class="text-muted small mb-0">Full chronological history of all promotional codes used during student checkout.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/coupons') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Coupons</a>
        <a href="<?= url('admin/coupons/export-redemptions') ?>" class="btn btn-primary btn-sm"><i class="bi bi-download me-1"></i> Export Redemptions CSV</a>
    </div>
</div>

<!-- Search & Filter Form -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-surface">
    <form action="<?= url('admin/coupons/redemptions') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" class="form-control form-control-sm border-start-0" placeholder="Search coupon code, student name, email, order #..." value="<?= e($filters['q']) ?>">
            </div>
        </div>

        <div class="col-6 col-md-3">
            <input type="date" name="start_date" class="form-control form-control-sm" value="<?= e($filters['start_date']) ?>" placeholder="Start Date">
        </div>

        <div class="col-6 col-md-3">
            <input type="date" name="end_date" class="form-control form-control-sm" value="<?= e($filters['end_date']) ?>" placeholder="End Date">
        </div>

        <div class="col-12 col-md-1">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel-fill"></i></button>
        </div>
    </form>
</div>

<!-- Redemptions Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-surface">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light text-muted text-uppercase">
                <tr>
                    <th>Redemption #</th>
                    <th>Code & Campaign</th>
                    <th>Student Customer</th>
                    <th>Order Reference</th>
                    <th>Course Purchased</th>
                    <th>Discount Given</th>
                    <th>Final Total</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($redemptions)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-2 mb-2 d-block"></i>
                            No redemptions found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($redemptions as $r): ?>
                        <tr>
                            <td><code>#<?= $r['id'] ?></code></td>
                            <td>
                                <a href="<?= url('admin/coupons/' . $r['coupon_id']) ?>" class="fw-bold font-monospace text-decoration-none text-primary-dark">
                                    <?= e($r['coupon_code']) ?>
                                </a>
                                <?php if (!empty($r['campaign_name'])): ?>
                                    <small class="text-muted d-block"><?= e($r['campaign_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($r['user_name']) ?></div>
                                <small class="text-muted"><?= e($r['user_email']) ?></small>
                            </td>
                            <td>
                                <a href="<?= url('admin/orders/' . $r['order_id']) ?>" class="font-monospace fw-bold text-decoration-none">
                                    <code><?= e($r['order_number']) ?></code>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 180px;"><?= e($r['course_title'] ?: 'Full Curriculum') ?></div>
                            </td>
                            <td class="text-success fw-bold">
                                -<?= format_money($r['discount_amount'], $r['currency']) ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <?= format_money($r['final_amount'], $r['currency']) ?>
                            </td>
                            <td class="text-muted">
                                <?= date('M d, Y H:i', strtotime($r['redeemed_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
