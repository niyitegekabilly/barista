<?php $pageTitle = 'Examination: ' . e($quiz['title']); ?>

<div class="container py-4 max-w-900 mx-auto">
    
    <!-- Top Examination Header -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <a href="<?= url('student/classroom/' . e($quiz['course_slug'] ?? '')) ?>" class="text-muted small text-decoration-none d-inline-flex align-items-center gap-1 mb-1">
                    <i class="bi bi-arrow-left"></i> Return to Classroom
                </a>
                <h3 class="font-heading fw-bold text-dark mb-0"><?= e($quiz['title']) ?></h3>
                <small class="text-muted"><?= e($quiz['course_title'] ?? 'Course Assessment') ?></small>
            </div>

            <!-- Countdown Timer & Meta -->
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <span class="text-muted small d-block">Time Limit</span>
                    <strong class="text-dark fs-5 font-monospace" id="quizTimerDisplay"><?= (int)$quiz['time_limit_minutes'] ?>:00</strong>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-4">
                    <i class="bi bi-stopwatch fs-3"></i>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3 pt-3 border-top text-center text-sm-start">
            <div class="col-sm-4">
                <span class="text-muted small d-block">Passing Score</span>
                <strong class="text-dark fw-bold"><?= e($quiz['passing_score']) ?>% required</strong>
            </div>
            <div class="col-sm-4">
                <span class="text-muted small d-block">Total Questions</span>
                <strong class="text-dark fw-bold"><?= count($questions) ?> Questions</strong>
            </div>
            <div class="col-sm-4">
                <span class="text-muted small d-block">Attempts Remaining</span>
                <strong class="text-dark fw-bold"><?= $attemptsLeft ?> of <?= $maxAttempts ?> attempts</strong>
            </div>
        </div>
    </div>

    <!-- Examination Form -->
    <form action="<?= url('student/quiz/' . e($quiz['id']) . '/submit') ?>" method="POST" id="examForm">
        <?= csrf_field() ?>
        <input type="hidden" name="duration_seconds" id="durationSecondsInput" value="0" />

        <div class="d-flex flex-column gap-4 mb-4">
            <?php foreach ($questions as $idx => $q): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface" id="question-card-<?= $q['id'] ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold">Q<?= $idx + 1 ?></span>
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size:0.75rem;">
                                <?= str_replace('_', ' ', $q['question_type']) ?>
                            </span>
                        </div>
                        <span class="badge bg-light text-muted border"><?= (int)$q['points'] ?> pt(s)</span>
                    </div>

                    <h5 class="font-heading fw-bold text-dark mb-4 lh-base">
                        <?= e($q['question_text']) ?>
                    </h5>

                    <!-- Options / Answers -->
                    <?php if ($q['question_type'] === 'single_choice' || $q['question_type'] === 'true_false'): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($q['options'] as $opt): ?>
                                <label class="p-3 border rounded-3 bg-white d-flex align-items-center gap-3 cursor-pointer option-label">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $opt['id'] ?>" class="form-check-input mt-0" required />
                                    <span class="text-dark small fw-medium"><?= e($opt['option_text']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($q['question_type'] === 'multiple_choice'): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($q['options'] as $opt): ?>
                                <label class="p-3 border rounded-3 bg-white d-flex align-items-center gap-3 cursor-pointer option-label">
                                    <input type="checkbox" name="answers[<?= $q['id'] ?>][]" value="<?= $opt['id'] ?>" class="form-check-input mt-0" />
                                    <span class="text-dark small fw-medium"><?= e($opt['option_text']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($q['question_type'] === 'essay' || $q['question_type'] === 'short_answer'): ?>
                        <div>
                            <textarea name="answers[<?= $q['id'] ?>]" rows="4" class="form-control rounded-3" placeholder="Provide detailed explanation or extraction protocol..." required></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Submit Strip -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface d-flex flex-wrap justify-content-between align-items-center gap-3">
            <span class="text-muted small">
                <i class="bi bi-shield-exclamation text-warning me-1"></i> Ensure all questions are answered before submitting your examination.
            </span>
            <button type="submit" class="btn btn-success btn-lg fw-bold px-5 shadow" id="btnSubmitExam">
                <i class="bi bi-send-check me-1"></i> Submit Examination
            </button>
        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let timeLimitMin = <?= (int)($quiz['time_limit_minutes'] ?? 20) ?>;
    let totalSeconds = timeLimitMin * 60;
    let secondsSpent = 0;

    const timerDisplay = document.getElementById('quizTimerDisplay');
    const durationInput = document.getElementById('durationSecondsInput');
    const form = document.getElementById('examForm');

    const timerInterval = setInterval(function () {
        secondsSpent++;
        durationInput.value = secondsSpent;

        let remaining = totalSeconds - secondsSpent;
        if (remaining <= 0) {
            clearInterval(timerInterval);
            alert('Time limit reached! Submitting your examination answers automatically.');
            form.submit();
            return;
        }

        let m = Math.floor(remaining / 60).toString().padStart(2, '0');
        let s = (remaining % 60).toString().padStart(2, '0');
        if (timerDisplay) {
            timerDisplay.innerText = `${m}:${s}`;
            if (remaining <= 120) {
                timerDisplay.classList.add('text-danger');
            }
        }
    }, 1000);
});
</script>
