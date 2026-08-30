<?php $pageTitle = 'Financial Ledger'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Double-Entry Financial Ledger</h2>
        <p class="text-muted small mb-0">Immutable, chronological transaction record of every credit and debit movement.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/finance') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-surface mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light text-muted text-uppercase">
                <tr>
                    <th>Tx #</th>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Reference / Order</th>
                    <th>Direction</th>
                    <th class="text-end">Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ledger['data'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-2 mb-2 d-block"></i>
                            No ledger entries recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ledger['data'] as $tx): ?>
                        <tr>
                            <td><code><?= e($tx['transaction_number']) ?></code></td>
                            <td class="text-muted"><?= date('M d, Y H:i:s', strtotime($tx['created_at'])) ?></td>
                            <td>
                                <span class="badge <?= $tx['type'] === 'charge' ? 'bg-success-subtle text-success border border-success' : ($tx['type'] === 'refund' ? 'bg-danger-subtle text-danger border border-danger' : 'bg-secondary-subtle text-secondary border') ?> text-uppercase">
                                    <?= e($tx['type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($tx['order_number'])): ?>
                                    <a href="<?= url('admin/orders/' . $tx['order_id']) ?>" class="fw-bold text-decoration-none">
                                        <?= e($tx['order_number']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted"><?= e($tx['reference'] ?: '—') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $tx['direction'] === 'credit' ? '<span class="badge bg-success">CREDIT (+)</span>' : '<span class="badge bg-danger">DEBIT (-)</span>' ?>
                            </td>
                            <td class="text-end fw-bold <?= $tx['direction'] === 'credit' ? 'text-success' : 'text-danger' ?>">
                                <?= $tx['direction'] === 'credit' ? '+' : '-' ?><?= format_money($tx['amount'], $tx['currency']) ?>
                            </td>
                            <td class="text-muted small"><?= e($tx['notes'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
