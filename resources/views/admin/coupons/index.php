<?php $pageTitle = 'Coupons & Discount Codes'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Coupons & Promo Codes</h2>
        <p class="text-muted small mb-0">Create, manage, and monitor promotional discounts and campaign codes.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('admin/coupons/dashboard') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-graph-up"></i> Analytics Dashboard
        </a>
        <a href="<?= url('admin/coupons/redemptions') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-journal-check"></i> Redemptions History
        </a>
        <a href="<?= url('admin/coupons/export') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <a href="<?= url('admin/coupons/bulk-generate') ?>" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-magic"></i> Bulk Generator
        </a>
        <a href="<?= url('admin/coupons/create') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> Create Coupon
        </a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-surface">
    <form action="<?= url('admin/coupons') ?>" method="GET" id="couponsFilterForm">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm border-start-0" placeholder="Search coupon code, campaign name..." value="<?= e($filters['q']) ?>">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="document.getElementById('couponsFilterForm').submit()">
                    <option value="all">All Statuses</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active & Usable</option>
                    <option value="scheduled" <?= $filters['status'] === 'scheduled' ? 'selected' : '' ?>>Upcoming / Scheduled</option>
                    <option value="expired" <?= $filters['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="disabled" <?= $filters['status'] === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                    <option value="archived" <?= $filters['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="campaign_id" class="form-select form-select-sm" onchange="document.getElementById('couponsFilterForm').submit()">
                    <option value="all">All Campaigns</option>
                    <?php foreach ($campaigns as $camp): ?>
                        <option value="<?= $camp['id'] ?>" <?= ($filters['campaign_id'] ?? '') == $camp['id'] ? 'selected' : '' ?>><?= e($camp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="<?= url('admin/coupons') ?>" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Coupons Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-surface">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Code & Campaign</th>
                    <th>Discount Rate</th>
                    <th>Redemptions / Limit</th>
                    <th>Revenue Generated</th>
                    <th>Validity Period</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coupons)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-ticket-perforated fs-2 mb-2 d-block"></i>
                            No coupons found matching your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($coupons as $c): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="<?= url('admin/coupons/' . $c['id']) ?>" class="fw-bold font-monospace text-decoration-none fs-6 text-primary-dark">
                                        <?= e($c['code']) ?>
                                    </a>
                                </div>
                                <?php if (!empty($c['campaign_name'])): ?>
                                    <small class="text-muted d-block"><i class="bi bi-megaphone me-1 text-primary"></i> <?= e($c['campaign_name']) ?></small>
                                <?php else: ?>
                                    <small class="text-muted d-block"><?= e($c['name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    <?php if ($c['discount_type'] === 'percentage'): ?>
                                        <span class="badge bg-warning text-dark font-monospace"><?= (float)$c['discount_value'] ?>% OFF</span>
                                        <?php if ((float)$c['max_discount_amount'] > 0): ?>
                                            <small class="text-muted d-block" style="font-size:0.7rem;">Cap: <?= format_rwf($c['max_discount_amount']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-success font-monospace"><?= format_money($c['discount_value'], $c['currency']) ?> OFF</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ((float)$c['min_spend'] > 0): ?>
                                    <small class="text-muted" style="font-size:0.7rem;">Min: <?= format_rwf($c['min_spend']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="min-width: 150px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold"><?= $c['uses_count'] ?> used</span>
                                    <span class="text-muted"><?= $c['max_uses'] > 0 ? $c['max_uses'] . ' max' : 'Unlimited' ?></span>
                                </div>
                                <?php if ($c['max_uses'] > 0): ?>
                                    <?php $pct = min(100, round(($c['uses_count'] / $c['max_uses']) * 100)); ?>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar <?= $pct >= 90 ? 'bg-danger' : ($pct >= 50 ? 'bg-warning' : 'bg-primary') ?>" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-bold text-success"><?= format_rwf($c['total_revenue_generated']) ?></span>
                            </td>
                            <td class="small">
                                <div><i class="bi bi-calendar-event me-1 text-muted"></i> <?= $c['start_date'] ? date('M d, Y', strtotime($c['start_date'])) : 'Immediate' ?></div>
                                <div class="text-muted"><i class="bi bi-clock-history me-1"></i> <?= $c['expires_at'] ? date('M d, Y', strtotime($c['expires_at'])) : 'No Expiry' ?></div>
                            </td>
                            <td>
                                <?php
                                $statusClass = match ($c['computed_status']) {
                                    'active' => 'bg-success',
                                    'scheduled' => 'bg-info text-dark',
                                    'expired' => 'bg-secondary',
                                    'depleted' => 'bg-warning text-dark',
                                    'disabled' => 'bg-danger',
                                    'archived' => 'bg-dark',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?> text-uppercase" style="font-size:0.72rem;">
                                    <?= e($c['computed_status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                        <li><a class="dropdown-item" href="<?= url('admin/coupons/' . $c['id']) ?>"><i class="bi bi-eye me-2 text-primary"></i> 360° Workspace</a></li>
                                        <li><a class="dropdown-item" href="<?= url('admin/coupons/' . $c['id'] . '/edit') ?>"><i class="bi bi-pencil me-2 text-secondary"></i> Edit Coupon</a></li>
                                        <li>
                                            <form action="<?= url('admin/coupons/' . $c['id'] . '/duplicate') ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item"><i class="bi bi-copy me-2 text-info"></i> Duplicate Code</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= url('admin/coupons/' . $c['id'] . '/toggle') ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-power me-2 <?= $c['is_active'] ? 'text-danger' : 'text-success' ?>"></i>
                                                    <?= $c['is_active'] ? 'Disable Coupon' : 'Enable Coupon' ?>
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="<?= url('admin/coupons/' . $c['id'] . '/archive') ?>" method="POST" onsubmit="return confirm('Archive this coupon?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item text-secondary"><i class="bi bi-archive me-2"></i> Archive</button>
                                            </form>
                                        </li>
                                        <?php if ($c['uses_count'] == 0): ?>
                                            <li>
                                                <form action="<?= url('admin/coupons/' . $c['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this coupon code?')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Delete</button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
