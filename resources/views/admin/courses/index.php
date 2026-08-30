<?php $pageTitle = 'Course Management'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="font-heading fw-bold mb-0">Course Management</h2>
    <div class="d-flex gap-2">
        <select class="form-select form-select-sm" style="width:160px;" onchange="window.location='<?= url('admin/courses') ?>?status='+this.value">
            <option value="">All Statuses</option>
            <option value="published" <?= ($_GET['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft"     <?= ($_GET['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Draft / Unpublished</option>
        </select>
    </div>
</div>

<?php
// Resolve thumbnail fallbacks
$imgFallbacks = [
    'barista.jpeg', 'cappuccino.jpg', 'coffee-cups.jpg',
    'coffeshop.jpg', 'class.png', 'best.jpg',
];
?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Course</th>
                    <th>Instructor</th>
                    <th>Price</th>
                    <th class="text-center">Students</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $idx => $course): ?>
                    <?php
                    // Derive status string from is_published flag
                    $isPublished = (bool)($course['is_published'] ?? 0);
                    $statusLabel = $isPublished ? 'published' : 'draft';

                    // Resolve thumbnail
                    $thumb = $course['thumbnail'] ?? '';
                    if ($thumb && file_exists(BASE_PATH . '/public/assets/img/' . $thumb)) {
                        $thumbUrl = asset('img/' . $thumb);
                    } elseif ($thumb && file_exists(BASE_PATH . '/public/uploads/' . $thumb)) {
                        $thumbUrl = asset('uploads/' . $thumb);
                    } else {
                        $thumbUrl = asset('img/' . $imgFallbacks[$idx % count($imgFallbacks)]);
                    }
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= $thumbUrl ?>"
                                     style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #eee;" alt="">
                                <div>
                                    <div class="fw-bold small"><?= e($course['title']) ?></div>
                                    <small class="text-primary fw-semibold"><?= e($course['category_name'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="small"><?= e($course['instructor_name'] ?? '—') ?></td>
                        <td class="fw-bold small">
                            <?php if ($course['is_free'] ?? false): ?>
                                <span class="badge bg-success">Free</span>
                            <?php else: ?>
                                RWF <?= number_format((float)($course['price'] ?? 0)) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center small"><?= (int)($course['enrollment_count'] ?? 0) ?></td>
                        <td>
                            <?php if ($isPublished): ?>
                                <span class="badge bg-success">Published</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end flex-wrap">
                                <a href="<?= url('courses/' . e($course['slug'])) ?>" target="_blank"
                                   class="btn btn-sm btn-outline-secondary" title="Preview on site">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (!$isPublished): ?>
                                    <form action="<?= url('admin/courses/' . $course['id'] . '/approve') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg me-1"></i>Publish
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= url('admin/courses/' . $course['id'] . '/unpublish') ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pause-circle me-1"></i>Unpublish
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($courses)): ?>
        <div class="p-5 text-center text-muted">
            <i class="bi bi-collection-play fs-1 d-block mb-2 opacity-40"></i>
            No courses found for the selected filter.
        </div>
    <?php endif; ?>
</div>
