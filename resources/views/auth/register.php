<div class="card border-0 shadow-xl rounded-4 p-4 p-lg-5">
    <div class="text-center mb-5">
        <h3 class="font-heading fw-bold mb-2">Student Admission Form</h3>
        <p class="text-muted small">Join Beyond Barista Academy & start your hospitality journey</p>
    </div>

    <form action="<?= url('register') ?>" method="POST">
        <?= csrf_field() ?>

        <!-- PERSONAL INFORMATION SECTION -->
        <div class="mb-4">
            <h5 class="font-heading fw-bold text-primary mb-3" style="border-bottom: 2px solid #C67C4E; padding-bottom: 0.5rem;">
                <i class="bi bi-person-badge me-2"></i> Personal Information
            </h5>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Diane Mugisha" value="<?= e(old('name')) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= e(old('email')) ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Phone Number (Rwanda) *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+250 788 000 000" value="<?= e(old('phone')) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">City/District *</label>
                        <input type="text" name="city" class="form-control" placeholder="e.g. Kigali" value="<?= e(old('city')) ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Professional Headline</label>
                <input type="text" name="headline" class="form-control" placeholder="e.g. Aspiring Barista / Hospitality Professional" value="<?= e(old('headline')) ?>">
            </div>
        </div>

        <!-- EDUCATIONAL BACKGROUND -->
        <div class="mb-4">
            <h5 class="font-heading fw-bold text-primary mb-3" style="border-bottom: 2px solid #C67C4E; padding-bottom: 0.5rem;">
                <i class="bi bi-book me-2"></i> Educational Background
            </h5>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Highest Education Level *</label>
                        <select name="education_level" class="form-select" required>
                            <option value="">-- Select Level --</option>
                            <option value="primary" <?= old('education_level') === 'primary' ? 'selected' : '' ?>>Primary School</option>
                            <option value="secondary" <?= old('education_level') === 'secondary' ? 'selected' : '' ?>>Secondary School</option>
                            <option value="diploma" <?= old('education_level') === 'diploma' ? 'selected' : '' ?>>Diploma</option>
                            <option value="bachelors" <?= old('education_level') === 'bachelors' ? 'selected' : '' ?>>Bachelor's Degree</option>
                            <option value="masters" <?= old('education_level') === 'masters' ? 'selected' : '' ?>>Master's Degree</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Years of Experience in Hospitality</label>
                        <select name="experience_level" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="none" <?= old('experience_level') === 'none' ? 'selected' : '' ?>>No Experience</option>
                            <option value="1_3" <?= old('experience_level') === '1_3' ? 'selected' : '' ?>>1-3 Years</option>
                            <option value="3_5" <?= old('experience_level') === '3_5' ? 'selected' : '' ?>>3-5 Years</option>
                            <option value="5_plus" <?= old('experience_level') === '5_plus' ? 'selected' : '' ?>>5+ Years</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- PROGRAM INTERESTS -->
        <div class="mb-4">
            <h5 class="font-heading fw-bold text-primary mb-3" style="border-bottom: 2px solid #C67C4E; padding-bottom: 0.5rem;">
                <i class="bi bi-cup-hot me-2"></i> Program Interests
            </h5>

            <div class="mb-3">
                <label class="form-label small fw-bold">Which programs interest you? *</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="interests[]" value="barista" id="int_barista" <?= strpos(old('interests') ?? '', 'barista') !== false ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="int_barista">Professional Barista Skills</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="interests[]" value="roasting" id="int_roasting" <?= strpos(old('interests') ?? '', 'roasting') !== false ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="int_roasting">Coffee Roasting & Cupping</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="interests[]" value="management" id="int_management" <?= strpos(old('interests') ?? '', 'management') !== false ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="int_management">Hospitality Management</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="interests[]" value="food_safety" id="int_food" <?= strpos(old('interests') ?? '', 'food_safety') !== false ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="int_food">Food Safety & HACCP</label>
                </div>
            </div>
        </div>

        <!-- ACCOUNT SECURITY -->
        <div class="mb-4">
            <h5 class="font-heading fw-bold text-primary mb-3" style="border-bottom: 2px solid #C67C4E; padding-bottom: 0.5rem;">
                <i class="bi bi-shield-lock me-2"></i> Account Security
            </h5>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                        <small class="text-muted d-block mt-1">Include uppercase, lowercase, and numbers for security</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- TERMS & CONDITIONS -->
        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="agree_terms" id="agree_terms" required>
                <label class="form-check-label small" for="agree_terms">
                    I agree to the <a href="<?= url('terms') ?>" target="_blank" class="text-accent">Terms of Service</a> and
                    <a href="<?= url('privacy') ?>" target="_blank" class="text-accent">Privacy Policy</a> *
                </label>
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="subscribe_updates" id="subscribe_updates" checked>
                <label class="form-check-label small" for="subscribe_updates">
                    Send me updates about courses and special offers
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3">
            <i class="bi bi-check-circle me-2"></i> Complete Admission
        </button>

        <div class="text-center text-muted small">
            Already registered? <a href="<?= url('login') ?>" class="fw-bold text-accent">Sign in here</a>
        </div>
    </form>
</div>
