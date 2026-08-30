<?php $pageTitle = 'Course Curriculum Builder — ' . e($course['title']); ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('instructor/courses') ?>">Courses</a></li>
                <li class="breadcrumb-item active"><?= e($course['title']) ?></li>
            </ol>
        </nav>
        <h2 class="font-heading fw-bold mb-0">Curriculum & Video Lessons</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('courses/' . e($course['slug'])) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-eye me-1"></i> Preview Course Page
        </a>
        <a href="<?= url('instructor/courses/' . e($course['id']) . '/edit') ?>" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-pencil me-1"></i> Edit Details
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Main Curriculum Section -->
    <div class="col-lg-8">
        <?php if (empty($modules)): ?>
            <div class="card p-5 text-center bg-light border-0 rounded-4 mb-4">
                <i class="bi bi-collection-play display-3 text-muted mb-3 opacity-50"></i>
                <h4 class="font-heading mb-2">No Modules Yet</h4>
                <p class="text-muted small max-w-500 mx-auto mb-4">Start by creating your first module/section (e.g., "Module 1: Introduction to Specialty Coffee"), then add your video lessons.</p>
            </div>
        <?php else: ?>
            <div id="curriculum-container">
                <?php foreach ($modules as $modIndex => $module): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-3" id="module-<?= $module['id'] ?>">
                        <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between rounded-top-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary fw-bold">Section <?= $modIndex + 1 ?></span>
                                <h5 class="mb-0 font-heading fw-bold text-dark"><?= e($module['title']) ?></h5>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-success fw-bold add-lesson-btn" data-module-id="<?= $module['id'] ?>" data-module-title="<?= e($module['title']) ?>">
                                    <i class="bi bi-plus-circle me-1"></i> Add Lesson
                                </button>
                                <form action="<?= url('instructor/modules/' . $module['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this module and ALL its lessons?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Module">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <?php if (empty($module['lessons'])): ?>
                                <div class="text-center py-4 bg-light rounded-3">
                                    <p class="text-muted small mb-2">No lessons in this module yet.</p>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-lesson-btn" data-module-id="<?= $module['id'] ?>" data-module-title="<?= e($module['title']) ?>">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Lesson
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($module['lessons'] as $lesIndex => $lesson): ?>
                                        <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-white hover-shadow transition">
                                            <div class="d-flex align-items-center gap-3">
                                                <?php if (($lesson['lesson_type'] ?? 'video') === 'video'): ?>
                                                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                                        <i class="bi bi-play-circle-fill fs-5"></i>
                                                    </div>
                                                <?php elseif (($lesson['lesson_type'] ?? '') === 'pdf'): ?>
                                                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                                        <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                                        <i class="bi bi-file-text-fill fs-5"></i>
                                                    </div>
                                                <?php endif; ?>

                                                <div>
                                                    <div class="fw-bold small text-dark"><?= e($lesson['title']) ?></div>
                                                    <div class="d-flex align-items-center gap-2 mt-1">
                                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size:0.65rem;">
                                                            <?= e($lesson['lesson_type'] ?? 'video') ?>
                                                        </span>
                                                        <?php if (!empty($lesson['video_provider']) && ($lesson['lesson_type'] ?? '') === 'video'): ?>
                                                            <span class="badge bg-primary-subtle text-primary" style="font-size:0.65rem;">
                                                                <i class="bi bi-link-45deg"></i> <?= ucfirst(e($lesson['video_provider'])) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($lesson['duration_minutes'])): ?>
                                                            <span class="text-muted small" style="font-size:0.7rem;">
                                                                <i class="bi bi-clock"></i> <?= (int)$lesson['duration_minutes'] ?>m
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($lesson['is_free_preview'])): ?>
                                                            <span class="badge bg-warning text-dark fw-bold" style="font-size:0.65rem;">Free Preview</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <a href="<?= url('instructor/lessons/' . $lesson['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit Lesson & Video Link">
                                                    <i class="bi bi-pencil me-1"></i> Edit
                                                </a>
                                                <form action="<?= url('instructor/lessons/' . $lesson['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this lesson?')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Lesson">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Add Module Card -->
        <div class="card p-4 border-2 border-dashed rounded-4 bg-light">
            <h5 class="font-heading fw-bold mb-2"><i class="bi bi-folder-plus text-primary me-2"></i>Create New Section / Module</h5>
            <p class="text-muted small mb-3">Group your course into structured sections (e.g. "Module 1: Coffee Origin & Science", "Module 2: Milk Steaming & Texturing").</p>
            <form action="<?= url('instructor/courses/' . e($course['id']) . '/modules/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="row g-2">
                    <div class="col-md-9">
                        <input type="text" name="title" class="form-control form-control-lg" placeholder="e.g. Module 1: Foundational Espresso Calibration" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold w-100">
                            <i class="bi bi-plus-lg me-1"></i> Add Module
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar: Add Lesson Panel -->
    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm rounded-4 sticky-top" style="top:90px;" id="add-lesson-panel">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="font-heading fw-bold mb-0">
                    <i class="bi bi-camera-video text-primary me-1"></i> Add Video Lesson
                </h5>
                <span class="badge bg-success-subtle text-success small fw-bold">Free Video Hosting</span>
            </div>

            <form action="<?= url('instructor/lessons/store') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Target Module <span class="text-danger">*</span></label>
                    <select name="module_id" id="lesson_module_id" class="form-select" required>
                        <?php if (empty($modules)): ?>
                            <option value="">— Please add a module first —</option>
                        <?php else: ?>
                            <?php foreach ($modules as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= e($m['title']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Lesson Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. 1.1 Coffee Extraction Science & Dial-in" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Content Type</label>
                    <select name="content_type" class="form-select" id="contentTypeSelect">
                        <option value="video">🎥 Video (YouTube, Google Drive, Vimeo, Loom, etc.)</option>
                        <option value="pdf">📄 PDF Document / Handbook</option>
                        <option value="text">📝 Article / Reading Material</option>
                        <option value="audio">🎙️ Audio / Podcast Lesson</option>
                    </select>
                </div>

                <!-- Video Provider & URL Section -->
                <div id="videoUrlDiv" class="p-3 bg-light rounded-3 border mb-3">
                    <div class="mb-2">
                        <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                            <span>Video Hosting Source</span>
                            <span class="badge bg-primary-subtle text-primary" style="font-size:0.65rem;">Protected Embed</span>
                        </label>
                        <select name="video_provider" id="videoProviderSelect" class="form-select form-select-sm">
                            <?php foreach ($providers as $key => $prov): ?>
                                <option value="<?= $key ?>" data-guide="<?= e($prov['guide']) ?>" data-placeholder="<?= e($prov['placeholder']) ?>">
                                    <?= e($prov['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Video URL / Share Link <span class="text-danger">*</span></label>
                        <input type="text" name="video_url" id="videoUrlInput" class="form-control form-control-sm" placeholder="https://www.youtube.com/watch?v=... or Google Drive share link">
                        <small class="text-muted d-block mt-1" id="providerGuideText" style="font-size:0.75rem;">
                            Supports YouTube (Unlisted), Google Drive, Vimeo, Loom, Dailymotion, Archive.org, and MP4.
                        </small>
                    </div>

                    <!-- Free Hosting Tips Accordion -->
                    <div class="mt-2 pt-2 border-top">
                        <a class="text-decoration-none small d-flex align-items-center justify-content-between text-primary" data-bs-toggle="collapse" href="#freeHostingTips" role="button">
                            <span><i class="bi bi-info-circle me-1"></i> Free Video Hosting Tips</span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse mt-2" id="freeHostingTips">
                            <ul class="small text-muted ps-3 mb-0" style="font-size:0.75rem;line-height:1.5;">
                                <li><strong>YouTube:</strong> Upload as <em>Unlisted</em> — 100% free, unlimited storage, only enrolled students can view.</li>
                                <li><strong>Google Drive:</strong> Upload video, click <em>Share &rarr; Anyone with link can view</em>. Paste link here.</li>
                                <li><strong>Loom:</strong> Record directly in browser & copy share link.</li>
                                <li><strong>Internet Archive:</strong> Free permanent non-profit storage.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- PDF Upload Div -->
                <div class="mb-3 d-none p-3 bg-light rounded-3 border" id="pdfUploadDiv">
                    <label class="form-label small fw-bold">Upload PDF Document</label>
                    <input type="file" name="pdf_file" class="form-control form-control-sm" accept=".pdf">
                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Upload study guides, recipe cards, or training slides.</small>
                </div>

                <!-- Article Content Div -->
                <div class="mb-3 d-none" id="textContentDiv">
                    <label class="form-label small fw-bold">Lesson Summary & Lecture Notes</label>
                    <textarea name="text_content" class="form-control" rows="4" placeholder="Add study notes, key formulas, or takeaways for this lesson..."></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Duration (Minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="10" min="1">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check pb-2">
                            <input class="form-check-input" type="checkbox" name="is_free_preview" value="1" id="freePreviewCheck">
                            <label class="form-check-label small" for="freePreviewCheck">
                                Free Preview
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm" <?= empty($modules) ? 'disabled' : '' ?>>
                    <i class="bi bi-plus-circle me-1"></i> Add Lesson to Course
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const addBtns           = document.querySelectorAll('.add-lesson-btn');
    const moduleIdInput     = document.getElementById('lesson_module_id');
    const contentTypeSelect = document.getElementById('contentTypeSelect');
    const videoDiv          = document.getElementById('videoUrlDiv');
    const pdfDiv            = document.getElementById('pdfUploadDiv');
    const textDiv           = document.getElementById('textContentDiv');
    const providerSelect    = document.getElementById('videoProviderSelect');
    const videoUrlInput     = document.getElementById('videoUrlInput');
    const guideText         = document.getElementById('providerGuideText');

    // Handle "Add Lesson" button click on specific module
    addBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetModuleId = btn.dataset.moduleId;
            if (moduleIdInput && targetModuleId) {
                moduleIdInput.value = targetModuleId;
            }
            document.getElementById('add-lesson-panel').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Content type toggle
    contentTypeSelect.addEventListener('change', () => {
        const v = contentTypeSelect.value;
        videoDiv.classList.toggle('d-none', v !== 'video' && v !== 'audio');
        pdfDiv.classList.toggle('d-none', v !== 'pdf');
        textDiv.classList.toggle('d-none', v !== 'text');
    });

    // Provider select helper update
    providerSelect.addEventListener('change', () => {
        const selected = providerSelect.options[providerSelect.selectedIndex];
        const placeholder = selected.getAttribute('data-placeholder');
        const guide = selected.getAttribute('data-guide');
        if (placeholder) videoUrlInput.setAttribute('placeholder', placeholder);
        if (guide) guideText.textContent = guide;
    });

    // Live URL provider auto-detection
    videoUrlInput.addEventListener('input', () => {
        const val = videoUrlInput.value.trim().toLowerCase();
        if (providerSelect.value === 'auto') {
            if (val.includes('youtube.com') || val.includes('youtu.be')) {
                guideText.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-youtube"></i> YouTube video detected.</span> Ready to embed.';
            } else if (val.includes('drive.google.com')) {
                guideText.innerHTML = '<span class="text-primary fw-bold"><i class="bi bi-google"></i> Google Drive video detected.</span> Ensure link permission is set to "Anyone with link can view".';
            } else if (val.includes('vimeo.com')) {
                guideText.innerHTML = '<span class="text-info fw-bold"><i class="bi bi-vimeo"></i> Vimeo video detected.</span>';
            } else if (val.includes('loom.com')) {
                guideText.innerHTML = '<span class="text-warning fw-bold"><i class="bi bi-camera-video"></i> Loom recording detected.</span>';
            } else if (val.includes('dailymotion.com') || val.includes('dai.ly')) {
                guideText.innerHTML = '<span class="text-primary fw-bold"><i class="bi bi-play-btn"></i> Dailymotion video detected.</span>';
            } else if (val.includes('archive.org')) {
                guideText.innerHTML = '<span class="text-secondary fw-bold"><i class="bi bi-bank"></i> Internet Archive video detected.</span>';
            } else if (val.includes('.mp4') || val.includes('.webm')) {
                guideText.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-file-play"></i> Direct video file detected.</span> Protected HTML5 player enabled.';
            }
        }
    });
});
</script>
