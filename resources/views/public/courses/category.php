<?php
$title = ($category['seo_title'] ?: $category['name']) . ' Courses — Beyond Barista Academy';
?>

<!-- Category Hero Banner -->
<section class="py-5 position-relative text-white" style="background: linear-gradient(135deg, #180D06 0%, #26140A 50%, #3B1E0E 100%); border-bottom: 2px solid rgba(243,199,142,0.2);">
    <div class="container py-3">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= url() ?>" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('courses') ?>" class="text-white-50 text-decoration-none">Courses</a></li>
                <?php foreach ($breadcrumbs as $bc): ?>
                    <?php if ($bc['id'] != $category['id']): ?>
                        <li class="breadcrumb-item"><a href="<?= url('courses/category/' . $bc['slug']) ?>" class="text-white-50 text-decoration-none"><?= e($bc['name']) ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active text-warning" aria-current="page"><?= e($bc['name']) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>

        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-4 text-white d-flex align-items-center justify-content-center shadow-lg"
                         style="width:64px;height:64px;flex-shrink:0;background-color:<?= e($category['color'] ?: '#4C3103') ?>;font-size:1.8rem;border:2px solid rgba(255,255,255,0.2);">
                        <i class="bi <?= e($category['icon'] ?: 'bi-cup-hot') ?>"></i>
                    </div>
                    <div>
                        <span class="badge bg-warning text-dark text-uppercase px-2 py-1 mb-1" style="font-size:0.72rem; letter-spacing:0.5px;">Training Category</span>
                        <h1 class="display-6 font-heading fw-bold mb-0 text-white"><?= e($category['name']) ?></h1>
                    </div>
                </div>

                <p class="lead text-white-50 mb-4" style="font-size:1.1rem; max-width:680px;">
                    <?= e($category['short_description'] ?: ($category['description'] ?: 'Explore certified masterclasses, practical coffee workshops, and hospitality training in Kigali, Rwanda.')) ?>
                </p>

                <div class="d-flex flex-wrap align-items-center gap-3 small text-white-50">
                    <div><i class="bi bi-journal-bookmark-fill text-warning me-1"></i> <strong class="text-white"><?= $totalCourses ?></strong> Available Courses</div>
                    <div><i class="bi bi-patch-check-fill text-success me-1"></i> SCA Standardized Certification</div>
                    <div><i class="bi bi-award-fill text-warning me-1"></i> Verified Diploma Included</div>
                </div>
            </div>
            
            <?php if (!empty($category['cover_image'])): ?>
                <div class="col-lg-4 text-center d-none d-lg-block">
                    <img src="<?= asset('uploads/' . e($category['cover_image'])) ?>" class="img-fluid rounded-4 shadow-lg border border-2 border-white-50" style="max-height: 240px; object-fit: cover;" alt="<?= e($category['name']) ?>">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Subcategories & Course Grid -->
<section class="py-5 bg-light">
    <div class="container">

        <!-- Subcategories Filter Navigation -->
        <?php if (!empty($subcategories)): ?>
            <div class="mb-4">
                <h6 class="text-uppercase fw-bold text-muted small mb-3" style="letter-spacing:1px;">
                    <i class="bi bi-diagram-2 me-1 text-primary"></i> Explore Subcategories in <?= e($category['name']) ?>
                </h6>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('courses/category/' . $category['slug']) ?>" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold">
                        All <?= e($category['name']) ?> (<?= $totalCourses ?>)
                    </a>
                    <?php foreach ($subcategories as $sub): ?>
                        <a href="<?= url('courses/category/' . $sub['slug']) ?>" class="btn btn-sm btn-outline-secondary bg-white rounded-pill px-3 d-inline-flex align-items-center gap-2 shadow-sm">
                            <i class="bi <?= e($sub['icon'] ?: 'bi-cup-hot') ?> text-primary"></i>
                            <span><?= e($sub['name']) ?></span>
                            <span class="badge bg-light text-muted border"><?= $sub['courses_count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Courses Grid -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="font-heading fw-bold mb-0 text-primary-dark">Available Programs & Masterclasses</h4>
            <span class="text-muted small"><?= $totalCourses ?> courses found</span>
        </div>

        <?php if (empty($courses)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <i class="bi bi-journal-x fs-1 text-muted mb-3 d-block"></i>
                <h5 class="fw-bold">No Courses Available Yet</h5>
                <p class="text-muted small mb-4">Courses in this specialized category are currently being curated by our certified trainers.</p>
                <div>
                    <a href="<?= url('courses') ?>" class="btn btn-primary btn-sm px-4">Browse All Courses</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($courses as $c): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white transition-hover">
                            <div class="position-relative">
                                <?php if (!empty($c['thumbnail'])): ?>
                                    <img src="<?= asset('uploads/' . e($c['thumbnail'])) ?>" class="card-img-top" style="height:200px; object-fit:cover;" alt="<?= e($c['title']) ?>">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-dark text-warning" style="height:200px; background:linear-gradient(135deg, #180D06, #3B1E0E);">
                                        <i class="bi bi-cup-hot fs-1"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="badge bg-primary position-absolute top-0 start-0 m-3 px-2 py-1 text-uppercase" style="font-size:0.7rem;">
                                    <?= e($c['level'] ?? 'All Levels') ?>
                                </span>
                                <?php if ($c['price'] <= 0): ?>
                                    <span class="badge bg-success position-absolute top-0 end-0 m-3 px-2 py-1">FREE</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted small"><i class="bi bi-person me-1"></i><?= e($c['instructor_name']) ?></span>
                                    <span class="text-warning small fw-bold"><i class="bi bi-star-fill"></i> <?= number_format($c['avg_rating'] ?: 5.0, 1) ?></span>
                                </div>

                                <h5 class="card-title fw-bold mb-2">
                                    <a href="<?= url('courses/' . $c['slug']) ?>" class="text-dark text-decoration-none hover-primary">
                                        <?= e($c['title']) ?>
                                    </a>
                                </h5>

                                <p class="card-text text-muted small mb-4 flex-grow-1" style="line-height:1.5;">
                                    <?= e($c['short_description'] ?: substr(strip_tags($c['description'] ?? ''), 0, 120) . '...') ?>
                                </p>

                                <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-auto">
                                    <div class="fw-bold fs-5 text-primary-dark">
                                        <?= $c['price'] > 0 ? ('$' . number_format($c['price'], 2)) : '<span class="text-success">Free Enrollment</span>' ?>
                                    </div>
                                    <a href="<?= url('courses/' . $c['slug']) ?>" class="btn btn-outline-primary btn-sm fw-bold px-3">
                                        View Program <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- SEO Schema.org JSON-LD -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CourseList",
  "name": <?= json_encode($category['name'] . ' Courses at Beyond Barista Academy') ?>,
  "description": <?= json_encode($category['description'] ?: $category['short_description']) ?>,
  "provider": {
    "@type": "Organization",
    "name": "Beyond Barista Academy",
    "sameAs": "https://beyondbarista.rw"
  }
}
</script>
