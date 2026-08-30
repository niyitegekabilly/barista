<?php $pageTitle = 'Cohort: ' . e($cohort['name']); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/cohorts') ?>" class="text-decoration-none text-muted">Cohorts</a></li>
        <li class="breadcrumb-item active"><?= e($cohort['name']) ?></li>
    </ol>
</nav>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary text-uppercase" style="font-family:monospace;"><?= e($cohort['code']) ?></span>
                <span class="badge bg-success-subtle text-success text-capitalize"><?= e($cohort['status']) ?></span>
            </div>
            <h3 class="font-heading fw-bold mb-1 text-dark"><?= e($cohort['name']) ?></h3>
            <p class="text-muted small mb-0"><?= e($cohort['description'] ?: 'Specialty barista training intake batch.') ?></p>
        </div>
        <div class="text-end">
            <div class="fw-bold fs-4 text-primary"><?= count($members) ?> / <?= $cohort['max_students'] ?></div>
            <small class="text-muted">Enrolled Students</small>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-people-fill me-2"></i> Cohort Members (<?= count($members) ?>)</h5>
    
    <?php if (empty($members)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-people fs-1 mb-2 d-block"></i>
            No students are currently enrolled in this cohort batch.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Role in Cohort</th>
                        <th>Status</th>
                        <th>Enrolled Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:34px;height:34px;font-size:0.85rem;">
                                        <?= strtoupper(substr($m['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold small"><?= e($m['name']) ?></div>
                                        <div class="text-muted" style="font-size:0.75rem;"><?= e($m['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><code><?= e($m['student_id'] ?? '—') ?></code></td>
                            <td><span class="badge bg-light text-dark border text-uppercase"><?= e($m['role_in_cohort']) ?></span></td>
                            <td>
                                <span class="badge <?= $m['status'] === 'active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                                    <?= strtoupper($m['status']) ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= date('M d, Y', strtotime($m['enrolled_at'])) ?></td>
                            <td class="text-end">
                                <a href="<?= url('admin/users/' . $m['id']) ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
