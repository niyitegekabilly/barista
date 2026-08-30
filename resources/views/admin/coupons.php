<?php $pageTitle = 'Coupon Management'; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <h5 class="font-heading fw-bold mb-3">Create Coupon</h5>
            <form action="<?= url('admin/coupons/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Coupon Code</label>
                    <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. BARISTA20" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Discount Type</label>
                    <select name="discount_type" class="form-select">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (RWF)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Discount Value</label>
                    <input type="number" name="discount_value" class="form-control" placeholder="e.g. 20 for 20%" required min="1">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Max Uses (0 = unlimited)</label>
                    <input type="number" name="max_uses" class="form-control" value="100" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Minimum Cart Amount (RWF)</label>
                    <input type="number" name="minimum_amount" class="form-control" value="0" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Expires At</label>
                    <input type="date" name="expires_at" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary fw-bold w-100">Create Coupon</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <h2 class="font-heading fw-bold mb-3">Active Coupons</h2>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Uses</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td class="fw-bold font-monospace"><?= e($coupon['code']) ?></td>
                                <td class="small">
                                    <?= $coupon['discount_type'] === 'percentage'
                                        ? e($coupon['discount_value']) . '%'
                                        : format_rwf($coupon['discount_value']) ?>
                                </td>
                                <td class="small text-center"><?= e($coupon['used_count']) ?>/<?= e($coupon['max_uses'] ?: '∞') ?></td>
                                <td class="text-muted small">
                                    <?= $coupon['expires_at'] ? date('M d, Y', strtotime($coupon['expires_at'])) : 'No expiry' ?>
                                </td>
                                <td>
                                    <span class="badge <?= $coupon['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $coupon['is_active'] ? 'ACTIVE' : 'DISABLED' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="<?= url('admin/coupons/' . $coupon['id'] . '/toggle') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <?= $coupon['is_active'] ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                    <form action="<?= url('admin/coupons/' . $coupon['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete coupon?')">
                                        <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
