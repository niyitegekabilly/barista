<?php $pageTitle = 'Membership Plans & Tiers'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark"><i class="bi bi-layers-fill text-primary me-2"></i> Membership Plans & Tiers</h2>
        <p class="text-muted small mb-0">Configure subscription packages, course access tiers, and recurring pricing rules.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/memberships') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-people"></i> Subscriptions</a>
        <a href="<?= url('admin/membership-plans/create') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-lg"></i> Create Plan
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($plans as $p): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-surface position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-primary-subtle text-primary border text-uppercase" style="font-size:0.7rem;">
                        Tier <?= $p['tier_level'] ?> • <?= e($p['billing_interval']) ?>
                    </span>
                    <span class="badge <?= $p['is_active'] ? 'bg-success' : 'bg-secondary' ?>" style="font-size:0.7rem;">
                        <?= $p['is_active'] ? 'Active' : 'Disabled' ?>
                    </span>
                </div>

                <h4 class="font-heading fw-bold mb-1 text-dark"><?= e($p['name']) ?></h4>
                <p class="text-muted small mb-3 text-truncate-2" style="min-height: 38px;">
                    <?= e($p['description'] ?: 'No description.') ?>
                </p>

                <div class="d-flex align-items-baseline gap-1 mb-3">
                    <h3 class="fw-bold text-dark mb-0"><?= format_rwf($p['price']) ?></h3>
                    <span class="text-muted small">/ <?= e($p['billing_interval']) ?></span>
                </div>

                <div class="p-3 bg-light rounded-3 mb-3 small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Active Subscribers:</span>
                        <strong class="text-primary"><?= $p['active_subscribers_count'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">MRR Contribution:</span>
                        <strong class="text-success"><?= format_rwf($p['mrr_contribution']) ?></strong>
                    </div>
                </div>

                <div class="border-top pt-3 mt-auto d-flex justify-content-between align-items-center">
                    <a href="<?= url('admin/membership-plans/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Edit Plan</a>
                    <form action="<?= url('admin/membership-plans/' . $p['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete or archive this membership plan?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
