<?php $pageTitle = 'Cohorts & Training Batches'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Cohorts & Training Batches</h2>
        <p class="text-muted small mb-0">Organize students into structured training intakes, monitor batch capacities, and track graduation cycles.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createCohortModal">
            <i class="bi bi-plus-circle-fill"></i> Create New Cohort
        </button>
    </div>
</div>

<!-- Cohorts Grid -->
<div class="row g-4 mb-4">
    <?php if (empty($cohorts)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-surface">
                <i class="bi bi-collection text-muted fs-1 mb-3"></i>
                <h5 class="fw-bold">No Training Cohorts Created</h5>
                <p class="text-muted small mb-3">Create your first student batch to group learners and schedule practical academy intakes.</p>
                <button type="button" class="btn btn-primary btn-sm d-inline-block" data-bs-toggle="modal" data-bs-target="#createCohortModal">Create Cohort</button>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($cohorts as $c): ?>
            <?php 
                $capacityPct = $c['max_students'] > 0 ? min(100, round(($c['members_count'] / $c['max_students']) * 100)) : 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary text-uppercase" style="font-family:monospace;"><?= e($c['code']) ?></span>
                        <span class="badge <?= $c['status'] === 'active' ? 'bg-success' : ($c['status'] === 'upcoming' ? 'bg-info text-dark' : 'bg-secondary') ?> text-capitalize">
                            <?= e($c['status']) ?>
                        </span>
                    </div>

                    <h5 class="fw-bold mb-2 text-dark"><?= e($c['name']) ?></h5>
                    <p class="text-muted small mb-3" style="min-height:40px;"><?= e($c['description'] ?: 'Specialty barista training intake batch.') ?></p>

                    <div class="border-top pt-3 mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted"><i class="bi bi-calendar-event me-1"></i> Start Date:</span>
                            <span class="fw-bold"><?= $c['start_date'] ? date('M d, Y', strtotime($c['start_date'])) : 'TBD' ?></span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted"><i class="bi bi-calendar-check me-1"></i> End Date:</span>
                            <span class="fw-bold"><?= $c['end_date'] ? date('M d, Y', strtotime($c['end_date'])) : 'TBD' ?></span>
                        </div>

                        <!-- Capacity Bar -->
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Capacity:</span>
                            <span class="fw-bold"><?= $c['members_count'] ?> / <?= $c['max_students'] ?> (<?= $capacityPct ?>%)</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-<?= $capacityPct >= 90 ? 'danger' : ($capacityPct >= 70 ? 'warning' : 'success') ?>" style="width: <?= $capacityPct ?>%;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <a href="<?= url('admin/cohorts/' . $c['id']) ?>" class="btn btn-outline-primary btn-sm w-100 fw-bold">
                            <i class="bi bi-people-fill me-1"></i> View Students (<?= $c['members_count'] ?>)
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal: Create New Cohort -->
<div class="modal fade" id="createCohortModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/cohorts/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-collection-fill text-primary me-2"></i> Create Training Cohort</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Cohort / Batch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Barista Pro Masterclass Q2 2026" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Batch Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="BBA-2026-Q2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Max Capacity</label>
                            <input type="number" name="max_students" class="form-control" value="25" min="1" max="500">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="upcoming" selected>Upcoming</option>
                            <option value="active">Active / In-Session</option>
                            <option value="completed">Completed / Graduated</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description (Optional)</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Batch training schedule notes, venue, instructor assignment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Create Cohort</button>
                </div>
            </form>
        </div>
    </div>
</div>
