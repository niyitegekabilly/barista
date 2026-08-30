<?php $pageTitle = 'My Courses'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="font-heading fw-bold mb-0">My Courses</h2>
    <a href="<?= url('instructor/courses/create') ?>" class="btn btn-primary fw-bold">
        <i class="bi bi-plus-circle me-1"></i> Create New Course
    </a>
</div>

<?php if (empty($courses)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4">
        <i class="bi bi-camera-video display-4 text-muted mb-3"></i>
        <h4 class="font-heading">You haven't created any courses yet</h4>
        <p class="text-muted small mb-4">Start sharing your barista expertise with students across Rwanda.</p>
        <div><a href="<?= url('instructor/courses/create') ?>" class="btn btn-primary">Create Your First Course</a></div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($courses as $course): ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div class="row g-0 h-100">
                        <div class="col-4">
                            <img src="<?= e(course_thumbnail($course['thumbnail'] ?? '', $idx ?? 0)) ?>"
                                 alt="<?= e($course['title']) ?>" class="img-fluid h-100" style="object-fit:cover;">
                        </div>
                        <div class="col-8 p-3 d-flex flex-column">
                            <?php if (!empty($course['is_published'])): ?>
                                <span class="badge bg-success mb-2 align-self-start">PUBLISHED</span>
                            <?php else: ?>
                                <span class="badge bg-secondary mb-2 align-self-start">DRAFT</span>
                            <?php endif; ?>
                            <h6 class="font-heading fw-bold mb-1"><?= e($course['title']) ?></h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-people-fill me-1"></i> <?= e($course['enrollment_count']) ?> students
                                &nbsp;·&nbsp;
                                <i class="bi bi-star-fill text-warning me-1"></i> <?= number_format($course['avg_rating'] ?? 0, 1) ?>
                            </p>
                            <div class="d-flex gap-2 mt-auto">
                                <a href="<?= url('instructor/courses/' . $course['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="<?= url('instructor/courses/' . $course['id'] . '/curriculum') ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-collection-play"></i> Curriculum
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
