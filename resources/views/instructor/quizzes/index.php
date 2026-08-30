<?php $pageTitle = 'Manage Quizzes & Exams'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-heading fw-bold mb-0">Quizzes & Assessment Exams</h2>
        <p class="text-muted small mb-0">Manage course evaluations and generate questions with AI</p>
    </div>
    <?php if (!empty($courses)): ?>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#createQuizModal">
            <i class="bi bi-plus-circle me-1"></i> Create New Quiz
        </button>
    <?php endif; ?>
</div>

<?php if (empty($quizzes)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4">
        <i class="bi bi-patch-question display-4 text-muted mb-3 opacity-50"></i>
        <h4 class="font-heading mb-2">No Quizzes Created Yet</h4>
        <p class="text-muted small max-w-500 mx-auto mb-4">Quizzes test your students' comprehension, track progress, and are required for issuing verified completion certificates.</p>
        <?php if (!empty($courses)): ?>
            <div>
                <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                    <i class="bi bi-plus-circle me-1"></i> Create First Quiz
                </button>
            </div>
        <?php else: ?>
            <div>
                <a href="<?= url('instructor/courses/create') ?>" class="btn btn-primary fw-bold">Create a Course First</a>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($quizzes as $quiz): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card p-4 border-0 shadow-sm rounded-4 h-100 d-flex flex-column hover-shadow transition">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-primary-subtle text-primary fw-bold">Assessment</span>
                        <span class="badge bg-success-subtle text-success"><?= (int)$quiz['passing_score'] ?>% Pass Mark</span>
                    </div>

                    <h5 class="font-heading fw-bold text-dark mb-1"><?= e($quiz['title']) ?></h5>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-book me-1"></i> <?= e($quiz['course_title']) ?>
                        <?php if (!empty($quiz['module_title'])): ?>
                            <span class="d-block text-muted">Section: <?= e($quiz['module_title']) ?></span>
                        <?php endif; ?>
                    </p>

                    <div class="p-3 bg-light rounded-3 mb-4 mt-auto">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span><i class="bi bi-question-circle me-1"></i> Questions:</span>
                            <span class="fw-bold text-dark"><?= (int)$quiz['question_count'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span><i class="bi bi-clock me-1"></i> Time Limit:</span>
                            <span class="fw-bold text-dark"><?= (int)$quiz['time_limit_minutes'] ?> min</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span><i class="bi bi-award me-1"></i> Total Points:</span>
                            <span class="fw-bold text-dark"><?= (int)($quiz['total_points'] ?? 0) ?> pts</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= url('instructor/quizzes/' . $quiz['id'] . '/edit') ?>" class="btn btn-primary btn-sm fw-bold flex-grow-1">
                            <i class="bi bi-pencil-square me-1"></i> Manage & AI Questions
                        </a>
                        <form action="<?= url('instructor/quizzes/' . $quiz['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this quiz?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Quiz">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Create Quiz Modal -->
<?php if (!empty($courses)): ?>
    <div class="modal fade" id="createQuizModal" tabindex="-1" aria-labelledby="createQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light p-4">
                    <h5 class="modal-title font-heading fw-bold" id="createQuizModalLabel">
                        <i class="bi bi-patch-question-fill text-primary me-2"></i>Create New Quiz
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= url('instructor/courses/' . $courses[0]['id'] . '/quizzes/store') ?>" method="POST" id="modalCreateQuizForm">
                    <?= csrf_field() ?>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Course <span class="text-danger">*</span></label>
                            <select name="course_id" id="modalCourseSelect" class="form-select" required>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Quiz / Exam Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Module 1 Dial-in Assessment or Final Certification Exam" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Passing Score (%)</label>
                                <input type="number" name="passing_score" class="form-control" value="70" min="1" max="100">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Time Limit (Min)</label>
                                <input type="number" name="time_limit_minutes" class="form-control" value="20" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-3 bg-light">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold">Create & Open Builder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('modalCourseSelect');
        const form   = document.getElementById('modalCreateQuizForm');
        if (select && form) {
            select.addEventListener('change', () => {
                form.action = "<?= app_url() ?>/instructor/courses/" + select.value + "/quizzes/store";
            });
        }
    });
    </script>
<?php endif; ?>
