<div class="auth-card mx-auto shadow-lg rounded-4 p-4 p-md-5" style="max-width: 480px; background: #fff;">
    <div class="text-center mb-4">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width:64px;height:64px;background:#26140A;color:#F3C78E;font-size:1.8rem;">
            <i class="bi bi-cup-hot-fill"></i>
        </div>
        <h3 class="font-heading fw-bold text-dark mb-1">Set Up Your Account</h3>
        <p class="text-muted small mb-0">Welcome to <strong>Beyond Barista Academy</strong>, <strong><?= e($invitation['name']) ?></strong>!</p>
    </div>

    <div class="alert alert-light border rounded-3 p-3 mb-4 small">
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Account Email:</span>
            <strong class="text-dark"><?= e($invitation['email']) ?></strong>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Assigned Role:</span>
            <span class="badge bg-primary text-capitalize"><?= e($invitation['role_name']) ?></span>
        </div>
        <?php if (!empty($invitation['cohort_name'])): ?>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Assigned Cohort:</span>
                <span class="badge bg-secondary"><?= e($invitation['cohort_name']) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <form action="<?= url('invite/accept/' . $token) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label small fw-bold">Phone Number (Optional)</label>
            <input type="text" name="phone" class="form-control" placeholder="+250 788 000 000">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Create Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required minlength="8">
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type your password" required minlength="8">
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
            <i class="bi bi-shield-check me-1"></i> Complete Setup & Activate
        </button>
    </form>
</div>
