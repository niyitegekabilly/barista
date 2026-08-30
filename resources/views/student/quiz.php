<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Quiz Header & Timer Card -->
            <div class="card p-4 border-0 shadow-sm rounded-4 mb-4 bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-warning text-dark mb-1">Pass Requirement: <?= e($quiz['passing_score']) ?>%</span>
                        <h3 class="font-heading text-white fw-bold mb-0"><?= e($quiz['title']) ?></h3>
                    </div>

                    <div class="p-2 px-3 bg-white text-dark rounded-3 text-center shadow-sm">
                        <small class="text-muted d-block" style="font-size:0.75rem;">Time Remaining</small>
                        <span class="fs-4 fw-bold font-monospace" id="quizTimer" data-seconds="<?= e($quiz['time_limit_minutes'] * 60) ?>">
                            <?= sprintf('%02d:00', $quiz['time_limit_minutes']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quiz Questions Form -->
            <form action="<?= url('student/quiz/' . e($quiz['id']) . '/submit') ?>" method="POST" id="quizForm">
                <?= csrf_field() ?>

                <div class="d-flex flex-column gap-4 mb-5">
                    <?php foreach ($quiz['questions'] as $index => $q): ?>
                        <div class="card p-4 border-0 shadow-sm rounded-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-light text-dark border">Question <?= $index + 1 ?> of <?= count($quiz['questions']) ?></span>
                                <span class="text-muted small fw-bold"><?= e($q['points']) ?> Points</span>
                            </div>

                            <h5 class="font-heading mb-4 text-dark"><?= e($q['question_text']) ?></h5>

                            <?php if ($q['question_type'] === 'single_choice' || $q['question_type'] === 'true_false'): ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($q['options'] as $opt): ?>
                                        <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer hover-bg-light">
                                            <input class="form-check-input mt-0" type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt['id'] ?>" required>
                                            <span class="small text-dark"><?= e($opt['option_text']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($q['question_type'] === 'fill_blank'): ?>
                                <div class="mb-2">
                                    <input type="text" name="answers[<?= $q['id'] ?>]" class="form-control" placeholder="Type your answer here..." required>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="p-4 bg-white border rounded-4 shadow-sm text-center">
                    <p class="text-muted small mb-3">Make sure you have reviewed all answers before submitting. Your grade will be calculated immediately.</p>
                    <button type="submit" class="btn btn-accent btn-lg px-5 fw-bold">
                        <i class="bi bi-send-check me-1"></i> Submit Examination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
