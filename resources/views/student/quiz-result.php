<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card p-5 border-0 shadow-xl rounded-4 text-center">
                <?php if ($result['is_passed']): ?>
                    <div class="display-3 text-success mb-3"><i class="bi bi-award-fill"></i></div>
                    <span class="badge bg-success px-3 py-2 fs-6 mb-3">PASSED & CERTIFIED</span>
                    <h2 class="font-heading fw-bold mb-2">Congratulations, <?= e(auth()['name']) ?>!</h2>
                    <p class="text-muted mb-4">You have successfully passed the certification assessment.</p>

                    <div class="p-4 bg-light rounded-4 mb-4 border">
                        <div class="row g-3">
                            <div class="col-6 border-end">
                                <span class="text-muted small d-block">Your Score</span>
                                <span class="display-6 fw-bold text-success"><?= e($result['score_percentage']) ?>%</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Passing Score</span>
                                <span class="display-6 fw-bold text-dark"><?= e($result['passing_score']) ?>%</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <a href="<?= url('student/certificates') ?>" class="btn btn-primary btn-lg fw-bold">
                            <i class="bi bi-patch-check-fill me-1"></i> View & Download Your Certificate
                        </a>
                        <a href="<?= url('student/courses') ?>" class="btn btn-outline-secondary">
                            Return to My Courses
                        </a>
                    </div>
                <?php else: ?>
                    <div class="display-3 text-danger mb-3"><i class="bi bi-exclamation-circle-fill"></i></div>
                    <span class="badge bg-danger px-3 py-2 fs-6 mb-3">NOT PASSED</span>
                    <h2 class="font-heading fw-bold mb-2">Keep Practicing!</h2>
                    <p class="text-muted mb-4">You scored <strong><?= e($result['score_percentage']) ?>%</strong> (Required: <strong><?= e($result['passing_score']) ?>%</strong>).</p>

                    <div class="p-4 bg-light rounded-4 mb-4 border text-start">
                        <h6 class="font-heading mb-2"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Next Steps:</h6>
                        <ul class="text-muted small mb-0">
                            <li>Review the video lessons and dial-in handbook again.</li>
                            <li>Take notes on extraction times, temperatures, and ratios.</li>
                            <li>Retake the exam when you feel prepared.</li>
                        </ul>
                    </div>

                    <a href="<?= url('student/courses') ?>" class="btn btn-primary btn-lg fw-bold">
                        Review Course Modules
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
