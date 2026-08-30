<?php $pageTitle = 'Campaign: ' . e($campaign['name']); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/campaigns') ?>" class="text-decoration-none text-muted">Campaigns</a></li>
        <li class="breadcrumb-item active"><?= e($campaign['name']) ?></li>
    </ol>
</nav>

<!-- Campaign Hero Card -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="font-heading fw-bold mb-0 text-dark"><?= e($campaign['name']) ?></h3>
                <span class="badge bg-primary-subtle text-primary border text-uppercase"><?= e($campaign['status']) ?></span>
            </div>
            <p class="text-muted small mb-0"><?= e($campaign['description'] ?: 'No description.') ?></p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= url('admin/coupons/create?campaign_id=' . $campaign['id']) ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-plus-lg"></i> Add Coupon to Campaign
            </a>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface text-center">
            <span class="text-muted small d-block">Associated Coupons</span>
            <h4 class="fw-bold text-primary mb-0"><?= $campaign['coupons_count'] ?></h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface text-center">
            <span class="text-muted small d-block">Total Redemptions</span>
            <h4 class="fw-bold text-success mb-0"><?= $campaign['redemptions_count'] ?></h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface text-center">
            <span class="text-muted small d-block">Promotional Savings Given</span>
            <h4 class="fw-bold text-warning mb-0"><?= format_rwf($campaign['total_discount']) ?></h4>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface text-center">
            <span class="text-muted small d-block">Gross Sales Generated</span>
            <h4 class="fw-bold text-dark mb-0"><?= format_rwf($campaign['total_revenue']) ?></h4>
        </div>
    </div>
</div>

<!-- Associated Coupons Table -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-ticket-perforated text-primary me-2"></i> Promotional Coupons in this Campaign</h5>

    <?php if (empty($campaign['coupons'])): ?>
        <p class="text-muted small mb-0">No coupons linked to this campaign yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Discount Rate</th>
                        <th>Uses / Limit</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaign['coupons'] as $c): ?>
                        <tr>
                            <td><code><?= e($c['code']) ?></code></td>
                            <td class="fw-bold"><?= e($c['name']) ?></td>
                            <td><?= $c['discount_type'] === 'percentage' ? (float)$c['discount_value'] . '%' : format_money($c['discount_value'], $c['currency']) ?></td>
                            <td><?= $c['uses_count'] ?> / <?= $c['max_uses'] > 0 ? $c['max_uses'] : '∞' ?></td>
                            <td><span class="badge bg-success"><?= e($c['status']) ?></span></td>
                            <td class="text-end">
                                <a href="<?= url('admin/coupons/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
