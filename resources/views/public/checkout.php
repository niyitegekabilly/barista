<div class="bg-primary-dark text-white py-4" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-2">
        <h2 class="font-heading text-white fw-bold mb-1">Secure Checkout</h2>
        <p class="text-light opacity-80 mb-0">Complete your enrollment with instant access to Beyond Barista Academy training.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-7">
            <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4 mb-4">
                <h4 class="font-heading mb-4">Payment Method</h4>

                <form action="<?= url('checkout/process') ?>" method="POST" id="checkoutForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="item_type" value="<?= e($itemType) ?>">
                    <input type="hidden" name="item_id" value="<?= e($item['id']) ?>">
                    <input type="hidden" name="coupon_code" id="hiddenCouponCode" value="<?= e($couponCode ?? '') ?>">

                    <!-- Payment Provider Radios -->
                    <div class="d-flex flex-column gap-3 mb-4">
                        <label class="p-3 border rounded-3 d-flex align-items-center justify-content-between cursor-pointer">
                            <div class="d-flex align-items-center gap-3">
                                <input class="form-check-input mt-0" type="radio" name="payment_method" value="momo" checked>
                                <div>
                                    <span class="fw-bold d-block text-dark">Mobile Money (MTN / Airtel Rwanda)</span>
                                    <small class="text-muted">Instant prompt on your Rwanda phone</small>
                                </div>
                            </div>
                            <span class="badge bg-warning text-dark fw-bold">Popular</span>
                        </label>

                        <label class="p-3 border rounded-3 d-flex align-items-center justify-content-between cursor-pointer">
                            <div class="d-flex align-items-center gap-3">
                                <input class="form-check-input mt-0" type="radio" name="payment_method" value="stripe">
                                <div>
                                    <span class="fw-bold d-block text-dark">Credit / Debit Card (Visa, Mastercard)</span>
                                    <small class="text-muted">Secure international payment gateway</small>
                                </div>
                            </div>
                            <i class="bi bi-credit-card-2-front fs-4 text-muted"></i>
                        </label>

                        <label class="p-3 border rounded-3 d-flex align-items-center justify-content-between cursor-pointer bg-light">
                            <div class="d-flex align-items-center gap-3">
                                <input class="form-check-input mt-0" type="radio" name="payment_method" value="sandbox">
                                <div>
                                    <span class="fw-bold d-block text-dark">Instant Demo Sandbox Pay</span>
                                    <small class="text-muted">Simulate successful transaction immediately</small>
                                </div>
                            </div>
                            <span class="badge bg-success">Test Mode</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-accent btn-lg w-100 fw-bold py-3">
                        <i class="bi bi-lock-fill me-1"></i> Complete Payment (<?= format_rwf($finalAmount) ?>)
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <!-- Order Summary -->
            <div class="card p-4 border-0 shadow-sm rounded-4 mb-4">
                <h5 class="font-heading mb-3">Order Summary</h5>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <span class="fw-bold text-dark d-block"><?= e($itemTitle) ?></span>
                        <small class="text-muted"><?= ucfirst($itemType) ?> Access</small>
                    </div>
                    <span class="fw-bold text-dark"><?= format_rwf($originalPrice) ?></span>
                </div>

                <!-- Coupon Form -->
                <div class="py-3 border-bottom">
                    <label class="form-label small fw-bold text-muted">Have a Coupon Code?</label>
                    <form action="<?= url('checkout') ?>" method="GET" class="d-flex gap-2">
                        <input type="hidden" name="type" value="<?= e($itemType) ?>">
                        <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                        <input type="text" name="coupon" class="form-control form-control-sm" placeholder="e.g. BARISTA2026" value="<?= e($couponCode ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
                    </form>
                    <?php if ($discountAmount > 0): ?>
                        <div class="text-success small mt-1">
                            <i class="bi bi-tag-fill me-1"></i> Coupon applied! -<?= format_rwf($discountAmount) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted">Subtotal</span>
                    <span class="text-muted"><?= format_rwf($originalPrice) ?></span>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 text-success">
                        <span>Discount</span>
                        <span>-<?= format_rwf($discountAmount) ?></span>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center py-3 border-top mt-2">
                    <span class="fs-5 fw-bold text-dark">Total Amount</span>
                    <span class="fs-4 fw-bold text-primary"><?= format_rwf($finalAmount) ?></span>
                </div>
            </div>

            <div class="p-3 bg-light rounded-3 text-muted small">
                <i class="bi bi-shield-lock-fill text-success me-1"></i> 256-bit encrypted SSL checkout. Instant course access upon payment completion.
            </div>
        </div>
    </div>
</div>
