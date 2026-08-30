<div class="bg-primary-dark text-white py-5" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-3">
        <h6 class="text-accent fw-bold text-uppercase tracking-wider">Hospitality & Coffee Culture</h6>
        <h1 class="font-heading text-white fw-bold display-5 mb-2">Beyond Barista Academy Blog</h1>
        <p class="text-light opacity-80 max-w-700">Barista guides, roasting science, cafe management tips, and hospitality industry news from Rwanda.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($posts as $p): ?>
            <div class="col-lg-6">
                <div class="card h-100 p-4 border-0 shadow-sm rounded-4 card-hover-elevate">
                    <span class="badge bg-warning text-dark align-self-start mb-2"><?= e($p['category_name']) ?></span>
                    <h4 class="font-heading mb-2">
                        <a href="<?= url('blog/' . e($p['slug'])) ?>" class="text-dark hover-accent">
                            <?= e($p['title']) ?>
                        </a>
                    </h4>
                    <p class="text-muted small mb-4 flex-grow-1"><?= e($p['excerpt']) ?></p>
                    <div class="d-flex align-items-center justify-content-between text-muted small pt-3 border-top mt-auto">
                        <span><i class="bi bi-person me-1"></i> <?= e($p['author_name']) ?></span>
                        <span><i class="bi bi-calendar3 me-1"></i> <?= date('M d, Y', strtotime($p['published_at'])) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
