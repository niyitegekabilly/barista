<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-heading fw-bold mb-1">My Enrolled Courses</h2>
        <p class="text-muted small mb-0">Track your progress and access your course materials</p>
    </div>
    <a href="<?= url('courses') ?>" class="btn btn-primary btn-sm"><i class="bi bi-compass me-1"></i> Browse Catalog</a>
</div>

<?php if (empty($courses)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4">
        <i class="bi bi-book display-4 text-muted mb-3 opacity-50"></i>
        <h4 class="font-heading">No enrolled courses yet</h4>
        <p class="text-muted small mb-4">Enroll in your first specialty coffee or hospitality course today!</p>
        <div>
            <a href="<?= url('courses') ?>" class="btn btn-primary fw-bold">Explore Courses</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($courses as $c): ?>
            <?php 
                $title     = $c['title'] ?? $c['course_title'] ?? 'Course';
                $slug      = $c['slug'] ?? $c['course_slug'] ?? '';
                $catName   = $c['category_name'] ?? 'Hospitality';
                $status    = $c['status'] ?? 'active';
                $progress  = (int)($c['progress_percent'] ?? 0);
                $completed = (int)($c['completed_lessons'] ?? 0);
                $total     = (int)($c['total_lessons'] ?? 0);
            ?>
            <div class="col-md-6 col-lg-6">
                <div class="card p-4 border-0 shadow-sm rounded-4 h-100 d-flex flex-column hover-shadow transition">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary-subtle text-primary fw-bold"><?= e($catName) ?></span>
                        <span class="badge <?= $status === 'completed' || $progress >= 100 ? 'bg-success' : 'bg-secondary' ?>">
                            <?= strtoupper($status) ?>
                        </span>
                    </div>

                    <h4 class="font-heading mb-2 text-dark fs-5"><?= e($title) ?></h4>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Progress</span>
                            <span class="fw-bold text-dark"><?= $progress ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar <?= $status === 'completed' || $progress >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= $progress ?>%;"></div>
                        </div>
                    </div>

                    <div class="text-muted small mb-4">
                        <i class="bi bi-check2-circle text-success me-1"></i> <?= $completed ?> of <?= $total ?> lessons completed
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="<?= url('student/classroom/' . e($slug)) ?>" class="btn btn-primary flex-grow-1 fw-bold">
                            <i class="bi bi-play-circle me-1"></i> <?= $progress > 0 ? 'Resume Learning' : 'Start Course' ?>
                        </a>
                        <?php if (($status === 'completed' || $progress >= 100) && !empty($c['certificate_number'])): ?>
                            <a href="<?= url('student/certificates/' . e($c['certificate_number'])) ?>" class="btn btn-outline-success" title="View Certificate">
                                <i class="bi bi-award-fill"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
