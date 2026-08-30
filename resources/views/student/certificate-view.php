<?php $pageTitle = 'Certificate - ' . e($certificate['certificate_number']); ?>

<div class="container py-4">
    <?php if (empty($isPrintOnly)): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
            <a href="<?= url('student/certificates') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-arrow-left"></i> Back to My Certificates
            </a>

            <div class="d-flex align-items-center gap-2">
                <a href="<?= url('certificate/verify/' . e($certificate['certificate_number'])) ?>" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm" target="_blank">
                    <i class="bi bi-shield-check"></i> Verify Online
                </a>

                <?php 
                    $linkedInUrl = "https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME" .
                        "&name=" . urlencode($certificate['course_title']) .
                        "&organizationName=" . urlencode('Beyond Barista Academy') .
                        "&issueYear=" . date('Y', strtotime($certificate['issue_date'])) .
                        "&issueMonth=" . date('n', strtotime($certificate['issue_date'])) .
                        "&certUrl=" . urlencode(url('certificate/verify/' . $certificate['certificate_number'])) .
                        "&certId=" . urlencode($certificate['certificate_number']);
                ?>
                <a href="<?= $linkedInUrl ?>" target="_blank" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-linkedin"></i> Add to LinkedIn
                </a>

                <button onclick="window.print()" class="btn btn-accent btn-sm fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-printer"></i> Print / Save PDF
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Official Certificate Frame -->
    <div class="certificate-frame-outer shadow-lg p-3 bg-white mx-auto rounded-3" style="max-width: 960px;">
        <div class="certificate-frame p-5 text-center position-relative" style="border: 12px double #2B1810; background: #FFFCF7; min-height: 640px;">
            
            <!-- Corner Decorative Borders -->
            <div style="position: absolute; top: 12px; left: 12px; font-size: 1.5rem; color: #C59B27;">❖</div>
            <div style="position: absolute; top: 12px; right: 12px; font-size: 1.5rem; color: #C59B27;">❖</div>
            <div style="position: absolute; bottom: 12px; left: 12px; font-size: 1.5rem; color: #C59B27;">❖</div>
            <div style="position: absolute; bottom: 12px; right: 12px; font-size: 1.5rem; color: #C59B27;">❖</div>

            <!-- Header Seal & Academy Title -->
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center mb-2" style="width:68px; height:68px; border-radius:50%; background: linear-gradient(135deg, #180D06, #3D2214); color:#F3C78E; box-shadow: 0 4px 15px rgba(24,13,6,0.2);">
                    <i class="bi bi-award-fill fs-2"></i>
                </div>
                <h6 class="text-uppercase fw-bold text-muted mb-0" style="letter-spacing: 3px; font-size: 0.8rem;">Beyond Barista Academy • Kigali, Rwanda</h6>
                <h1 class="font-heading fw-bold text-dark mt-2 mb-0" style="font-size: 2.3rem; letter-spacing: 1px;">Certificate of Mastery</h1>
                <p class="text-muted fst-italic small mb-0">Specialty Coffee & Hospitality Vocational Excellence</p>
            </div>

            <div class="my-4">
                <span class="text-muted small text-uppercase" style="letter-spacing: 2px;">This is proudly presented to</span>
                <h2 class="font-heading fw-bold text-primary mt-2 mb-1" style="font-size: 2.2rem; text-decoration: underline; text-decoration-color: #F3C78E; text-underline-offset: 8px;">
                    <?= e($certificate['student_name']) ?>
                </h2>
                <?php if (!empty($certificate['student_id'])): ?>
                    <small class="text-muted font-monospace">Student ID: <?= e($certificate['student_id']) ?></small>
                <?php endif; ?>
            </div>

            <div class="my-4 max-w-700 mx-auto">
                <p class="text-dark lh-lg mb-1" style="font-size: 1.05rem;">
                    For successfully completing the comprehensive practical curriculum, sensory evaluations, and professional dial-in assessment for:
                </p>
                <h3 class="font-heading fw-bold text-dark my-2" style="font-size: 1.6rem; color: #2B1810 !important;">
                    <?= e($certificate['course_title']) ?>
                </h3>
                <?php if (!empty($certificate['grade_score'])): ?>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white border border-warning rounded-pill shadow-sm mt-1">
                        <i class="bi bi-star-fill text-warning"></i>
                        <span class="fw-bold text-dark small">Final Grade: <?= e($certificate['grade_score']) ?>% • <?= e($certificate['grade_letter'] ?? 'Passed') ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer: Signatures & Verification Stamp -->
            <div class="row align-items-end mt-5 pt-3">
                <div class="col-4 text-center">
                    <div class="border-bottom border-dark pb-1 mx-auto" style="max-width: 180px;">
                        <span class="font-heading fst-italic fw-bold text-dark" style="font-family: 'Brush Script MT', cursive; font-size: 1.4rem;">Billy Niyitegeka</span>
                    </div>
                    <span class="text-dark fw-bold d-block small mt-1"><?= e($certificate['instructor_name'] ?? 'Head Trainer') ?></span>
                    <small class="text-muted" style="font-size:0.7rem;">Lead Barista Trainer</small>
                </div>

                <div class="col-4 text-center">
                    <div class="d-inline-block p-2 bg-white border border-2 border-warning rounded-3 shadow-sm mb-1">
                        <!-- Direct QR Code Container -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=<?= urlencode(url('certificate/verify/' . $certificate['certificate_number'])) ?>" 
                             alt="Verification QR Code" style="width: 76px; height: 76px; display: block;" />
                    </div>
                    <span class="font-monospace text-dark fw-bold d-block" style="font-size: 0.75rem;"><?= e($certificate['certificate_number']) ?></span>
                    <small class="text-muted d-block" style="font-size:0.68rem;">Scan to Verify Authenticity</small>
                </div>

                <div class="col-4 text-center">
                    <div class="border-bottom border-dark pb-1 mx-auto" style="max-width: 180px;">
                        <span class="fw-bold text-dark" style="font-size: 0.95rem;"><?= date('F d, Y', strtotime($certificate['issue_date'])) ?></span>
                    </div>
                    <span class="text-dark fw-bold d-block small mt-1">Date of Issuance</span>
                    <small class="text-muted" style="font-size:0.7rem;">Official Registrar</small>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media print {
    body {
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .no-print, nav, header, footer, .sidebar, .app-header {
        display: none !important;
    }
    .certificate-frame-outer {
        box-shadow: none !important;
        max-width: 100% !important;
        padding: 0 !important;
    }
    .certificate-frame {
        border-width: 8px !important;
        min-height: auto !important;
        page-break-inside: avoid;
    }
}
</style>
