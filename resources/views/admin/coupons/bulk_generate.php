<?php $pageTitle = 'Bulk Coupon Generator'; ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/coupons') ?>" class="text-decoration-none text-muted">Coupons</a></li>
        <li class="breadcrumb-item active">Bulk Generator</li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark"><i class="bi bi-magic text-primary me-2"></i> Bulk Coupon Code Generator</h2>
        <p class="text-muted small mb-0">Generate hundreds of unique, single-use or multi-use student discount codes at once.</p>
    </div>
    <a href="<?= url('admin/coupons') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Coupons</a>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface" style="max-width: 800px;">
    <form action="<?= url('admin/coupons/bulk-generate') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Number of Codes to Generate <span class="text-danger">*</span></label>
                <input type="number" name="count" class="form-control" value="25" min="1" max="500" required>
                <small class="text-muted" style="font-size:0.7rem;">Generate between 1 and 500 unique codes.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Code Prefix</label>
                <input type="text" name="prefix" class="form-control font-monospace text-uppercase" value="BARISTA" placeholder="e.g. INTAKE2026">
                <small class="text-muted" style="font-size:0.7rem;">Prepended to every generated code (e.g. BARISTA-7X8Y).</small>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Random Code Length</label>
                <input type="number" name="length" class="form-control" value="6" min="4" max="16" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Assign to Campaign</label>
                <select name="campaign_id" class="form-select">
                    <option value="">-- No Campaign --</option>
                    <?php foreach ($campaigns as $camp): ?>
                        <option value="<?= $camp['id'] ?>"><?= e($camp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Discount Type <span class="text-danger">*</span></label>
                <select name="discount_type" class="form-select" required>
                    <option value="percentage">Percentage Discount (%)</option>
                    <option value="fixed">Fixed Amount (RWF)</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Discount Value <span class="text-danger">*</span></label>
                <input type="number" name="discount_value" class="form-control" value="15" min="1" step="0.01" required>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Max Uses Per Code</label>
                <input type="number" name="max_uses" class="form-control" value="1" min="1" required>
                <small class="text-muted" style="font-size:0.7rem;">Set to 1 for unique single-use student promo codes.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Expiration Date</label>
                <input type="datetime-local" name="expires_at" class="form-control form-control-sm">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg fw-bold px-4 shadow">
            <i class="bi bi-gear-wide-connected me-1"></i> Generate & Save Promotion Codes
        </button>
    </form>
</div>
