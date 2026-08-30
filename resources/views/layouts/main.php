<!DOCTYPE html>
<html lang="<?= e(session('locale', config('app.locale', 'en'))) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($title ?? config('app.name')) ?> — Beyond Barista Academy Rwanda</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Premium Coffee Design System -->
    <link rel="stylesheet" href="<?= asset('css/premium-design.css') ?>">
    <!-- Homepage Premium Styling -->
    <link rel="stylesheet" href="<?= asset('css/homepage.css') ?>">
    <!-- Custom Application CSS (overrides) -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Top Bar for Announcements & Language -->
    <div class="topbar-luxury py-2 px-3 d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="badge-location d-inline-flex align-items-center">
                    <span class="status-dot"></span> RWANDA
                </span>
                <span class="topbar-link small"><i class="bi bi-geo-alt-fill text-warning me-1"></i> KG 11 Ave, Kigali Innovation Hub</span>
                <span class="text-white-50 opacity-25">|</span>
                <a href="tel:+250788000111" class="topbar-link small"><i class="bi bi-telephone-fill text-warning me-1"></i> +250 788 000 111</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= url('certificate/verify') ?>" class="topbar-link small d-flex align-items-center">
                    <i class="bi bi-patch-check-fill text-warning me-1 fs-6"></i> <?= __('app.verify_certificate') ?>
                </a>
                <span class="text-white-50 opacity-25">|</span>
                <div class="dropdown">
                    <button class="btn btn-sm text-white-50 dropdown-toggle p-0 small fw-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-globe2 me-1 text-warning"></i> <?= strtoupper(session('locale', 'en')) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-1" style="border-radius: 10px; font-size: 0.85rem;">
                        <li><a class="dropdown-item py-2" href="?lang=en">🇬🇧 English</a></li>
                        <li><a class="dropdown-item py-2" href="?lang=fr">🇫🇷 Français</a></li>
                        <li><a class="dropdown-item py-2" href="?lang=rw">🇷🇼 Ikinyarwanda</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="brand-emblem-wrapper" href="<?= url() ?>">
                <div class="brand-emblem-box">
                    <img src="<?= asset('img/logo.png') ?>" alt="Beyond Barista Academy" style="height: 44px; width: auto; object-fit: contain;">
                </div>
                <div>
                    <span class="brand-title">Beyond Barista</span>
                    <small class="brand-subtitle">ACADEMY RWANDA</small>
                </div>
            </a>

            <button class="navbar-toggler border-0 shadow-none p-2" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-label="Toggle navigation">
                <i class="bi bi-list fs-1 text-primary"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link <?= nav_active('') ?>" href="<?= url() ?>"><?= __('app.home') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= nav_active('courses') ?>" href="<?= url('courses') ?>">
                            <?= __('app.courses') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= nav_active('pricing') ?>" href="<?= url('pricing') ?>">
                            <?= __('app.pricing') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= nav_active('events') ?>" href="<?= url('events') ?>"><?= __('app.events') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= nav_active('jobs') ?>" href="<?= url('jobs') ?>">
                            <?= __('app.jobs') ?>
                            <span class="badge-nav-pill badge-nav-hot ms-1">HOT</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= nav_active('blog') ?>" href="<?= url('blog') ?>"><?= __('app.blog') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= nav_active('about') ?>" href="<?= url('about') ?>"><?= __('app.about') ?></a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2 pt-2 pt-lg-0">
                    <!-- Theme switch button -->
                    <button class="btn-theme-luxury me-1" id="themeToggleBtn" title="Toggle Light / Dark Mode" type="button">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>

                    <?php if (auth_check()): ?>
                        <?php $user = auth(); ?>
                        <div class="dropdown">
                            <button class="btn-nav-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="user-avatar-badge"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                                <span class="fw-semibold small text-dark d-none d-xl-inline"><?= e($user['name']) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 py-2" style="min-width: 230px; border-radius: 14px;">
                                <li class="px-3 py-2 border-bottom mb-1">
                                    <p class="mb-0 fw-bold text-truncate"><?= e($user['name']) ?></p>
                                    <small class="text-muted text-truncate d-block"><?= e($user['email']) ?></small>
                                </li>
                                <?php if ($user['role_slug'] === 'super_admin' || $user['role_slug'] === 'admin'): ?>
                                    <li><a class="dropdown-item py-2" href="<?= url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2 text-danger"></i> <?= __('app.admin_panel') ?></a></li>
                                <?php elseif ($user['role_slug'] === 'instructor'): ?>
                                    <li><a class="dropdown-item py-2" href="<?= url('instructor/dashboard') ?>"><i class="bi bi-mortarboard-fill me-2 text-warning"></i> <?= __('app.instructor_panel') ?></a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item py-2" href="<?= url('student/dashboard') ?>"><i class="bi bi-grid me-2 text-primary"></i> <?= __('app.dashboard') ?></a></li>
                                    <li><a class="dropdown-item py-2" href="<?= url('student/courses') ?>"><i class="bi bi-book me-2 text-primary"></i> <?= __('app.my_courses') ?></a></li>
                                    <li><a class="dropdown-item py-2" href="<?= url('student/certificates') ?>"><i class="bi bi-award me-2 text-success"></i> <?= __('app.certificates') ?></a></li>
                                    <li><a class="dropdown-item py-2" href="<?= url('student/wishlist') ?>"><i class="bi bi-heart me-2 text-danger"></i> <?= __('app.wishlist') ?></a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item py-2" href="<?= url('student/profile') ?>"><i class="bi bi-person-gear me-2"></i> <?= __('app.profile') ?></a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="<?= url('logout') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item py-2 text-danger fw-medium"><i class="bi bi-box-arrow-right me-2"></i> <?= __('app.logout') ?></button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('login') ?>" class="btn-nav-signin">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span><?= __('app.login') ?></span>
                        </a>
                        <a href="<?= url('register') ?>" class="btn-nav-register">
                            <i class="bi bi-cup-hot-fill"></i>
                            <span><?= __('app.register') ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Notifications -->
    <div class="container mt-3">
        <?php foreach (['success', 'warning', 'danger', 'info'] as $alertType): ?>
            <?php if ($msg = \App\Core\Session::getFlash($alertType)): ?>
                <div class="alert alert-<?= $alertType ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Main Dynamic Content -->
    <main>
        <?= $content ?>
    </main>

    <!-- Premium Footer -->
    <footer class="footer-custom mt-5">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="<?= asset('img/logo.png') ?>" alt="Beyond Barista Academy" style="height: 40px; width: auto; object-fit: contain; filter: brightness(1.2);">
                        <h5 class="text-white mb-0 font-heading fw-bold">Beyond Barista Academy</h5>
                    </div>
                    <p class="text-muted pe-lg-4">
                        Rwanda's premier hospitality and specialty coffee education institution. Equipping modern baristas, roasters, managers, and service professionals with international standard credentials.
                    </p>
                    <div class="d-flex gap-3 fs-5 mt-3">
                        <a href="#" class="text-muted hover-accent"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-muted hover-accent"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-muted hover-accent"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-muted hover-accent"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white font-heading mb-3">Training Areas</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2" style="font-size:0.9rem;">
                        <li><a href="<?= url('courses?category=barista-skills') ?>">Professional Barista</a></li>
                        <li><a href="<?= url('courses?category=roasting-cupping') ?>">Coffee Roasting</a></li>
                        <li><a href="<?= url('courses?category=mixology-beverage') ?>">Beverage Mixology</a></li>
                        <li><a href="<?= url('courses?category=hotel-front-office') ?>">Hotel Operations</a></li>
                        <li><a href="<?= url('courses?category=food-safety-haccp') ?>">Food Safety & HACCP</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white font-heading mb-3">Explore</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2" style="font-size:0.9rem;">
                        <li><a href="<?= url('pricing') ?>">Membership Plans</a></li>
                        <li><a href="<?= url('events') ?>">Upcoming Workshops</a></li>
                        <li><a href="<?= url('jobs') ?>">Hospitality Job Board</a></li>
                        <li><a href="<?= url('blog') ?>">Coffee Culture Blog</a></li>
                        <li><a href="<?= url('certificate/verify') ?>">Verify Certificate</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white font-heading mb-3">Kigali Training Campus</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 text-muted" style="font-size:0.9rem;">
                        <li><i class="bi bi-geo-alt-fill text-accent me-2"></i> KG 11 Ave, Kigali Innovation Hub, Rwanda</li>
                        <li><i class="bi bi-telephone-fill text-accent me-2"></i> +250 788 000 111 / +250 788 123 456</li>
                        <li><i class="bi bi-envelope-fill text-accent me-2"></i> info@beyondbarista.rw</li>
                        <li><i class="bi bi-clock-fill text-accent me-2"></i> Mon – Sat: 8:00 AM – 6:00 PM</li>
                    </ul>
                </div>
            </div>

            <div class="border-top border-secondary pt-4 d-flex flex-column flex-md-row justify-content-between align-items-center text-muted" style="font-size:0.85rem;">
                <p class="mb-2 mb-md-0">&copy; <?= date('Y') ?> Beyond Barista Academy Rwanda. All rights reserved.</p>
                <div class="d-flex gap-3">
                    <a href="<?= url('about') ?>">About Us</a>
                    <a href="<?= url('contact') ?>">Contact</a>
                    <a href="<?= url('privacy') ?>">Privacy Policy</a>
                    <a href="<?= url('terms') ?>">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.BBA_URL = "<?= app_url() ?>";
    </script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
