<?php $pageTitle = 'Category — ' . e($category['name']); ?>

<!-- Breadcrumbs & Navigation -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/categories') ?>" class="text-decoration-none text-muted">Categories</a></li>
        <?php foreach ($category['breadcrumbs'] as $bc): ?>
            <?php if ($bc['id'] != $category['id']): ?>
                <li class="breadcrumb-item"><a href="<?= url('admin/categories/' . $bc['id']) ?>" class="text-decoration-none text-muted"><?= e($bc['name']) ?></a></li>
            <?php else: ?>
                <li class="breadcrumb-item active"><?= e($bc['name']) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>

<!-- Category 360° Hero Card -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface position-relative overflow-hidden" style="border-left: 5px solid <?= e($category['color'] ?: '#4C3103') ?> !important;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-4 text-white d-flex align-items-center justify-content-center shadow"
                 style="width:68px;height:68px;flex-shrink:0;background-color:<?= e($category['color'] ?: '#4C3103') ?>;font-size:1.8rem;">
                <i class="bi <?= e($category['icon'] ?: 'bi-cup-hot') ?>"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="font-heading fw-bold mb-0 text-dark"><?= e($category['name']) ?></h3>
                    <span class="badge <?= $category['status'] === 'active' ? 'bg-success-subtle text-success border border-success' : ($category['status'] === 'draft' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-secondary-subtle text-secondary border') ?> text-capitalize px-2 py-1">
                        <?= e($category['status']) ?>
                    </span>
                    <?php if (!empty($category['is_featured'])): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i> Featured in Catalog</span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small mb-1">
                    <code><?= e($category['slug']) ?></code>
                    <?php if (!empty($category['parent_name'])): ?>
                        <span class="mx-1">•</span> Subcategory of: <a href="<?= url('admin/categories/' . $category['parent_id']) ?>" class="fw-bold text-decoration-none"><?= e($category['parent_name']) ?></a>
                    <?php else: ?>
                        <span class="mx-1">•</span> <span class="badge bg-light text-dark border">Top-Level Root Category</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($category['short_description'])): ?>
                    <div class="small text-secondary"><?= e($category['short_description']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?= url('courses/category/' . $category['slug']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-box-arrow-up-right"></i> View Public Landing
            </a>
            <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                <i class="bi bi-pencil-square"></i> Edit Category
            </button>
        </div>
    </div>
</div>

<!-- 360° Category Nav Tabs -->
<ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="categoryTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-overview">
            <i class="bi bi-info-circle me-1"></i> Overview
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-courses">
            <i class="bi bi-journal-bookmark me-1"></i> Assigned Courses (<?= count($courses) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-subcategories">
            <i class="bi bi-diagram-2 me-1"></i> Subcategories (<?= count($subcategories) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-analytics">
            <i class="bi bi-graph-up me-1"></i> Category Analytics
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-seo">
            <i class="bi bi-search me-1"></i> SEO & Meta Preview
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-activity">
            <i class="bi bi-clock-history me-1"></i> Activity Logs (<?= count($activity_logs) ?>)
        </button>
    </li>
</ul>

<!-- Tab Content Panes -->
<div class="tab-content" id="categoryTabsContent">

    <!-- 1. Overview Tab -->
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
                    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-file-text me-2"></i> Category Overview & Description</h5>
                    
                    <?php if (!empty($category['cover_image'])): ?>
                        <div class="rounded-3 overflow-hidden mb-3 border">
                            <img src="<?= asset('uploads/' . e($category['cover_image'])) ?>" class="w-100" style="max-height: 220px; object-fit: cover;" alt="Cover Banner">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Curriculum Scope</label>
                        <div class="p-3 bg-light rounded-3 small">
                            <?= nl2br(e($category['description'] ?: 'No detailed educational description specified.')) ?>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="text-muted small fw-bold text-uppercase d-block">Display Order</label>
                            <span class="fw-bold">#<?= $category['sort_order'] ?></span>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small fw-bold text-uppercase d-block">Public URL</label>
                            <a href="<?= url('courses/category/' . $category['slug']) ?>" target="_blank" class="small text-truncate d-block">
                                /courses/category/<?= e($category['slug']) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
                    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-diagram-3 me-2"></i> Hierarchy & Metadata</h5>
                    <table class="table table-borderless small mb-0">
                        <tr>
                            <td class="text-muted" style="width:40%;">Internal ID:</td>
                            <td class="fw-bold">#<?= $category['id'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Parent Category:</td>
                            <td>
                                <?php if ($category['parent_id']): ?>
                                    <a href="<?= url('admin/categories/' . $category['parent_id']) ?>" class="fw-bold"><?= e($category['parent_name']) ?></a>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border">None (Root)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Direct Subcategories:</td>
                            <td><span class="badge bg-info-subtle text-info border border-info"><?= count($subcategories) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Assigned Courses:</td>
                            <td><span class="badge bg-primary-subtle text-primary border border-primary"><?= count($courses) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By:</td>
                            <td><?= e($category['creator_name'] ?? 'System') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created Date:</td>
                            <td><?= date('M d, Y H:i', strtotime($category['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Updated:</td>
                            <td><?= date('M d, Y H:i', strtotime($category['updated_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Courses Tab -->
    <div class="tab-pane fade" id="tab-courses">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-journal-bookmark me-2"></i> Assigned Courses (<?= count($courses) ?>)</h5>
                <a href="<?= url('instructor/courses/create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Create Course in Category
                </a>
            </div>

            <?php if (empty($courses)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-2 mb-2 d-block"></i>
                    No courses are currently assigned to this category.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Course Title</th>
                                <th>Category Mapping</th>
                                <th>Instructor</th>
                                <th>Level</th>
                                <th>Enrolled</th>
                                <th>Rating</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $crs): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($crs['title']) ?></div>
                                        <small class="text-muted"><?= $crs['lessons_count'] ?> lessons • <?= $crs['duration_hours'] ?>h</small>
                                    </td>
                                    <td>
                                        <?= $crs['is_primary'] ? '<span class="badge bg-primary">Primary Category</span>' : '<span class="badge bg-secondary">Secondary Category</span>' ?>
                                    </td>
                                    <td><?= e($crs['instructor_name'] ?? 'Instructor') ?></td>
                                    <td><span class="badge bg-light text-dark border text-capitalize"><?= e($crs['level']) ?></span></td>
                                    <td><span class="badge bg-light text-dark border"><i class="bi bi-people me-1"></i><?= $crs['students_count'] ?></span></td>
                                    <td>
                                        <span class="text-warning small"><i class="bi bi-star-fill"></i> <?= number_format($crs['avg_rating'] ?: 5.0, 1) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openReassignModal(<?= $crs['id'] ?>, '<?= addslashes(e($crs['title'])) ?>')">
                                            Reassign...
                                        </button>
                                        <a href="<?= url('instructor/courses/' . $crs['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Subcategories Tab -->
    <div class="tab-pane fade" id="tab-subcategories">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-diagram-2 me-2"></i> Nested Subcategories (<?= count($subcategories) ?>)</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Subcategory
                </button>
            </div>

            <?php if (empty($subcategories)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-folder-x fs-2 mb-2 d-block"></i>
                    No subcategories have been added under <strong><?= e($category['name']) ?></strong> yet.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($subcategories as $sub): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded-4 p-3 bg-light d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-3 text-white d-flex align-items-center justify-content-center"
                                         style="width:36px;height:36px;background-color:<?= e($sub['color'] ?: '#4C3103') ?>;">
                                        <i class="bi <?= e($sub['icon'] ?: 'bi-cup-hot') ?>"></i>
                                    </div>
                                    <div>
                                        <a href="<?= url('admin/categories/' . $sub['id']) ?>" class="fw-bold text-dark text-decoration-none d-block">
                                            <?= e($sub['name']) ?>
                                        </a>
                                        <small class="text-muted"><?= $sub['courses_count'] ?> courses</small>
                                    </div>
                                </div>
                                <a href="<?= url('admin/categories/' . $sub['id']) ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4. Category Analytics Tab -->
    <div class="tab-pane fade" id="tab-analytics">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface text-center">
                    <span class="text-muted small fw-bold text-uppercase">Total Enrollments</span>
                    <h3 class="fw-bold my-1 text-primary"><?= number_format($analytics['total_enrollments']) ?></h3>
                    <small class="text-muted">Student Seats</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface text-center">
                    <span class="text-muted small fw-bold text-uppercase">Category Revenue</span>
                    <h3 class="fw-bold my-1 text-success">$<?= number_format($analytics['total_revenue'], 2) ?></h3>
                    <small class="text-muted">Direct Course Sales</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface text-center">
                    <span class="text-muted small fw-bold text-uppercase">Average Rating</span>
                    <h3 class="fw-bold my-1 text-warning"><i class="bi bi-star-fill"></i> <?= number_format($analytics['avg_rating'] ?: 5.0, 1) ?></h3>
                    <small class="text-muted">Across All Courses</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface text-center">
                    <span class="text-muted small fw-bold text-uppercase">Active Courses</span>
                    <h3 class="fw-bold my-1 text-dark"><?= number_format($analytics['total_courses']) ?></h3>
                    <small class="text-muted">Published</small>
                </div>
            </div>
        </div>

        <?php if (!empty($analytics['top_course'])): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
                <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-trophy-fill text-warning me-2"></i> Most Popular Course in Category</h5>
                <div class="border rounded-4 p-3 bg-light d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-1"><?= e($analytics['top_course']['title']) ?></h6>
                        <div class="text-muted small">
                            <i class="bi bi-people-fill me-1"></i> <?= $analytics['top_course']['students_count'] ?> Enrolled Students • 
                            <i class="bi bi-person me-1"></i> Instructor: <?= e($analytics['top_course']['instructor_name']) ?>
                        </div>
                    </div>
                    <a href="<?= url('instructor/courses/' . $analytics['top_course']['id'] . '/edit') ?>" class="btn btn-sm btn-primary">View Course</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- 5. SEO & Google Snippet Tab -->
    <div class="tab-pane fade" id="tab-seo">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-google me-2 text-danger"></i> Google SERP Search Snippet Preview</h5>
            
            <div class="p-3 border rounded-3 bg-white" style="max-width: 600px;">
                <div class="text-success small mb-1">https://beyondbarista.rw/courses/category/<?= e($category['slug']) ?></div>
                <h6 class="text-primary fw-bold mb-1 hover-underline" style="cursor: pointer;">
                    <?= e($category['seo_title'] ?: ($category['name'] . ' Courses — Beyond Barista Academy')) ?>
                </h6>
                <p class="text-muted small mb-0" style="line-height: 1.4;">
                    <?= e($category['seo_description'] ?: ($category['short_description'] ?: ('Explore certified ' . $category['name'] . ' masterclasses, practical barista workshops, and professional training in Kigali, Rwanda.'))) ?>
                </p>
            </div>

            <h5 class="font-heading fw-bold mt-4 mb-3 text-primary-dark"><i class="bi bi-share-fill me-2 text-primary"></i> Open Graph & Social Card Status</h5>
            <table class="table table-borderless small mb-0" style="max-width: 600px;">
                <tr><td class="text-muted" style="width:35%;">og:title</td><td class="fw-bold"><?= e($category['seo_title'] ?: $category['name']) ?></td></tr>
                <tr><td class="text-muted">og:description</td><td><?= e($category['seo_description'] ?: $category['short_description']) ?></td></tr>
                <tr><td class="text-muted">og:url</td><td><code><?= e($category['canonical_url'] ?: url('courses/category/' . $category['slug'])) ?></code></td></tr>
                <tr><td class="text-muted">og:image</td><td><?= !empty($category['cover_image']) ? '<span class="badge bg-success">Configured</span>' : '<span class="badge bg-secondary">Default Brand Card</span>' ?></td></tr>
            </table>
        </div>
    </div>

    <!-- 6. Activity Logs Tab -->
    <div class="tab-pane fade" id="tab-activity">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-clock-history me-2"></i> Category Audit Trail</h5>
            <?php if (empty($activity_logs)): ?>
                <p class="text-muted small mb-0">No lifecycle events recorded for this category yet.</p>
            <?php else: ?>
                <div class="timeline position-relative ps-4" style="border-left: 2px solid #E5E7EB;">
                    <?php foreach ($activity_logs as $act): ?>
                        <div class="mb-3 position-relative">
                            <span class="position-absolute bg-primary rounded-circle" style="width:10px;height:10px;left:-21px;top:5px;"></span>
                            <div class="fw-bold small text-dark"><?= e(str_replace('_', ' ', strtoupper($act['action']))) ?></div>
                            <div class="text-muted small" style="font-size:0.75rem;">
                                <?= date('M d, Y \a\t H:i', strtotime($act['created_at'])) ?> • Performed by: <?= e($act['user_name'] ?? 'Admin') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal: Reassign Course -->
<div class="modal fade" id="reassignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/categories/' . $category['id'] . '/reassign-course') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="course_id" id="reassignCourseId">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-arrow-left-right me-2 text-primary"></i> Reassign Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Move <strong id="reassignCourseTitle"></strong> to another category.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Target Category <span class="text-danger">*</span></label>
                        <select name="new_category_id" class="form-select" required>
                            <option value="">Select target category...</option>
                            <?php foreach ($allCategories as $c): ?>
                                <?php if ($c['id'] != $category['id']): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['indented_name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Reassign Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Subcategory Quick Form -->
<div class="modal fade" id="addSubcategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/categories/store') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="parent_id" value="<?= $category['id'] ?>">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-plus-circle-fill text-primary me-2"></i> Add Subcategory under <?= e($category['name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Subcategory Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Free-pour Heart Patterns" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Short Description</label>
                        <input type="text" name="short_description" class="form-control" placeholder="One-line summary...">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Icon</label>
                            <input type="text" name="icon" class="form-control" value="bi-cup-hot">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Color</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="<?= e($category['color'] ?: '#4C3103') ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Create Subcategory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/categories/' . $category['id'] . '/update') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Category: <?= e($category['name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($category['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Parent Category</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Root Category)</option>
                                <?php foreach ($allCategories as $fc): ?>
                                    <option value="<?= $fc['id'] ?>" <?= $category['parent_id'] == $fc['id'] ? 'selected' : '' ?>><?= e($fc['indented_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Icon</label>
                            <input type="text" name="icon" class="form-control" value="<?= e($category['icon']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Color</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="<?= e($category['color'] ?: '#4C3103') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Short Description</label>
                        <input type="text" name="short_description" class="form-control" value="<?= e($category['short_description'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Description</label>
                        <textarea name="description" rows="3" class="form-control"><?= e($category['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $category['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="draft" <?= $category['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="inactive" <?= $category['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check p-2 border rounded-3 bg-light w-100">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_featured" value="1" id="editCatFeatured" <?= !empty($category['is_featured']) ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-bold" for="editCatFeatured">Featured on Homepage</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Thumbnail</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cover Banner</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary-dark border-bottom pb-1 mt-3 mb-2 small">SEO Metadata</h6>
                    <div class="mb-2">
                        <label class="form-label small">SEO Title</label>
                        <input type="text" name="seo_title" class="form-control form-control-sm" value="<?= e($category['seo_title'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">SEO Description</label>
                        <textarea name="seo_description" rows="2" class="form-control form-control-sm"><?= e($category['seo_description'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label small">SEO Keywords</label>
                        <input type="text" name="seo_keywords" class="form-control form-control-sm" value="<?= e($category['seo_keywords'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReassignModal(courseId, courseTitle) {
    document.getElementById('reassignCourseId').value = courseId;
    document.getElementById('reassignCourseTitle').textContent = courseTitle;
    const modal = new bootstrap.Modal(document.getElementById('reassignModal'));
    modal.show();
}

// Auto open hash tab if present
const hash = window.location.hash;
if (hash) {
    const triggerEl = document.querySelector(`button[data-bs-target="${hash}"]`);
    if (triggerEl) {
        bootstrap.Tab.getOrCreateInstance(triggerEl).show();
    }
}
</script>
