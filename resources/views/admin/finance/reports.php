<?php $pageTitle = 'Financial Reports & Analytics'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Financial Reports & Sales Analytics</h2>
        <p class="text-muted small mb-0">Multi-dimensional breakdown of academy earnings across courses, categories, and payment channels.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/finance') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <a href="<?= url('admin/orders/export') ?>" class="btn btn-primary btn-sm"><i class="bi bi-download me-1"></i> Export Complete Report CSV</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Course Sales Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-journal-code text-primary me-2"></i> Course Revenue Breakdown</h5>
            <?php if (empty($chartData['courses'])): ?>
                <p class="text-muted small">No course sales recorded.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Course</th>
                                <th class="text-center">Orders</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chartData['courses'] as $c): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($c['title']) ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= $c['sales_count'] ?></span></td>
                                    <td class="text-end fw-bold text-success"><?= format_rwf($c['total_revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Category Sales Breakdown -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-diagram-3 text-warning me-2"></i> Category Revenue Breakdown</h5>
            <?php if (empty($chartData['categories'])): ?>
                <p class="text-muted small">No category revenue data available.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Gross Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chartData['categories'] as $cat): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($cat['category_name'] ?: 'Uncategorized') ?></td>
                                    <td class="text-end fw-bold text-primary"><?= format_rwf($cat['total_revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
