<?php $pageTitle = 'Certificates & Credentials Management'; ?>

<div class="container-fluid py-4">
    
    <!-- Top Action Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="font-heading fw-bold text-dark mb-1">Certificates & Credentials Hub</h2>
            <p class="text-muted small mb-0">Monitor issued qualifications, manage revocations, and issue manual course certificates.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('admin/certificates/export') ?>" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
                <i class="bi bi-download me-1"></i> Export CSV
            </a>
            <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalIssueCert">
                <i class="bi bi-plus-lg me-1"></i> Issue Manual Certificate
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Total Issued</span>
                        <h3 class="font-heading fw-bold text-dark mb-0"><?= number_format($kpis['total_issued']) ?></h3>
                    </div>
                    <div class="p-3 bg-primary-subtle text-primary rounded-4">
                        <i class="bi bi-award-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Active & Valid</span>
                        <h3 class="font-heading fw-bold text-success mb-0"><?= number_format($kpis['total_valid']) ?></h3>
                    </div>
                    <div class="p-3 bg-success-subtle text-success rounded-4">
                        <i class="bi bi-patch-check-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Revoked</span>
                        <h3 class="font-heading fw-bold text-danger mb-0"><?= number_format($kpis['total_revoked']) ?></h3>
                    </div>
                    <div class="p-3 bg-danger-subtle text-danger rounded-4">
                        <i class="bi bi-slash-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Total Verifications</span>
                        <h3 class="font-heading fw-bold text-warning mb-0"><?= number_format($kpis['total_verifications']) ?></h3>
                    </div>
                    <div class="p-3 bg-warning-subtle text-warning rounded-4">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table Card -->
    <div class="card border-0 shadow-sm rounded-4 bg-surface p-4">
        
        <form method="GET" action="<?= url('admin/certificates') ?>" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search Certificate</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Serial, student name, email..." value="<?= e($search) ?>" />
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm bg-light">
                    <option value="">All Statuses</option>
                    <option value="valid" <?= $status === 'valid' ? 'selected' : '' ?>>Valid</option>
                    <option value="revoked" <?= $status === 'revoked' ? 'selected' : '' ?>>Revoked</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small text-muted">Course</label>
                <select name="course_id" class="form-select form-select-sm bg-light">
                    <option value="0">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $courseId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Filter</button>
            </div>
        </form>

        <!-- Certificates Data Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
                        <th>Certificate #</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Issue Date</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Verifications</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($certificates)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-award display-4 d-block mb-2 opacity-50"></i>
                                No certificate records found matching your filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($certificates as $cert): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('certificate/print/' . e($cert['certificate_number'])) ?>" target="_blank" class="fw-bold font-monospace text-primary text-decoration-none">
                                        <?= e($cert['certificate_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <strong class="d-block text-dark"><?= e($cert['student_name']) ?></strong>
                                    <small class="text-muted"><?= e($cert['student_email']) ?></small>
                                </td>
                                <td>
                                    <span class="d-block text-dark small fw-medium"><?= e($cert['course_title']) ?></span>
                                </td>
                                <td>
                                    <span class="text-dark small"><?= date('M d, Y', strtotime($cert['issue_date'])) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($cert['grade_score'])): ?>
                                        <span class="badge bg-light text-dark border"><?= e($cert['grade_score']) ?>% (<?= e($cert['grade_letter'] ?? 'Pass') ?>)</span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($cert['status'] === 'valid'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">Valid</span>
                                    <?php elseif ($cert['status'] === 'revoked'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger" title="<?= e($cert['revocation_reason']) ?>">Revoked</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border"><?= ucfirst($cert['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-muted border"><?= (int)$cert['verifications_count'] ?> views</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('certificate/print/' . e($cert['certificate_number'])) ?>" target="_blank" class="btn btn-outline-secondary" title="Print Layout">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="<?= url('certificate/verify/' . e($cert['certificate_number'])) ?>" target="_blank" class="btn btn-outline-secondary" title="Verify Portal">
                                            <i class="bi bi-shield-check"></i>
                                        </a>
                                        <?php if ($cert['status'] === 'valid'): ?>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRevokeCert<?= $cert['id'] ?>" title="Revoke">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        <?php else: ?>
                                            <form action="<?= url('admin/certificates/' . $cert['id'] . '/reissue') ?>" method="POST" class="d-inline" onsubmit="return confirm('Restore this certificate to valid status?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-success" title="Restore Valid">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Revoke Modal -->
                                    <?php if ($cert['status'] === 'valid'): ?>
                                        <div class="modal fade" id="modalRevokeCert<?= $cert['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg rounded-4 text-start">
                                                    <form action="<?= url('admin/certificates/' . $cert['id'] . '/revoke') ?>" method="POST">
                                                        <?= csrf_field() ?>
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="modal-title font-heading fw-bold text-danger">Revoke Certificate</h5>
                                                            <button type="button" class="btn-close" data-bs-toggle="modal"></button>
                                                        </div>
                                                        <div class="modal-body py-3">
                                                            <p class="text-dark small mb-3">Are you sure you want to revoke certificate <strong class="font-monospace"><?= e($cert['certificate_number']) ?></strong> for <strong><?= e($cert['student_name']) ?></strong>?</p>
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold">Reason for Revocation</label>
                                                                <textarea name="revocation_reason" class="form-control form-control-sm" rows="3" placeholder="e.g. Academic integrity violation or duplicate issuance..." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger btn-sm fw-bold">Confirm Revocation</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['last_page'] > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <small class="text-muted">Showing <?= count($certificates) ?> of <?= $pagination['total'] ?> certificates</small>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                        <li class="page-item <?= $pagination['current_page'] === $i ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('admin/certificates?page=' . $i . '&q=' . urlencode($search) . '&status=' . urlencode($status) . '&course_id=' . $courseId) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal: Issue Manual Certificate -->
<div class="modal fade" id="modalIssueCert" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/certificates/issue') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-heading fw-bold text-dark">Issue Manual Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Student</label>
                        <select name="user_id" class="form-select form-select-sm" required>
                            <option value="">Choose student...</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>) <?= !empty($u['student_id']) ? '[' . e($u['student_id']) . ']' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Course</label>
                        <select name="course_id" class="form-select form-select-sm" required>
                            <option value="">Choose course...</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Grade Score (%)</label>
                        <input type="number" name="grade_score" class="form-control form-control-sm" min="60" max="100" step="0.5" value="100.00" required />
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Issue Certificate</button>
                </div>
            </form>
        </div>
    </div>
</div>
