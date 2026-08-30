<?php $pageTitle = 'Users & Identity Management'; ?>

<!-- Top Header with Actions -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Users & Access Management</h2>
        <p class="text-muted small mb-0">Manage student identities, instructor credentials, cohorts, and granular access permissions.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('admin/users/export?' . http_build_query($filters)) ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#importCsvModal">
            <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
        </button>
        <button type="button" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center gap-1 shadow-sm text-dark" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
            <i class="bi bi-send-fill text-warning"></i> Invite User
        </button>
        <a href="<?= url('admin/users/create') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-person-plus-fill"></i> Add New User
        </a>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Users</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(111,78,55,0.1);color:#6F4E37;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total_users']) ?></h3>
            <small class="text-success" style="font-size:0.72rem;"><i class="bi bi-arrow-up-short"></i> Active in LMS</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Active Learners</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(16,185,129,0.1);color:#10B981;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['active_learners']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Enrolled Students</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Instructors</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(217,119,6,0.1);color:#D97706;">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['instructors']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Barista Trainers</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Graduates</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(99,102,241,0.1);color:#6366F1;">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['certified_students']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Certified Alumni</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Pending Invites</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(245,158,11,0.1);color:#F59E0B;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['pending_invites']) ?></h3>
            <small class="text-warning" style="font-size:0.72rem;">Awaiting Setup</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-surface">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Suspended / Locked</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:rgba(239,68,68,0.1);color:#EF4444;">
                    <i class="bi bi-shield-slash-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-danger"><?= number_format($kpis['suspended_or_locked']) ?></h3>
            <small class="text-muted" style="font-size:0.72rem;">Restricted Access</small>
        </div>
    </div>
</div>

<!-- Search & Advanced Filter Toolbar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form action="<?= url('admin/users') ?>" method="GET" id="filterForm">
        <div class="row g-2 align-items-center">
            <!-- Keyword Search -->
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control form-control-sm border-start-0" placeholder="Search name, email, student ID..." value="<?= e($filters['q']) ?>">
                </div>
            </div>

            <!-- Role Filter -->
            <div class="col-6 col-md-2">
                <select name="role" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= e($r['slug']) ?>" <?= $filters['role'] === $r['slug'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="suspended" <?= $filters['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="locked" <?= $filters['status'] === 'locked' ? 'selected' : '' ?>>Locked</option>
                </select>
            </div>

            <!-- Cohort Filter -->
            <div class="col-6 col-md-2">
                <select name="cohort_id" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Cohorts / Batches</option>
                    <?php foreach ($cohorts as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filters['cohort_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Course Filter -->
            <div class="col-6 col-md-2">
                <select name="course_id" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Enrolled Course...</option>
                    <?php foreach ($courses as $crs): ?>
                        <option value="<?= $crs['id'] ?>" <?= $filters['course_id'] == $crs['id'] ? 'selected' : '' ?>><?= e($crs['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Actions -->
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply Filter"><i class="bi bi-funnel-fill"></i></button>
                <a href="<?= url('admin/users') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Bulk Action Floating Bar (shown when checkboxes are selected) -->
<form action="<?= url('admin/users/bulk') ?>" method="POST" id="bulkActionForm">
    <?= csrf_field() ?>
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-3 bg-dark text-white d-none" id="bulkActionBar">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark px-2 py-1"><span id="selectedCount">0</span> selected</span>
                <span class="small text-white-50">Choose an action to apply across all selected accounts:</span>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <select name="bulk_action" id="bulkActionSelect" class="form-select form-select-sm" style="width:180px;" required>
                    <option value="">Select Bulk Action...</option>
                    <optgroup label="Status Change">
                        <option value="activate">Activate Selected</option>
                        <option value="suspend">Suspend Selected</option>
                        <option value="lock">Lock Selected</option>
                        <option value="archive">Archive Selected</option>
                    </optgroup>
                    <optgroup label="Assignments">
                        <option value="assign_role">Assign Role</option>
                        <option value="assign_cohort">Assign to Cohort</option>
                        <option value="enroll_course">Enroll in Course</option>
                    </optgroup>
                </select>

                <!-- Dynamic Payload Selectors -->
                <select name="bulk_role_id" id="bulkRoleSelect" class="form-select form-select-sm d-none" style="width:160px;">
                    <option value="">Choose Role...</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="bulk_cohort_id" id="bulkCohortSelect" class="form-select form-select-sm d-none" style="width:180px;">
                    <option value="">Choose Cohort...</option>
                    <?php foreach ($cohorts as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="bulk_course_id" id="bulkCourseSelect" class="form-select form-select-sm d-none" style="width:200px;">
                    <option value="">Choose Course...</option>
                    <?php foreach ($courses as $crs): ?>
                        <option value="<?= $crs['id'] ?>"><?= e($crs['title']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3" onclick="return confirm('Apply this bulk action to all selected accounts?')">
                    Apply Action
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                        </th>
                        <th>User & Identity</th>
                        <th>Role & Access</th>
                        <th>Cohort / Group</th>
                        <th>LMS Progress</th>
                        <th>Status</th>
                        <th>Joined / Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
                                        <i class="bi bi-people text-muted fs-3"></i>
                                    </div>
                                    <h5 class="fw-bold">No users found</h5>
                                    <p class="text-muted small mb-3">Try adjusting your filters or keyword search query.</p>
                                    <a href="<?= url('admin/users') ?>" class="btn btn-outline-primary btn-sm">Reset All Filters</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="user_ids[]" value="<?= $u['id'] ?>" class="form-check-input user-checkbox">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <?php if (!empty($u['avatar'])): ?>
                                                <img src="<?= asset('uploads/' . e($u['avatar'])) ?>" class="rounded-circle" style="width:42px;height:42px;object-fit:cover;" alt="Avatar">
                                            <?php else: ?>
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                                                     style="width:42px;height:42px;background:linear-gradient(135deg, <?= $u['primary_role_slug'] === 'super_admin' ? '#4C3103, #1A0D06' : ($u['primary_role_slug'] === 'instructor' ? '#D97706, #92400E' : '#2563EB, #1D4ED8') ?>);font-size:0.95rem;">
                                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <span class="position-absolute bottom-0 end-0 p-1 border border-light rounded-circle <?= $u['status'] === 'active' ? 'bg-success' : ($u['status'] === 'pending' ? 'bg-warning' : 'bg-danger') ?>" style="width:10px;height:10px;"></span>
                                        </div>
                                        <div>
                                            <a href="<?= url('admin/users/' . $u['id']) ?>" class="fw-bold text-dark text-decoration-none hover-primary d-block">
                                                <?= e($u['name']) ?>
                                            </a>
                                            <div class="text-muted small" style="font-size:0.78rem;">
                                                <i class="bi bi-envelope me-1"></i><?= e($u['email']) ?>
                                                <?php if (!empty($u['phone'])): ?>
                                                    <span class="mx-1">•</span> <i class="bi bi-telephone me-1"></i><?= e($u['phone']) ?>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($u['student_id'])): ?>
                                                <span class="badge bg-light text-secondary border mt-1" style="font-size:0.68rem; font-family:monospace;"><?= e($u['student_id']) ?></span>
                                            <?php elseif (!empty($u['instructor_id'])): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning mt-1" style="font-size:0.68rem; font-family:monospace;"><?= e($u['instructor_id']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if (!empty($u['all_roles'])): ?>
                                            <?php foreach ($u['all_roles'] as $role): ?>
                                                <span class="badge <?= $role['slug'] === 'super_admin' ? 'bg-dark' : ($role['slug'] === 'instructor' ? 'bg-warning text-dark' : ($role['slug'] === 'admin' ? 'bg-info text-dark' : 'bg-primary')) ?>">
                                                    <?= e($role['name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= e($u['primary_role_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($u['cohorts'])): ?>
                                        <?php foreach ($u['cohorts'] as $ch): ?>
                                            <span class="badge bg-light text-dark border d-inline-block text-truncate" style="max-width:140px;" title="<?= e($ch['name']) ?>">
                                                <i class="bi bi-collection me-1 text-primary"></i><?= e($ch['code']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark border" title="Course Enrollments">
                                            <i class="bi bi-journal-bookmark me-1 text-primary"></i><?= $u['enrollments_count'] ?>
                                        </span>
                                        <?php if ($u['certificates_count'] > 0): ?>
                                            <span class="badge bg-success-subtle text-success border border-success" title="Earned Certificates">
                                                <i class="bi bi-award-fill me-1"></i><?= $u['certificates_count'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                        $statusBadges = [
                                            'active' => 'bg-success-subtle text-success border border-success',
                                            'pending' => 'bg-warning-subtle text-warning border border-warning',
                                            'suspended' => 'bg-danger-subtle text-danger border border-danger',
                                            'locked' => 'bg-dark-subtle text-dark border border-dark',
                                            'archived' => 'bg-secondary-subtle text-secondary border'
                                        ];
                                    ?>
                                    <span class="badge <?= $statusBadges[$u['status']] ?? 'bg-secondary' ?> text-capitalize px-2 py-1">
                                        <?= e($u['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <div><i class="bi bi-calendar-event me-1"></i><?= date('M d, Y', strtotime($u['created_at'])) ?></div>
                                        <div style="font-size:0.72rem;">
                                            Last: <?= $u['last_login_at'] ? date('M d, H:i', strtotime($u['last_login_at'])) : '<span class="text-warning">Never</span>' ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Manage
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="border-radius:12px;">
                                            <li><a class="dropdown-item py-2" href="<?= url('admin/users/' . $u['id']) ?>"><i class="bi bi-person-lines-fill me-2 text-primary"></i> 360° Profile</a></li>
                                            <li><a class="dropdown-item py-2" href="<?= url('admin/users/' . $u['id'] . '/edit') ?>"><i class="bi bi-pencil-square me-2 text-secondary"></i> Edit User</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="<?= url('admin/users/' . $u['id'] . '/reset-password') ?>" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item py-2 text-warning" onclick="return confirm('Generate a password reset link for this user?')">
                                                        <i class="bi bi-key-fill me-2"></i> Reset Password
                                                    </button>
                                                </form>
                                            </li>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <li>
                                                    <button type="button" class="dropdown-item py-2 text-danger" onclick="triggerSingleBulk('suspend', <?= $u['id'] ?>)">
                                                        <i class="bi bi-shield-x me-2"></i> Suspend Account
                                                    </button>
                                                </li>
                                            <?php else: ?>
                                                <li>
                                                    <button type="button" class="dropdown-item py-2 text-success" onclick="triggerSingleBulk('activate', <?= $u['id'] ?>)">
                                                        <i class="bi bi-shield-check me-2"></i> Activate Account
                                                    </button>
                                                </li>
                                            <?php endif; ?>
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

<!-- Pagination Bar -->
<?php if ($pagination['total_pages'] > 1): ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
        <div class="small text-muted">
            Showing <strong><?= $pagination['from'] ?></strong> to <strong><?= $pagination['to'] ?></strong> of <strong><?= number_format($pagination['total']) ?></strong> users
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('admin/users?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1]))) ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == 1 || $i == $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2): ?>
                        <li class="page-item <?= $pagination['current_page'] == $i ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('admin/users?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>"><?= $i ?></a>
                        </li>
                    <?php elseif ($i == 2 || $i == $pagination['total_pages'] - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= url('admin/users?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1]))) ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MODALS -->
<!-- ========================================================================= -->

<!-- 1. Invite User Modal -->
<div class="modal fade" id="inviteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/users/invite') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-send-fill text-warning me-2"></i> Invite User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Send a secure onboarding invitation link to a student, instructor, or administrator. They will set their own password securely.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Marie Mukamana" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="marie@example.com" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Account Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= $r['slug'] === 'student' ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Assign Cohort</label>
                            <select name="cohort_id" class="form-select">
                                <option value="">None (Optional)</option>
                                <?php foreach ($cohorts as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-3">Generate & Send Invite</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Bulk CSV Import Modal with 2-Step Preview -->
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title font-heading fw-bold"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i> Bulk CSV User Import</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Step 1: Upload File -->
                <div id="importStep1">
                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> CSV must include column headers: <code>name</code>, <code>email</code>, and optional: <code>phone</code>, <code>role</code> (student/instructor/admin), <code>status</code>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select CSV File</label>
                        <input type="file" id="csvFileInput" class="form-control" accept=".csv">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Default Cohort for Imported Students</label>
                        <select id="importCohortSelect" class="form-select">
                            <option value="0">None</option>
                            <?php foreach ($cohorts as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" id="btnPreviewCsv">
                        <i class="bi bi-eye me-1"></i> Preview & Validate CSV
                    </button>
                </div>

                <!-- Step 2: Validation Preview Grid -->
                <div id="importStep2" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">Import Preview & Validation</h6>
                        <div>
                            <span class="badge bg-success" id="validCountBadge">0 Valid</span>
                            <span class="badge bg-danger" id="errorCountBadge">0 Errors</span>
                        </div>
                    </div>
                    <div class="table-responsive border rounded-3 mb-3" style="max-height:260px; overflow-y:auto;">
                        <table class="table table-sm table-striped small align-middle mb-0" id="previewTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Validation</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm w-50" id="btnBackToStep1">Back</button>
                        <button type="button" class="btn btn-success btn-sm w-50 fw-bold" id="btnConfirmImport">
                            <i class="bi bi-check-circle me-1"></i> Import Valid Rows
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Checkbox & Bulk Actions logic
const selectAll = document.getElementById('selectAllCheckbox');
const userCheckboxes = document.querySelectorAll('.user-checkbox');
const bulkBar = document.getElementById('bulkActionBar');
const selectedCount = document.getElementById('selectedCount');
const bulkActionSelect = document.getElementById('bulkActionSelect');

function updateBulkBar() {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    selectedCount.textContent = checked.length;
    if (checked.length > 0) {
        bulkBar.classList.remove('d-none');
    } else {
        bulkBar.classList.add('d-none');
    }
}

if (selectAll) {
    selectAll.addEventListener('change', function () {
        userCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });
}

userCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

if (bulkActionSelect) {
    bulkActionSelect.addEventListener('change', function () {
        const val = this.value;
        document.getElementById('bulkRoleSelect').classList.toggle('d-none', val !== 'assign_role');
        document.getElementById('bulkCohortSelect').classList.toggle('d-none', val !== 'assign_cohort');
        document.getElementById('bulkCourseSelect').classList.toggle('d-none', val !== 'enroll_course');
    });
}

function triggerSingleBulk(action, userId) {
    if (!confirm('Are you sure you want to perform this action?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url('admin/users/bulk') ?>';
    form.innerHTML = `
        <?= csrf_field() ?>
        <input type="hidden" name="bulk_action" value="${action}">
        <input type="hidden" name="user_ids[]" value="${userId}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// CSV Preview & Import AJAX
let validatedRows = [];

document.getElementById('btnPreviewCsv')?.addEventListener('click', function () {
    const fileInput = document.getElementById('csvFileInput');
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Please choose a CSV file.');
        return;
    }

    const formData = new FormData();
    formData.append('csv_file', fileInput.files[0]);
    formData.append('<?= csrf_name() ?>', '<?= csrf_token() ?>');

    fetch('<?= url('admin/users/import/preview') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'CSV Validation failed.');
            return;
        }

        validatedRows = data.rows || [];
        document.getElementById('validCountBadge').textContent = `${data.valid_count} Valid`;
        document.getElementById('errorCountBadge').textContent = `${data.error_count} Errors`;

        const tbody = document.querySelector('#previewTable tbody');
        tbody.innerHTML = '';

        validatedRows.forEach(r => {
            const tr = document.createElement('tr');
            tr.className = r.is_valid ? '' : 'table-danger';
            tr.innerHTML = `
                <td>${r.row_number}</td>
                <td>${r.name}</td>
                <td>${r.email}</td>
                <td>${r.role}</td>
                <td>${r.status}</td>
                <td>${r.is_valid ? '<span class="text-success"><i class="bi bi-check-circle"></i> Ready</span>' : '<span class="text-danger small">' + r.errors.join(', ') + '</span>'}</td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('importStep1').classList.add('d-none');
        document.getElementById('importStep2').classList.remove('d-none');
    })
    .catch(err => {
        alert('Error validating CSV file.');
    });
});

document.getElementById('btnBackToStep1')?.addEventListener('click', function () {
    document.getElementById('importStep2').classList.add('d-none');
    document.getElementById('importStep1').classList.remove('d-none');
});

document.getElementById('btnConfirmImport')?.addEventListener('click', function () {
    const cohortId = document.getElementById('importCohortSelect').value;

    fetch('<?= url('admin/users/import/process') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= csrf_token() ?>'
        },
        body: JSON.stringify({
            rows: validatedRows,
            cohort_id: cohortId,
            <?= csrf_name() ?>: '<?= csrf_token() ?>'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(`Import completed! Successfully imported ${data.imported} user(s).`);
            window.location.reload();
        } else {
            alert(data.message || 'Import failed.');
        }
    });
});
</script>
