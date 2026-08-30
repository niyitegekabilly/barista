<?php $pageTitle = 'Marketing Campaigns'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark"><i class="bi bi-megaphone-fill text-primary me-2"></i> Marketing Promotion Campaigns</h2>
        <p class="text-muted small mb-0">Group promotional codes into strategic marketing campaigns and track budget utilization.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/coupons') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-ticket-perforated"></i> Coupons Hub</a>
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
            <i class="bi bi-plus-lg"></i> Create Campaign
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php if (empty($campaigns)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-surface">
                <i class="bi bi-megaphone text-muted fs-1 mb-2 d-block"></i>
                <h5 class="fw-bold text-dark">No Marketing Campaigns Yet</h5>
                <p class="text-muted small mb-3">Organize multiple discount codes under a single seasonal or intake campaign.</p>
                <button type="button" class="btn btn-primary btn-sm mx-auto" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
                    <i class="bi bi-plus-lg me-1"></i> Create First Campaign
                </button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($campaigns as $camp): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-surface position-relative overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary-subtle text-primary border text-uppercase" style="font-size:0.7rem;">
                            <?= e($camp['status']) ?>
                        </span>
                        <small class="text-muted"><?= $camp['coupons_count'] ?> coupon(s)</small>
                    </div>

                    <h5 class="font-heading fw-bold mb-1">
                        <a href="<?= url('admin/campaigns/' . $camp['id']) ?>" class="text-decoration-none text-dark hover-primary">
                            <?= e($camp['name']) ?>
                        </a>
                    </h5>
                    <p class="text-muted small mb-3 text-truncate-2" style="min-height: 38px;">
                        <?= e($camp['description'] ?: 'No campaign description provided.') ?>
                    </p>

                    <!-- Budget Progress Bar -->
                    <?php if ((float)$camp['budget_limit'] > 0): ?>
                        <?php $budgetPct = min(100, round(($camp['discount_spent'] / $camp['budget_limit']) * 100)); ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Budget Spent: <?= format_rwf($camp['discount_spent']) ?></span>
                                <span class="fw-bold"><?= $budgetPct ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar <?= $budgetPct >= 90 ? 'bg-danger' : 'bg-success' ?>" style="width: <?= $budgetPct ?>%;"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="border-top pt-3 mt-auto d-flex justify-content-between align-items-center small">
                        <div>
                            <span class="text-muted d-block" style="font-size:0.7rem;">Sales Generated</span>
                            <strong class="text-success"><?= format_rwf($camp['total_revenue']) ?></strong>
                        </div>
                        <a href="<?= url('admin/campaigns/' . $camp['id']) ?>" class="btn btn-sm btn-outline-primary">Manage &rarr;</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal: Create Campaign -->
<div class="modal fade" id="createCampaignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/campaigns/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold text-primary-dark"><i class="bi bi-megaphone me-2"></i> Create Marketing Campaign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Campaign Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Christmas Coffee Festival 2026" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Goals and target audience for this campaign..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Promotional Budget Limit (RWF)</label>
                        <input type="number" name="budget_limit" class="form-control" placeholder="0 = Unlimited budget" value="0" min="0" step="1">
                        <small class="text-muted" style="font-size:0.7rem;">Discounts will automatically halt when budget is depleted.</small>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="datetime-local" name="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="datetime-local" name="end_date" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Create Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>
