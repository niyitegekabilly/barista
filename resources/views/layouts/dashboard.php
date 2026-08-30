<!DOCTYPE html>
<html lang="<?= e(session('locale', config('app.locale', 'en'))) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($title ?? 'Dashboard') ?> — Beyond Barista Academy</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php 
        $user = auth(); 
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
    ?>

    <!-- Top Dashboard Header -->
    <header class="navbar navbar-expand-lg navbar-custom sticky-top py-2 px-3 border-bottom">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3">
                <a class="navbar-brand navbar-brand-logo py-0" href="<?= url() ?>">
                    <div class="navbar-brand-icon" style="width:34px;height:34px;font-size:1.1rem;">
                        <i class="bi bi-cup-hot-fill"></i>
                    </div>
                    <span class="fs-6 fw-bold font-heading text-primary-dark">Beyond Barista</span>
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="<?= url('courses') ?>" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1">
                    <i class="bi bi-compass"></i> Course Catalog
                </a>

                <!-- Dark Mode Toggle -->
                <button class="btn btn-sm btn-outline-secondary rounded-circle" id="themeToggleBtn" style="width:36px;height:36px;">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2 py-1 px-2" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:28px;height:28px;font-size:0.8rem;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <span class="d-none d-md-inline fw-semibold"><?= e($user['name']) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="px-3 py-1">
                            <span class="badge bg-secondary"><?= strtoupper($user['role_slug']) ?></span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('student/profile') ?>"><i class="bi bi-person me-2"></i> Profile Settings</a></li>
                        <li><a class="dropdown-item" href="<?= url() ?>"><i class="bi bi-globe me-2"></i> Public Website</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="<?= url('logout') ?>" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="dashboard-sidebar">
            <div class="mb-4 px-2">
                <small class="text-uppercase fw-bold text-muted" style="font-size:0.75rem; letter-spacing:1px;">Navigation</small>
            </div>

            <nav class="nav flex-column">
                <?php if ($user['role_slug'] === 'super_admin' || $user['role_slug'] === 'admin'): ?>
                    <!-- Admin Navigation -->
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/dashboard') ? 'active' : '' ?>" href="<?= url('admin/dashboard') ?>">
                        <i class="bi bi-speedometer2 text-danger"></i> <span>Dashboard (KPIs)</span>
                    </a>
                    <a class="sidebar-nav-item <?= (str_contains($_SERVER['REQUEST_URI'], '/admin/users') || str_contains($_SERVER['REQUEST_URI'], '/admin/user')) ? 'active' : '' ?>" href="<?= url('admin/users') ?>">
                        <i class="bi bi-people-fill"></i> <span>Users & Directory</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/roles') ? 'active' : '' ?>" href="<?= url('admin/roles') ?>">
                        <i class="bi bi-shield-lock-fill"></i> <span>Roles & Permissions</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/cohorts') ? 'active' : '' ?>" href="<?= url('admin/cohorts') ?>">
                        <i class="bi bi-collection-fill"></i> <span>Cohorts & Batches</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/courses') ? 'active' : '' ?>" href="<?= url('admin/courses') ?>">
                        <i class="bi bi-journal-code"></i> <span>Courses & Approval</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/categories') ? 'active' : '' ?>" href="<?= url('admin/categories') ?>">
                        <i class="bi bi-tags-fill"></i> <span>Training Categories</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/orders') ? 'active' : '' ?>" href="<?= url('admin/orders') ?>">
                        <i class="bi bi-receipt"></i> <span>Finance & Orders</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/coupons') ? 'active' : '' ?>" href="<?= url('admin/coupons') ?>">
                        <i class="bi bi-ticket-perforated-fill"></i> <span>Coupons</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/blog') ? 'active' : '' ?>" href="<?= url('admin/blog') ?>">
                        <i class="bi bi-pencil-square"></i> <span>Blog CMS</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/events') ? 'active' : '' ?>" href="<?= url('admin/events') ?>">
                        <i class="bi bi-calendar-event-fill"></i> <span>Events & Workshops</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/jobs') ? 'active' : '' ?>" href="<?= url('admin/jobs') ?>">
                        <i class="bi bi-briefcase-fill"></i> <span>Job Board</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/settings') ? 'active' : '' ?>" href="<?= url('admin/settings') ?>">
                        <i class="bi bi-sliders"></i> <span>Academy Settings</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($_SERVER['REQUEST_URI'], '/admin/audit-logs') ? 'active' : '' ?>" href="<?= url('admin/audit-logs') ?>">
                        <i class="bi bi-shield-check"></i> <span>Security Audit Logs</span>
                    </a>
                <?php elseif ($user['role_slug'] === 'instructor'): ?>
                    <!-- Instructor Navigation -->
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/instructor/dashboard') ? 'active' : '' ?>" href="<?= url('instructor/dashboard') ?>">
                        <i class="bi bi-speedometer2 text-warning"></i> <span>Instructor Hub</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/instructor/courses') ? 'active' : '' ?>" href="<?= url('instructor/courses') ?>">
                        <i class="bi bi-mortarboard-fill"></i> <span>My Created Courses</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/instructor/courses/create') ? 'active' : '' ?>" href="<?= url('instructor/courses/create') ?>">
                        <i class="bi bi-plus-circle-fill text-success"></i> <span>Add New Course</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/instructor/quizzes') ? 'active' : '' ?>" href="<?= url('instructor/quizzes') ?>">
                        <i class="bi bi-patch-question-fill text-info"></i> <span>Quizzes & AI Generator</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/instructor/students') ? 'active' : '' ?>" href="<?= url('instructor/students') ?>">
                        <i class="bi bi-people"></i> <span>Student Progress</span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/instructor/reviews') ? 'active' : '' ?>" href="<?= url('instructor/reviews') ?>">
                        <i class="bi bi-star-fill text-warning"></i> <span>Student Reviews</span>
                    </a>
                <?php else: ?>
                    <!-- Student Navigation -->
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/student/dashboard') ? 'active' : '' ?>" href="<?= url('student/dashboard') ?>">
                        <i class="bi bi-grid-fill text-primary"></i> <span><?= __('app.dashboard') ?></span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/student/courses') ? 'active' : '' ?>" href="<?= url('student/courses') ?>">
                        <i class="bi bi-book-half"></i> <span><?= __('app.my_courses') ?></span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/student/certificates') ? 'active' : '' ?>" href="<?= url('student/certificates') ?>">
                        <i class="bi bi-award-fill text-success"></i> <span><?= __('app.certificates') ?></span>
                    </a>
                    <a class="sidebar-nav-item <?= str_contains($currentUri, '/student/wishlist') ? 'active' : '' ?>" href="<?= url('student/wishlist') ?>">
                        <i class="bi bi-heart-fill text-danger"></i> <span><?= __('app.wishlist') ?></span>
                    </a>
                <?php endif; ?>

                <hr class="my-3 text-muted">
                <a class="sidebar-nav-item <?= str_contains($currentUri, '/student/profile') ? 'active' : '' ?>" href="<?= url('student/profile') ?>">
                    <i class="bi bi-person-gear"></i> <span><?= __('app.profile') ?></span>
                </a>
                <a class="sidebar-nav-item text-danger" href="<?= url('logout') ?>" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                    <i class="bi bi-box-arrow-right text-danger"></i> <span>Logout</span>
                </a>
                <form id="sidebar-logout-form" action="<?= url('logout') ?>" method="POST" class="d-none">
                    <?= csrf_field() ?>
                </form>
            </nav>
        </aside>

        <!-- Main Dashboard Content Area -->
        <main class="dashboard-main">
            <!-- Flash Alerts -->
            <?php foreach (['success', 'warning', 'danger', 'info'] as $alertType): ?>
                <?php if ($msg = \App\Core\Session::getFlash($alertType)): ?>
                    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> <?= e($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?= $content ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.BBA_URL = "<?= app_url() ?>";
    </script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
