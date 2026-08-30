<!DOCTYPE html>
<html lang="<?= e(session('locale', config('app.locale', 'en'))) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($title ?? 'Authentication') ?> — Beyond Barista Academy</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-5">
    <div class="container">
        <div class="text-center mb-4">
            <a href="<?= url() ?>" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                <img src="<?= asset('img/logo.png') ?>" alt="Beyond Barista Academy" style="height: 52px; width: auto; object-fit: contain;">
                <div class="text-start">
                    <span class="d-block lh-1 text-primary-dark fw-bold fs-4">Beyond Barista</span>
                    <small class="text-muted fw-semibold" style="font-size: 0.7rem; letter-spacing: 1px;">ACADEMY RWANDA</small>
                </div>
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <!-- Flash Alerts -->
                <?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger', 'info' => 'info'] as $flashKey => $alertType): ?>
                    <?php if ($msg = \App\Core\Session::getFlash($flashKey)): ?>
                        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-<?= $alertType === 'danger' ? 'exclamation-circle-fill' : 'info-circle-fill' ?> me-2"></i>
                            <span><?= $msg ?></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?= $content ?>

                <div class="text-center mt-4 text-muted" style="font-size:0.85rem;">
                    <a href="<?= url() ?>" class="text-muted"><i class="bi bi-arrow-left me-1"></i> Return to Homepage</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.BBA_URL = "<?= app_url() ?>";
    </script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
