<?php $pageTitle = 'Courses & Approval'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Courses & Approval</h2>
        <p class="text-muted small mb-0">Manage the full course lifecycle — authoring, review, publishing, and archiving.</p>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <?php
    $kpiCards = [
        ['label' => 'Total Courses', 'value' => $kpis['total'], 'icon' => 'bi-collection-play-fill', 'color' => '#6F4E37'],
        ['label' => 'Published', 'value' => $kpis['published'], 'icon' => 'bi-check-circle-fill', 'color' => '#10B981'],
        ['label' => 'Drafts', 'value' => $kpis['drafts'], 'icon' => 'bi-pencil-square', 'color' => '#6B7280'],
        ['label' => 'Pending Review', 'value' => $kpis['pending_review'], 'icon' => 'bi-hourglass-split', 'color' => '#F59E0B'],
        ['label' => 'Changes Requested', 'value' => $kpis['changes_requested'], 'icon' => 'bi-arrow-repeat', 'color' => '#D97706'],
        ['label' => 'Scheduled', 'value' => $kpis['scheduled'], 'icon' => 'bi-calendar-event-fill', 'color' => '#6366F1'],
        ['label' => 'Archived', 'value' => $kpis['archived'], 'icon' => 'bi-archive-fill', 'color' => '#374151'],
        ['label' => 'Free / Paid', 'value' => $kpis['free'] . ' / ' . $kpis['paid'], 'icon' => 'bi-cash-coin', 'color' => '#2563EB'],
    ];
    ?>
    <?php foreach ($kpiCards as $card): ?>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold"><?= e($card['label']) ?></span>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:<?= $card['color'] ?>1a;color:<?= $card['color'] ?>;">
                        <i class="bi <?= $card['icon'] ?>"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark"><?= is_numeric($card['value']) ? number_format((float)$card['value']) : e((string)$card['value']) ?></h3>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Search & Advanced Filter Toolbar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form action="<?= url('admin/courses') ?>" method="GET" id="filterForm">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm border-start-0" placeholder="Search title or instructor..." value="<?= e($filters['q']) ?>">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <?php foreach (\App\Services\CourseApprovalService::STATUSES as $s): ?>
                        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $s))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="category_id" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (string)$filters['category_id'] === (string)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="instructor_id" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Instructors</option>
                    <?php foreach ($instructors as $ins): ?>
                        <option value="<?= $ins['id'] ?>" <?= (string)$filters['instructor_id'] === (string)$ins['id'] ? 'selected' : '' ?>><?= e($ins['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="is_free" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Free & Paid</option>
                    <option value="1" <?= $filters['is_free'] === '1' ? 'selected' : '' ?>>Free Only</option>
                    <option value="0" <?= $filters['is_free'] === '0' ? 'selected' : '' ?>>Paid Only</option>
                </select>
            </div>

            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply Filter"><i class="bi bi-funnel-fill"></i></button>
                <a href="<?= url('admin/courses') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<?php
$statusBadges = [
    'draft' => 'bg-secondary',
    'pending_review' => 'bg-warning-subtle text-warning border border-warning',
    'under_review' => 'bg-info-subtle text-info border border-info',
    'changes_requested' => 'bg-warning text-dark',
    'approved' => 'bg-success-subtle text-success border border-success',
    'scheduled' => 'bg-primary-subtle text-primary border border-primary',
    'published' => 'bg-success',
    'unpublished' => 'bg-secondary-subtle text-secondary border',
    'archived' => 'bg-dark-subtle text-dark border',
    'rejected' => 'bg-danger-subtle text-danger border border-danger',
];
?>

<!-- Bulk Action Floating Bar -->
<form action="<?= url('admin/courses/bulk') ?>" method="POST" id="bulkActionForm">
    <?= csrf_field() ?>
    <?php if ($canPublish || $canArchive): ?>
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-3 bg-dark text-white d-none" id="bulkActionBar">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark px-2 py-1"><span id="selectedCount">0</span> selected</span>
                <span class="small text-white-50">Choose a bulk action:</span>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select name="bulk_action" id="bulkActionSelect" class="form-select form-select-sm" style="width:180px;" required>
                    <option value="">Select Action...</option>
                    <?php if ($canPublish): ?>
                        <option value="publish">Publish Selected</option>
                        <option value="unpublish">Unpublish Selected</option>
                    <?php endif; ?>
                    <?php if ($canArchive): ?>
                        <option value="archive">Archive Selected</option>
                    <?php endif; ?>
                </select>
                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3" onclick="return confirm('Apply this action to all selected courses?')">
                    Apply Action
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th class="text-center">Enrollments</th>
                        <th class="text-center">Completion</th>
                        <th class="text-center">Rating</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="py-4">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                                        <i class="bi bi-collection-play text-muted fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold">No courses found</h5>
                                    <p class="text-muted small mb-3">Try adjusting your filters or keyword search.</p>
                                    <a href="<?= url('admin/courses') ?>" class="btn btn-outline-primary btn-sm">Reset All Filters</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $idx => $course): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="course_ids[]" value="<?= $course['id'] ?>" class="form-check-input course-checkbox">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= course_thumbnail($course['thumbnail'] ?? null, $idx) ?>"
                                             style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #eee;" alt="">
                                        <div>
                                            <a href="<?= url('admin/courses/' . $course['id']) ?>" class="fw-bold small text-dark text-decoration-none d-block"><?= e($course['title']) ?></a>
                                            <small class="text-primary fw-semibold"><?= e($course['category_name'] ?? '—') ?></small>
                                            <?php if ($course['is_free']): ?>
                                                <span class="badge bg-success-subtle text-success border border-success ms-1" style="font-size:0.65rem;">Free</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">RWF <?= number_format((float)$course['price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="small"><?= e($course['instructor_name'] ?? '—') ?></td>
                                <td class="text-center small">
                                    <span class="badge bg-light text-dark border"><i class="bi bi-people-fill me-1 text-primary"></i><?= (int)$course['enrollment_count'] ?></span>
                                </td>
                                <td class="text-center small">
                                    <?= $course['completion_rate'] !== null ? e($course['completion_rate']) . '%' : '—' ?>
                                </td>
                                <td class="text-center small">
                                    <?php if ($course['avg_rating']): ?>
                                        <i class="bi bi-star-fill text-warning"></i> <?= e($course['avg_rating']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusBadges[$course['status']] ?? 'bg-secondary' ?> text-capitalize px-2 py-1">
                                        <?= e(str_replace('_', ' ', $course['status'])) ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?= date('M d, Y', strtotime($course['updated_at'])) ?></td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">Manage</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="border-radius:12px;">
                                            <li><a class="dropdown-item py-2" href="<?= url('admin/courses/' . $course['id']) ?>"><i class="bi bi-eye-fill me-2 text-primary"></i> Review / Detail</a></li>
                                            <li><a class="dropdown-item py-2" href="<?= url('courses/' . e($course['slug'])) ?>" target="_blank"><i class="bi bi-box-arrow-up-right me-2 text-secondary"></i> View on Site</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
        <div class="small text-muted">
            Showing <strong><?= $pagination['from'] ?></strong> to <strong><?= $pagination['to'] ?></strong> of <strong><?= number_format($pagination['total']) ?></strong> courses
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('admin/courses?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1]))) ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == 1 || $i == $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2): ?>
                        <li class="page-item <?= $pagination['current_page'] == $i ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('admin/courses?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>"><?= $i ?></a>
                        </li>
                    <?php elseif ($i == 2 || $i == $pagination['total_pages'] - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('admin/courses?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1]))) ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>

<script>
const selectAll = document.getElementById('selectAllCheckbox');
const courseCheckboxes = document.querySelectorAll('.course-checkbox');
const bulkBar = document.getElementById('bulkActionBar');
const selectedCount = document.getElementById('selectedCount');

function updateBulkBar() {
    if (!bulkBar) return;
    const checked = document.querySelectorAll('.course-checkbox:checked');
    selectedCount.textContent = checked.length;
    bulkBar.classList.toggle('d-none', checked.length === 0);
}

if (selectAll) {
    selectAll.addEventListener('change', function () {
        courseCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });
}
courseCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));
</script>
