<?php $pageTitle = 'Edit Coupon: ' . e($coupon['code']); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/coupons') ?>" class="text-decoration-none text-muted">Coupons</a></li>
        <li class="breadcrumb-item"><a href="<?= url('admin/coupons/' . $coupon['id']) ?>" class="text-decoration-none text-muted"><?= e($coupon['code']) ?></a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Edit Coupon: <span class="font-monospace text-primary"><?= e($coupon['code']) ?></span></h2>
        <p class="text-muted small mb-0">Modify promotion parameters, limits, scheduling, and applicability rules.</p>
    </div>
    <a href="<?= url('admin/coupons/' . $coupon['id']) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Workspace</a>
</div>

<form action="<?= url('admin/coupons/' . $coupon['id'] . '/update') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row g-4">
        <div class="col-lg-8">
            
            <!-- Section 1: Basic Information -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-info-circle-fill text-primary me-2"></i> 1. Basic Information</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Coupon Code</label>
                        <input type="text" class="form-control font-monospace text-uppercase fw-bold bg-light" value="<?= e($coupon['code']) ?>" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Marketing Campaign</label>
                        <select name="campaign_id" class="form-select">
                            <option value="">-- Standalone (No Campaign) --</option>
                            <?php foreach ($campaigns as $camp): ?>
                                <option value="<?= $camp['id'] ?>" <?= $coupon['campaign_id'] == $camp['id'] ? 'selected' : '' ?>><?= e($camp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Internal Campaign / Promo Name</label>
                        <input type="text" name="name" class="form-control" value="<?= e($coupon['name']) ?>">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Internal Description / Notes</label>
                        <textarea name="description" rows="2" class="form-control"><?= e($coupon['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Discount Rules -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-percent text-warning me-2"></i> 2. Discount Configuration</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Discount Type <span class="text-danger">*</span></label>
                        <select name="discount_type" class="form-select" required>
                            <option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage Discount (%)</option>
                            <option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>>Fixed Amount Discount (RWF)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Discount Value <span class="text-danger">*</span></label>
                        <input type="number" name="discount_value" class="form-control" value="<?= (float)$coupon['discount_value'] ?>" min="1" step="0.01" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Maximum Discount Cap (RWF)</label>
                        <input type="number" name="max_discount_amount" class="form-control" value="<?= (float)$coupon['max_discount_amount'] ?>" min="0" step="1">
                        <small class="text-muted" style="font-size:0.7rem;">0 = Unlimited Cap.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Minimum Order Amount (RWF)</label>
                        <input type="number" name="min_spend" class="form-control" value="<?= (float)$coupon['min_spend'] ?>" min="0" step="1">
                    </div>
                </div>
            </div>

            <!-- Section 3: Inclusions & Exclusions -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-diagram-3-fill text-info me-2"></i> 3. Course & Category Applicability</h5>

                <?php
                $incCourseIds = array_column($coupon['included_courses'] ?? [], 'id');
                $excCourseIds = array_column($coupon['excluded_courses'] ?? [], 'id');
                $incCategoryIds = array_column($coupon['included_categories'] ?? [], 'id');
                $excCategoryIds = array_column($coupon['excluded_categories'] ?? [], 'id');
                ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-success"><i class="bi bi-check2-circle"></i> Included Courses (Whitelist)</label>
                        <select name="included_courses[]" class="form-select form-select-sm" multiple size="4">
                            <?php foreach ($courses as $crs): ?>
                                <option value="<?= $crs['id'] ?>" <?= in_array($crs['id'], $incCourseIds) ? 'selected' : '' ?>><?= e($crs['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-danger"><i class="bi bi-x-circle"></i> Excluded Courses (Blacklist)</label>
                        <select name="excluded_courses[]" class="form-select form-select-sm" multiple size="4">
                            <?php foreach ($courses as $crs): ?>
                                <option value="<?= $crs['id'] ?>" <?= in_array($crs['id'], $excCourseIds) ? 'selected' : '' ?>><?= e($crs['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            
            <!-- Usage Limits Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-speedometer2 text-primary me-2"></i> 4. Usage Limits</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Total Redemptions Limit</label>
                    <input type="number" name="max_uses" class="form-control" value="<?= (int)$coupon['max_uses'] ?>" min="0" step="1" required>
                    <small class="text-muted" style="font-size:0.7rem;">Currently used: <?= (int)$coupon['uses_count'] ?> times.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Per-User Limit</label>
                    <input type="number" name="per_user_limit" class="form-control" value="<?= (int)$coupon['per_user_limit'] ?>" min="1" step="1" required>
                </div>
            </div>

            <!-- Scheduling Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-calendar2-range text-warning me-2"></i> 5. Scheduling</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Start Date & Time</label>
                    <input type="datetime-local" name="start_date" class="form-control form-control-sm" value="<?= $coupon['start_date'] ? date('Y-m-d\TH:i', strtotime($coupon['start_date'])) : '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Expiration Date & Time</label>
                    <input type="datetime-local" name="expires_at" class="form-control form-control-sm" value="<?= $coupon['expires_at'] ? date('Y-m-d\TH:i', strtotime($coupon['expires_at'])) : '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="active" <?= $coupon['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="disabled" <?= $coupon['status'] === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                        <option value="archived" <?= $coupon['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow">
                    <i class="bi bi-save me-1"></i> Update Coupon
                </button>
            </div>

        </div>
    </div>
</form>
