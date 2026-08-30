<?php $pageTitle = 'Official Certificate Verification'; ?>

<section class="py-5 bg-surface" style="min-height: 80vh;">
    <div class="container py-4">
        
        <!-- Header -->
        <div class="text-center max-w-700 mx-auto mb-5">
            <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width:72px; height:72px; border-radius:50%; background: linear-gradient(135deg, #180D06, #3D2214); color:#F3C78E; box-shadow: 0 6px 20px rgba(24,13,6,0.2);">
                <i class="bi bi-shield-check fs-1"></i>
            </div>
            <h1 class="font-heading fw-bold text-dark mb-2">Certificate Verification Portal</h1>
            <p class="text-muted">Verify the authenticity of digital certificates issued by Beyond Barista Academy for specialty coffee and hospitality vocational programs.</p>

            <!-- Verification Search Form -->
            <form action="<?= url('certificate/verify') ?>" method="GET" class="card p-2 border-0 shadow-sm rounded-pill max-w-500 mx-auto mt-4 bg-white">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="code" class="form-control border-0 shadow-none font-monospace" placeholder="e.g. BBA-CERT-202608-ABC12" value="<?= e($code ?? '') ?>" required />
                    <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill">
                        Verify Now
                    </button>
                </div>
            </form>
        </div>

        <!-- Verification Result -->
        <?php if (!empty($verification)): ?>
            <div class="max-w-700 mx-auto">
                <?php if ($verification['valid']): ?>
                    <?php $cert = $verification['certificate']; ?>
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                        <div class="p-4 bg-success text-white text-center">
                            <i class="bi bi-patch-check-fill display-4 mb-2 d-block"></i>
                            <h3 class="font-heading fw-bold mb-1">Authentic Certificate Verified</h3>
                            <p class="mb-0 small opacity-90">Issued by Beyond Barista Academy Registrar</p>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <div class="row g-4 mb-4">
                                <div class="col-sm-6">
                                    <span class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:1px;">Recipient Name</span>
                                    <h5 class="fw-bold text-dark mb-0"><?= e($cert['student_name']) ?></h5>
                                    <?php if (!empty($cert['student_id'])): ?>
                                        <small class="text-muted font-monospace">ID: <?= e($cert['student_id']) ?></small>
                                    <?php endif; ?>
                                </div>

                                <div class="col-sm-6">
                                    <span class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:1px;">Course & Credential</span>
                                    <h5 class="fw-bold text-dark mb-0"><?= e($cert['course_title']) ?></h5>
                                    <small class="text-muted"><?= e($cert['course_level'] ?? 'Professional Certification') ?></small>
                                </div>

                                <div class="col-sm-6">
                                    <span class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:1px;">Certificate Number</span>
                                    <span class="badge bg-light text-dark border font-monospace fs-6 px-3 py-2"><?= e($cert['certificate_number']) ?></span>
                                </div>

                                <div class="col-sm-6">
                                    <span class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:1px;">Date of Issuance</span>
                                    <span class="fw-bold text-dark fs-6"><?= date('F d, Y', strtotime($cert['issue_date'])) ?></span>
                                </div>

                                <?php if (!empty($cert['grade_score'])): ?>
                                    <div class="col-sm-6">
                                        <span class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:1px;">Academic Standing</span>
                                        <span class="fw-bold text-success fs-6"><i class="bi bi-star-fill text-warning me-1"></i> <?= e($cert['grade_score']) ?>% • <?= e($cert['grade_letter'] ?? 'Passed') ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="col-sm-6">
                                    <span class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:1px;">Issuing Instructor</span>
                                    <span class="fw-bold text-dark fs-6"><?= e($cert['instructor_name'] ?? 'Beyond Barista Master Trainers') ?></span>
                                </div>
                            </div>

                            <hr class="my-4" />

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <a href="<?= url('certificate/print/' . $cert['certificate_number']) ?>" target="_blank" class="btn btn-outline-secondary fw-bold px-3">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> View Print Layout
                                </a>

                                <?php if (!empty($verification['linkedInUrl'])): ?>
                                    <a href="<?= $verification['linkedInUrl'] ?>" target="_blank" class="btn btn-primary fw-bold px-4">
                                        <i class="bi bi-linkedin me-1"></i> Add to LinkedIn Profile
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                        <i class="bi bi-exclamation-triangle-fill display-4 text-warning mb-3"></i>
                        <h4 class="font-heading fw-bold text-dark">Verification Unsuccessful</h4>
                        <p class="text-muted small max-w-500 mx-auto mb-0"><?= e($verification['message']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
