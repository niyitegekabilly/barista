<?php $pageTitle = 'Student Subscriptions Hub'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark"><i class="bi bi-people-fill text-primary me-2"></i> Student Subscriptions Hub</h2>
        <p class="text-muted small mb-0">Manage active memberships, track expiration dates, and process complimentary extensions.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('admin/memberships/dashboard') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-graph-up"></i> MRR Analytics
        </a>
        <a href="<?= url('admin/membership-plans') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-layers"></i> Membership Plans
        </a>
        <a href="<?= url('admin/memberships/export') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-surface">
    <form action="<?= url('admin/memberships') ?>" method="GET" id="subscriptionsFilterForm">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm border-start-0" placeholder="Search subscription #, student name, email, plan..." value="<?= e($filters['q']) ?>">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="document.getElementById('subscriptionsFilterForm').submit()">
                    <option value="all">All Statuses</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active Access</option>
                    <option value="trialing" <?= $filters['status'] === 'trialing' ? 'selected' : '' ?>>In Free Trial</option>
                    <option value="grace_period" <?= $filters['status'] === 'grace_period' ? 'selected' : '' ?>>In Grace Period</option>
                    <option value="expired" <?= $filters['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="plan_id" class="form-select form-select-sm" onchange="document.getElementById('subscriptionsFilterForm').submit()">
                    <option value="all">All Plans & Tiers</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($filters['plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel-fill"></i></button>
                <a href="<?= url('admin/memberships') ?>" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Subscriptions Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-surface">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Subscription #</th>
                    <th>Student Member</th>
                    <th>Plan & Billing</th>
                    <th>Validity Period</th>
                    <th>Days Remaining</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subscriptions)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-2 mb-2 d-block"></i>
                            No subscriptions found matching your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subscriptions as $s): ?>
                        <tr>
                            <td>
                                <a href="<?= url('admin/memberships/' . $s['id']) ?>" class="fw-bold font-monospace text-decoration-none text-primary-dark">
                                    <code><?= e($s['subscription_number']) ?></code>
                                </a>
                                <small class="text-muted d-block"><?= $s['auto_renew'] ? '<span class="text-success"><i class="bi bi-arrow-repeat"></i> Auto-Renew</span>' : '<span class="text-muted">Manual</span>' ?></small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($s['user_name']) ?></div>
                                <small class="text-muted"><?= e($s['user_email']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border"><?= e($s['plan_name']) ?></span>
                                <small class="text-muted d-block"><?= format_rwf($s['plan_price']) ?> / <?= e($s['billing_interval']) ?></small>
                            </td>
                            <td class="small">
                                <div><i class="bi bi-calendar-check me-1 text-muted"></i> <?= date('M d, Y', strtotime($s['start_date'])) ?></div>
                                <div class="text-muted"><i class="bi bi-calendar-x me-1"></i> <?= date('M d, Y', strtotime($s['end_date'])) ?></div>
                            </td>
                            <td style="min-width: 140px;">
                                <?php if (in_array($s['status'], ['active', 'trialing', 'grace_period']) && $s['days_remaining'] > 0): ?>
                                    <div class="d-flex justify-content-between small fw-bold mb-1">
                                        <span class="<?= $s['days_remaining'] <= 3 ? 'text-danger' : 'text-success' ?>"><?= $s['days_remaining'] ?> day(s) left</span>
                                    </div>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar <?= $s['days_remaining'] <= 3 ? 'bg-danger' : 'bg-success' ?>" style="width: <?= min(100, $s['days_remaining'] * 3.3) ?>%;"></div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">0 days (Ended)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusBadge = match ($s['status']) {
                                    'active' => 'bg-success',
                                    'trialing' => 'bg-info text-dark',
                                    'grace_period' => 'bg-warning text-dark',
                                    'expired' => 'bg-secondary',
                                    'cancelled' => 'bg-danger',
                                    'paused' => 'bg-dark',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusBadge ?> text-uppercase" style="font-size:0.72rem;">
                                    <?= e($s['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('admin/memberships/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    360° Workspace
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['last_page'] > 1): ?>
        <div class="card-footer bg-surface border-0 py-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing page <?= $pagination['current_page'] ?> of <?= $pagination['last_page'] ?> (<?= $pagination['total'] ?> total)</small>
            <div class="d-flex gap-1">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="<?= url('admin/memberships?page=' . ($pagination['current_page'] - 1) . '&' . http_build_query($filters)) ?>" class="btn btn-sm btn-outline-secondary">&laquo; Previous</a>
                <?php endif; ?>
                <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                    <a href="<?= url('admin/memberships?page=' . ($pagination['current_page'] + 1) . '&' . http_build_query($filters)) ?>" class="btn btn-sm btn-outline-secondary">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
