<?php
$title = 'Payment Pending / Failed — Order #' . e($order['order_number']) . ' — Beyond Barista Academy';
$courseSlug = $order['items'][0]['course_slug'] ?? 'courses';
?>

<section class="py-5 bg-light">
    <div class="container py-5 text-center" style="max-width: 600px;">
        <div class="card border-0 shadow-lg rounded-4 p-5 bg-white">
            <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mb-3 mx-auto shadow-sm" style="width:72px;height:72px;">
                <i class="bi bi-exclamation-triangle-fill fs-1"></i>
            </div>
            
            <h2 class="font-heading fw-bold text-primary-dark mb-1">Payment Incomplete</h2>
            <p class="text-muted small mb-4">We haven't received confirmation for order <strong>#<?= e($order['order_number']) ?></strong> yet.</p>

            <div class="alert alert-warning border-0 rounded-3 text-start small mb-4">
                <i class="bi bi-info-circle-fill me-1"></i> If you paid via Mobile Money or Bank Transfer, confirmation may take 1–2 minutes. You can also retry your payment with an alternative method below.
            </div>

            <div class="d-flex flex-column gap-2">
                <a href="<?= url('checkout/' . $courseSlug) ?>" class="btn btn-primary btn-lg fw-bold py-3 shadow">
                    <i class="bi bi-arrow-repeat me-1"></i> Retry Checkout with Another Method
                </a>
                <a href="<?= url('contact') ?>" class="btn btn-outline-secondary btn-sm">Contact Academy Support</a>
            </div>
        </div>
    </div>
</section>
