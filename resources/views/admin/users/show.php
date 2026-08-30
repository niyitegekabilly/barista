<?php $pageTitle = 'Profile — ' . e($user['name']); ?>

<!-- Breadcrumbs & Quick Header -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/users') ?>" class="text-decoration-none text-muted">Users Directory</a></li>
        <li class="breadcrumb-item active"><?= e($user['name']) ?></li>
    </ol>
</nav>

<!-- User 360° Hero Card -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="position-relative">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= asset('uploads/' . e($user['avatar'])) ?>" class="rounded-circle border border-3 border-light shadow-sm" style="width:72px;height:72px;object-fit:cover;" alt="Avatar">
                <?php else: ?>
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm"
                         style="width:72px;height:72px;background:linear-gradient(135deg, <?= $user['role_slug'] === 'super_admin' ? '#4C3103, #1A0D06' : ($user['role_slug'] === 'instructor' ? '#D97706, #92400E' : '#2563EB, #1D4ED8') ?>);font-size:1.6rem;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <span class="position-absolute bottom-0 end-0 p-1 border border-2 border-white rounded-circle <?= $user['status'] === 'active' ? 'bg-success' : ($user['status'] === 'pending' ? 'bg-warning' : 'bg-danger') ?>" style="width:14px;height:14px;"></span>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="font-heading fw-bold mb-0 text-dark"><?= e($user['name']) ?></h3>
                    <?php
                        $statusBadges = [
                            'active' => 'bg-success-subtle text-success border border-success',
                            'pending' => 'bg-warning-subtle text-warning border border-warning',
                            'suspended' => 'bg-danger-subtle text-danger border border-danger',
                            'locked' => 'bg-dark-subtle text-dark border border-dark',
                            'archived' => 'bg-secondary-subtle text-secondary border'
                        ];
                    ?>
                    <span class="badge <?= $statusBadges[$user['status']] ?? 'bg-secondary' ?> text-capitalize px-2 py-1">
                        <?= e($user['status']) ?>
                    </span>
                </div>
                <div class="text-muted small mb-1">
                    <i class="bi bi-envelope me-1"></i><?= e($user['email']) ?>
                    <?php if (!empty($user['phone'])): ?>
                        <span class="mx-1">•</span> <i class="bi bi-telephone me-1"></i><?= e($user['phone']) ?>
                    <?php endif; ?>
                    <?php if (!empty($user['city'])): ?>
                        <span class="mx-1">•</span> <i class="bi bi-geo-alt me-1"></i><?= e($user['city']) ?>, <?= e($user['country'] ?? 'Rwanda') ?>
                    <?php endif; ?>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if (!empty($user['student_id'])): ?>
                        <span class="badge bg-light text-secondary border" style="font-family:monospace;">
                            <i class="bi bi-person-vcard me-1 text-primary"></i><?= e($user['student_id']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($user['instructor_id'])): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning" style="font-family:monospace;">
                            <i class="bi bi-award me-1"></i><?= e($user['instructor_id']) ?>
                        </span>
                    <?php endif; ?>
                    <?php foreach ($roles as $r): ?>
                        <span class="badge <?= $r['slug'] === 'super_admin' ? 'bg-dark' : ($r['slug'] === 'instructor' ? 'bg-warning text-dark' : 'bg-primary') ?>">
                            <?= e($r['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#manualEnrollModal">
                <i class="bi bi-journal-plus"></i> Enroll in Course
            </button>
            <form action="<?= url('admin/users/' . $user['id'] . '/reset-password') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center gap-1 shadow-sm text-dark" onclick="return confirm('Generate a password reset link for this user?')">
                    <i class="bi bi-key-fill text-warning"></i> Reset Password
                </button>
            </form>
            <a href="<?= url('admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-pencil-square"></i> Edit Profile
            </a>
        </div>
    </div>
</div>

<!-- 360° Profile Nav Tabs -->
<ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="profileTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-overview">
            <i class="bi bi-person-lines-fill me-1"></i> Overview
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-roles">
            <i class="bi bi-shield-lock-fill me-1"></i> Roles & Permissions (<?= count($permissions) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-learning">
            <i class="bi bi-mortarboard-fill me-1"></i> Learning & Enrollments (<?= count($enrollments) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-certificates">
            <i class="bi bi-patch-check-fill me-1"></i> Certificates (<?= count($certificates) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-finance">
            <i class="bi bi-credit-card-2-front-fill me-1"></i> Orders & Finance ($<?= number_format($total_spent, 2) ?>)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-security">
            <i class="bi bi-shield-check me-1"></i> Security & Sessions
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-timeline">
            <i class="bi bi-clock-history me-1"></i> Activity Timeline
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-notes">
            <i class="bi bi-sticky-fill me-1"></i> Admin Notes (<?= count($notes) ?>)
        </button>
    </li>
</ul>

<!-- Tab Content Panes -->
<div class="tab-content" id="profileTabsContent">

    <!-- 1. Overview Tab -->
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-surface">
                    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-person-badge me-2"></i> Personal & Contact Info</h5>
                    <table class="table table-borderless small mb-0">
                        <tr><td class="text-muted" style="width:35%;">Full Name:</td><td class="fw-bold"><?= e($user['name']) ?></td></tr>
                        <tr><td class="text-muted">Email Address:</td><td class="fw-bold"><?= e($user['email']) ?></td></tr>
                        <tr><td class="text-muted">Phone Number:</td><td><?= e($user['phone'] ?? '—') ?></td></tr>
                        <tr><td class="text-muted">Headline / Title:</td><td><?= e($user['headline'] ?? '—') ?></td></tr>
                        <tr><td class="text-muted">Bio:</td><td><?= nl2br(e($user['bio'] ?? 'No bio provided.')) ?></td></tr>
                        <tr><td class="text-muted">Location:</td><td><?= e($user['city'] ?? 'Kigali') ?>, <?= e($user['country'] ?? 'Rwanda') ?></td></tr>
                        <tr><td class="text-muted">Preferred Language:</td><td><?= strtoupper(e($user['language'] ?? 'en')) ?></td></tr>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-surface">
                    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-gear-fill me-2"></i> Account & System Metadata</h5>
                    <table class="table table-borderless small mb-0">
                        <tr><td class="text-muted" style="width:40%;">Internal User ID:</td><td class="fw-bold">#<?= $user['id'] ?></td></tr>
                        <tr><td class="text-muted">Student ID:</td><td><code><?= e($user['student_id'] ?? 'Not Assigned') ?></code></td></tr>
                        <tr><td class="text-muted">Instructor ID:</td><td><code><?= e($user['instructor_id'] ?? 'Not Assigned') ?></code></td></tr>
                        <tr><td class="text-muted">Account Status:</td><td><span class="badge <?= $statusBadges[$user['status']] ?? 'bg-secondary' ?>"><?= strtoupper($user['status']) ?></span></td></tr>
                        <tr><td class="text-muted">Email Verified:</td><td><?= $user['email_verified_at'] ? '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Verified</span>' : '<span class="text-warning fw-bold"><i class="bi bi-clock"></i> Unverified</span>' ?></td></tr>
                        <tr><td class="text-muted">Registered On:</td><td><?= date('F d, Y \a\t H:i', strtotime($user['created_at'])) ?></td></tr>
                        <tr><td class="text-muted">Last Active:</td><td><?= $user['last_login_at'] ? date('F d, Y \a\t H:i', strtotime($user['last_login_at'])) : '<span class="text-muted">Never logged in</span>' ?></td></tr>
                        <tr><td class="text-muted">Last IP Address:</td><td><code><?= e($user['last_login_ip'] ?? '—') ?></code></td></tr>
                    </table>
                </div>
            </div>

            <!-- Cohort & Group Cards -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
                    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-collection-fill me-2"></i> Assigned Cohorts & Training Batches</h5>
                    <?php if (empty($cohorts)): ?>
                        <p class="text-muted small mb-0">This user is not currently assigned to any training cohorts.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($cohorts as $ch): ?>
                                <div class="col-md-4">
                                    <div class="border rounded-3 p-3 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary text-uppercase"><?= e($ch['code']) ?></span>
                                            <span class="badge bg-success-subtle text-success"><?= e($ch['status']) ?></span>
                                        </div>
                                        <h6 class="fw-bold mb-1"><?= e($ch['name']) ?></h6>
                                        <small class="text-muted d-block"><?= date('M d, Y', strtotime($ch['start_date'])) ?> – <?= date('M d, Y', strtotime($ch['end_date'])) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Roles & Permissions Tab -->
    <div class="tab-pane fade" id="tab-roles">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-shield-check me-2"></i> Assigned Roles</h5>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <?php foreach ($roles as $r): ?>
                    <div class="p-3 border rounded-3 bg-light d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="bi bi-shield-shaded"></i>
                        </div>
                        <div>
                            <div class="fw-bold"><?= e($r['name']) ?> <?= $r['is_primary'] ? '<span class="badge bg-warning text-dark ms-1">Primary</span>' : '' ?></div>
                            <small class="text-muted"><?= e($r['description'] ?? 'System role') ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-grid-fill me-2"></i> Effective Granular Permissions (<?= count($permissions) ?>)</h5>
            <div class="row g-2">
                <?php foreach ($permissions as $perm): ?>
                    <div class="col-md-4">
                        <div class="p-2 border rounded-3 bg-white d-flex align-items-center gap-2 small">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <div>
                                <span class="fw-bold"><?= e($perm['name']) ?></span>
                                <code class="text-muted d-block" style="font-size:0.7rem;"><?= e($perm['slug']) ?></code>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 3. Learning & Enrollments Tab -->
    <div class="tab-pane fade" id="tab-learning">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-mortarboard-fill me-2"></i> Enrolled Courses</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manualEnrollModal">
                    <i class="bi bi-plus-circle me-1"></i> Enroll in New Course
                </button>
            </div>

            <?php if (empty($enrollments)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-2 mb-2 d-block"></i>
                    No course enrollments found for this student.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Course Title</th>
                                <th>Category</th>
                                <th>Progress</th>
                                <th>Enrolled Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enr): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($enr['course_title']) ?></div>
                                        <small class="text-muted"><?= $enr['completed_lessons'] ?> of <?= $enr['total_lessons'] ?> lessons completed</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= e($enr['category_name'] ?? 'General') ?></span></td>
                                    <td style="width:200px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $enr['progress_percentage'] ?>%;"></div>
                                            </div>
                                            <span class="small fw-bold"><?= $enr['progress_percentage'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="small text-muted"><?= date('M d, Y', strtotime($enr['enrolled_at'])) ?></td>
                                    <td><span class="badge bg-success-subtle text-success text-capitalize"><?= e($enr['status']) ?></span></td>
                                    <td class="text-end">
                                        <form action="<?= url('admin/users/' . $user['id'] . '/drop-course/' . $enr['course_id']) ?>" method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Drop this student from this course?')">
                                                <i class="bi bi-trash"></i> Drop
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4. Certificates Tab -->
    <div class="tab-pane fade" id="tab-certificates">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-patch-check-fill me-2"></i> Issued Academy Certificates</h5>
            <?php if (empty($certificates)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-award fs-2 mb-2 d-block"></i>
                    No certificates have been issued to this user yet.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($certificates as $cert): ?>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 bg-light d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-success text-uppercase mb-1"><i class="bi bi-check2-circle me-1"></i> Verified</span>
                                    <h6 class="fw-bold mb-1"><?= e($cert['course_title']) ?></h6>
                                    <div class="small text-muted mb-1">Number: <code><?= e($cert['certificate_number'] ?? $cert['certificate_code'] ?? '—') ?></code></div>
                                    <small class="text-muted">Issued: <?= date('M d, Y', strtotime($cert['issue_date'] ?? $cert['created_at'])) ?></small>
                                </div>
                                <a href="<?= url('certificate/verify/' . ($cert['certificate_number'] ?? $cert['certificate_code'] ?? '')) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right"></i> Verify
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. Orders & Finance Tab -->
    <div class="tab-pane fade" id="tab-finance">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-heading fw-bold mb-0 text-primary-dark"><i class="bi bi-receipt me-2"></i> Order History</h5>
                <div class="fw-bold text-success">Total Lifetime Value: $<?= number_format($total_spent, 2) ?></div>
            </div>

            <?php if (empty($orders)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-credit-card fs-2 mb-2 d-block"></i>
                    No purchase orders recorded for this user.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Order Code</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td class="fw-bold font-monospace"><?= e($ord['order_number'] ?? ('#ORD-' . $ord['id'])) ?></td>
                                    <td class="fw-bold text-success">$<?= number_format($ord['total_amount'], 2) ?></td>
                                    <td><span class="badge bg-light text-dark border text-uppercase"><?= e($ord['payment_method'] ?? 'Online') ?></span></td>
                                    <td>
                                        <span class="badge <?= $ord['status'] === 'completed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= strtoupper($ord['status']) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= date('M d, Y H:i', strtotime($ord['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 6. Security & Sessions Tab -->
    <div class="tab-pane fade" id="tab-security">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface mb-4">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-shield-lock me-2"></i> Account Security Controls</h5>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <form action="<?= url('admin/users/' . $user['id'] . '/reset-password') ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-warning btn-sm fw-bold">
                        <i class="bi bi-key me-1"></i> Send Password Reset Link
                    </button>
                </form>
                <?php if ($user['status'] === 'active'): ?>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="triggerSingleBulk('suspend', <?= $user['id'] ?>)">
                        <i class="bi bi-shield-x me-1"></i> Suspend Account
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="triggerSingleBulk('activate', <?= $user['id'] ?>)">
                        <i class="bi bi-shield-check me-1"></i> Reactivate Account
                    </button>
                <?php endif; ?>
            </div>

            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-laptop me-2"></i> Recent Login Sessions</h5>
            <?php if (empty($logins)): ?>
                <p class="text-muted small mb-0">No login session logs recorded yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>IP Address</th>
                                <th>User Agent / Device</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logins as $lg): ?>
                                <tr>
                                    <td><code><?= e($lg['ip_address']) ?></code></td>
                                    <td class="text-muted"><?= e($lg['user_agent']) ?></td>
                                    <td><span class="badge <?= $lg['status'] === 'success' ? 'bg-success' : 'bg-danger' ?>"><?= strtoupper($lg['status']) ?></span></td>
                                    <td class="text-muted"><?= date('M d, Y H:i:s', strtotime($lg['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 7. Activity Timeline Tab -->
    <div class="tab-pane fade" id="tab-timeline">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-clock-history me-2"></i> User Audit Trail & Timeline</h5>
            <?php if (empty($audit_trail)): ?>
                <p class="text-muted small mb-0">No audit activity logged for this account.</p>
            <?php else: ?>
                <div class="timeline position-relative ps-4" style="border-left: 2px solid #E5E7EB;">
                    <?php foreach ($audit_trail as $audit): ?>
                        <div class="mb-3 position-relative">
                            <span class="position-absolute bg-primary rounded-circle" style="width:10px;height:10px;left:-21px;top:5px;"></span>
                            <div class="fw-bold small text-dark"><?= e(str_replace('_', ' ', strtoupper($audit['action']))) ?></div>
                            <div class="text-muted small" style="font-size:0.75rem;">
                                <?= date('M d, Y \a\t H:i', strtotime($audit['created_at'])) ?> • Performed by: <?= e($audit['performed_by_name'] ?? 'System') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 8. Admin Notes Tab -->
    <div class="tab-pane fade" id="tab-notes">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-sticky-fill me-2 text-warning"></i> Internal Administrative Notes</h5>
            <p class="small text-muted">These notes are confidential and only visible to academy staff and administrators.</p>

            <!-- Add Note Form -->
            <form action="<?= url('admin/users/' . $user['id'] . '/notes') ?>" method="POST" class="mb-4">
                <?= csrf_field() ?>
                <div class="row g-2">
                    <div class="col-md-3">
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="general">General Note</option>
                            <option value="academic">Academic / Practical Assessment</option>
                            <option value="disciplinary">Disciplinary / Attendance</option>
                            <option value="financial">Financial / Scholarship</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Write internal note..." required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">Add Note</button>
                    </div>
                </div>
            </form>

            <!-- Notes List -->
            <?php if (empty($notes)): ?>
                <p class="text-muted small mb-0">No internal notes added yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($notes as $n): ?>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-secondary text-uppercase" style="font-size:0.68rem;"><?= e($n['type']) ?></span>
                                <small class="text-muted" style="font-size:0.72rem;"><?= date('M d, Y H:i', strtotime($n['created_at'])) ?> by <?= e($n['author_name'] ?? 'Admin') ?></small>
                            </div>
                            <div class="small text-dark"><?= nl2br(e($n['note'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal: Manual Course Enrollment -->
<div class="modal fade" id="manualEnrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/users/' . $user['id'] . '/enroll') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-journal-plus me-2 text-primary"></i> Manual Course Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Directly enroll <strong><?= e($user['name']) ?></strong> into a course without requiring payment checkout.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Choose a course...</option>
                            <?php foreach ($allCourses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['title']) ?> (<?= e($c['level'] ?? 'All Levels') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Enroll Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

// Auto open hash tab if present
const hash = window.location.hash;
if (hash) {
    const triggerEl = document.querySelector(`button[data-bs-target="${hash}"]`);
    if (triggerEl) {
        bootstrap.Tab.getOrCreateInstance(triggerEl).show();
    }
}
</script>
