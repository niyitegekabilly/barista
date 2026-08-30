<?php $pageTitle = 'Instructor Dashboard'; ?>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <i class="bi bi-play-circle-fill fs-1 text-primary mb-2"></i>
            <h3 class="font-heading fw-bold mb-0"><?= e($stats['total_courses']) ?></h3>
            <p class="text-muted small mb-0">Active Courses</p>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <i class="bi bi-people-fill fs-1 text-success mb-2"></i>
            <h3 class="font-heading fw-bold mb-0"><?= e($stats['total_students']) ?></h3>
            <p class="text-muted small mb-0">Students Enrolled</p>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <i class="bi bi-star-fill fs-1 text-warning mb-2"></i>
            <h3 class="font-heading fw-bold mb-0"><?= number_format($stats['avg_rating'], 1) ?></h3>
            <p class="text-muted small mb-0">Average Rating</p>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <i class="bi bi-cash-stack fs-1 text-accent mb-2"></i>
            <h3 class="font-heading fw-bold mb-0"><?= format_rwf($stats['total_earnings']) ?></h3>
            <p class="text-muted small mb-0">Total Earnings</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0">My Courses</h5>
                <a href="<?= url('instructor/courses/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> New Course
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Course</th>
                            <th>Students</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold small"><?= e($course['title']) ?></div>
                                    <small class="text-muted"><?= e($course['category_name'] ?? '') ?></small>
                                </td>
                                <td class="text-center"><?= e($course['enrollment_count']) ?></td>
                                <td>
                                    <i class="bi bi-star-fill text-warning small"></i>
                                    <span class="small"><?= number_format($course['avg_rating'] ?? 0, 1) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($course['is_published'])): ?>
                                        <span class="badge bg-success">PUBLISHED</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">DRAFT</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('instructor/courses/' . $course['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                                    <a href="<?= url('instructor/courses/' . $course['id'] . '/curriculum') ?>" class="btn btn-sm btn-outline-primary">Curriculum</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <h5 class="font-heading fw-bold mb-3">Recent Reviews</h5>
            <?php if (empty($recent_reviews)): ?>
                <p class="text-muted small text-center py-3">No reviews yet.</p>
            <?php else: ?>
                <?php foreach ($recent_reviews as $review): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <strong class="small"><?= e($review['student_name']) ?></strong>
                            <span class="text-warning small">
                                <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                            </span>
                        </div>
                        <p class="text-muted small mb-1"><?= e(substr($review['comment'], 0, 80)) ?>...</p>
                        <small class="text-muted"><?= e($review['course_title']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
