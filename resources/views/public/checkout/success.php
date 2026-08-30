<?php
$title = 'Enrollment Confirmed — Order #' . e($order['order_number']) . ' — Beyond Barista Academy';
$courseSlug = $order['items'][0]['course_slug'] ?? 'courses';
?>

<section class="py-5 bg-light">
    <div class="container py-5 text-center" style="max-width: 650px;">
        <div class="card border-0 shadow-lg rounded-4 p-5 bg-white">
            <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3 mx-auto shadow-sm" style="width:72px;height:72px;">
                <i class="bi bi-check-lg fs-1"></i>
            </div>
            
            <h2 class="font-heading fw-bold text-primary-dark mb-1">Enrollment Confirmed!</h2>
            <p class="text-muted small mb-4">Thank you for your enrollment. Your order <strong>#<?= e($order['order_number']) ?></strong> has been processed successfully.</p>

            <!-- Order Details Card Mini -->
            <div class="p-3 bg-light rounded-4 text-start mb-4">
                <div class="d-flex justify-content-between py-1 border-bottom small">
                    <span class="text-muted">Purchased Course:</span>
                    <strong class="text-dark"><?= e($order['items'][0]['item_title'] ?? 'Specialty Coffee Course') ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom small">
                    <span class="text-muted">Total Paid:</span>
                    <strong class="text-success"><?= format_money($order['final_amount'], $order['currency']) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom small">
                    <span class="text-muted">Payment Status:</span>
                    <span class="badge bg-success text-capitalize"><?= e($order['payment_status']) ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Payment Channel:</span>
                    <span class="fw-bold text-uppercase"><?= e($order['payment_method']) ?></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-column gap-2 mb-4">
                <a href="<?= url('student/classroom/' . $courseSlug) ?>" class="btn btn-primary btn-lg fw-bold py-3 shadow">
                    <i class="bi bi-play-circle-fill me-1"></i> Go to Classroom & Start Learning
                </a>
                
                <div class="d-flex gap-2">
                    <?php if (!empty($order['invoices'][0])): ?>
                        <a href="<?= url('invoice/' . $order['invoices'][0]['invoice_number']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-50">
                            <i class="bi bi-receipt me-1"></i> View Official Invoice
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($order['receipts'][0])): ?>
                        <a href="<?= url('receipt/' . $order['receipts'][0]['receipt_number']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-50">
                            <i class="bi bi-file-earmark-check me-1"></i> View Payment Receipt
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <small class="text-muted">
                A confirmation has been sent to <strong><?= e($order['user']['email'] ?? '') ?></strong>.
            </small>
        </div>
    </div>
</section>
