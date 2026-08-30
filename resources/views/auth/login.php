<div class="card border-0 shadow-xl rounded-4 p-4 p-lg-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #C67C4E;">
            <i class="bi bi-cup-hot-fill"></i>
        </div>
        <h2 class="font-heading fw-bold mb-1">Welcome Back</h2>
        <p class="text-muted">Sign in to resume your specialty coffee and hospitality learning journey</p>
    </div>

    <!-- Login Form -->
    <form action="<?= url('login') ?>" method="POST" id="loginForm">
        <?= csrf_field() ?>

        <!-- Email Field -->
        <div class="mb-4">
            <label class="form-label small fw-bold d-flex align-items-center">
                <i class="bi bi-envelope me-2 text-primary"></i>
                Email Address or Username
            </label>
            <input
                type="email"
                name="email"
                id="loginEmail"
                class="form-control form-control-lg rounded-3"
                placeholder="admin@beyondbarista.rw"
                value="<?= e(old('email')) ?>"
                required
                autofocus>
            <small class="text-muted d-block mt-1">Enter your registered email address</small>
        </div>

        <!-- Password Field -->
        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label small fw-bold d-flex align-items-center mb-0">
                    <i class="bi bi-lock me-2 text-primary"></i>
                    Password
                </label>
                <a href="<?= url('forgot-password') ?>" class="small text-accent fw-semibold">Forgot password?</a>
            </div>
            <input
                type="password"
                name="password"
                id="loginPassword"
                class="form-control form-control-lg rounded-3 mt-2"
                placeholder="••••••••"
                required>
            <small class="text-muted d-block mt-1">6+ characters, case-sensitive</small>
        </div>

        <!-- Remember Me -->
        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember_me" id="rememberMe">
                <label class="form-check-label small" for="rememberMe">
                    Keep me signed in for 30 days
                </label>
            </div>
        </div>

        <!-- Sign In Button -->
        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-4" style="border-radius: 0.75rem; padding: 0.75rem;">
            <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Account
        </button>

        <!-- Sign Up Link -->
        <div class="text-center mb-4">
            <p class="text-muted small mb-0">
                New to Beyond Barista Academy?
                <a href="<?= url('register') ?>" class="fw-bold text-accent">Create a free account</a>
            </p>
        </div>

        <!-- Demo Accounts (Development) -->
        <?php if (env('APP_ENV') === 'local' || env('APP_DEBUG')): ?>
            <hr class="my-4">
            <div class="p-3 bg-light rounded-3 border-2" style="border-color: #FFB703; font-size:0.85rem;">
                <span class="fw-bold text-dark d-block mb-2">
                    <i class="bi bi-gear text-warning me-1"></i> Demo Accounts (Development):
                </span>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm py-2" onclick="fillLogin('admin@beyondbarista.rw', 'Admin@2026')">
                        <i class="bi bi-person-badge me-1"></i> Admin Account
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-2" onclick="fillLogin('instructor@beyondbarista.rw', 'Instructor@2026')">
                        <i class="bi bi-mortarboard me-1"></i> Instructor Account
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-2" onclick="fillLogin('student@beyondbarista.rw', 'Student@2026')">
                        <i class="bi bi-book me-1"></i> Student Account
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </form>

    <!-- Security Notice -->
    <div class="mt-4 pt-4 border-top">
        <p class="text-muted small mb-1">
            <i class="bi bi-shield-check text-success me-1"></i>
            <strong>Your data is secure.</strong> We use industry-standard encryption.
        </p>
        <p class="text-muted small">
            <a href="<?= url('privacy') ?>" class="text-muted">Privacy Policy</a> •
            <a href="<?= url('terms') ?>" class="text-muted">Terms of Service</a>
        </p>
    </div>
</div>

<script>
function fillLogin(email, password) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = password;
    document.getElementById('loginForm').focus();
}
</script>
