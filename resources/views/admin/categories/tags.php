<?php $pageTitle = 'Taxonomy Tags'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Course Taxonomy Tags</h2>
        <p class="text-muted small mb-0">Organize skills, micro-topics, certifications, and educational tags across courses.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/categories') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-arrow-left"></i> Back to Categories
        </a>
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createTagModal">
            <i class="bi bi-plus-circle-fill"></i> Add New Tag
        </button>
    </div>
</div>

<!-- Popular Tags Widget -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-fire text-danger me-2"></i> Most Popular Tags</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($popular as $p): ?>
                    <span class="badge bg-light text-dark border p-2 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                        <span class="fw-bold text-primary">#<?= e($p['name']) ?></span>
                        <span class="badge bg-primary text-white"><?= $p['courses_count'] ?> courses</span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-surface h-100">
            <h5 class="font-heading fw-bold mb-2 text-primary-dark"><i class="bi bi-tags me-2"></i> Tag Statistics</h5>
            <div class="d-flex justify-content-between py-1 border-bottom small">
                <span class="text-muted">Total Tags:</span>
                <strong class="text-dark"><?= count($tags) ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom small">
                <span class="text-muted">Active In Courses:</span>
                <strong class="text-success"><?= count(array_filter($tags, fn($t) => $t['courses_count'] > 0)) ?></strong>
            </div>
            <div class="d-flex justify-content-between py-1 small">
                <span class="text-muted">Unused Tags:</span>
                <strong class="text-warning"><?= count($unused) ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Tags Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-surface">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="font-heading fw-bold mb-0 text-primary-dark">All Taxonomy Tags (<?= count($tags) ?>)</h5>
        <input type="text" id="tagSearchInput" class="form-control form-control-sm" style="max-width: 250px;" placeholder="Filter tags...">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tagsTable">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th>Tag Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Usage (Courses)</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $t): ?>
                    <tr>
                        <td>
                            <span class="fw-bold text-dark"><i class="bi bi-hash text-muted"></i><?= e($t['name']) ?></span>
                        </td>
                        <td><code><?= e($t['slug']) ?></code></td>
                        <td class="small text-muted"><?= e($t['description'] ?: '—') ?></td>
                        <td>
                            <span class="badge bg-<?= $t['courses_count'] > 0 ? 'primary-subtle text-primary border border-primary' : 'secondary-subtle text-secondary border' ?> px-2 py-1">
                                <?= $t['courses_count'] ?> course(s)
                            </span>
                        </td>
                        <td class="small text-muted"><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="openEditTagModal(<?= $t['id'] ?>, '<?= addslashes(e($t['name'])) ?>', '<?= addslashes(e($t['description'] ?? '')) ?>')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="<?= url('admin/tags/' . $t['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this tag?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Tag -->
<div class="modal fade" id="createTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/tags/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-tag-fill text-primary me-2"></i> Add Taxonomy Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tag Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Cold Brew Extraction" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description (Optional)</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Brief explanation of this skill/topic..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Save Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Tag -->
<div class="modal fade" id="editTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="editTagForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tag Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editTagName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" id="editTagDesc" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Update Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditTagModal(id, name, desc) {
    document.getElementById('editTagForm').action = '<?= url('admin/tags/') ?>' + id + '/update';
    document.getElementById('editTagName').value = name;
    document.getElementById('editTagDesc').value = desc;
    new bootstrap.Modal(document.getElementById('editTagModal')).show();
}

document.getElementById('tagSearchInput')?.addEventListener('keyup', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tagsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
