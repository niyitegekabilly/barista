<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-heading fw-bold mb-1">Student Learning Dashboard</h2>
        <p class="text-muted small mb-0">Welcome back, <?= e(auth()['name']) ?>! Keep advancing your hospitality credentials.</p>
    </div>
    <a href="<?= url('courses') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> Explore More Courses</a>
</div>

<!-- Student Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-book-half"></i>
            </div>
            <div>
                <h4 class="font-heading mb-0"><?= count($enrolledCourses) ?></h4>
                <small class="text-muted">Enrolled Courses</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-award-fill"></i>
            </div>
            <div>
                <h4 class="font-heading mb-0"><?= count($certificates) ?></h4>
                <small class="text-muted">Certificates Earned</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon accent">
                <i class="bi bi-fire"></i>
            </div>
            <div>
                <h4 class="font-heading mb-0">7 Days</h4>
                <small class="text-muted">Active Learning Streak</small>
            </div>
        </div>
    </div>
</div>

<!-- In-Progress Courses -->
<div class="card p-4 border-0 shadow-sm rounded-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="font-heading mb-0">Continue Learning</h4>
        <a href="<?= url('student/courses') ?>" class="text-accent small fw-bold text-decoration-none">View All My Courses</a>
    </div>

    <?php if (empty($enrolledCourses)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-bookmark display-4 d-block mb-3 opacity-50"></i>
            <p class="mb-3">You haven't enrolled in any courses yet.</p>
            <a href="<?= url('courses') ?>" class="btn btn-primary btn-sm">Browse Course Catalog</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach (array_slice($enrolledCourses, 0, 4) as $idx => $course): ?>
                <?php 
                    $courseTitle = $course['title'] ?? $course['course_title'] ?? 'Course';
                    $courseSlug  = $course['slug'] ?? $course['course_slug'] ?? '';
                    $completed   = (int)($course['completed_lessons'] ?? 0);
                    $total       = (int)($course['total_lessons'] ?? 0);
                    $progress    = (int)($course['progress_percent'] ?? 0);
                    $catName     = $course['category_name'] ?? 'Hospitality';
                    $status      = $course['status'] ?? 'active';
                ?>
                <div class="col-lg-6">
                    <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary-subtle text-primary fw-bold"><?= e($catName) ?></span>
                            <span class="fw-bold small <?= $status === 'completed' || $progress >= 100 ? 'text-success' : 'text-primary' ?>">
                                <?= $progress ?>% Completed
                            </span>
                        </div>
                        <h5 class="font-heading mb-2 text-dark"><?= e($courseTitle) ?></h5>
                        
                        <!-- Progress bar -->
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar <?= $status === 'completed' || $progress >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= $progress ?>%;"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="text-muted small">
                                <i class="bi bi-check2-circle text-success me-1"></i> <?= $completed ?> of <?= $total ?> lessons
                            </span>
                            
                            <?php if (($status === 'completed' || $progress >= 100) && !empty($course['certificate_number'])): ?>
                                <a href="<?= url('student/certificates/' . e($course['certificate_number'])) ?>" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-award me-1"></i> Certificate
                                </a>
                            <?php else: ?>
                                <a href="<?= url('student/classroom/' . e($courseSlug)) ?>" class="btn btn-sm btn-accent fw-bold">
                                    <i class="bi bi-play-circle me-1"></i> Resume Learning
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
