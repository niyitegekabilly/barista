<div class="classroom-container">
    <!-- Left Learning Player Stage -->
    <div class="classroom-player-area">
        <?php if (empty($currentLesson)): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="<?= url('student/courses') ?>" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Back to My Courses
                    </a>
                    <h3 class="font-heading fw-bold text-dark mt-1 mb-0"><?= e($course['title']) ?></h3>
                </div>
            </div>

            <div class="card p-5 text-center bg-light border-0 shadow-sm rounded-4">
                <i class="bi bi-collection-play display-3 text-primary mb-3 opacity-75"></i>
                <h4 class="font-heading">Course Curriculum Under Preparation</h4>
                <p class="text-muted small max-w-500 mx-auto mb-4">The instructor is finalizing the video lessons, recipe sheets, and assessment quizzes for this course. Please check back shortly.</p>
                <div>
                    <a href="<?= url('student/courses') ?>" class="btn btn-primary fw-bold">Return to My Courses</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Top Lesson Breadcrumb & Title -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="<?= url('student/courses') ?>" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Back to My Courses
                    </a>
                    <h3 class="font-heading fw-bold text-dark mt-1 mb-0"><?= e($currentLesson['title']) ?></h3>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <?php if ($prevLesson): ?>
                        <a href="<?= url('student/classroom/' . e($course['slug']) . '?lesson=' . e($prevLesson['id'])) ?>" class="btn btn-outline-secondary btn-sm" title="Previous Lesson">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php if ($nextLesson): ?>
                        <a href="<?= url('student/classroom/' . e($course['slug']) . '?lesson=' . e($nextLesson['id'])) ?>" class="btn btn-outline-primary btn-sm" title="Next Lesson">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Media Stage -->
            <div class="mb-4">
                <?php if (($currentLesson['lesson_type'] ?? 'video') === 'video'): ?>
                    <?= \App\Services\VideoService::renderEmbed(
                        $currentLesson['video_url'] ?? '',
                        $currentLesson['video_provider'] ?? 'auto',
                        $currentLesson['title'] ?? 'Lesson Video'
                    ) ?>
                <?php elseif (($currentLesson['lesson_type'] ?? '') === 'pdf'): ?>
                    <div class="card p-5 text-center bg-light border-0 shadow-sm rounded-4">
                        <i class="bi bi-file-earmark-pdf-fill display-3 text-danger mb-3"></i>
                        <h4 class="font-heading"><?= e($currentLesson['title']) ?></h4>
                        <p class="text-muted small max-w-700 mx-auto mb-4">Official Beyond Barista Academy training manual and dial-in handbook.</p>
                        <?php if (!empty($currentLesson['pdf_path'])): ?>
                            <div>
                                <a href="<?= asset('uploads/' . e($currentLesson['pdf_path'])) ?>" class="btn btn-primary fw-bold" target="_blank" download>
                                    <i class="bi bi-download me-1"></i> Download PDF Reference Material
                                </a>
                            </div>
                        <?php else: ?>
                            <div>
                                <a href="<?= url('courses') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-book me-1"></i> View Course Materials
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif (($currentLesson['lesson_type'] ?? '') === 'audio'): ?>
                    <div class="card p-4 bg-light border-0 shadow-sm rounded-4 text-center">
                        <i class="bi bi-soundwave display-3 text-primary mb-3"></i>
                        <h5 class="font-heading mb-3"><?= e($currentLesson['title']) ?></h5>
                        <?php if (!empty($currentLesson['video_url'])): ?>
                            <audio controls controlsList="nodownload" class="w-100 mb-2">
                                <source src="<?= e($currentLesson['video_url']) ?>" type="audio/mpeg">
                                Your browser does not support audio element.
                            </audio>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Lesson Content Notes -->
            <?php if (!empty($currentLesson['content'])): ?>
                <div class="card p-4 border-0 shadow-sm rounded-4 mb-4">
                    <h5 class="font-heading mb-3">Lesson Summary & Practical Notes</h5>
                    <div class="text-muted lh-lg">
                        <?= nl2br(e($currentLesson['content'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Controls -->
            <div class="d-flex justify-content-between align-items-center p-3 bg-white border rounded-3 shadow-sm">
                <span class="text-muted small">
                    <i class="bi bi-info-circle text-primary me-1"></i> Marking complete updates your verified completion record.
                </span>

                <button type="button" class="btn btn-success fw-bold px-4" id="btnCompleteLesson" 
                        data-lesson-id="<?= e($currentLesson['id']) ?>" 
                        data-course-id="<?= e($course['id']) ?>"
                        data-enrollment-id="<?= e($enrollment['id']) ?>">
                    <i class="bi bi-check2-circle me-1"></i> Mark Lesson as Complete
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Curriculum Navigation Checklist -->
    <div class="classroom-sidebar">
        <div class="p-3 border-bottom bg-light">
            <h6 class="font-heading mb-1 text-dark"><?= e($course['title']) ?></h6>
            <div class="d-flex justify-content-between text-muted small mb-1">
                <span>Course Progress</span>
                <span class="fw-bold text-primary"><?= e($enrollment['progress_percent'] ?? 0) ?>%</span>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-primary" style="width: <?= e($enrollment['progress_percent'] ?? 0) ?>%;"></div>
            </div>
        </div>

        <div class="classroom-curriculum-list">
            <?php if (empty($modules)): ?>
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-hourglass-split d-block fs-3 mb-2 opacity-50"></i>
                    No modules added yet.
                </div>
            <?php else: ?>
                <?php foreach ($modules as $mod): ?>
                    <div class="p-2 px-3 bg-light border-bottom text-uppercase fw-bold text-muted" style="font-size:0.75rem; letter-spacing:0.5px;">
                        <?= e($mod['title']) ?>
                    </div>

                    <?php if (!empty($mod['lessons'])): ?>
                        <?php foreach ($mod['lessons'] as $les): ?>
                            <?php 
                                $isCurrent = !empty($currentLesson) && ((int)$les['id'] === (int)$currentLesson['id']);
                                $isDone = in_array((int)$les['id'], $completedLessonIds ?? [], true);
                            ?>
                            <a href="<?= url('student/classroom/' . e($course['slug']) . '?lesson=' . e($les['id'])) ?>" class="lesson-item-link <?= $isCurrent ? 'active' : '' ?> d-flex align-items-center gap-2">
                                <img src="<?= lesson_thumbnail($les['thumbnail'] ?? null, $les['id']) ?>" alt="<?= e($les['title']) ?> thumbnail" class="img-fluid" style="width:40px;height:40px;object-fit:cover;border-radius:4px;" />
                                <div class="mt-1">
                                    <?php if ($isDone): ?>
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle text-muted fs-5"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="small d-block text-dark <?= $isCurrent ? 'fw-bold' : '' ?>"><?= e($les['title']) ?></span>
                                    <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i> <?= e($les['duration_minutes']) ?> min</small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($mod['quizzes'])): ?>
                        <?php foreach ($mod['quizzes'] as $q): ?>
                            <a href="<?= url('student/quiz/' . e($q['id'])) ?>" class="lesson-item-link bg-warning-subtle">
                                <div class="mt-1">
                                    <i class="bi bi-patch-question-fill text-warning fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="small d-block fw-bold text-dark"><?= e($q['title']) ?></span>
                                    <small class="text-dark" style="font-size:0.75rem;">Certification Assessment Exam</small>
                                </div>
                                <span class="badge bg-warning text-dark align-self-center">Quiz</span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
