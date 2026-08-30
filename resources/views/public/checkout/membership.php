<?php $pageTitle = 'Subscribe to ' . e($plan['name']); ?>

<div class="bg-primary-dark text-white py-5">
    <div class="container py-3">
        <h6 class="text-accent fw-bold text-uppercase tracking-wider">Beyond Barista Academy Subscriptions</h6>
        <h1 class="font-heading fw-bold display-5 mb-2">Subscribe to <?= e($plan['name']) ?></h1>
        <p class="text-light opacity-80 mb-0">Complete your membership checkout to unlock courses, workshops, and verified certifications.</p>
    </div>
</div>

<div class="container py-5">
    <form action="<?= url('checkout/membership/process') ?>" method="POST" id="membershipCheckoutForm">
        <?= csrf_field() ?>
        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
        <input type="hidden" name="coupon_code" id="appliedCouponCode" value="">

        <div class="row g-4 justify-content-center">
            
            <!-- Left 7 Cols: Plan Details & Student Information -->
            <div class="col-lg-7">
                
                <!-- Plan Summary Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary border text-uppercase mb-1">Tier <?= $plan['tier_level'] ?> Package</span>
                            <h3 class="font-heading fw-bold mb-1 text-dark"><?= e($plan['name']) ?></h3>
                            <p class="text-muted small mb-0"><?= e($plan['description']) ?></p>
                        </div>
                        <div class="text-end">
                            <h3 class="fw-bold text-success mb-0"><?= format_rwf($plan['price']) ?></h3>
                            <small class="text-muted">/ <?= e($plan['billing_interval']) ?></small>
                        </div>
                    </div>

                    <?php $features = json_decode($plan['features'] ?? '[]', true) ?: []; ?>
                    <?php if (!empty($features)): ?>
                        <div class="p-3 bg-light rounded-4">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-stars text-warning me-1"></i> Included Plan Benefits:</h6>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-0 small">
                                <?php foreach ($features as $f): ?>
                                    <li class="d-flex align-items-start gap-2 text-dark">
                                        <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                                        <span><?= e($f) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Student Billing Details Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-surface">
                    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-person-fill text-primary me-2"></i> Subscriber Billing Information</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="billing_name" class="form-control" value="<?= e($user['name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="billing_email" class="form-control" value="<?= e($user['email']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Phone (MoMo)</label>
                            <input type="tel" name="billing_phone" class="form-control" placeholder="e.g. 0788 123 456" value="<?= e($user['phone'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">City / Location</label>
                            <input type="text" name="billing_city" class="form-control" value="Kigali, Rwanda">
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selector Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
                    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-credit-card-2-front-fill text-warning me-2"></i> Payment Method</h5>

                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($gateways as $gw): ?>
                            <label class="card border p-3 rounded-3 cursor-pointer d-flex align-items-center justify-content-between hover-shadow">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0" type="radio" name="payment_method" value="<?= e($gw['identifier']) ?>" <?= $gw['identifier'] === 'momo' ? 'checked' : '' ?>>
                                    <div>
                                        <strong class="d-block text-dark"><?= e($gw['name']) ?></strong>
                                        <small class="text-muted"><?= e($gw['description']) ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-muted border text-uppercase" style="font-size:0.7rem;"><?= e($gw['identifier']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Right 5 Cols: Pricing Breakdown & Coupon Card -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface sticky-top" style="top: 100px;">
                    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-bag-check-fill text-success me-2"></i> Order Summary</h5>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subscription Subtotal:</span>
                            <span class="fw-bold text-dark" id="dispSubtotal"><?= format_rwf($plan['price']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-success d-none mb-1" id="discountRow">
                            <span>Promotional Discount:</span>
                            <strong id="dispDiscount">-0 RWF</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Tax / VAT:</span>
                            <span class="text-muted">0 RWF</span>
                        </div>
                    </div>

                    <!-- Promo Code Input -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold"><i class="bi bi-tag-fill text-warning me-1"></i> Have a Promo Code?</label>
                        <div class="input-group">
                            <input type="text" id="couponCodeInput" class="form-control font-monospace text-uppercase" placeholder="Enter coupon code">
                            <button type="button" id="btnApplyCoupon" class="btn btn-outline-primary fw-bold">Apply</button>
                        </div>
                        <div id="couponFeedback" class="small mt-1"></div>
                    </div>

                    <!-- Final Total Display -->
                    <div class="p-3 bg-light rounded-4 mb-4">
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="fw-bold text-dark fs-5">Total Due:</span>
                            <span class="display-6 fw-bold text-success mb-0" id="dispTotal"><?= format_rwf($plan['price']) ?></span>
                        </div>
                        <small class="text-muted d-block mt-1">Billed <?= e($plan['billing_interval']) ?>. Cancel anytime.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow">
                        <i class="bi bi-lock-fill me-1"></i> Complete Subscription
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const basePrice = <?= (float)$plan['price'] ?>;
    const btnApply = document.getElementById('btnApplyCoupon');
    const couponInput = document.getElementById('couponCodeInput');
    const hiddenCoupon = document.getElementById('appliedCouponCode');
    const feedback = document.getElementById('couponFeedback');
    const discountRow = document.getElementById('discountRow');
    const dispDiscount = document.getElementById('dispDiscount');
    const dispTotal = document.getElementById('dispTotal');

    btnApply.addEventListener('click', function () {
        const code = couponInput.value.trim();
        if (!code) return;

        btnApply.disabled = true;
        btnApply.innerText = 'Checking...';
        feedback.innerHTML = '';

        fetch('<?= url("checkout/coupon") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                'code': code,
                'amount': basePrice,
                'csrf_token': '<?= csrf_token() ?>'
            })
        })
        .then(r => r.json())
        .then(data => {
            btnApply.disabled = false;
            btnApply.innerText = 'Apply';

            if (data.valid) {
                hiddenCoupon.value = code;
                feedback.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> ${data.message}</span>`;
                discountRow.classList.remove('d-none');
                dispDiscount.innerText = `-${Number(data.discount_amount).toLocaleString()} RWF`;
                dispTotal.innerText = `${Number(data.final_amount).toLocaleString()} RWF`;
            } else {
                hiddenCoupon.value = '';
                discountRow.classList.add('d-none');
                dispTotal.innerText = `${Number(basePrice).toLocaleString()} RWF`;
                feedback.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> ${data.message}</span>`;
            }
        })
        .catch(err => {
            btnApply.disabled = false;
            btnApply.innerText = 'Apply';
            feedback.innerHTML = '<span class="text-danger">Error validating promo code.</span>';
        });
    });
});
</script>
