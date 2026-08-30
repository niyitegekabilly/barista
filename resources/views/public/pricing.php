<div class="bg-primary-dark text-white py-5 text-center" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-4">
        <h6 class="text-accent fw-bold text-uppercase tracking-wider">Flexible Learning Options</h6>
        <h1 class="font-heading text-white fw-bold display-5 mb-3">Membership Plans for Every Goal</h1>
        <p class="fs-5 text-light opacity-80 max-w-700 mx-auto">
            Choose the membership that fits your career aspirations in Rwanda's rapidly growing specialty coffee and hospitality ecosystem.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4 justify-content-center">
        <?php foreach ($plans as $plan): ?>
            <?php $isPopular = ($plan['slug'] === 'pro-monthly'); ?>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 p-4 p-lg-5 border-0 shadow-lg rounded-4 position-relative <?= $isPopular ? 'border border-2 border-warning' : '' ?>">
                    <?php if ($isPopular): ?>
                        <span class="position-absolute top-0 start-50 translate-middle badge bg-warning text-dark px-3 py-2 fw-bold rounded-pill shadow-sm">
                            MOST POPULAR IN RWANDA
                        </span>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h4 class="font-heading mb-2 text-dark"><?= e($plan['name']) ?></h4>
                        <p class="text-muted small mb-4"><?= e($plan['description']) ?></p>
                        
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="display-5 fw-bold text-dark"><?= format_price($plan['price']) ?></span>
                            <span class="text-muted">/ <?= e($plan['billing_interval']) ?></span>
                        </div>
                    </div>

                    <?php $features = json_decode($plan['features'] ?? '[]', true) ?: []; ?>
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-5 flex-grow-1">
                        <?php foreach ($features as $f): ?>
                            <li class="d-flex align-items-start gap-2 small">
                                <i class="bi bi-check-circle-fill text-success fs-6 mt-1 flex-shrink-0"></i>
                                <span><?= e($f) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="mt-auto">
                        <?php if ((float)$plan['price'] === 0.0): ?>
                            <a href="<?= url('register') ?>" class="btn btn-outline-primary btn-lg w-100 fw-bold">Join Free Community</a>
                        <?php else: ?>
                            <a href="<?= url('checkout?type=membership&id=' . e($plan['id'])) ?>" class="btn <?= $isPopular ? 'btn-accent' : 'btn-primary' ?> btn-lg w-100 fw-bold">
                                Subscribe with Momo / Card
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
