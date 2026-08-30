<?php $pageTitle = 'Add New User'; ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= url('admin/users') ?>" class="text-decoration-none text-muted">Users Directory</a></li>
        <li class="breadcrumb-item active">Add New User</li>
    </ol>
</nav>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface" style="max-width: 900px; margin: 0 auto;">
    <div class="mb-4">
        <h3 class="font-heading fw-bold mb-1 text-primary-dark">Create User Account</h3>
        <p class="text-muted small mb-0">Register a new student, instructor, or staff member in Beyond Barista Academy.</p>
    </div>

    <form action="<?= url('admin/users/store') ?>" method="POST">
        <?= csrf_field() ?>

        <!-- Basic Account Details -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-person-fill me-2"></i> Account Identity</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Marie Mukamana" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="marie@example.com" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Primary Role <span class="text-danger">*</span></label>
                <select name="role_id" id="roleSelect" class="form-select" required>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= $r['slug'] === 'student' ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Account Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="active" selected>Active</option>
                    <option value="pending">Pending Verification</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>

        <!-- Custom IDs & Cohort -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-person-vcard me-2"></i> Academy Identifiers & Cohorts</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Student ID (Optional)</label>
                <input type="text" name="student_id" class="form-control" placeholder="Auto-generated if blank">
                <small class="text-muted" style="font-size:0.7rem;">Leave blank to auto-generate (e.g. BBA-STU-2026-0042)</small>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Instructor ID (Optional)</label>
                <input type="text" name="instructor_id" class="form-control" placeholder="e.g. BBA-INS-0007">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Assign Initial Cohort</label>
                <select name="cohort_id" class="form-select">
                    <option value="">None (Optional)</option>
                    <?php foreach ($cohorts as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Password Setup -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-shield-lock me-2"></i> Security Credentials</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password or leave blank for auto-generate">
                <small class="text-muted" style="font-size:0.7rem;">If left blank, a secure password will be generated.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="+250 788 123 456">
            </div>
        </div>

        <!-- Profile Details -->
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary-dark"><i class="bi bi-geo-alt me-2"></i> Profile & Location</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">City</label>
                <input type="text" name="city" class="form-control" value="Kigali">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Country</label>
                <input type="text" name="country" class="form-control" value="Rwanda">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Headline / Barista Specialization</label>
                <input type="text" name="headline" class="form-control" placeholder="e.g. Specialty Barista & Sensory Cupper">
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Bio</label>
                <textarea name="bio" rows="3" class="form-control" placeholder="Short biography..."></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center border-top pt-3">
            <a href="<?= url('admin/users') ?>" class="btn btn-light btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4">
                <i class="bi bi-check-circle-fill me-1"></i> Save User Account
            </button>
        </div>
    </form>
</div>
