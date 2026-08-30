<?php $pageTitle = 'Coupon: ' . e($coupon['code']); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/coupons') ?>" class="text-decoration-none text-muted">Coupons</a></li>
        <li class="breadcrumb-item active font-monospace"><?= e($coupon['code']) ?></li>
    </ol>
</nav>

<!-- Coupon Hero Header -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-4 bg-dark text-warning d-flex align-items-center justify-content-center p-3 shadow-sm" style="width:64px;height:64px;">
                <i class="bi bi-ticket-perforated-fill fs-2"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="font-heading fw-bold mb-0 text-dark font-monospace"><?= e($coupon['code']) ?></h3>
                    <?php
                    $statusClass = match ($coupon['computed_status']) {
                        'active' => 'bg-success',
                        'scheduled' => 'bg-info text-dark',
                        'expired' => 'bg-secondary',
                        'depleted' => 'bg-warning text-dark',
                        'disabled' => 'bg-danger',
                        'archived' => 'bg-dark',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?= $statusClass ?> text-uppercase px-2 py-1">
                        <?= e($coupon['computed_status']) ?>
                    </span>
                    <span class="badge bg-warning text-dark font-monospace">
                        <?= $coupon['discount_type'] === 'percentage' ? (float)$coupon['discount_value'] . '% OFF' : format_money($coupon['discount_value'], $coupon['currency']) . ' OFF' ?>
                    </span>
                </div>
                <p class="text-muted small mb-0">
                    <?= e($coupon['name']) ?>
                    <?php if (!empty($coupon['campaign_name'])): ?>
                        • Campaign: <a href="<?= url('admin/campaigns/' . $coupon['campaign_id']) ?>" class="fw-bold text-decoration-none text-primary"><i class="bi bi-megaphone me-1"></i><?= e($coupon['campaign_name']) ?></a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= url('admin/coupons/' . $coupon['id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-pencil"></i> Edit Coupon
            </a>
            <form action="<?= url('admin/coupons/' . $coupon['id'] . '/duplicate') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-copy"></i> Duplicate
                </button>
            </form>
            <form action="<?= url('admin/coupons/' . $coupon['id'] . '/toggle') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn <?= $coupon['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?> btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-power"></i> <?= $coupon['is_active'] ? 'Disable' : 'Enable' ?>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 7-Tab Navigation Workspace -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
    <ul class="nav nav-pills gap-2 mb-4 border-bottom pb-3" id="couponTabs">
        <li class="nav-item"><a class="nav-link active rounded-pill px-3" data-bs-toggle="pill" href="#tabOverview"><i class="bi bi-info-circle me-1"></i> Overview</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabRedemptions"><i class="bi bi-journal-check me-1"></i> Redemptions (<?= count($redemptions) ?>)</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabCourses"><i class="bi bi-journal-code me-1"></i> Eligible Courses</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabUsers"><i class="bi bi-people me-1"></i> Eligible Users</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabAnalytics"><i class="bi bi-graph-up me-1"></i> Analytics</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabActivity"><i class="bi bi-clock-history me-1"></i> Activity Logs</a></li>
        <li class="nav-item"><a class="nav-link rounded-pill px-3" data-bs-toggle="pill" href="#tabSettings"><i class="bi bi-gear me-1"></i> Settings</a></li>
    </ul>

    <div class="tab-content">
        
        <!-- Tab 1: Overview -->
        <div class="tab-pane fade show active" id="tabOverview">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 h-100">
                        <h6 class="fw-bold text-primary-dark mb-3"><i class="bi bi-sliders me-1"></i> Promotion Attributes</h6>
                        <table class="table table-borderless small mb-0">
                            <tr><td class="text-muted">Discount Type:</td><td class="fw-bold text-capitalize"><?= e($coupon['discount_type']) ?></td></tr>
                            <tr><td class="text-muted">Discount Value:</td><td class="fw-bold text-warning"><?= $coupon['discount_type'] === 'percentage' ? (float)$coupon['discount_value'] . '%' : format_money($coupon['discount_value'], $coupon['currency']) ?></td></tr>
                            <tr><td class="text-muted">Maximum Cap:</td><td><?= (float)$coupon['max_discount_amount'] > 0 ? format_rwf($coupon['max_discount_amount']) : '<span class="text-muted">Unlimited</span>' ?></td></tr>
                            <tr><td class="text-muted">Minimum Spend:</td><td><?= (float)$coupon['min_spend'] > 0 ? format_rwf($coupon['min_spend']) : '<span class="text-muted">None</span>' ?></td></tr>
                            <tr><td class="text-muted">Per-User Limit:</td><td class="fw-bold"><?= $coupon['per_user_limit'] ?> use(s) per student</td></tr>
                            <tr><td class="text-muted">Sale Price Rule:</td><td class="text-capitalize"><?= str_replace('_', ' ', $coupon['sale_price_rule']) ?></td></tr>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-4 h-100">
                        <h6 class="fw-bold text-primary-dark mb-3"><i class="bi bi-speedometer2 me-1"></i> Usage & Performance</h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span><?= $coupon['uses_count'] ?> of <?= $coupon['max_uses'] > 0 ? $coupon['max_uses'] : 'Unlimited' ?> Redemptions</span>
                                <?php if ($coupon['max_uses'] > 0): ?>
                                    <span><?= min(100, round(($coupon['uses_count'] / $coupon['max_uses']) * 100)) ?>%</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($coupon['max_uses'] > 0): ?>
                                <?php $pct = min(100, round(($coupon['uses_count'] / $coupon['max_uses']) * 100)); ?>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar <?= $pct >= 90 ? 'bg-danger' : 'bg-primary' ?>" style="width: <?= $pct ?>%;"></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row g-2 text-center mt-3">
                            <div class="col-6">
                                <div class="p-2 border rounded-3 bg-white">
                                    <span class="text-muted small d-block" style="font-size:0.7rem;">Discount Given</span>
                                    <strong class="text-warning small"><?= format_rwf($coupon['total_discount_given']) ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded-3 bg-white">
                                    <span class="text-muted small d-block" style="font-size:0.7rem;">Revenue Generated</span>
                                    <strong class="text-success small"><?= format_rwf($coupon['total_revenue_generated']) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Redemptions -->
        <div class="tab-pane fade" id="tabRedemptions">
            <?php if (empty($redemptions)): ?>
                <p class="text-muted small py-4 text-center">No redemptions recorded for this coupon yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Order #</th>
                                <th>Course</th>
                                <th>Original Price</th>
                                <th>Discount</th>
                                <th>Final Paid</th>
                                <th>Redeemed Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($redemptions as $r): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($r['user_name']) ?></div>
                                        <small class="text-muted"><?= e($r['user_email']) ?></small>
                                    </td>
                                    <td><a href="<?= url('admin/orders/' . $r['order_id']) ?>" class="font-monospace fw-bold text-decoration-none"><code><?= e($r['order_number']) ?></code></a></td>
                                    <td><?= e($r['course_title'] ?: 'Full Cart') ?></td>
                                    <td><?= format_money($r['original_amount'], $r['currency']) ?></td>
                                    <td class="text-success fw-bold">-<?= format_money($r['discount_amount'], $r['currency']) ?></td>
                                    <td class="fw-bold"><?= format_money($r['final_amount'], $r['currency']) ?></td>
                                    <td class="text-muted"><?= date('M d, Y H:i', strtotime($r['redeemed_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 3: Eligible Courses & Categories -->
        <div class="tab-pane fade" id="tabCourses">
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-check2-circle me-1"></i> Included Courses</h6>
                    <?php if (empty($coupon['included_courses'])): ?>
                        <p class="text-muted small">Applies to all courses in the academy.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush small">
                            <?php foreach ($coupon['included_courses'] as $crs): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= e($crs['title']) ?></span>
                                    <span class="text-muted"><?= format_rwf($crs['price']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <h6 class="fw-bold text-danger mb-2"><i class="bi bi-x-circle me-1"></i> Explicitly Excluded Courses</h6>
                    <?php if (empty($coupon['excluded_courses'])): ?>
                        <p class="text-muted small">No courses explicitly excluded.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush small">
                            <?php foreach ($coupon['excluded_courses'] as $crs): ?>
                                <li class="list-group-item text-danger"><i class="bi bi-ban me-1"></i> <?= e($crs['title']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab 4: Eligible Users -->
        <div class="tab-pane fade" id="tabUsers">
            <div class="p-3 bg-light rounded-4">
                <h6 class="fw-bold text-primary-dark mb-2">Student Eligibility Rule</h6>
                <div class="small mb-3">
                    <?php if ($coupon['user_eligibility'] === 'all'): ?>
                        <span class="badge bg-success">All Students & Guests</span>
                        <p class="text-muted mt-2 mb-0">Open to all registered learners across the academy.</p>
                    <?php elseif ($coupon['user_eligibility'] === 'new_users_only'): ?>
                        <span class="badge bg-warning text-dark">New Students Only</span>
                        <p class="text-muted mt-2 mb-0">Restricted to learners who have never purchased a paid course before.</p>
                    <?php elseif ($coupon['user_eligibility'] === 'specific_users'): ?>
                        <span class="badge bg-info text-dark">Specific Whitelist</span>
                        <p class="text-muted mt-2 mb-0">Restricted to <?= count($coupon['restricted_users'] ?? []) ?> specific student account(s).</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab 5: Analytics -->
        <div class="tab-pane fade" id="tabAnalytics">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-4 text-center">
                        <span class="text-muted small d-block">Total Redemptions</span>
                        <h4 class="fw-bold text-primary mb-0"><?= $coupon['redemptions_count'] ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-4 text-center">
                        <span class="text-muted small d-block">Gross Revenue Impact</span>
                        <h4 class="fw-bold text-success mb-0"><?= format_rwf($coupon['total_revenue_generated']) ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-4 text-center">
                        <span class="text-muted small d-block">Total Savings Given</span>
                        <h4 class="fw-bold text-warning mb-0"><?= format_rwf($coupon['total_discount_given']) ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 6: Activity Logs -->
        <div class="tab-pane fade" id="tabActivity">
            <?php if (empty($activityLogs)): ?>
                <p class="text-muted small py-4 text-center">No activity logged for this coupon yet.</p>
            <?php else: ?>
                <div class="timeline position-relative ps-4" style="border-left: 2px solid #E5E7EB;">
                    <?php foreach ($activityLogs as $al): ?>
                        <div class="mb-3 position-relative">
                            <span class="position-absolute bg-primary rounded-circle" style="width:10px;height:10px;left:-21px;top:5px;"></span>
                            <div class="fw-bold small text-dark text-capitalize"><?= str_replace('_', ' ', $al['action']) ?></div>
                            <small class="text-muted"><?= e($al['user_name'] ?? 'System') ?> • <?= date('M d, Y H:i', strtotime($al['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 7: Settings & Danger Zone -->
        <div class="tab-pane fade" id="tabSettings">
            <div class="p-3 border rounded-4 bg-light mb-3">
                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Promotion Lifecycle Actions</h6>
                <p class="text-muted small mb-3">Archive or permanently remove this promotional coupon.</p>
                <div class="d-flex gap-2">
                    <form action="<?= url('admin/coupons/' . $coupon['id'] . '/archive') ?>" method="POST" onsubmit="return confirm('Archive this coupon?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-secondary btn-sm fw-bold">Archive Coupon</button>
                    </form>
                    <?php if ($coupon['redemptions_count'] == 0): ?>
                        <form action="<?= url('admin/coupons/' . $coupon['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Permanently delete this coupon code?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm fw-bold">Delete Coupon</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
