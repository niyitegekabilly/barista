<?php $pageTitle = 'Category Management'; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <h5 class="font-heading fw-bold mb-3">Add Category</h5>
            <form action="<?= url('admin/categories/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Category Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Latte Art" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Icon (Bootstrap Icon class)</label>
                    <input type="text" name="icon" class="form-control" placeholder="bi-cup-hot" value="bi-cup-hot">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Color (HEX)</label>
                    <input type="color" name="color" class="form-control form-control-color" value="#4C3103">
                </div>
                <button type="submit" class="btn btn-primary fw-bold w-100">Add Category</button>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <h2 class="font-heading fw-bold mb-3">All Categories</h2>
        <div class="row g-3">
            <?php foreach ($categories as $cat): ?>
                <div class="col-md-6">
                    <div class="card p-3 border-0 shadow-sm rounded-4 d-flex flex-row align-items-center gap-3">
                        <div class="rounded-3 text-white d-flex align-items-center justify-content-center fs-4"
                             style="width:50px;height:50px;flex-shrink:0;background-color:<?= e($cat['color'] ?? '#4C3103') ?>;">
                            <i class="bi <?= e($cat['icon'] ?? 'bi-cup-hot') ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold small"><?= e($cat['name']) ?></div>
                            <small class="text-muted"><?= e($cat['course_count'] ?? 0) ?> courses</small>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCatModal<?= $cat['id'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="<?= url('admin/categories/' . $cat['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Delete this category?')">
                                <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editCatModal<?= $cat['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title font-heading">Edit Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="<?= url('admin/categories/' . $cat['id'] . '/update') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Name</label>
                                            <input type="text" name="name" class="form-control" value="<?= e($cat['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Description</label>
                                            <textarea name="description" class="form-control" rows="2"><?= e($cat['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Icon class</label>
                                            <input type="text" name="icon" class="form-control" value="<?= e($cat['icon'] ?? 'bi-cup-hot') ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary fw-bold w-100">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
