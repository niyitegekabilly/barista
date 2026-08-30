<?php $pageTitle = 'Create New Course'; ?>
<div class="mb-4">
    <h2 class="font-heading fw-bold mb-1">Create a New Course</h2>
    <p class="text-muted small mb-0">Fill in course details below. After saving, you can add curriculum modules and lessons.</p>
</div>

<div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4">
    <form action="<?= url('instructor/courses/store') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Course Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Advanced Espresso Extraction Mastery" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Short Description <span class="text-danger">*</span></label>
                    <input type="text" name="short_description" class="form-control" placeholder="One-line course summary for listings..." maxlength="200" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Full Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="6" placeholder="Detailed course overview, what students will learn, prerequisites..." required></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Level</label>
                        <select name="level" class="form-select">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="all_levels">All Levels</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Language</label>
                        <select name="language" class="form-select">
                            <option value="en">English</option>
                            <option value="fr">French (Français)</option>
                            <option value="rw">Kinyarwanda</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Price (RWF) — leave 0 for Free</label>
                        <input type="number" name="price" class="form-control" value="0" min="0" step="500">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Discount / Sale Price (RWF) — Optional</label>
                        <input type="number" name="discount_price" class="form-control" placeholder="Leave blank if no sale" min="0" step="500">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Estimated Duration (hours)</label>
                        <input type="number" name="duration_hours" class="form-control" value="0" min="0" step="0.5">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold small">Requirements / Prerequisites</label>
                    <textarea name="requirements" class="form-control" rows="3" placeholder="One requirement per line..."></textarea>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-bold small">What Students Will Learn (Learning Outcomes)</label>
                    <textarea name="what_you_learn" class="form-control" rows="4" placeholder="One learning outcome per line..."></textarea>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Course Thumbnail</label>
                    <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    <small class="text-muted">Recommended: 1280×720px (16:9), max 2MB.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Preview Video URL (YouTube/Vimeo)</label>
                    <input type="url" name="preview_video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                </div>

                <div class="card p-3 bg-light border-0 rounded-3">
                    <h6 class="font-heading small fw-bold mb-2">Publishing Notes</h6>
                    <ul class="text-muted" style="font-size:0.8rem; padding-left:1.1rem;">
                        <li>New courses are saved as <strong>Draft</strong> first.</li>
                        <li>An admin will review and approve your course before it goes live.</li>
                        <li>You can add curriculum (modules & lessons) after saving.</li>
                    </ul>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary fw-bold px-5">Save Draft Course</button>
            <a href="<?= url('instructor/courses') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
