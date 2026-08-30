<div class="bg-primary-dark text-white py-5" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-3">
        <h6 class="text-accent fw-bold text-uppercase tracking-wider">Career Hub Rwanda</h6>
        <h1 class="font-heading text-white fw-bold display-5 mb-2">Hospitality Jobs & Internships</h1>
        <p class="text-light opacity-80 max-w-700">Explore openings for certified baristas, roasters, F&B supervisors, and hotel personnel across Kigali.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($jobs as $job): ?>
            <div class="col-lg-6">
                <div class="card h-100 p-4 border-0 shadow-sm rounded-4 card-hover-elevate">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary"><?= strtoupper(str_replace('_', ' ', $job['job_type'])) ?></span>
                        <span class="text-muted small"><i class="bi bi-clock me-1"></i> Apply by <?= date('M d, Y', strtotime($job['deadline'])) ?></span>
                    </div>

                    <h4 class="font-heading mb-1"><?= e($job['title']) ?></h4>
                    <h6 class="text-accent mb-3"><?= e($job['company']) ?> • <?= e($job['location']) ?></h6>

                    <p class="text-muted small mb-4 flex-grow-1"><?= e($job['description']) ?></p>

                    <div class="p-3 bg-light rounded-3 small mb-4">
                        <strong>Requirements:</strong><br>
                        <?= e($job['requirements']) ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                        <span class="fw-bold text-success"><?= e($job['salary_range'] ?? 'Competitive') ?></span>
                        <a href="mailto:careers@beyondbarista.rw?subject=Application for <?= urlencode($job['title']) ?>" class="btn btn-sm btn-primary">
                            Apply for Role <i class="bi bi-send ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
