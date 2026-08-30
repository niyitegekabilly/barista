<?php $pageTitle = 'Quiz Builder — ' . e($quiz['title']); ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('instructor/courses') ?>">Courses</a></li>
                <li class="breadcrumb-item"><a href="<?= url('instructor/courses/' . e($quiz['course_id']) . '/curriculum') ?>"><?= e($quiz['course_title'] ?? 'Course') ?></a></li>
                <li class="breadcrumb-item active"><?= e($quiz['title']) ?></li>
            </ol>
        </nav>
        <h2 class="font-heading fw-bold mb-0">Quiz & Assessment Builder</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('instructor/courses/' . e($quiz['course_id']) . '/curriculum') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Curriculum
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- AI Quiz Generation Banner Card -->
        <div class="card p-4 border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #1E1B4B 0%, #312E81 50%, #4338CA 100%); color: #fff;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center flex-shrink-0" style="width:54px;height:54px;">
                        <i class="bi bi-stars fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h4 class="font-heading fw-bold mb-1 text-white">Generate Questions with AI</h4>
                        <p class="small mb-0 text-white-50">Instantly synthesize high-accuracy barista, coffee science, mixology, or hospitality questions with explanations.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-warning fw-bold px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
                    <i class="bi bi-stars me-1"></i> Launch AI Generator
                </button>
            </div>
        </div>

        <!-- Quiz Settings Card -->
        <div class="card p-4 border-0 shadow-sm rounded-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0"><i class="bi bi-sliders text-primary me-2"></i>Quiz Settings</h5>
                <span class="badge bg-primary-subtle text-primary"><?= count($questions) ?> Questions Added</span>
            </div>
            <form action="<?= url('instructor/quizzes/' . e($quiz['id']) . '/update') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">Quiz Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= e($quiz['title']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Passing Score (%)</label>
                        <input type="number" name="passing_score" class="form-control" value="<?= (int)($quiz['passing_score'] ?? 70) ?>" min="1" max="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Time Limit (Minutes)</label>
                        <input type="number" name="time_limit_minutes" class="form-control" value="<?= (int)($quiz['time_limit_minutes'] ?? 20) ?>" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Max Attempts</label>
                        <input type="number" name="max_attempts" class="form-control" value="<?= (int)($quiz['max_attempts'] ?? 3) ?>" min="1">
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-outline-primary btn-sm fw-bold">
                        <i class="bi bi-save me-1"></i> Update Quiz Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Questions List -->
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <h5 class="font-heading fw-bold mb-0">Questions (<?= count($questions) ?>)</h5>
            <span class="text-muted small">Total Points: <?= array_sum(array_column($questions, 'points')) ?> pts</span>
        </div>

        <?php if (empty($questions)): ?>
            <div class="card p-5 text-center bg-light border-0 rounded-4">
                <i class="bi bi-patch-question display-3 text-muted mb-3 opacity-50"></i>
                <h5 class="font-heading mb-2">No Questions in this Quiz Yet</h5>
                <p class="text-muted small max-w-500 mx-auto mb-4">Click "Launch AI Generator" to create a set of questions instantly, or use the form on the right to add them manually.</p>
                <div>
                    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
                        <i class="bi bi-stars me-1"></i> Generate with AI
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($questions as $index => $q): ?>
                    <div class="card p-4 border-0 shadow-sm rounded-4 position-relative">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary fw-bold">Q<?= $index + 1 ?></span>
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size:0.7rem;">
                                    <?= ucwords(str_replace('_', ' ', $q['question_type'])) ?>
                                </span>
                                <span class="badge bg-success-subtle text-success fw-bold" style="font-size:0.7rem;">
                                    <?= (int)$q['points'] ?> Points
                                </span>
                            </div>
                            <form action="<?= url('instructor/questions/' . $q['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this question?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Question">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>

                        <p class="fw-bold mb-3 text-dark fs-6"><?= e($q['question_text']) ?></p>

                        <?php if (!empty($q['options'])): ?>
                            <div class="bg-light p-3 rounded-3 mb-2">
                                <div class="row g-2">
                                    <?php foreach ($q['options'] as $optIdx => $opt): ?>
                                        <div class="col-md-6">
                                            <div class="p-2 rounded border <?= $opt['is_correct'] ? 'bg-success-subtle border-success text-success fw-bold' : 'bg-white' ?> small d-flex align-items-center gap-2">
                                                <span><?= $opt['is_correct'] ? '✓' : chr(65 + $optIdx) . '.' ?></span>
                                                <span class="text-dark"><?= e($opt['option_text']) ?></span>
                                                <?php if ($opt['is_correct']): ?>
                                                    <span class="badge bg-success ms-auto" style="font-size:0.6rem;">Correct</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($q['explanation'])): ?>
                            <div class="small text-muted mt-2 pt-2 border-top">
                                <i class="bi bi-info-circle text-primary me-1"></i> <strong>Explanation:</strong> <?= e($q['explanation']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Manual Add Question Panel -->
    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm rounded-4 sticky-top" style="top:90px;">
            <h5 class="font-heading fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill text-success"></i> Add Question Manually
            </h5>
            <form action="<?= url('instructor/quizzes/' . e($quiz['id']) . '/questions/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Question Type</label>
                    <select name="question_type" class="form-select" id="qTypeSelect">
                        <option value="single_choice">Single Choice (4 Options)</option>
                        <option value="true_false">True / False</option>
                        <option value="fill_blank">Fill in the Blank</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Question Prompt <span class="text-danger">*</span></label>
                    <textarea name="question_text" class="form-control" rows="3" placeholder="e.g. What is the standard brew ratio for espresso?" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Points Awarded</label>
                    <input type="number" name="points" class="form-control" value="10" min="1">
                </div>

                <!-- Single Choice Options (4 items) -->
                <div id="optionsSection">
                    <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                        <span>Answer Options</span>
                        <small class="text-muted">Select the correct radio</small>
                    </label>
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <div class="input-group mb-2">
                            <div class="input-group-text bg-white">
                                <input class="form-check-input mt-0" type="radio" name="correct_option" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> title="Mark as correct">
                            </div>
                            <input type="text" name="options[]" class="form-control form-control-sm" placeholder="Option <?= chr(65 + $i) ?>" <?= $i < 2 ? 'required' : '' ?>>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- True/False Section -->
                <div id="trueFalseSection" class="mb-3 d-none">
                    <label class="form-label small fw-bold">Correct Answer</label>
                    <select name="tf_correct" class="form-select">
                        <option value="True">True</option>
                        <option value="False">False</option>
                    </select>
                </div>

                <!-- Fill blank answer -->
                <div id="fillBlankSection" class="mb-3 d-none">
                    <label class="form-label small fw-bold">Exact Correct Answer</label>
                    <input type="text" name="fill_blank_answer" class="form-control" placeholder="e.g. 1:2">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Explanation (Optional)</label>
                    <textarea name="explanation" class="form-control form-control-sm" rows="2" placeholder="Why this answer is correct (shown to student in quiz review)"></textarea>
                </div>

                <button type="submit" class="btn btn-success fw-bold w-100 py-2">
                    <i class="bi bi-plus-circle me-1"></i> Save Question
                </button>
            </form>
        </div>
    </div>
</div>

<!-- AI Generator Modal -->
<div class="modal fade" id="aiGenerateModal" tabindex="-1" aria-labelledby="aiGenerateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #1E1B4B 0%, #312E81 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="bi bi-stars fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-heading fw-bold text-white mb-0" id="aiGenerateModalLabel">AI Quiz Question Generator</h5>
                        <small class="text-white-50">Generate professional, syllabus-aligned assessment questions in seconds</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= url('instructor/quizzes/' . e($quiz['id']) . '/generate-ai') ?>" method="POST" id="aiQuizForm">
                <?= csrf_field() ?>
                <div class="modal-body p-4 p-md-5">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Topic / Subject <span class="text-danger">*</span></label>
                        <input type="text" name="topic" class="form-control form-control-lg"
                               value="<?= e($quiz['title']) ?><?= !empty($quiz['course_title']) ? ' — ' . e($quiz['course_title']) : '' ?>"
                               placeholder="e.g. Espresso Extraction Mechanics, Milk Steaming, HACCP Hygiene, Cocktail Formulations" required>
                        <small class="text-muted">Specify the topic or learning module for AI question synthesis.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Number of Questions</label>
                            <select name="count" class="form-select">
                                <option value="3">3 Questions (Quick Check)</option>
                                <option value="5" selected>5 Questions (Standard Section Quiz)</option>
                                <option value="10">10 Questions (Comprehensive Exam)</option>
                                <option value="15">15 Questions (Final Assessment)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Difficulty Level</label>
                            <select name="difficulty" class="form-select">
                                <option value="beginner">Beginner (Foundations & Definitions)</option>
                                <option value="intermediate" selected>Intermediate (Applied Practical Skills)</option>
                                <option value="advanced">Advanced (Troubleshooting & Calibration)</option>
                                <option value="expert">Expert / Certification Exam Level</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Optional Lesson Notes / Source Context</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Paste excerpts from your lesson text, recipe guidelines, temperature rules, or specific points you want tested..."></textarea>
                        <small class="text-muted">Providing custom lesson notes helps the AI generate questions targeted directly to your teaching content.</small>
                    </div>

                    <div class="p-3 bg-light rounded-3 border small text-muted d-flex align-items-center gap-3">
                        <i class="bi bi-shield-check fs-4 text-success flex-shrink-0"></i>
                        <div>
                            Each AI question is synthesized with 4 options, verified correct answer, points allocation, and detailed student feedback explanations.
                        </div>
                    </div>
                </div>

                <div class="modal-footer p-4 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4 py-2" id="btnRunAi">
                        <i class="bi bi-stars me-1"></i> Generate & Add Questions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const qTypeSelect = document.getElementById('qTypeSelect');
    const optionsSection = document.getElementById('optionsSection');
    const trueFalseSection = document.getElementById('trueFalseSection');
    const fillBlankSection = document.getElementById('fillBlankSection');

    qTypeSelect.addEventListener('change', function () {
        const v = this.value;
        optionsSection.classList.toggle('d-none', v !== 'single_choice');
        trueFalseSection.classList.toggle('d-none', v !== 'true_false');
        fillBlankSection.classList.toggle('d-none', v !== 'fill_blank');
    });

    const aiForm = document.getElementById('aiQuizForm');
    const btnRunAi = document.getElementById('btnRunAi');
    if (aiForm && btnRunAi) {
        aiForm.addEventListener('submit', () => {
            btnRunAi.disabled = true;
            btnRunAi.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Synthesizing Questions with AI...';
        });
    }
});
</script>
