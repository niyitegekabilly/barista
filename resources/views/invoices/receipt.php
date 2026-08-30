<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt <?= e($receipt['receipt_number']) ?> — Beyond Barista Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #F8F9FA; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #1F2937; }
        .receipt-card { background: #FFFFFF; max-width: 650px; margin: 40px auto; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); padding: 40px; }
        @media print {
            body { background: #FFF; }
            .receipt-card { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 650px; margin: 20px auto 0;">
        <a href="<?= url('student/dashboard') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print Receipt</button>
    </div>

    <div class="receipt-card">
        <div class="text-center border-bottom pb-4 mb-4">
            <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-2" style="width:54px;height:54px;">
                <i class="bi bi-check2 fs-2"></i>
            </div>
            <h4 class="fw-bold mb-0 text-dark">Payment Receipt</h4>
            <div class="text-muted small">Beyond Barista Academy Ltd • Kigali, Rwanda</div>
            <span class="badge bg-success text-uppercase px-3 py-1 mt-2">PAID IN FULL</span>
        </div>

        <div class="p-3 bg-light rounded-4 mb-4">
            <div class="d-flex justify-content-between py-1 border-bottom small">
                <span class="text-muted">Receipt Number:</span>
                <strong class="font-monospace"><?= e($receipt['receipt_number']) ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom small">
                <span class="text-muted">Order Reference:</span>
                <strong class="font-monospace"><?= e($receipt['order']['order_number'] ?? '') ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom small">
                <span class="text-muted">Payment Reference (Tx):</span>
                <code class="text-dark"><?= e($receipt['transaction_reference']) ?></code>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom small">
                <span class="text-muted">Customer Name:</span>
                <strong><?= e($receipt['user']['name'] ?? 'Customer') ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom small">
                <span class="text-muted">Payment Date:</span>
                <strong><?= date('F d, Y H:i', strtotime($receipt['created_at'])) ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Payment Method:</span>
                <strong class="text-uppercase"><?= e($receipt['payment_method']) ?></strong>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center border-top border-bottom py-3 mb-4">
            <span class="fw-bold fs-6">Total Amount Paid:</span>
            <span class="fw-bold fs-4 text-success"><?= format_money($receipt['amount'], $receipt['currency']) ?></span>
        </div>

        <div class="text-center small text-muted">
            <p class="mb-0">This document verifies that payment has been received and verified by Beyond Barista Academy.</p>
        </div>
    </div>
</div>

</body>
</html>
