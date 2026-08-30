<div class="bg-primary-dark text-white py-5" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-3">
        <h1 class="font-heading text-white fw-bold mb-2">Hospitality & Coffee Course Catalog</h1>
        <p class="text-light opacity-80 mb-0">Discover certified training programs designed for Rwanda and international hospitality standards.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card p-4 shadow-sm border-0 sticky-top" style="top: 90px;">
                <h5 class="font-heading mb-3">Filter Courses</h5>
                
                <form action="<?= url('courses') ?>" method="GET">
                    <!-- Search input -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Search</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search keywords..." value="<?= e($filters['search'] ?? '') ?>">
                            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted"><?= __('app.all_categories') ?></label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Disciplines</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat['slug']) ?>" <?= ($filters['category'] ?? '') === $cat['slug'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?> (<?= e($cat['courses_count']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Difficulty Level -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted"><?= __('app.filter_by_level') ?></label>
                        <select name="level" class="form-select" onchange="this.form.submit()">
                            <option value="all"><?= __('app.all_levels') ?></option>
                            <option value="beginner" <?= ($filters['level'] ?? '') === 'beginner' ? 'selected' : '' ?>><?= __('app.beginner') ?></option>
                            <option value="intermediate" <?= ($filters['level'] ?? '') === 'intermediate' ? 'selected' : '' ?>><?= __('app.intermediate') ?></option>
                            <option value="advanced" <?= ($filters['level'] ?? '') === 'advanced' ? 'selected' : '' ?>><?= __('app.advanced') ?></option>
                        </select>
                    </div>

                    <!-- Free / Paid Filter -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Pricing Tier</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_free" id="priceAll" value="" <?= !isset($filters['is_free']) || $filters['is_free'] === '' ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label small" for="priceAll">All Courses</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_free" id="priceFree" value="1" <?= ($filters['is_free'] ?? '') === '1' ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label small" for="priceFree">Free Tuition Courses</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_free" id="pricePaid" value="0" <?= ($filters['is_free'] ?? '') === '0' ? 'checked' : '' ?> onchange="this.form.submit()">
                            <label class="form-check-label small" for="pricePaid">Premium Masterclasses</label>
                        </div>
                    </div>

                    <a href="<?= url('courses') ?>" class="btn btn-sm btn-outline-secondary w-100">Reset All Filters</a>
                </form>
            </div>
        </div>

        <!-- Course Listings Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-muted small">Showing <strong><?= count($courses) ?></strong> of <strong><?= $totalCount ?></strong> courses</span>
            </div>

            <?php if (empty($courses)): ?>
                <div class="card text-center p-5 border-0 shadow-sm">
                    <div class="display-4 text-muted mb-3"><i class="bi bi-search"></i></div>
                    <h4 class="font-heading">No courses match your criteria</h4>
                    <p class="text-muted small">Try broadening your search keyword or clearing selected category filters.</p>
                    <div>
                        <a href="<?= url('courses') ?>" class="btn btn-primary">Clear Filters</a>
                    </div>
                </div>
            <?php else: ?>
                <?php
                $fallbackImages = [
                    asset('img/barista.jpeg'),
                    asset('img/cappuccino.jpg'),
                    asset('img/coffee-cups.jpg'),
                    asset('img/coffeshop.jpg'),
                    asset('img/class.png'),
                    asset('img/best.jpg')
                ];
                ?>
                <div class="row g-4">
                    <?php foreach ($courses as $idx => $course): ?>
                        <div class="col-md-6">
                            <div class="card course-card card-hover-elevate border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                <div class="course-card-img-wrapper" style="height: 200px; position: relative; overflow: hidden; background: linear-gradient(135deg, #C67C4E, #D4A574);">
                                    <img src="<?= e(course_thumbnail($course['thumbnail'] ?? '', $idx)) ?>"
                                         alt="<?= e($course['title']) ?>"
                                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; display: block;"
                                         onerror="this.style.display='none'">

                                    <div class="badge-floating position-absolute top-0 start-0 m-3">
                                        <?php if ($course['is_free']): ?>
                                            <span class="badge bg-success shadow-sm px-3 py-2 fw-bold">FREE</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark shadow-sm px-3 py-2 fw-bold">PREMIUM</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="badge-level position-absolute bottom-0 start-0 m-3 badge bg-dark bg-opacity-75 text-white px-2 py-1 small">
                                        <i class="bi bi-bar-chart-fill me-1"></i> <?= ucfirst(str_replace('_', ' ', $course['level'])) ?>
                                    </div>
                                </div>

                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-accent fw-bold text-uppercase" style="font-size:0.75rem;"><?= e($course['category_name']) ?></small>
                                        <button class="btn btn-sm btn-link p-0 text-muted btn-wishlist-toggle" data-course-id="<?= e($course['id']) ?>" title="Add to Wishlist">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                    </div>
                                    <h5 class="font-heading mb-2">
                                        <a href="<?= url('course/' . e($course['slug'])) ?>" class="text-dark hover-accent">
                                            <?= e($course['title']) ?>
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-4 flex-grow-1">
                                        <?= e(substr($course['short_description'], 0, 110)) ?>...
                                    </p>

                                    <div class="d-flex align-items-center justify-content-between text-muted small py-2 border-top border-bottom mb-3">
                                        <span><i class="bi bi-clock me-1"></i> <?= e($course['duration_hours']) ?> Hours</span>
                                        <span><i class="bi bi-people me-1"></i> <?= e($course['students_count']) ?> Enrolled</span>
                                        <span class="text-warning"><i class="bi bi-star-fill"></i> <?= number_format($course['rating_avg'], 1) ?></span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <div>
                                            <?php if ($course['is_free']): ?>
                                                <span class="fs-5 fw-bold text-success">FREE</span>
                                            <?php else: ?>
                                                <span class="fs-5 fw-bold text-dark"><?= format_rwf($course['discount_price'] ?: $course['price']) ?></span>
                                                <?php if ($course['discount_price']): ?>
                                                    <small class="text-muted text-decoration-line-through d-block" style="font-size:0.75rem;"><?= format_rwf($course['price']) ?></small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?= url('course/' . e($course['slug'])) ?>" class="btn btn-sm btn-primary">
                                            <?= __('app.enroll_now') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
