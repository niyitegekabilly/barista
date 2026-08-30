<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= e($invoice['invoice_number']) ?> — Beyond Barista Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #F8F9FA; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #1F2937; }
        .invoice-card { background: #FFFFFF; max-width: 800px; margin: 40px auto; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); padding: 40px; }
        @media print {
            body { background: #FFF; }
            .invoice-card { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Top Print & Back Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 800px; margin: 20px auto 0;">
        <a href="<?= url('student/dashboard') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print / Save PDF</button>
    </div>

    <div class="invoice-card">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1" style="color: #4C3103 !important;">Beyond Barista Academy</h3>
                <p class="text-muted small mb-0">Specialty Coffee Training & Hospitality Masterclasses</p>
                <p class="text-muted small mb-0">Kigali, Rwanda • info@beyondbarista.rw • +250 788 000 000</p>
            </div>
            <div class="text-end">
                <span class="badge <?= $invoice['status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?> text-uppercase px-3 py-2 fs-6 mb-2">
                    <?= e($invoice['status']) ?>
                </span>
                <h4 class="fw-bold text-secondary mb-0">INVOICE</h4>
                <div class="small fw-bold">#<?= e($invoice['invoice_number']) ?></div>
            </div>
        </div>

        <!-- Billing Info -->
        <div class="row mb-4">
            <div class="col-6">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Billed To:</span>
                <h6 class="fw-bold mb-1"><?= e($invoice['user']['name'] ?? 'Student') ?></h6>
                <div class="small text-muted"><?= e($invoice['user']['email'] ?? '') ?></div>
                <div class="small text-muted"><?= e($invoice['order']['billing_phone'] ?? '') ?></div>
                <div class="small text-muted"><?= e($invoice['order']['billing_address'] ?: 'Kigali, Rwanda') ?></div>
            </div>
            <div class="col-6 text-end">
                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Invoice Details:</span>
                <div class="small"><span class="text-muted">Order Reference:</span> <strong>#<?= e($invoice['order']['order_number'] ?? '') ?></strong></div>
                <div class="small"><span class="text-muted">Date of Issue:</span> <strong><?= date('M d, Y', strtotime($invoice['created_at'])) ?></strong></div>
                <div class="small"><span class="text-muted">Payment Channel:</span> <strong class="text-uppercase"><?= e($invoice['order']['payment_method'] ?? 'sandbox') ?></strong></div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table align-middle">
                <thead class="table-light small">
                    <tr>
                        <th>Item Description</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoice['order']['items'])): ?>
                        <?php foreach ($invoice['order']['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= e($item['item_title']) ?></div>
                                    <small class="text-muted">Curriculum Lifetime Access & Certification</small>
                                </td>
                                <td class="text-end"><?= format_money($item['unit_price'], $invoice['currency']) ?></td>
                                <td class="text-end text-success"><?= $item['discount_amount'] > 0 ? ('-' . format_money($item['discount_amount'], $invoice['currency'])) : '0 RWF' ?></td>
                                <td class="text-end fw-bold"><?= format_money($item['total_amount'], $invoice['currency']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="row justify-content-end mb-4">
            <div class="col-md-5">
                <table class="table table-borderless small mb-0">
                    <tr><td class="text-muted">Subtotal:</td><td class="text-end fw-bold"><?= format_money($invoice['subtotal'], $invoice['currency']) ?></td></tr>
                    <?php if ($invoice['discount'] > 0): ?>
                        <tr><td class="text-success">Coupon Discount:</td><td class="text-end text-success fw-bold">-<?= format_money($invoice['discount'], $invoice['currency']) ?></td></tr>
                    <?php endif; ?>
                    <tr><td class="text-muted">Taxes & VAT (18% Incl.):</td><td class="text-end text-muted">0 RWF (Included)</td></tr>
                    <tr class="border-top"><td class="fw-bold fs-6">Grand Total:</td><td class="text-end fw-bold fs-5" style="color: #4C3103;"><?= format_money($invoice['total'], $invoice['currency']) ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-top pt-4 text-center small text-muted">
            <p class="mb-1">Thank you for training with <strong>Beyond Barista Academy</strong>.</p>
            <p class="mb-0">This document serves as an official tax and accounting receipt of educational training services.</p>
        </div>
    </div>
</div>

</body>
</html>
