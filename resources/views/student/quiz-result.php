<?php $pageTitle = 'Examination Results - ' . e($quiz['title']); ?>

<div class="container py-4 max-w-900 mx-auto">
    
    <!-- Result Banner -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 bg-white">
        <div class="p-5 text-center <?= $result['is_passed'] ? 'bg-success text-white' : 'bg-danger text-white' ?>">
            <?php if ($result['is_passed']): ?>
                <i class="bi bi-patch-check-fill display-2 mb-2 d-block"></i>
                <h2 class="font-heading fw-bold mb-1">Congratulations! You Passed!</h2>
                <p class="mb-0 opacity-90">You have successfully met the academic passing standards for Beyond Barista Academy.</p>
            <?php else: ?>
                <i class="bi bi-x-circle-fill display-2 mb-2 d-block"></i>
                <h2 class="font-heading fw-bold mb-1">Assessment Not Passed</h2>
                <p class="mb-0 opacity-90">You scored below the <?= e($result['passing_score']) ?>% passing threshold. Please review the lessons and retake the exam.</p>
            <?php endif; ?>
        </div>

        <div class="p-4 p-md-5">
            <div class="row g-4 text-center mb-4">
                <div class="col-sm-4">
                    <span class="text-muted small text-uppercase d-block mb-1">Your Score</span>
                    <h2 class="font-heading fw-bold mb-0 <?= $result['is_passed'] ? 'text-success' : 'text-danger' ?>">
                        <?= e($result['percentage']) ?>%
                    </h2>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted small text-uppercase d-block mb-1">Passing Requirement</span>
                    <h2 class="font-heading fw-bold text-dark mb-0"><?= e($result['passing_score']) ?>%</h2>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted small text-uppercase d-block mb-1">Points Earned</span>
                    <h2 class="font-heading fw-bold text-dark mb-0"><?= (int)$result['earned_points'] ?> / <?= (int)$result['total_points'] ?></h2>
                </div>
            </div>

            <!-- Certificate Action if earned -->
            <?php if (!empty($certificateNumber)): ?>
                <div class="p-4 bg-success-subtle border border-success rounded-4 text-center mb-4">
                    <i class="bi bi-award-fill text-success fs-1 mb-2 d-block"></i>
                    <h4 class="font-heading fw-bold text-success mb-1">Official Certificate Issued!</h4>
                    <p class="text-dark small max-w-500 mx-auto mb-3">Your course curriculum is 100% complete and your certificate serial number is <strong class="font-monospace"><?= e($certificateNumber) ?></strong>.</p>
                    <a href="<?= url('student/certificates/' . e($certificateNumber)) ?>" class="btn btn-success btn-lg fw-bold px-4 shadow">
                        <i class="bi bi-award me-1"></i> View & Print Your Certificate
                    </a>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="<?= url('student/courses') ?>" class="btn btn-outline-secondary px-4 fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Return to My Courses
                </a>
                <a href="<?= url('student/quiz/' . e($quiz['id'])) ?>" class="btn btn-primary px-4 fw-bold">
                    <i class="bi bi-arrow-repeat me-1"></i> Retake Examination
                </a>
            </div>
        </div>
    </div>

    <!-- Detailed Answer Breakdown -->
    <?php if (!empty($result['graded_answers'])): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <h4 class="font-heading fw-bold text-dark mb-4">
                <i class="bi bi-list-check text-primary me-2"></i> Question-by-Question Review
            </h4>

            <div class="d-flex flex-column gap-3">
                <?php foreach ($result['graded_answers'] as $idx => $ga): ?>
                    <div class="p-3 border rounded-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge <?= $ga['is_correct'] ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-1">
                                <?= $ga['is_correct'] ? 'Correct (+'.$ga['points_awarded'].' pt)' : 'Incorrect (0 pt)' ?>
                            </span>
                            <small class="text-muted">Question <?= $idx + 1 ?></small>
                        </div>
                        <h6 class="fw-bold text-dark mb-2"><?= e($ga['question_text']) ?></h6>

                        <?php if (!empty($ga['explanation'])): ?>
                            <div class="p-2 bg-light rounded-3 text-muted small mt-2">
                                <strong class="text-dark"><i class="bi bi-info-circle me-1"></i> Explanation:</strong> <?= e($ga['explanation']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
