<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <a href="<?= url('student/certificates') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Certificates
        </a>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Print / Save as PDF
            </button>
            <a href="<?= url('certificate/verify/' . e($certificate['certificate_number'])) ?>" class="btn btn-outline-success" target="_blank">
                <i class="bi bi-shield-check me-1"></i> Public Verification
            </a>
        </div>
    </div>

    <!-- Official Certificate Frame -->
    <div class="certificate-frame p-5 bg-white text-center">
        <!-- Header / Logo -->
        <div class="d-flex align-items-center justify-content-center gap-3 mb-4">
            <div class="navbar-brand-icon" style="width:48px;height:48px;font-size:1.5rem;">
                <i class="bi bi-cup-hot-fill"></i>
            </div>
            <div>
                <h3 class="font-heading fw-bold text-dark mb-0 text-uppercase tracking-wider">Beyond Barista Academy</h3>
                <small class="text-accent fw-bold tracking-wider" style="font-size:0.75rem;">REPUBLIC OF RWANDA • HOSPITALITY LEARNING STANDARDS</small>
            </div>
        </div>

        <p class="text-muted text-uppercase tracking-wider small mb-3">This is to certify that</p>
        
        <h1 class="font-heading display-4 fw-bold text-dark mb-3" style="font-family:'Poppins',serif; letter-spacing:1px;">
            <?= e($certificate['student_name']) ?>
        </h1>

        <p class="text-muted text-uppercase tracking-wider small mb-3">has successfully completed the comprehensive training program and passed all examinations for</p>

        <h3 class="font-heading text-primary fw-bold mb-4" style="font-size:1.75rem;">
            <?= e($certificate['course_title']) ?>
        </h3>

        <div class="row align-items-center justify-content-between mt-5 pt-4 border-top">
            <div class="col-4 text-center">
                <div class="border-bottom border-dark pb-2 mb-1" style="font-family:cursive; font-size:1.25rem;">
                    Jean-Luc Mugisha
                </div>
                <small class="text-muted d-block fw-bold" style="font-size:0.75rem;">Head Barista Trainer</small>
                <small class="text-muted" style="font-size:0.7rem;">SCA Certified Roaster</small>
            </div>

            <div class="col-4 text-center">
                <div class="certificate-seal mx-auto mb-2">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <small class="text-muted d-block font-monospace" style="font-size:0.75rem;"><?= e($certificate['certificate_number']) ?></small>
                <small class="text-muted" style="font-size:0.7rem;">Issued: <?= date('M d, Y', strtotime($certificate['issue_date'])) ?></small>
            </div>

            <div class="col-4 text-center">
                <div class="border-bottom border-dark pb-2 mb-1" style="font-family:cursive; font-size:1.25rem;">
                    Academic Board
                </div>
                <small class="text-muted d-block fw-bold" style="font-size:0.75rem;">Director of Learning</small>
                <small class="text-muted" style="font-size:0.7rem;">Beyond Barista Rwanda</small>
            </div>
        </div>
    </div>
</div>
