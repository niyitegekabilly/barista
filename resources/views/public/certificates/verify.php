<div class="bg-primary-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-3">
        <div class="certificate-seal mx-auto mb-3" style="width:70px;height:70px;font-size:1.8rem;">
            <i class="bi bi-shield-check"></i>
        </div>
        <h1 class="font-heading text-white fw-bold display-6 mb-2">Certificate Authentication Portal</h1>
        <p class="text-light opacity-80 max-w-700 mx-auto">
            Official public verification system for Beyond Barista Academy Rwanda credentials.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Search Form -->
            <div class="card p-4 p-lg-5 border-0 shadow-lg rounded-4 mb-5">
                <form action="<?= url('certificate/verify') ?>" method="GET">
                    <label class="form-label fw-bold text-dark mb-2">Enter Certificate Number</label>
                    <div class="input-group input-group-lg mb-2">
                        <span class="input-group-text bg-light"><i class="bi bi-qr-code-scan"></i></span>
                        <input type="text" name="code" class="form-control" placeholder="e.g. BBA-2026-000123" value="<?= e($searchedCode ?? '') ?>" required>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Verify Credential</button>
                    </div>
                    <small class="text-muted">The certificate number can be found on the bottom-right corner of the official diploma or below the QR code.</small>
                </form>
            </div>

            <!-- Sample Diploma Showcase when no certificate code queried -->
            <?php if (!isset($certificate)): ?>
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
                    <div class="card-header bg-dark text-white p-4">
                        <h5 class="font-heading mb-1 text-white"><i class="bi bi-award-fill text-warning me-2"></i> Sample Beyond Barista Digital Diploma</h5>
                        <p class="text-white-50 small mb-0">Every graduate receives an official verifiable credential like the sample below.</p>
                    </div>
                    <div class="p-3 bg-light text-center">
                        <img src="<?= asset('img/cert3.jpg') ?>" alt="Official Certificate Sample" class="img-fluid rounded-3 shadow-sm border" style="max-height: 380px; width: auto; object-fit: contain;">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Verification Result -->
            <?php if (isset($certificate)): ?>
                <?php if ($certificate): ?>
                    <div class="card border-0 shadow-xl rounded-4 overflow-hidden">
                        <div class="bg-success text-white py-3 px-4 d-flex align-items-center gap-2">
                            <i class="bi bi-patch-check-fill fs-3"></i>
                            <div>
                                <h5 class="mb-0 fw-bold">Valid & Authenticated Credential</h5>
                                <small class="text-white-50">Issued by Beyond Barista Academy Rwanda</small>
                            </div>
                        </div>

                        <div class="card-body p-4 p-lg-5">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Certificate Holder</small>
                                        <h3 class="font-heading text-dark mb-0"><?= e($certificate['student_name']) ?></h3>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Training Program</small>
                                        <h5 class="font-heading text-primary mb-0"><?= e($certificate['course_title']) ?></h5>
                                    </div>

                                    <div class="row g-3 text-muted small">
                                        <div class="col-6">
                                            <strong>Certificate Number:</strong><br>
                                            <span class="font-monospace text-dark fs-6"><?= e($certificate['certificate_number']) ?></span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Date of Issue:</strong><br>
                                            <span class="text-dark"><?= date('F d, Y', strtotime($certificate['issue_date'])) ?></span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Discipline:</strong><br>
                                            <span class="text-dark"><?= e($certificate['category_name']) ?></span>
                                        </div>
                                        <div class="col-6">
                                            <strong>Status:</strong><br>
                                            <span class="badge bg-success">ACTIVE & VALID</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 text-center">
                                    <div class="p-3 bg-light rounded-3 d-inline-block border">
                                        <img src="<?= e($certificate['qr_code_url']) ?>" alt="QR Code" style="width:140px;height:140px;" class="img-fluid">
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size:0.75rem;">Official Digital Stamp</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-md rounded-4 p-5 text-center bg-danger-subtle text-danger">
                        <div class="display-3 mb-3"><i class="bi bi-x-circle-fill text-danger"></i></div>
                        <h4 class="font-heading">Certificate Not Found</h4>
                        <p class="mb-0 text-muted">The certificate number <strong><?= e($searchedCode) ?></strong> does not exist in our registry or has been revoked. Please check the code and try again.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
