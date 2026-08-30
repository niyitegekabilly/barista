<!-- Course Detail Hero -->
<div class="bg-dark text-white py-5" style="background: linear-gradient(135deg, #1E1301 0%, #352102 50%, #4C3103 100%);">
    <div class="container py-3">
        <div class="row g-5 align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-white-50 small mb-3">
                        <li class="breadcrumb-item"><a href="<?= url() ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('courses') ?>" class="text-white-50">Courses</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('courses?category=' . e($course['category_slug'])) ?>" class="text-white-50"><?= e($course['category_name']) ?></a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= e($course['title']) ?></li>
                    </ol>
                </nav>

                <span class="badge bg-warning text-dark px-3 py-1 fw-bold mb-3"><?= e($course['category_name']) ?></span>
                <h1 class="font-heading text-white fw-bold display-6 mb-3"><?= e($course['title']) ?></h1>
                <p class="fs-5 text-light opacity-85 mb-4"><?= e($course['short_description']) ?></p>

                <div class="d-flex flex-wrap align-items-center gap-4 text-light opacity-90 small">
                    <span class="text-warning"><i class="bi bi-star-fill"></i> <?= number_format($course['rating_avg'], 1) ?> (<?= e($course['reviews_count']) ?> reviews)</span>
                    <span><i class="bi bi-people-fill me-1"></i> <?= e($course['students_count']) ?> Students</span>
                    <span><i class="bi bi-bar-chart-fill me-1"></i> <?= ucfirst(str_replace('_', ' ', $course['level'])) ?></span>
                    <span><i class="bi bi-clock-fill me-1"></i> <?= e($course['duration_hours']) ?> Total Hours</span>
                    <span><i class="bi bi-globe me-1"></i> English / Kinyarwanda</span>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card bg-white text-dark shadow-2xl border-0 rounded-4 overflow-hidden">
                    <div class="position-relative overflow-hidden" style="height: 220px;">
                        <img src="<?= e(course_thumbnail($course['thumbnail'] ?? '')) ?>" 
                             alt="<?= e($course['title']) ?>" 
                             style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-30 d-flex align-items-center justify-content-center">
                            <i class="bi bi-play-circle-fill display-3 text-warning shadow-lg" style="opacity: 0.9;"></i>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <?php if ($course['is_free']): ?>
                                    <span class="display-6 fw-bold text-success">FREE</span>
                                    <small class="text-muted d-block">Lifetime Access</small>
                                <?php else: ?>
                                    <span class="display-6 fw-bold text-dark"><?= format_rwf($course['discount_price'] ?: $course['price']) ?></span>
                                    <?php if ($course['discount_price']): ?>
                                        <span class="text-muted text-decoration-line-through fs-6 ms-2"><?= format_rwf($course['price']) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($isEnrolled): ?>
                            <a href="<?= url('student/classroom/' . e($course['slug'])) ?>" class="btn btn-success btn-lg w-100 fw-bold mb-3">
                                <i class="bi bi-play-circle me-1"></i> <?= __('app.continue_learning') ?>
                            </a>
                        <?php else: ?>
                            <?php if ($course['is_free']): ?>
                                <form action="<?= url('courses/enroll/' . e($course['id'])) ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-accent btn-lg w-100 fw-bold mb-3">
                                        <i class="bi bi-check2-circle me-1"></i> <?= __('app.enroll_now') ?> (Free)
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?= url('checkout/' . e($course['slug'])) ?>" class="btn btn-primary btn-lg w-100 fw-bold mb-3 shadow">
                                    <i class="bi bi-credit-card me-1"></i> Enroll Now — <?= format_money($course['discount_price'] ?: $course['price'], 'RWF') ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0">
                            <li><i class="bi bi-check2 text-success me-2"></i> <?= count($modules) ?> Structured Modules</li>
                            <li><i class="bi bi-check2 text-success me-2"></i> <?= e($course['lessons_count']) ?> HD Video & PDF Lessons</li>
                            <li><i class="bi bi-check2 text-success me-2"></i> Practice Exercises & Quizzes</li>
                            <li><i class="bi bi-check2 text-success me-2"></i> Verified Digital Certificate</li>
                            <li><i class="bi bi-check2 text-success me-2"></i> Full Lifetime Access on Mobile & PC</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Course Body: Syllabus & Details -->
<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-8">
            <!-- Learning Outcomes -->
            <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4 mb-4">
                <h4 class="font-heading mb-4"><i class="bi bi-check-circle-fill text-accent me-2"></i> <?= __('app.what_you_will_learn') ?></h4>
                <?php $outcomes = json_decode($course['learning_outcomes'] ?? '[]', true) ?: []; ?>
                <div class="row g-3">
                    <?php foreach ($outcomes as $outcome): ?>
                        <div class="col-md-6 d-flex gap-2">
                            <i class="bi bi-check2 text-success fs-5 flex-shrink-0"></i>
                            <span class="small"><?= e($outcome) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Course Description -->
            <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4 mb-4">
                <h4 class="font-heading mb-3"><?= __('app.course_overview') ?></h4>
                <div class="text-muted lh-lg">
                    <?= nl2br(e($course['description'])) ?>
                </div>
            </div>

            <!-- Curriculum Accordion -->
            <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="font-heading mb-0"><?= __('app.curriculum') ?></h4>
                    <span class="text-muted small"><?= count($modules) ?> Modules • <?= e($course['lessons_count']) ?> Lessons</span>
                </div>

                <div class="accordion" id="curriculumAccordion">
                    <?php foreach ($modules as $idx => $mod): ?>
                        <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                            <h2 class="accordion-header" id="heading<?= $mod['id'] ?>">
                                <button class="accordion-button <?= $idx > 0 ? 'collapsed' : '' ?> fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $mod['id'] ?>">
                                    <i class="bi bi-folder2-open me-2 text-primary"></i> <?= e($mod['title']) ?>
                                    <span class="badge bg-light text-muted ms-auto me-2"><?= count($mod['lessons']) ?> lessons</span>
                                </button>
                            </h2>
                            <div id="collapse<?= $mod['id'] ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#curriculumAccordion">
                                <div class="accordion-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($mod['lessons'] as $lesson): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if ($lesson['lesson_type'] === 'video'): ?>
                                                        <i class="bi bi-play-circle-fill text-accent"></i>
                                                    <?php elseif ($lesson['lesson_type'] === 'pdf'): ?>
                                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-file-text-fill text-primary"></i>
                                                    <?php endif; ?>
                                                    <span class="small fw-medium"><?= e($lesson['title']) ?></span>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <?php if ($lesson['is_free_preview']): ?>
                                                        <span class="badge bg-info-subtle text-info">Free Preview</span>
                                                    <?php endif; ?>
                                                    <span class="text-muted small"><?= e($lesson['duration_minutes']) ?> min</span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>

                                        <?php foreach ($mod['quizzes'] as $q): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 bg-light">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-patch-question-fill text-warning"></i>
                                                    <span class="small fw-bold"><?= e($q['title']) ?></span>
                                                </div>
                                                <span class="badge bg-warning text-dark">Quiz (Pass: <?= e($q['passing_score']) ?>%)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Instructor Bio -->
            <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4 mb-4">
                <h4 class="font-heading mb-4"><?= __('app.instructor') ?></h4>
                <div class="d-flex gap-4 align-items-start">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-3 flex-shrink-0" style="width:72px;height:72px;">
                        <?= strtoupper(substr($course['instructor_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h5 class="font-heading mb-1"><?= e($course['instructor_name']) ?></h5>
                        <p class="text-accent fw-medium small mb-2"><?= e($course['instructor_headline'] ?? 'Academy Instructor') ?></p>
                        <p class="text-muted small mb-0"><?= nl2br(e($course['instructor_bio'] ?? 'Specialty hospitality educator.')) ?></p>
                    </div>
                </div>
            </div>

            <!-- Student Reviews -->
            <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="font-heading mb-0">Student Reviews</h4>
                    <span class="text-warning fw-bold fs-5"><i class="bi bi-star-fill"></i> <?= number_format($course['rating_avg'], 1) ?> / 5.0</span>
                </div>

                <?php if (empty($reviews)): ?>
                    <p class="text-muted small mb-0">No reviews yet for this course. Be the first to leave a review after completing!</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="p-3 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold small"><?= e($rev['user_name']) ?></span>
                                    <div class="text-warning small">
                                        <?php for ($i = 0; $i < $rev['rating']; $i++): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0"><?= e($rev['comment']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Side Widgets -->
        <div class="col-lg-4">
            <div class="card p-4 border-0 shadow-sm rounded-4 mb-4">
                <h5 class="font-heading mb-3">Requirements</h5>
                <?php $reqs = json_decode($course['requirements'] ?? '[]', true) ?: []; ?>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0">
                    <?php foreach ($reqs as $req): ?>
                        <li><i class="bi bi-dot text-primary fs-5"></i> <?= e($req) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card p-4 border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #1E293B, #0F172A); color: white;">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="bi bi-award-fill text-warning fs-1"></i>
                    <div>
                        <h6 class="font-heading text-white mb-0">Academy Certificate</h6>
                        <small class="text-white-50">Included upon completion</small>
                    </div>
                </div>
                <p class="text-light opacity-75 small mb-0">
                    Pass the final assessment quiz with 75% or higher to receive your verifiable certificate.
                </p>
            </div>
        </div>
    </div>
</div>
