<?php $pageTitle = 'Edit Plan: ' . e($plan['name']); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/membership-plans') ?>" class="text-decoration-none text-muted">Plans</a></li>
        <li class="breadcrumb-item active">Edit Plan</li>
    </ol>
</nav>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Edit Plan: <?= e($plan['name']) ?></h2>
        <p class="text-muted small mb-0">Modify pricing, intervals, access gating, and perks.</p>
    </div>
    <a href="<?= url('admin/membership-plans') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Plans</a>
</div>

<form action="<?= url('admin/membership-plans/' . $plan['id'] . '/update') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="row g-4">
        <div class="col-lg-8">
            
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-info-circle-fill text-primary me-2"></i> 1. Plan Overview</h5>
                
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= e($plan['name']) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Tier Level</label>
                        <select name="tier_level" class="form-select">
                            <option value="1" <?= $plan['tier_level'] == 1 ? 'selected' : '' ?>>Tier 1 (Basic / Community)</option>
                            <option value="2" <?= $plan['tier_level'] == 2 ? 'selected' : '' ?>>Tier 2 (Pro / Academy)</option>
                            <option value="3" <?= $plan['tier_level'] == 3 ? 'selected' : '' ?>>Tier 3 (VIP Master / Enterprise)</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" rows="2" class="form-control"><?= e($plan['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-currency-exchange text-success me-2"></i> 2. Pricing & Intervals</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Price (RWF) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" value="<?= (float)$plan['price'] ?>" min="0" step="1" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Billing Interval <span class="text-danger">*</span></label>
                        <select name="billing_interval" class="form-select" required>
                            <option value="month" <?= $plan['billing_interval'] === 'month' ? 'selected' : '' ?>>Monthly</option>
                            <option value="year" <?= $plan['billing_interval'] === 'year' ? 'selected' : '' ?>>Yearly (Annual)</option>
                            <option value="lifetime" <?= $plan['billing_interval'] === 'lifetime' ? 'selected' : '' ?>>Lifetime Access</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Free Trial Period (Days)</label>
                        <input type="number" name="trial_period_days" class="form-control" value="<?= (int)$plan['trial_period_days'] ?>" min="0" step="1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Grace Period (Days)</label>
                        <input type="number" name="grace_period_days" class="form-control" value="<?= (int)$plan['grace_period_days'] ?>" min="0" step="1">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-shield-lock-fill text-warning me-2"></i> 3. Course Access & Content Gating</h5>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Access Gating Type</label>
                    <select name="course_access_type" id="courseAccessType" class="form-select">
                        <option value="all_courses" <?= $plan['course_access_type'] === 'all_courses' ? 'selected' : '' ?>>Full Academy Access (All Published Courses)</option>
                        <option value="specific_courses" <?= $plan['course_access_type'] === 'specific_courses' ? 'selected' : '' ?>>Specific Selected Courses Only</option>
                        <option value="specific_categories" <?= $plan['course_access_type'] === 'specific_categories' ? 'selected' : '' ?>>Specific Categories Only</option>
                    </select>
                </div>

                <?php
                $planCourseIds = array_column($plan['courses'] ?? [], 'id');
                $planCategoryIds = array_column($plan['categories'] ?? [], 'id');
                ?>

                <div class="mb-3 <?= $plan['course_access_type'] === 'specific_courses' ? '' : 'd-none' ?>" id="specificCoursesSection">
                    <label class="form-label small fw-bold">Select Included Courses</label>
                    <select name="courses[]" class="form-select form-select-sm" multiple size="4">
                        <?php foreach ($courses as $crs): ?>
                            <option value="<?= $crs['id'] ?>" <?= in_array($crs['id'], $planCourseIds) ? 'selected' : '' ?>><?= e($crs['title']) ?> (<?= format_rwf($crs['price']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3 <?= $plan['course_access_type'] === 'specific_categories' ? '' : 'd-none' ?>" id="specificCategoriesSection">
                    <label class="form-label small fw-bold">Select Included Categories</label>
                    <select name="categories[]" class="form-select form-select-sm" multiple size="3">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $planCategoryIds) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php
            $features = json_decode($plan['features'] ?? '[]', true) ?: [];
            $featuresStr = implode("\n", $features);
            ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-list-check text-info me-2"></i> 4. Student-Facing Features</h5>

                <label class="form-label small fw-bold">Features Checklist (One per line)</label>
                <textarea name="features" rows="5" class="form-control"><?= e($featuresStr) ?></textarea>
            </div>

        </div>

        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-star-fill text-warning me-2"></i> 5. Perks & Settings</h5>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="has_certificate_access" value="1" id="certCheck" <?= !empty($plan['has_certificate_access']) ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold" for="certCheck">Includes Certificate Issuance</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="has_live_workshops" value="1" id="workshopCheck" <?= !empty($plan['has_live_workshops']) ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold" for="workshopCheck">Includes Live Workshops Access</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="has_job_board_priority" value="1" id="jobCheck" <?= !empty($plan['has_job_board_priority']) ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold" for="jobCheck">Priority Job Board Access</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featuredCheck" <?= !empty($plan['is_featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold text-primary" for="featuredCheck">Highlight as "Most Popular"</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeCheck" <?= !empty($plan['is_active']) ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold text-success" for="activeCheck">Active & Available on Pricing Page</label>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow">
                    <i class="bi bi-save me-1"></i> Update Plan
                </button>
            </div>

        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const accessType = document.getElementById('courseAccessType');
    const coursesSec = document.getElementById('specificCoursesSection');
    const catsSec = document.getElementById('specificCategoriesSection');

    function syncAccessFields() {
        if (accessType.value === 'specific_courses') {
            coursesSec.classList.remove('d-none');
            catsSec.classList.add('d-none');
        } else if (accessType.value === 'specific_categories') {
            catsSec.classList.remove('d-none');
            coursesSec.classList.add('d-none');
        } else {
            coursesSec.classList.add('d-none');
            catsSec.classList.add('d-none');
        }
    }

    accessType.addEventListener('change', syncAccessFields);
});
</script>
