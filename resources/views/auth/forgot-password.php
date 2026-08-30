<div class="card border-0 shadow-xl rounded-4 p-4 p-lg-5">
    <div class="text-center mb-4">
        <h3 class="font-heading fw-bold mb-1">Reset Password</h3>
        <p class="text-muted small">Enter your account email to receive reset instructions</p>
    </div>

    <form action="<?= url('forgot-password') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-4">
            <label class="form-label small fw-bold">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3">
            Send Reset Link
        </button>

        <div class="text-center text-muted small">
            Remembered your password? <a href="<?= url('login') ?>" class="fw-bold text-accent">Sign in</a>
        </div>
    </form>
</div>
