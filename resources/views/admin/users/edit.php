<?php $pageTitle = 'Edit User — ' . e($user['name']); ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/users') ?>" class="text-decoration-none text-muted">Users Directory</a></li>
        <li class="breadcrumb-item"><a href="<?= url('admin/users/' . $user['id']) ?>" class="text-decoration-none text-muted"><?= e($user['name']) ?></a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface" style="max-width: 900px; margin: 0 auto;">
    <div class="mb-4">
        <h3 class="font-heading fw-bold mb-1 text-primary-dark">Edit User Account</h3>
        <p class="text-muted small mb-0">Update identity, access roles, status, and profile information for <strong><?= e($user['name']) ?></strong>.</p>
    </div>

    <form action="<?= url('admin/users/' . $user['id'] . '/update') ?>" method="POST">
        <?= csrf_field() ?>

        <!-- Basic Account Details -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-person-fill me-2"></i> Account Identity</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Primary Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select" required>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $user['role_id'] == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Account Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : '' ?>>Pending Verification</option>
                    <option value="suspended" <?= $user['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="locked" <?= $user['status'] === 'locked' ? 'selected' : '' ?>>Locked</option>
                    <option value="archived" <?= $user['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
        </div>

        <!-- Custom IDs & Multi-Roles -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-person-vcard me-2"></i> Identifiers & Multi-Roles</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Student ID</label>
                <input type="text" name="student_id" class="form-control" value="<?= e($user['student_id'] ?? '') ?>" placeholder="e.g. BBA-STU-2026-0042">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Instructor ID</label>
                <input type="text" name="instructor_id" class="form-control" value="<?= e($user['instructor_id'] ?? '') ?>" placeholder="e.g. BBA-INS-0007">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Secondary Roles (Multi-Role Assignment)</label>
                <div class="d-flex flex-wrap gap-3 p-3 border rounded-3 bg-light">
                    <?php 
                        $userRoleIds = array_column($user['roles'] ?? [], 'id');
                    ?>
                    <?php foreach ($roles as $r): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $r['id'] ?>" id="role_<?= $r['id'] ?>" <?= in_array($r['id'], $userRoleIds) ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-bold" for="role_<?= $r['id'] ?>"><?= e($r['name']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Cohort Batches -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-collection me-2"></i> Cohorts & Training Batches</h5>
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-3 p-3 border rounded-3 bg-light">
                <?php 
                    $userCohortIds = array_column($user['cohorts'] ?? [], 'id');
                ?>
                <?php foreach ($cohorts as $c): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cohort_ids[]" value="<?= $c['id'] ?>" id="cohort_<?= $c['id'] ?>" <?= in_array($c['id'], $userCohortIds) ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="cohort_<?= $c['id'] ?>"><?= e($c['name']) ?> (<code><?= e($c['code']) ?></code>)</label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Password Change -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-shield-lock me-2"></i> Change Password (Optional)</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
            </div>
        </div>

        <!-- Profile Details -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-geo-alt me-2"></i> Profile & Location</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">City</label>
                <input type="text" name="city" class="form-control" value="<?= e($user['city'] ?? 'Kigali') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Country</label>
                <input type="text" name="country" class="form-control" value="<?= e($user['country'] ?? 'Rwanda') ?>">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Headline / Specialization</label>
                <input type="text" name="headline" class="form-control" value="<?= e($user['headline'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Bio</label>
                <textarea name="bio" rows="3" class="form-control"><?= e($user['bio'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center border-top pt-3">
            <a href="<?= url('admin/users/' . $user['id']) ?>" class="btn btn-light btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4">
                <i class="bi bi-check-circle-fill me-1"></i> Update User Account
            </button>
        </div>
    </form>
</div>
