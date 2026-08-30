<?php $pageTitle = 'Create Promotional Coupon'; ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/coupons') ?>" class="text-decoration-none text-muted">Coupons</a></li>
        <li class="breadcrumb-item active">Create Promotion</li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Create Promotional Coupon</h2>
        <p class="text-muted small mb-0">Configure discount parameters, eligibility rules, course inclusions, and usage limits.</p>
    </div>
    <a href="<?= url('admin/coupons') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<form action="<?= url('admin/coupons/store') ?>" method="POST" id="createCouponForm">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- Left 8 Cols: Form Sections -->
        <div class="col-lg-8">
            
            <!-- Section 1: Basic Information -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-info-circle-fill text-primary me-2"></i> 1. Basic Information</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Coupon Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="code" id="couponCodeInput" class="form-control font-monospace text-uppercase fw-bold" placeholder="e.g. BARISTA20" value="<?= e($suggestedCode) ?>" required>
                            <button type="button" class="btn btn-outline-primary" id="btnGenRandomCode" title="Generate Random Code"><i class="bi bi-dice-5-fill"></i></button>
                        </div>
                        <small class="text-muted" style="font-size:0.7rem;">Learners will type this code during checkout.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Marketing Campaign</label>
                        <select name="campaign_id" class="form-select">
                            <option value="">-- Standalone (No Campaign) --</option>
                            <?php foreach ($campaigns as $camp): ?>
                                <option value="<?= $camp['id'] ?>"><?= e($camp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Internal Campaign / Promo Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Easter Specialty Coffee 20% Discount">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Internal Description / Notes</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Optional internal notes about this promotion..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Discount Rules -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-percent text-warning me-2"></i> 2. Discount Configuration</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Discount Type <span class="text-danger">*</span></label>
                        <select name="discount_type" id="discountTypeSelect" class="form-select" required>
                            <option value="percentage">Percentage Discount (%)</option>
                            <option value="fixed">Fixed Amount Discount (RWF)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Discount Value <span class="text-danger">*</span></label>
                        <input type="number" name="discount_value" id="discountValueInput" class="form-control" placeholder="e.g. 20 for 20%" value="20" min="1" step="0.01" required>
                    </div>

                    <div class="col-md-6" id="maxCapCol">
                        <label class="form-label small fw-bold">Maximum Discount Cap (RWF)</label>
                        <input type="number" name="max_discount_amount" class="form-control" placeholder="0 = Unlimited Cap" value="0" min="0" step="1">
                        <small class="text-muted" style="font-size:0.7rem;">Optional ceiling cap for percentage discounts (e.g. 15,000 RWF).</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Minimum Order Amount (RWF)</label>
                        <input type="number" name="min_spend" class="form-control" placeholder="0 = No minimum" value="0" min="0" step="1">
                        <small class="text-muted" style="font-size:0.7rem;">Minimum subtotal required to apply this coupon.</small>
                    </div>
                </div>
            </div>

            <!-- Section 3: Inclusions & Exclusions -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-diagram-3-fill text-info me-2"></i> 3. Course & Category Applicability</h5>

                <ul class="nav nav-pills mb-3 small" id="applicabilityTabs">
                    <li class="nav-item"><a class="nav-link active py-1 px-3" data-bs-toggle="pill" href="#tabCourses">Course Specifics</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-3" data-bs-toggle="pill" href="#tabCategories">Category Specifics</a></li>
                </ul>

                <div class="tab-content">
                    <!-- Courses Tab -->
                    <div class="tab-pane fade show active" id="tabCourses">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-success"><i class="bi bi-check2-circle"></i> Included Courses (Whitelist)</label>
                                <select name="included_courses[]" class="form-select form-select-sm" multiple size="4">
                                    <?php foreach ($courses as $crs): ?>
                                        <option value="<?= $crs['id'] ?>"><?= e($crs['title']) ?> (<?= format_rwf($crs['price']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted" style="font-size:0.7rem;">Leave unselected to apply to all academy courses.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-danger"><i class="bi bi-x-circle"></i> Excluded Courses (Blacklist)</label>
                                <select name="excluded_courses[]" class="form-select form-select-sm" multiple size="4">
                                    <?php foreach ($courses as $crs): ?>
                                        <option value="<?= $crs['id'] ?>"><?= e($crs['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted" style="font-size:0.7rem;">Explicitly disallowed courses (e.g. premium masterclasses).</small>
                            </div>
                        </div>
                    </div>

                    <!-- Categories Tab -->
                    <div class="tab-pane fade" id="tabCategories">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-success"><i class="bi bi-check2-circle"></i> Included Categories</label>
                                <select name="included_categories[]" class="form-select form-select-sm" multiple size="4">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-danger"><i class="bi bi-x-circle"></i> Excluded Categories</label>
                                <select name="excluded_categories[]" class="form-select form-select-sm" multiple size="4">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: User Eligibility -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-people-fill text-secondary me-2"></i> 4. Student Eligibility</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Eligible User Group</label>
                        <select name="user_eligibility" id="userEligibilitySelect" class="form-select">
                            <option value="all">All Students & Guests</option>
                            <option value="new_users_only">New Students Only (First Course Purchase)</option>
                            <option value="first_purchase_only">First-Time Checkout Only</option>
                            <option value="specific_users">Specific Whitelisted Students</option>
                        </select>
                    </div>

                    <div class="col-md-6 d-none" id="specificUsersCol">
                        <label class="form-label small fw-bold">Select Eligible Students</label>
                        <select name="restricted_users[]" class="form-select form-select-sm" multiple size="3">
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right 4 Cols: Limits, Scheduling, Stacking & Submit -->
        <div class="col-lg-4">
            
            <!-- Usage Limits Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-speedometer2 text-primary me-2"></i> 5. Usage Limits</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Total Redemptions Limit</label>
                    <input type="number" name="max_uses" class="form-control" value="100" min="0" step="1" required>
                    <small class="text-muted" style="font-size:0.7rem;">0 = Unlimited total redemptions.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Per-User Limit</label>
                    <input type="number" name="per_user_limit" class="form-control" value="1" min="1" step="1" required>
                    <small class="text-muted" style="font-size:0.7rem;">Number of times a single student can use this code.</small>
                </div>
            </div>

            <!-- Scheduling Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-calendar2-range text-warning me-2"></i> 6. Scheduling & Validity</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Start Date & Time</label>
                    <input type="datetime-local" name="start_date" class="form-control form-control-sm">
                    <small class="text-muted" style="font-size:0.7rem;">Leave blank for immediate activation.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Expiration Date & Time</label>
                    <input type="datetime-local" name="expires_at" class="form-control form-control-sm">
                    <small class="text-muted" style="font-size:0.7rem;">Leave blank for no expiration.</small>
                </div>
            </div>

            <!-- Stacking & Sale Rules Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-sliders text-secondary me-2"></i> 7. Rules & Behavior</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Sale Price Interaction</label>
                    <select name="sale_price_rule" class="form-select form-select-sm">
                        <option value="apply_to_sale_price">Apply on top of existing sale price</option>
                        <option value="exclude_sale_items">Exclude courses currently on sale</option>
                    </select>
                </div>

                <div class="form-check p-2 border rounded-3 bg-light">
                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_stackable" value="1" id="stackableCheck">
                    <label class="form-check-label small fw-bold" for="stackableCheck">Allow stacking with other coupons</label>
                </div>
            </div>

            <!-- Submit Button Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow">
                    <i class="bi bi-check2-circle me-1"></i> Save & Publish Coupon
                </button>
            </div>

        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const codeInput = document.getElementById('couponCodeInput');
    const btnGen = document.getElementById('btnGenRandomCode');
    const eligibilitySelect = document.getElementById('userEligibilitySelect');
    const specificUsersCol = document.getElementById('specificUsersCol');

    btnGen.addEventListener('click', function () {
        const charset = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        let code = 'BBA';
        for (let i = 0; i < 6; i++) {
            code += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        codeInput.value = code;
    });

    eligibilitySelect.addEventListener('change', function () {
        if (this.value === 'specific_users') {
            specificUsersCol.classList.remove('d-none');
        } else {
            specificUsersCol.classList.add('d-none');
        }
    });
});
</script>
