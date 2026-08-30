<?php
$title = 'Checkout — ' . e($course['title']) . ' — Beyond Barista Academy';
$price = (float)($course['discount_price'] ?: $course['price']);
?>

<section class="py-5 bg-light">
    <div class="container py-3">
        
        <!-- Top Header -->
        <div class="text-center mb-5">
            <span class="badge bg-warning text-dark px-3 py-1 text-uppercase fw-bold mb-2" style="font-size:0.75rem; letter-spacing:1px;">Secure LMS Checkout</span>
            <h2 class="display-6 font-heading fw-bold text-primary-dark">Complete Your Enrollment</h2>
            <p class="text-muted small">You are one step away from full lifetime access to this professional masterclass.</p>
        </div>

        <form action="<?= url('checkout/process') ?>" method="POST" id="checkoutForm">
            <?= csrf_field() ?>
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

            <div class="row g-4 justify-content-center">
                
                <!-- Left: Billing & Payment Method Selection -->
                <div class="col-lg-7">
                    <!-- 1. Customer Information Card -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                        <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i> 1. Student Billing Information</h5>
                        
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
                                <label class="form-label small fw-bold">Phone Number (MoMo) <span class="text-danger">*</span></label>
                                <input type="tel" name="billing_phone" class="form-control" placeholder="e.g. 078XXXXXXX or 073XXXXXXX" required>
                                <small class="text-muted" style="font-size:0.7rem;">Used to receive the MTN/Airtel prompt</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Location / City</label>
                                <input type="text" name="billing_address" class="form-control" placeholder="Kigali, Rwanda">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Payment Method Selector Card -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                        <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-credit-card-2-front text-primary me-2"></i> 2. Choose Payment Method</h5>

                        <div class="d-flex flex-column gap-3 mb-3">
                            <!-- MTN / Airtel MoMo -->
                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between cursor-pointer payment-method-card bg-light">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" name="payment_method" value="momo" class="form-check-input" checked>
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-phone-fill text-warning me-1"></i> MTN Mobile Money / Airtel Money</div>
                                        <small class="text-muted">Instant prompt sent directly to your phone in Rwanda</small>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark px-2 py-1">Popular in Rwanda</span>
                            </label>

                            <!-- Credit / Debit Card -->
                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between cursor-pointer payment-method-card bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" name="payment_method" value="stripe" class="form-check-input">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-credit-card-fill text-primary me-1"></i> Credit / Debit Card (Visa, Mastercard)</div>
                                        <small class="text-muted">International cards processed securely</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-1 text-muted fs-5">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                            </label>

                            <!-- Bank Transfer -->
                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between cursor-pointer payment-method-card bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" name="payment_method" value="bank_transfer" class="form-check-input">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-bank2 text-secondary me-1"></i> Direct Bank Transfer / Invoice (BK)</div>
                                        <small class="text-muted">Transfer to Bank of Kigali account with manual verification</small>
                                    </div>
                                </div>
                            </label>

                            <!-- Sandbox -->
                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between cursor-pointer payment-method-card bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" name="payment_method" value="sandbox" class="form-check-input">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-shield-check text-success me-1"></i> Sandbox Test (Instant Simulation)</div>
                                        <small class="text-muted">For testing the complete checkout flow</small>
                                    </div>
                                </div>
                                <span class="badge bg-success">Instant Access</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary & Coupon -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px;">
                        <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-receipt text-primary me-2"></i> Order Summary</h5>

                        <!-- Course Card Mini -->
                        <div class="d-flex gap-3 mb-4 pb-3 border-bottom">
                            <?php if (!empty($course['thumbnail'])): ?>
                                <img src="<?= asset('uploads/' . e($course['thumbnail'])) ?>" class="rounded-3" style="width:72px;height:72px;object-fit:cover;" alt="Course Thumbnail">
                            <?php else: ?>
                                <div class="rounded-3 bg-dark text-warning d-flex align-items-center justify-content-center" style="width:72px;height:72px;flex-shrink:0;">
                                    <i class="bi bi-cup-hot fs-3"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h6 class="fw-bold text-dark mb-1"><?= e($course['title']) ?></h6>
                                <span class="badge bg-light text-secondary border text-capitalize"><?= e($course['level'] ?? 'All Levels') ?></span>
                                <div class="small text-success mt-1"><i class="bi bi-patch-check-fill"></i> Lifetime Certification Included</div>
                            </div>
                        </div>

                        <!-- Coupon Code Input -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Have a Coupon or Discount Code?</label>
                            <div class="input-group">
                                <input type="text" name="coupon_code" id="couponInput" class="form-control text-uppercase" placeholder="e.g. BARISTA20">
                                <button type="button" class="btn btn-outline-primary fw-bold" id="btnApplyCoupon">Apply</button>
                            </div>
                            <div id="couponMessage" class="small mt-1 d-none"></div>
                        </div>

                        <!-- Price Breakdown -->
                        <table class="table table-borderless small mb-4">
                            <tr>
                                <td class="text-muted">Course Price:</td>
                                <td class="text-end fw-bold" id="rawPriceDisplay"><?= format_rwf($price) ?></td>
                            </tr>
                            <tr id="discountRow" class="d-none">
                                <td class="text-success">Coupon Discount:</td>
                                <td class="text-end text-success fw-bold" id="discountDisplay">-0 RWF</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Estimated Tax / VAT:</td>
                                <td class="text-end text-muted">0 RWF (Included)</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold fs-6 text-dark">Total Amount Due:</td>
                                <td class="text-end fw-bold fs-4 text-primary-dark" id="totalAmountDisplay"><?= format_rwf($price) ?></td>
                            </tr>
                        </table>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow" id="btnSubmitOrder">
                            <i class="bi bi-lock-fill me-1"></i> Pay & Enroll Now
                        </button>

                        <div class="text-center mt-3 small text-muted">
                            <i class="bi bi-shield-lock text-success me-1"></i> 256-bit Encrypted Transaction • Verified Academy Access
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rawPrice = <?= $price ?>;
    let appliedDiscount = 0;

    const btnApply = document.getElementById('btnApplyCoupon');
    const couponInput = document.getElementById('couponInput');
    const couponMsg = document.getElementById('couponMessage');
    const discountRow = document.getElementById('discountRow');
    const discountDisplay = document.getElementById('discountDisplay');
    const totalAmountDisplay = document.getElementById('totalAmountDisplay');

    btnApply.addEventListener('click', function () {
        const code = couponInput.value.trim();
        if (!code) return;

        btnApply.disabled = true;
        btnApply.textContent = '...';

        fetch('<?= url('api/checkout/validate-coupon') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': '<?= csrf_token() ?>'
            },
            body: `code=${encodeURIComponent(code)}&amount=${rawPrice}&course_id=<?= $course['id'] ?>`
        })
        .then(res => res.json())
        .then(data => {
            btnApply.disabled = false;
            btnApply.textContent = 'Apply';
            couponMsg.classList.remove('d-none', 'text-success', 'text-danger');

            if (data.valid) {
                appliedDiscount = data.discount_amount;
                couponMsg.classList.add('text-success');
                couponMsg.textContent = `Coupon applied! You saved ${data.discount_amount.toLocaleString()} RWF.`;
                discountRow.classList.remove('d-none');
                discountDisplay.textContent = `-${data.discount_amount.toLocaleString()} RWF`;
                totalAmountDisplay.textContent = `${data.final_amount.toLocaleString()} RWF`;
            } else {
                appliedDiscount = 0;
                couponMsg.classList.add('text-danger');
                couponMsg.textContent = data.message || 'Invalid coupon.';
                discountRow.classList.add('d-none');
                totalAmountDisplay.textContent = `${rawPrice.toLocaleString()} RWF`;
            }
        });
    });

    // Payment card click styling
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.payment-method-card').forEach(c => c.classList.replace('bg-light', 'bg-white'));
            this.classList.replace('bg-white', 'bg-light');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
});
</script>
