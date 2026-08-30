<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1">My Wishlist</h2>
        <p class="text-muted small mb-0">Courses you've saved for later</p>
    </div>
</div>

<?php if (empty($courses)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4">
        <i class="bi bi-heart display-4 text-muted mb-3"></i>
        <h4 class="font-heading">Your wishlist is empty</h4>
        <p class="text-muted small mb-4">Browse our course catalog and click the heart icon to save courses you're interested in.</p>
        <div><a href="<?= url('courses') ?>" class="btn btn-primary">Explore Courses</a></div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($courses as $course): ?>
            <div class="col-md-6 col-xl-4">
                <div class="course-card card border-0 shadow-sm rounded-4 h-100 d-flex flex-column overflow-hidden">
                    <div class="position-relative">
                        <img src="<?= e(course_thumbnail($course['thumbnail'] ?? '')) ?>"
                             class="card-img-top" alt="<?= e($course['title']) ?>" style="height: 180px; object-fit: cover;">
                        <!-- Remove from wishlist -->
                        <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle wishlist-toggle"
                                data-course-id="<?= $course['id'] ?>" style="width:36px;height:36px;" title="Remove from Wishlist">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <p class="text-accent small fw-bold mb-1"><?= e($course['category_name'] ?? 'Barista Skills') ?></p>
                        <h5 class="font-heading mb-1 flex-grow-1"><?= e($course['title']) ?></h5>
                        <p class="text-muted small mb-2">by <?= e($course['instructor_name'] ?? 'BBA Instructor') ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                            <span class="fw-bold text-primary"><?= $course['price'] > 0 ? format_rwf($course['price']) : 'Free' ?></span>
                            <a href="<?= url('courses/' . e($course['slug'])) ?>" class="btn btn-primary btn-sm">Enroll Now</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
