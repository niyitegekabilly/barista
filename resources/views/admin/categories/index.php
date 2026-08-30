<?php $pageTitle = 'Training Categories & Taxonomy'; ?>

<!-- Top Header with Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Categories & Taxonomy Hierarchy</h2>
        <p class="text-muted small mb-0">Organize courses, learning paths, and curriculum subjects into structured multi-level hierarchies.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('admin/tags') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-tags-fill"></i> Taxonomy Tags
        </a>
        <a href="<?= url('admin/categories/export') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#importCsvModal">
            <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
        </button>
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle-fill"></i> Create Category
        </button>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Categories</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(111,78,55,0.1);color:#6F4E37;">
                    <i class="bi bi-diagram-3-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total']) ?></h3>
            <small class="text-success" style="font-size:0.72rem;"><i class="bi bi-check2"></i> In System</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Active Categories</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(16,185,129,0.1);color:#10B981;">
                    <i class="bi bi-eye-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['active']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Visible in Catalog</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Root Parents</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(217,119,6,0.1);color:#D97706;">
                    <i class="bi bi-folder-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['root_parents']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Primary Fields</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Subcategories</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(99,102,241,0.1);color:#6366F1;">
                    <i class="bi bi-folder2-open"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['subcategories']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Nested Branches</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Courses Assigned</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(37,99,235,0.1);color:#2563EB;">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['assigned_courses']) ?></h3>
            <small class="text-primary" style="font-size:0.72rem;">Categorized</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Empty Categories</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(245,158,11,0.1);color:#F59E0B;">
                    <i class="bi bi-folder-x"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-warning"><?= number_format($kpis['empty_categories']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">0 Courses</small>
        </div>
    </div>
</div>

<!-- Search, Filters & View Mode Selector -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form action="<?= url('admin/categories') ?>" method="GET" id="categoryFilterForm">
        <div class="row g-2 align-items-center">
            <!-- Search -->
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm border-start-0" placeholder="Search categories, description, slug..." value="<?= e($filters['q']) ?>">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="document.getElementById('categoryFilterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <!-- Featured Filter -->
            <div class="col-6 col-md-2">
                <select name="is_featured" class="form-select form-select-sm" onchange="document.getElementById('categoryFilterForm').submit()">
                    <option value="">All Types</option>
                    <option value="1" <?= $filters['is_featured'] === '1' ? 'selected' : '' ?>>Featured Only</option>
                    <option value="0" <?= $filters['is_featured'] === '0' ? 'selected' : '' ?>>Standard Only</option>
                </select>
            </div>

            <!-- View Switcher -->
            <div class="col-6 col-md-2">
                <div class="btn-group btn-group-sm w-100" role="group">
                    <input type="radio" class="btn-check" name="view" id="viewTree" value="tree" <?= $filters['view'] !== 'table' ? 'checked' : '' ?> onchange="document.getElementById('categoryFilterForm').submit()">
                    <label class="btn btn-outline-primary" for="viewTree"><i class="bi bi-diagram-3 me-1"></i> Tree</label>

                    <input type="radio" class="btn-check" name="view" id="viewTable" value="table" <?= $filters['view'] === 'table' ? 'checked' : '' ?> onchange="document.getElementById('categoryFilterForm').submit()">
                    <label class="btn btn-outline-primary" for="viewTable"><i class="bi bi-table me-1"></i> Table</label>
                </div>
            </div>

            <!-- Reset / Submit -->
            <div class="col-6 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                <a href="<?= url('admin/categories') ?>" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Bulk Action Floating Bar -->
<form action="<?= url('admin/categories/bulk') ?>" method="POST" id="bulkCatForm">
    <?= csrf_field() ?>
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-3 bg-dark text-white d-none" id="bulkCatBar">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark px-2 py-1"><span id="selectedCatCount">0</span> selected</span>
                <span class="small text-white-50">Bulk action:</span>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select name="bulk_action" id="bulkCatActionSelect" class="form-select form-select-sm" style="width:180px;" required>
                    <option value="">Select Bulk Action...</option>
                    <option value="activate">Set Active</option>
                    <option value="deactivate">Set Inactive</option>
                    <option value="archive">Archive Selected</option>
                    <option value="change_parent">Change Parent Category</option>
                </select>

                <select name="bulk_parent_id" id="bulkParentSelect" class="form-select form-select-sm d-none" style="width:200px;">
                    <option value="">Choose Parent Category...</option>
                    <option value="0">None (Make Root)</option>
                    <?php foreach ($flatCategories as $fc): ?>
                        <option value="<?= $fc['id'] ?>"><?= e($fc['indented_name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3" onclick="return confirm('Apply bulk action to all selected categories?')">
                    Apply Action
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($categories)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-surface">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                <i class="bi bi-diagram-3 text-muted fs-3"></i>
            </div>
            <h5 class="fw-bold">No Categories Found</h5>
            <p class="text-muted small mb-3">No training categories match your current search or filters.</p>
            <button type="button" class="btn btn-primary btn-sm d-inline-block" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Create Category</button>
        </div>
    <?php elseif ($filters['view'] === 'table' || !empty($filters['q'])): ?>
        <!-- ================================================================= -->
        <!-- TABLE VIEW -->
        <!-- ================================================================= -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-surface">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input" id="selectAllCatCheckbox">
                            </th>
                            <th>Category</th>
                            <th>Slug & Hierarchy</th>
                            <th>Courses</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Order</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="category_ids[]" value="<?= $cat['id'] ?>" class="form-check-input cat-checkbox">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 text-white d-flex align-items-center justify-content-center shadow-sm"
                                             style="width:38px;height:38px;flex-shrink:0;background-color:<?= e($cat['color'] ?: '#4C3103') ?>;">
                                            <i class="bi <?= e($cat['icon'] ?: 'bi-cup-hot') ?>"></i>
                                        </div>
                                        <div>
                                            <a href="<?= url('admin/categories/' . $cat['id']) ?>" class="fw-bold text-dark text-decoration-none hover-primary">
                                                <?= e($cat['name']) ?>
                                            </a>
                                            <?php if (!empty($cat['short_description'])): ?>
                                                <small class="text-muted d-block text-truncate" style="max-width:260px;"><?= e($cat['short_description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code><?= e($cat['slug']) ?></code>
                                    <?php if (!empty($cat['parent_id'])): ?>
                                        <span class="badge bg-light text-secondary border d-block mt-1" style="font-size:0.68rem;">
                                            <i class="bi bi-arrow-return-right me-1"></i>Subcategory
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                                        <i class="bi bi-journal-bookmark me-1"></i><?= $cat['courses_count'] ?? 0 ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $cat['status'] === 'active' ? 'bg-success-subtle text-success border border-success' : ($cat['status'] === 'draft' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-secondary-subtle text-secondary border') ?> text-capitalize">
                                        <?= e($cat['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($cat['is_featured']) ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i> Featured</span>' : '<span class="text-muted small">—</span>' ?>
                                </td>
                                <td><code><?= $cat['sort_order'] ?></code></td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">Manage</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="border-radius:12px;">
                                            <li><a class="dropdown-item py-2" href="<?= url('admin/categories/' . $cat['id']) ?>"><i class="bi bi-eye-fill me-2 text-primary"></i> 360° Detail View</a></li>
                                            <li><a class="dropdown-item py-2" href="<?= url('courses/category/' . $cat['slug']) ?>" target="_blank"><i class="bi bi-box-arrow-up-right me-2 text-secondary"></i> Public Landing</a></li>
                                            <li>
                                                <form action="<?= url('admin/categories/' . $cat['id'] . '/duplicate') ?>" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item py-2 text-dark"><i class="bi bi-files me-2"></i> Duplicate</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item py-2 text-danger" onclick="triggerSafeDelete(<?= $cat['id'] ?>, '<?= addslashes(e($cat['name'])) ?>')">
                                                    <i class="bi bi-trash me-2"></i> Safe Delete...
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- ================================================================= -->
        <!-- VISUAL HIERARCHY TREE VIEW -->
        <!-- ================================================================= -->
        <div class="d-flex flex-column gap-3 mb-4">
            <?php 
                function renderCategoryNode($node) {
                    $hasChildren = !empty($node['children']);
                    ?>
                    <div class="category-tree-node card border-0 shadow-sm rounded-4 p-3 bg-surface position-relative" style="margin-left: <?= ($node['depth'] * 28) ?>px; border-left: 4px solid <?= e($node['color'] ?: '#4C3103') ?> !important;">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check m-0">
                                    <input type="checkbox" name="category_ids[]" value="<?= $node['id'] ?>" class="form-check-input cat-checkbox">
                                </div>
                                <div class="rounded-3 text-white d-flex align-items-center justify-content-center shadow-sm"
                                     style="width:42px;height:42px;flex-shrink:0;background-color:<?= e($node['color'] ?: '#4C3103') ?>;font-size:1.1rem;">
                                    <i class="bi <?= e($node['icon'] ?: 'bi-cup-hot') ?>"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <a href="<?= url('admin/categories/' . $node['id']) ?>" class="fw-bold text-dark text-decoration-none hover-primary fs-6">
                                            <?= e($node['name']) ?>
                                        </a>
                                        <span class="badge <?= $node['status'] === 'active' ? 'bg-success-subtle text-success border border-success' : ($node['status'] === 'draft' ? 'bg-warning-subtle text-warning border border-warning' : 'bg-secondary-subtle text-secondary border') ?> text-capitalize" style="font-size:0.7rem;">
                                            <?= e($node['status']) ?>
                                        </span>
                                        <?php if (!empty($node['is_featured'])): ?>
                                            <span class="badge bg-warning text-dark" style="font-size:0.68rem;"><i class="bi bi-star-fill me-1"></i>Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small" style="font-size:0.78rem;">
                                        <code><?= e($node['slug']) ?></code>
                                        <?php if (!empty($node['short_description'])): ?>
                                            <span class="mx-1">•</span> <?= e($node['short_description']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border px-2 py-1" title="Assigned Courses">
                                    <i class="bi bi-journal-bookmark text-primary me-1"></i><?= $node['courses_count'] ?? 0 ?> Courses
                                </span>
                                <?php if ($hasChildren): ?>
                                    <span class="badge bg-info-subtle text-info border border-info px-2 py-1">
                                        <i class="bi bi-diagram-2 me-1"></i><?= count($node['children']) ?> Subcategories
                                    </span>
                                <?php endif; ?>

                                <div class="dropdown ms-2">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">Manage</button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="border-radius:12px;">
                                        <li><a class="dropdown-item py-2" href="<?= url('admin/categories/' . $node['id']) ?>"><i class="bi bi-eye-fill me-2 text-primary"></i> 360° Detail View</a></li>
                                        <li><a class="dropdown-item py-2" href="<?= url('courses/category/' . $node['slug']) ?>" target="_blank"><i class="bi bi-box-arrow-up-right me-2 text-secondary"></i> Public Landing</a></li>
                                        <li>
                                            <form action="<?= url('admin/categories/' . $node['id'] . '/duplicate') ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item py-2 text-dark"><i class="bi bi-files me-2"></i> Duplicate</button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button" class="dropdown-item py-2 text-danger" onclick="triggerSafeDelete(<?= $node['id'] ?>, '<?= addslashes(e($node['name'])) ?>')">
                                                <i class="bi bi-trash me-2"></i> Safe Delete...
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    if ($hasChildren) {
                        foreach ($node['children'] as $child) {
                            renderCategoryNode($child);
                        }
                    }
                }

                foreach ($categories as $rootNode) {
                    renderCategoryNode($rootNode);
                }
            ?>
        </div>
    <?php endif; ?>
</form>

<!-- ========================================================================= -->
<!-- MODALS -->
<!-- ========================================================================= -->

<!-- 1. Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/categories/store') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-plus-circle-fill text-primary me-2"></i> Create Training Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="newCatName" class="form-control" placeholder="e.g. Espresso Extraction Theory" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Parent Category (Optional)</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Top-Level Root Category)</option>
                                <?php foreach ($flatCategories as $fc): ?>
                                    <option value="<?= $fc['id'] ?>"><?= e($fc['indented_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category Icon</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" id="iconPreviewSpan"><i class="bi bi-cup-hot" id="iconPreviewIcon"></i></span>
                                <input type="text" name="icon" id="catIconInput" class="form-control" value="bi-cup-hot" placeholder="bi-cup-hot">
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#iconPickerModal">Browse</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Accent Color</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#4C3103">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Short Punchy Description</label>
                        <input type="text" name="short_description" class="form-control" placeholder="A one-line description for catalog cards (max 350 chars)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Curriculum Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Detailed educational overview of this category..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Thumbnail Image</label>
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cover Banner Image</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="draft">Draft</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check p-2 border rounded-3 bg-light w-100">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_featured" value="1" id="newCatFeatured">
                                <label class="form-check-label small fw-bold" for="newCatFeatured">Featured on Homepage</label>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Accordion -->
                    <div class="accordion" id="seoAccordion">
                        <div class="accordion-item border rounded-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#seoCollapse">
                                    <i class="bi bi-search me-2"></i> SEO & Metadata (Optional)
                                </button>
                            </h2>
                            <div id="seoCollapse" class="accordion-collapse collapse p-3">
                                <div class="mb-2">
                                    <label class="form-label small">SEO Meta Title</label>
                                    <input type="text" name="seo_title" class="form-control form-control-sm" placeholder="Custom Google title tag">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">SEO Meta Description</label>
                                    <textarea name="seo_description" rows="2" class="form-control form-control-sm" placeholder="Google snippet description..."></textarea>
                                </div>
                                <div>
                                    <label class="form-label small">SEO Keywords</label>
                                    <input type="text" name="seo_keywords" class="form-control form-control-sm" placeholder="e.g. barista course, coffee extraction, kigali coffee academy">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Safe Deletion & Reassignment Modal -->
<div class="modal fade" id="safeDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="safeDeleteForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Safe Category Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">You are about to delete category <strong id="deleteCatName"></strong>.</p>
                    
                    <div id="deleteWarningBox" class="alert alert-warning border-0 rounded-3 small mb-3 d-none">
                        <i class="bi bi-info-circle-fill me-1"></i> This category currently contains <strong id="deleteCoursesCount">0</strong> course(s) and <strong id="deleteSubcatsCount">0</strong> subcategory(ies).
                    </div>

                    <div class="mb-3" id="reassignCoursesGroup">
                        <label class="form-label small fw-bold">Reassign Assigned Courses To: <span class="text-danger">*</span></label>
                        <select name="reassign_courses_to" id="reassignCoursesSelect" class="form-select">
                            <!-- Populated dynamically via AJAX -->
                        </select>
                        <small class="text-muted" style="font-size:0.7rem;">Courses will be safely moved to the target category with zero data loss.</small>
                    </div>

                    <div class="form-check p-2 border rounded-3 bg-light mb-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="archive_only" value="1" id="archiveOnlyCheckbox">
                        <label class="form-check-label small fw-bold" for="archiveOnlyCheckbox">Archive category instead of deleting (Recommended)</label>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">Confirm Deletion / Reassignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Curated Icon Picker Modal -->
<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title font-heading fw-bold"><i class="bi bi-palette me-2 text-primary"></i> Select Category Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 450px; overflow-y: auto;">
                <?php foreach ($iconsCatalog as $groupTitle => $icons): ?>
                    <h6 class="fw-bold text-primary-dark border-bottom pb-1 mb-2 small"><?= e($groupTitle) ?></h6>
                    <div class="row g-2 mb-3">
                        <?php foreach ($icons as $iconClass => $iconName): ?>
                            <div class="col-4 col-md-3">
                                <button type="button" class="btn btn-outline-light text-dark border w-100 p-2 d-flex align-items-center gap-2 text-start icon-select-btn" data-icon="<?= e($iconClass) ?>" style="font-size:0.78rem;">
                                    <i class="bi <?= e($iconClass) ?> fs-5 text-primary"></i>
                                    <span class="text-truncate"><?= e($iconName) ?></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- 4. Bulk CSV Import Modal with Preview -->
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title font-heading fw-bold"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i> Bulk Import Categories from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="importStep1">
                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> CSV must include column headers: <code>name</code>, and optional: <code>parent</code>, <code>icon</code>, <code>status</code>, <code>featured</code>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select CSV File</label>
                        <input type="file" id="catCsvFileInput" class="form-control" accept=".csv">
                    </div>
                    <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" id="btnPreviewCatCsv">
                        <i class="bi bi-eye me-1"></i> Preview & Validate CSV
                    </button>
                </div>

                <div id="importStep2" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">Validation Preview</h6>
                        <div>
                            <span class="badge bg-success" id="validCatCountBadge">0 Valid</span>
                            <span class="badge bg-danger" id="errorCatCountBadge">0 Errors</span>
                        </div>
                    </div>
                    <div class="table-responsive border rounded-3 mb-3" style="max-height:260px; overflow-y:auto;">
                        <table class="table table-sm table-striped small align-middle mb-0" id="previewCatTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Parent</th>
                                    <th>Status</th>
                                    <th>Validation</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm w-50" id="btnBackToStep1">Back</button>
                        <button type="button" class="btn btn-success btn-sm w-50 fw-bold" id="btnConfirmCatImport">
                            <i class="bi bi-check-circle me-1"></i> Import Valid Categories
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Checkbox & Bulk Actions
const selectAllCat = document.getElementById('selectAllCatCheckbox');
const catCheckboxes = document.querySelectorAll('.cat-checkbox');
const bulkCatBar = document.getElementById('bulkCatBar');
const selectedCatCount = document.getElementById('selectedCatCount');
const bulkCatActionSelect = document.getElementById('bulkCatActionSelect');

function updateBulkCatBar() {
    const checked = document.querySelectorAll('.cat-checkbox:checked');
    selectedCatCount.textContent = checked.length;
    if (checked.length > 0) {
        bulkCatBar.classList.remove('d-none');
    } else {
        bulkCatBar.classList.add('d-none');
    }
}

if (selectAllCat) {
    selectAllCat.addEventListener('change', function () {
        catCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkCatBar();
    });
}

catCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkCatBar);
});

if (bulkCatActionSelect) {
    bulkCatActionSelect.addEventListener('change', function () {
        document.getElementById('bulkParentSelect').classList.toggle('d-none', this.value !== 'change_parent');
    });
}

// Icon Picker Selection
document.querySelectorAll('.icon-select-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const iconClass = this.dataset.icon;
        document.getElementById('catIconInput').value = iconClass;
        document.getElementById('iconPreviewIcon').className = 'bi ' + iconClass;
        bootstrap.Modal.getInstance(document.getElementById('iconPickerModal')).hide();
    });
});

// Safe Delete Flow
function triggerSafeDelete(categoryId, categoryName) {
    fetch('<?= url('admin/categories/') ?>' + categoryId + '/delete-prompt')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            document.getElementById('deleteCatName').textContent = categoryName;
            document.getElementById('safeDeleteForm').action = '<?= url('admin/categories/') ?>' + categoryId + '/delete';

            const warningBox = document.getElementById('deleteWarningBox');
            const reassignGroup = document.getElementById('reassignCoursesGroup');
            const reassignSelect = document.getElementById('reassignCoursesSelect');

            document.getElementById('deleteCoursesCount').textContent = data.courses_count;
            document.getElementById('deleteSubcatsCount').textContent = data.subcategories_count;

            reassignSelect.innerHTML = '<option value="">Choose target category...</option>';
            data.target_categories.forEach(tc => {
                reassignSelect.innerHTML += `<option value="${tc.id}">${tc.indented_name}</option>`;
            });

            if (data.courses_count > 0 || data.subcategories_count > 0) {
                warningBox.classList.remove('d-none');
                reassignGroup.classList.remove('d-none');
            } else {
                warningBox.classList.add('d-none');
                reassignGroup.classList.add('d-none');
            }

            const modal = new bootstrap.Modal(document.getElementById('safeDeleteModal'));
            modal.show();
        });
}

// CSV Preview & Import
let validatedCatRows = [];

document.getElementById('btnPreviewCatCsv')?.addEventListener('click', function () {
    const fileInput = document.getElementById('catCsvFileInput');
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Please select a CSV file.');
        return;
    }

    const formData = new FormData();
    formData.append('csv_file', fileInput.files[0]);

    fetch('<?= url('admin/categories/import/preview') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Validation failed.');
            return;
        }

        validatedCatRows = data.rows || [];
        document.getElementById('validCatCountBadge').textContent = `${data.valid_count} Valid`;
        document.getElementById('errorCatCountBadge').textContent = `${data.error_count} Errors`;

        const tbody = document.querySelector('#previewCatTable tbody');
        tbody.innerHTML = '';

        validatedCatRows.forEach(r => {
            const tr = document.createElement('tr');
            tr.className = r.is_valid ? '' : 'table-danger';
            tr.innerHTML = `
                <td>${r.row_number}</td>
                <td>${r.name}</td>
                <td>${r.parent || 'None (Root)'}</td>
                <td>${r.status}</td>
                <td>${r.is_valid ? '<span class="text-success"><i class="bi bi-check-circle"></i> Ready</span>' : '<span class="text-danger small">' + r.errors.join(', ') + '</span>'}</td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('importStep1').classList.add('d-none');
        document.getElementById('importStep2').classList.remove('d-none');
    });
});

document.getElementById('btnBackToStep1')?.addEventListener('click', function () {
    document.getElementById('importStep2').classList.add('d-none');
    document.getElementById('importStep1').classList.remove('d-none');
});

document.getElementById('btnConfirmCatImport')?.addEventListener('click', function () {
    fetch('<?= url('admin/categories/import/process') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= csrf_token() ?>'
        },
        body: JSON.stringify({
            rows: validatedCatRows,
            <?= csrf_name() ?>: '<?= csrf_token() ?>'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully imported ${data.imported} categories.`);
            window.location.reload();
        } else {
            alert(data.message || 'Import failed.');
        }
    });
});
</script>
