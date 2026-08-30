<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1">My Earned Certificates</h2>
        <p class="text-muted small mb-0">Verified digital credentials issued by Beyond Barista Academy Rwanda</p>
    </div>
    <a href="<?= url('certificate/verify') ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
        <i class="bi bi-patch-check me-1"></i> Public Verification Portal
    </a>
</div>

<?php if (empty($certificates)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4">
        <i class="bi bi-award display-4 text-muted mb-3"></i>
        <h4 class="font-heading">No certificates earned yet</h4>
        <p class="text-muted small mb-4">Complete 100% of your course lessons and pass the final exam to receive your official certificate.</p>
        <div>
            <a href="<?= url('student/courses') ?>" class="btn btn-primary">Go to My Courses</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($certificates as $cert): ?>
            <div class="col-lg-6">
                <div class="card p-4 border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i> VERIFIED & VALID</span>
                        <span class="font-monospace text-muted small"><?= e($cert['certificate_number']) ?></span>
                    </div>

                    <h4 class="font-heading mb-2 text-dark"><?= e($cert['course_title']) ?></h4>
                    <p class="text-muted small mb-4">Issued on <?= date('F d, Y', strtotime($cert['issue_date'])) ?> to <strong><?= e($cert['student_name']) ?></strong></p>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="<?= url('student/certificates/' . e($cert['certificate_number'])) ?>" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-eye-fill me-1"></i> View Diploma
                        </a>
                        <a href="<?= url('certificate/verify/' . e($cert['certificate_number'])) ?>" class="btn btn-outline-secondary" target="_blank" title="Verify Online">
                            <i class="bi bi-qr-code-scan"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
