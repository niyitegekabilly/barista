<?php 
$pageTitle = 'Edit Course — ' . e($course['title']); 

$reqArray = json_decode($course['requirements'] ?? '', true);
$reqText = is_array($reqArray) ? implode("\n", $reqArray) : ($course['requirements'] ?? '');

$outArray = json_decode($course['learning_outcomes'] ?? '', true);
$outText = is_array($outArray) ? implode("\n", $outArray) : ($course['learning_outcomes'] ?? '');
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('instructor/courses') ?>">Courses</a></li>
                <li class="breadcrumb-item active"><?= e($course['title']) ?></li>
            </ol>
        </nav>
        <h2 class="font-heading fw-bold mb-0">Edit Course</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('instructor/courses/' . e($course['id']) . '/curriculum') ?>" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-collection-play me-1"></i> Curriculum & Lessons
        </a>
        <a href="<?= url('courses/' . e($course['slug'])) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live
        </a>
    </div>
</div>

<div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4">
    <form action="<?= url('instructor/courses/' . e($course['id']) . '/update') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Course Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-lg" value="<?= e($course['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Short Subtitle / Hook</label>
                    <input type="text" name="short_description" class="form-control" value="<?= e($course['short_description'] ?? '') ?>" maxlength="255" placeholder="Brief 1-sentence synopsis">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Full Course Overview & Description</label>
                    <textarea name="description" class="form-control" rows="7" placeholder="Provide detailed background, training objectives, and what students will accomplish..."><?= e($course['description'] ?? '') ?></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Category</label>
                        <select name="category_id" class="form-select">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $course['category_id'] ? 'selected' : '' ?>>
                                    <?= e($cat['indented_name'] ?? $cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Skill Level</label>
                        <select name="level" class="form-select">
                            <?php foreach (['beginner', 'intermediate', 'advanced', 'all_levels'] as $l): ?>
                                <option value="<?= $l ?>" <?= ($course['level'] ?? '') === $l ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $l)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Price (RWF)</label>
                        <input type="number" name="price" class="form-control" value="<?= e($course['price']) ?>" min="0" step="500">
                        <small class="text-muted">Enter 0 for Free courses.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Discount / Offer Price (RWF, Optional)</label>
                        <input type="number" name="discount_price" class="form-control" value="<?= e($course['discount_price'] ?? '') ?>" min="0" step="500" placeholder="Optional sale price">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="form-label fw-bold small">Key Learning Outcomes (One item per line)</label>
                    <textarea name="what_you_learn" class="form-control" rows="4" placeholder="Master espresso machine calibration&#10;Create consistent latte art micro-foam&#10;Understand extraction science"><?= e($outText) ?></textarea>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold small">Prerequisites & Requirements (One item per line)</label>
                    <textarea name="requirements" class="form-control" rows="3" placeholder="Basic hospitality enthusiasm&#10;No prior barista experience required"><?= e($reqText) ?></textarea>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="p-3 bg-light rounded-4 border mb-4">
                    <label class="form-label fw-bold small mb-2">Course Cover Thumbnail</label>
                    <?php if (!empty($course['thumbnail'])): ?>
                        <div class="mb-3 overflow-hidden rounded-3 border" style="max-height: 200px;">
                            <img src="<?= asset('uploads/' . e($course['thumbnail'])) ?>" class="img-fluid w-100 object-fit-cover" alt="Thumbnail">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="thumbnail" class="form-control form-control-sm" accept="image/*">
                    <small class="text-muted d-block mt-1">Recommended: 1280x720 (16:9 ratio, JPG/PNG)</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Preview Promo Video URL</label>
                    <input type="url" name="preview_video_url" class="form-control" value="<?= e($course['preview_video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
                    <small class="text-muted">YouTube, Vimeo, or Google Drive link shown on landing page.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Publication Status</label>
                    <select name="is_published" class="form-select">
                        <option value="1" <?= !empty($course['is_published']) ? 'selected' : '' ?>>Published (Live on Academy)</option>
                        <option value="0" <?= empty($course['is_published']) ? 'selected' : '' ?>>Draft / Unlisted</option>
                    </select>
                </div>

                <div class="p-3 rounded-3 bg-primary-subtle text-primary small mb-3">
                    <i class="bi bi-info-circle me-1"></i> Course content, video lessons, and AI quizzes can be built in the <strong>Curriculum</strong> tab.
                </div>
            </div>
        </div>

        <hr class="my-4">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <button type="submit" class="btn btn-primary fw-bold px-5 py-2">
                <i class="bi bi-check2-circle me-1"></i> Save Changes
            </button>
            <a href="<?= url('instructor/courses/' . e($course['id']) . '/curriculum') ?>" class="btn btn-outline-primary py-2">
                <i class="bi bi-collection-play me-1"></i> Manage Curriculum
            </a>
            <a href="<?= url('instructor/courses') ?>" class="btn btn-outline-secondary py-2 ms-auto">
                Cancel & Return
            </a>
        </div>
    </form>
</div>
