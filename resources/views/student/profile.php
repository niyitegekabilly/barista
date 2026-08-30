<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1">My Student Profile</h2>
        <p class="text-muted small mb-0">Update your account information and contact preferences</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4">
            <form action="<?= url('student/profile/update') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                    <small class="text-muted">Contact support to modify registered email address.</small>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Phone Number (Rwanda)</label>
                        <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+250 788 000 000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">City / Location</label>
                        <input type="text" name="city" class="form-control" value="<?= e($user['city'] ?? 'Kigali') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Professional Headline</label>
                    <input type="text" name="headline" class="form-control" value="<?= e($user['headline'] ?? '') ?>" placeholder="e.g. Aspiring Barista / Cafe Supervisor">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Biography</label>
                    <textarea name="bio" class="form-control" rows="4" placeholder="Tell us about your background and hospitality interests..."><?= e($user['bio'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary fw-bold px-4">
                    Save Profile Changes
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm rounded-4 text-center">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold display-4 mx-auto mb-3" style="width:90px;height:90px;">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <h5 class="font-heading mb-1"><?= e($user['name']) ?></h5>
            <span class="badge bg-secondary mb-2"><?= strtoupper($user['role_slug']) ?></span>
            <p class="text-muted small mb-0"><?= e($user['headline'] ?? 'Beyond Barista Student') ?></p>
        </div>
    </div>
</div>
