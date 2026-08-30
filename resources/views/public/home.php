<!-- Hero Section - Premium Coffee Design -->
<section class="hero-section position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(30, 19, 1, 0.92), rgba(76, 49, 3, 0.95)), url('<?= asset('img/herosection.jpg') ?>') center/cover no-repeat;">
    <div class="container position-relative py-4">
        <div class="row align-items-center g-5">
            <!-- Left Content -->
            <div class="col-lg-7 text-white">
                <div class="hero-badge shadow-sm mb-3">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span>Rwanda’s Premier Barista & Hospitality Academy</span>
                </div>

                <h1 class="hero-title fw-bold display-4 mb-3">
                    Master the Art of <span class="highlight text-accent-gold">Specialty Coffee</span> Excellence
                </h1>

                <p class="hero-description fs-5 opacity-90 mb-4 lh-lg">
                    Learn from SCA-certified roasters and award-winning baristas. Earn internationally recognized credentials and unlock thriving careers in Kigali and globally.
                </p>

                <div class="hero-actions d-flex flex-wrap gap-3">
                    <a href="<?= url('courses') ?>" class="btn btn-primary btn-lg px-4 shadow-lg fw-bold">
                        <i class="bi bi-play-circle-fill me-1"></i> Explore Courses
                    </a>
                    <a href="<?= url('register') ?>" class="btn btn-accent btn-lg px-4 shadow-md fw-bold">
                        Get Started Free
                    </a>
                </div>

                <!-- Trust Stats -->
                <div class="row g-4 mt-4 pt-4 border-top border-secondary border-opacity-25">
                    <div class="col-4">
                        <h3 class="fw-bold mb-1 text-accent-gold display-6">1,500+</h3>
                        <small class="opacity-75 text-uppercase tracking-wider">Graduates Certified</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold mb-1 text-accent-gold display-6">50+</h3>
                        <small class="opacity-75 text-uppercase tracking-wider">Master Modules</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold mb-1 text-accent-gold display-6">94%</h3>
                        <small class="opacity-75 text-uppercase tracking-wider">Employment Rate</small>
                    </div>
                </div>
            </div>

            <!-- Right Featured Card -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-2xl rounded-4 overflow-hidden text-white card-hover-elevate" style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px);">
                    <div style="height: 180px; overflow: hidden; position: relative;">
                        <img src="<?= asset('img/best.jpg') ?>" alt="Featured Masterclass" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15,23,42,0.9), transparent);"></div>
                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3 px-3 py-2 fw-bold shadow-sm">
                            <i class="bi bi-fire me-1"></i> FEATURED MASTERCLASS
                        </span>
                    </div>

                    <div class="p-4">
                        <h4 class="font-heading fw-bold mb-2 text-white">
                            Foundation Barista Skills
                        </h4>

                        <p class="text-light opacity-80 small mb-3 lh-relaxed">
                            Master espresso extraction, milk steaming, and latte art from SCA-certified trainers in our Kigali lab.
                        </p>

                        <div class="progress mb-3" style="height: 6px; background: rgba(255, 255, 255, 0.15);">
                            <div class="progress-bar bg-accent-gold" role="progressbar" style="width: 100%;"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-white-50 d-block">Tuition</small>
                                <span class="fs-4 fw-bold text-success">FREE</span>
                            </div>
                            <a href="<?= url('courses/foundation-barista-skills-espresso-mechanics') ?>" class="btn btn-accent px-4 fw-bold">
                                Start Learning
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Training Categories -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <h6 class="text-accent fw-bold text-uppercase tracking-wider">Hospitality Disciplines</h6>
            <h2 class="font-heading fw-bold display-6">Explore Our Professional Training Disciplines</h2>
            <p class="text-muted">From barista craft and sensory cupping to restaurant operations and food safety, we offer complete career pathways.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= url('courses?category=' . e($cat['slug'])) ?>" class="card h-100 p-4 card-hover-elevate text-decoration-none border-0 shadow-sm rounded-4">
                        <div class="stat-icon accent mb-3" style="width:56px;height:56px;font-size:1.6rem;">
                            <i class="bi <?= e($cat['icon']) ?>"></i>
                        </div>
                        <h5 class="font-heading mb-2 text-dark"><?= e($cat['name']) ?></h5>
                        <p class="text-muted small mb-3"><?= e($cat['description']) ?></p>
                        <div class="mt-auto d-flex align-items-center justify-content-between text-primary fw-semibold small">
                            <span><?= e($cat['courses_count']) ?> Courses Available</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section class="py-5" style="background-color: var(--color-bg);">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <h6 class="text-accent fw-bold text-uppercase tracking-wider">Top Rated Programs</h6>
                <h2 class="font-heading fw-bold mb-0">Featured & Popular Courses</h2>
            </div>
            <a href="<?= url('courses') ?>" class="btn btn-outline-primary mt-3 mt-md-0 fw-semibold">View Full Catalog (<?= count($featuredCourses) ?>+) <i class="bi bi-arrow-right ms-1"></i></a>
        </div>

        <?php
        $fallbackImages = [
            asset('img/barista.jpeg'),
            asset('img/cappuccino.jpg'),
            asset('img/coffee-cups.jpg'),
            asset('img/coffeshop.jpg'),
            asset('img/class.png'),
            asset('img/best.jpg')
        ];
        ?>

        <div class="row g-4">
            <?php foreach ($featuredCourses as $idx => $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card course-card card-hover-elevate border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="course-card-img-wrapper" style="height: 200px; position: relative; overflow: hidden;">
                            <img src="<?= e(course_thumbnail($course['thumbnail'] ?? '', $idx)) ?>" 
                                 alt="<?= e($course['title']) ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;">

                            <div class="badge-floating position-absolute top-0 start-0 m-3">
                                <?php if ($course['is_free']): ?>
                                    <span class="badge bg-success shadow-sm px-3 py-2 fw-bold">FREE</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark shadow-sm px-3 py-2 fw-bold">PREMIUM</span>
                                <?php endif; ?>
                            </div>

                            <div class="badge-level position-absolute bottom-0 start-0 m-3 badge bg-dark bg-opacity-75 text-white px-2 py-1 small">
                                <i class="bi bi-bar-chart-fill me-1"></i> <?= ucfirst(str_replace('_', ' ', $course['level'])) ?>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <small class="text-accent fw-bold text-uppercase mb-1" style="font-size:0.75rem;">
                                <?= e($course['category_name']) ?>
                            </small>
                            <h5 class="font-heading mb-2">
                                <a href="<?= url('course/' . e($course['slug'])) ?>" class="text-dark text-decoration-none hover-accent">
                                    <?= e($course['title']) ?>
                                </a>
                            </h5>
                            <p class="text-muted small mb-4 flex-grow-1">
                                <?= e(substr($course['short_description'], 0, 110)) ?>...
                            </p>

                            <div class="d-flex align-items-center justify-content-between text-muted small py-2 border-top border-bottom mb-3">
                                <span><i class="bi bi-clock me-1"></i> <?= e($course['duration_hours']) ?> Hours</span>
                                <span><i class="bi bi-people me-1"></i> <?= e($course['students_count']) ?> Students</span>
                                <span class="text-warning"><i class="bi bi-star-fill"></i> <?= number_format($course['rating_avg'], 1) ?></span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-auto">
                                <div>
                                    <?php if ($course['is_free']): ?>
                                        <span class="fs-5 fw-bold text-success">FREE</span>
                                    <?php else: ?>
                                        <span class="fs-5 fw-bold text-dark"><?= format_rwf($course['discount_price'] ?: $course['price']) ?></span>
                                        <?php if ($course['discount_price']): ?>
                                            <small class="text-muted text-decoration-line-through d-block" style="font-size:0.75rem;"><?= format_rwf($course['price']) ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= url('course/' . e($course['slug'])) ?>" class="btn btn-sm btn-primary px-3 fw-bold">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Practical Classroom Showcase -->
<section class="py-5 bg-dark text-white position-relative" style="background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.95)), url('<?= asset('img/coffeshop.jpg') ?>') center/cover fixed;">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h6 class="text-accent-gold fw-bold text-uppercase tracking-wider">Hands-On Practical Lab</h6>
                <h2 class="font-heading fw-bold text-white display-6 mb-4">State-of-the-Art Training Facilities in Kigali</h2>
                <p class="text-light opacity-90 lh-lg mb-4">
                    Our Kigali Innovation Hub campus features professional commercial espresso machines, specialty coffee grinders, cupping labs, and simulated hotel front office suites.
                </p>
                <div class="d-flex flex-wrap gap-4">
                    <div>
                        <h4 class="text-accent-gold fw-bold mb-0">Commercial Lab</h4>
                        <small class="text-white-50">La Marzocco & Victoria Arduino</small>
                    </div>
                    <div>
                        <h4 class="text-accent-gold fw-bold mb-0">1:1 Mentorship</h4>
                        <small class="text-white-50">SCA Certified Master Trainers</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="rounded-4 overflow-hidden shadow-2xl border border-secondary border-opacity-50">
                    <img src="<?= asset('img/class.png') ?>" alt="Beyond Barista Classroom" class="img-fluid w-100" style="object-fit: cover; max-height: 380px;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Academy Why Choose Us & Digital Certificate -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="p-4 p-lg-5 rounded-4 shadow-xl overflow-hidden position-relative text-white" style="background: linear-gradient(135deg, #1E293B, #0F172A);">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="certificate-seal" style="width:60px;height:60px;font-size:1.6rem;">
                            <i class="bi bi-patch-check-fill text-warning"></i>
                        </div>
                        <div>
                            <h4 class="font-heading text-white mb-0">Verified Digital Credentials</h4>
                            <small class="text-white-50">Tamper-proof online verification</small>
                        </div>
                    </div>
                    <p class="text-light opacity-80 mb-4">
                        Every course completed with Beyond Barista Academy includes a certified digital diploma equipped with a permanent QR code. Hospitality employers in Kigali and internationally can authenticate your transcript with a single scan.
                    </p>
                    
                    <div class="mb-4 rounded-3 overflow-hidden border border-secondary border-opacity-25 shadow-sm" style="max-height: 180px;">
                        <img src="<?= asset('img/cert3.jpg') ?>" alt="Sample Verified Certificate" style="width:100%; height:180px; object-fit:cover;">
                    </div>

                    <a href="<?= url('certificate/verify') ?>" class="btn btn-accent px-4 fw-bold">
                        <i class="bi bi-shield-check me-1"></i> Try Certificate Verification
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <h6 class="text-accent fw-bold text-uppercase tracking-wider">The Beyond Barista Advantage</h6>
                <h2 class="font-heading fw-bold mb-4">Why Professionals Choose Beyond Barista Academy</h2>

                <div class="d-flex gap-3 mb-4">
                    <div class="stat-icon primary flex-shrink-0">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div>
                        <h5 class="font-heading mb-1">Industry-Aligned Rwandan Curriculum</h5>
                        <p class="text-muted small mb-0">Structured in direct collaboration with leading hotels, specialty coffee exporters, and cafe owners across East Africa.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-4">
                    <div class="stat-icon success flex-shrink-0">
                        <i class="bi bi-laptop-fill"></i>
                    </div>
                    <div>
                        <h5 class="font-heading mb-1">Learn Anytime on Mobile or Desktop</h5>
                        <p class="text-muted small mb-0">High-definition video lessons, downloadable dial-in sheets, and interactive quizzes accessible 24/7 on any device.</p>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <div class="stat-icon warning flex-shrink-0">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div>
                        <h5 class="font-heading mb-1">Direct Hospitality Job Placement</h5>
                        <p class="text-muted small mb-0">Graduates gain priority access to our exclusive hospitality job board connecting certified talent with hiring employers in Kigali.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5" style="background-color: var(--color-bg);">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <h6 class="text-accent fw-bold text-uppercase tracking-wider">Success Stories</h6>
            <h2 class="font-heading fw-bold display-6">Loved by Students & Employers Across Rwanda</h2>
        </div>

        <div class="row g-4">
            <?php foreach ($testimonials as $t): ?>
                <div class="col-md-6">
                    <div class="card h-100 p-4 border-0 shadow-sm rounded-4">
                        <div class="d-flex align-items-center text-warning mb-3">
                            <?php for ($i = 0; $i < $t['rating']; $i++): ?>
                                <i class="bi bi-star-fill me-1"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-muted fs-6 mb-4 fst-italic">
                            "<?= e($t['content']) ?>"
                        </p>
                        <div class="d-flex align-items-center gap-3 mt-auto">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:44px;height:44px;">
                                <?= strtoupper(substr($t['author_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h6 class="font-heading mb-0 text-dark"><?= e($t['author_name']) ?></h6>
                                <small class="text-muted"><?= e($t['author_title']) ?><?= $t['author_company'] ? ' — ' . e($t['author_company']) : '' ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Final Call to Action -->
<section class="py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(76, 49, 3, 0.95), rgba(50, 32, 2, 0.98)), url('<?= asset('img/coffee-cups.jpg') ?>') center/cover no-repeat; color:white;">
    <div class="container py-5 text-center position-relative">
        <h2 class="font-heading text-white fw-bold mb-3 display-6">Ready to Advance Your Hospitality Career?</h2>
        <p class="fs-5 text-light opacity-90 max-w-700 mx-auto mb-4">
            Join hundreds of baristas, roasters, and hospitality leaders elevating standards across Rwanda.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= url('register') ?>" class="btn btn-accent btn-lg px-4 fw-bold shadow-lg"><?= __('app.get_started') ?></a>
            <a href="<?= url('courses') ?>" class="btn btn-outline-light btn-lg px-4 shadow-sm"><?= __('app.explore_courses') ?></a>
        </div>
    </div>
</section>
