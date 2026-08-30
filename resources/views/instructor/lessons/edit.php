<?php $pageTitle = 'Edit Lesson — ' . e($lesson['title']); ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('instructor/courses') ?>">Courses</a></li>
                <li class="breadcrumb-item"><a href="<?= url('instructor/courses/' . e($course['id']) . '/curriculum') ?>"><?= e($course['title']) ?></a></li>
                <li class="breadcrumb-item active"><?= e($lesson['title']) ?></li>
            </ol>
        </nav>
        <h2 class="font-heading fw-bold mb-0">Edit Lesson & Video Source</h2>
    </div>
    <a href="<?= url('instructor/courses/' . e($course['id']) . '/curriculum') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Curriculum
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4">
            <form action="<?= url('instructor/lessons/' . e($lesson['id']) . '/update') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Lesson Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= e($lesson['title']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Belongs to Section / Module</label>
                        <select name="module_id" class="form-select">
                            <?php foreach ($modules as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= (int)$m['id'] === (int)$lesson['module_id'] ? 'selected' : '' ?>>
                                    <?= e($m['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Content Type</label>
                    <select name="content_type" class="form-select" id="contentTypeSelect">
                        <option value="video" <?= ($lesson['lesson_type'] ?? '') === 'video' ? 'selected' : '' ?>>🎥 Video (YouTube, Google Drive, Vimeo, Loom, etc.)</option>
                        <option value="pdf"   <?= ($lesson['lesson_type'] ?? '') === 'pdf'   ? 'selected' : '' ?>>📄 PDF Document / Handbook</option>
                        <option value="text"  <?= ($lesson['lesson_type'] ?? '') === 'text'  ? 'selected' : '' ?>>📝 Article / Reading Material</option>
                        <option value="audio" <?= ($lesson['lesson_type'] ?? '') === 'audio' ? 'selected' : '' ?>>🎙️ Audio / Podcast Lesson</option>
                    </select>
                </div>

                <!-- Video Provider & URL Section -->
                <div id="videoUrlDiv" class="p-4 bg-light rounded-4 border mb-4">
                    <h6 class="font-heading fw-bold mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-camera-reels text-primary me-2"></i>Video Source & Hosting Platform</span>
                        <span class="badge bg-success-subtle text-success">Free Hosting Compatible</span>
                    </h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Video Hosting Platform</label>
                            <select name="video_provider" id="videoProviderSelect" class="form-select">
                                <?php foreach ($providers as $key => $prov): ?>
                                    <option value="<?= $key ?>" <?= ($lesson['video_provider'] ?? 'youtube') === $key ? 'selected' : '' ?>
                                            data-guide="<?= e($prov['guide']) ?>"
                                            data-placeholder="<?= e($prov['placeholder']) ?>">
                                        <?= e($prov['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">Video URL / Share Link</label>
                            <input type="text" name="video_url" id="videoUrlInput" class="form-control" value="<?= e($lesson['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=... or Google Drive share link">
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-6 flex-shrink-0 text-info"></i>
                        <span id="providerGuideText">
                            Supports YouTube (Unlisted), Google Drive (Public share), Vimeo, Loom, Dailymotion, Internet Archive, and direct MP4 streams.
                        </span>
                    </div>
                </div>

                <!-- PDF Upload Div -->
                <div class="mb-4 <?= ($lesson['lesson_type'] ?? '') === 'pdf' ? '' : 'd-none' ?> p-4 bg-light rounded-4 border" id="pdfUploadDiv">
                    <label class="form-label small fw-bold">PDF Document</label>
                    <?php if (!empty($lesson['pdf_path'])): ?>
                        <div class="mb-2 p-2 bg-white rounded border small d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-file-earmark-pdf text-danger me-1"></i> Current file: <?= e(basename($lesson['pdf_path'])) ?></span>
                            <a href="<?= asset('uploads/' . e($lesson['pdf_path'])) ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2">View</a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="pdf_file" class="form-control" accept=".pdf">
                    <small class="text-muted d-block mt-1">Upload a replacement PDF or training manual.</small>
                </div>

                <!-- Article Content Div -->
                <div class="mb-4 <?= ($lesson['lesson_type'] ?? '') === 'text' ? '' : 'd-none' ?>" id="textContentDiv">
                    <label class="form-label small fw-bold">Lesson Summary & Lecture Notes</label>
                    <textarea name="text_content" class="form-control" rows="8" placeholder="Enter lesson notes, step-by-step instructions, recipes, or key formulas..."><?= e($lesson['content'] ?? '') ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Estimated Duration (Minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="<?= (int)($lesson['duration_minutes'] ?? 10) ?>" min="1">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check p-3 bg-light rounded-3 border w-100">
                            <input class="form-check-input" type="checkbox" name="is_free_preview" value="1" id="freePreviewCheck" <?= !empty($lesson['is_free_preview']) ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-bold" for="freePreviewCheck">
                                Free Preview Lesson (Publicly accessible before enrollment)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="<?= url('instructor/courses/' . e($course['id']) . '/curriculum') ?>" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg fw-bold px-5">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Preview Sidebar -->
    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm rounded-4 sticky-top" style="top:90px;">
            <h6 class="font-heading fw-bold mb-3 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-display text-primary me-2"></i>Live Video Preview</span>
                <span class="badge bg-light text-muted border small">Classroom View</span>
            </h6>

            <?php if (!empty($lesson['video_url']) && ($lesson['lesson_type'] ?? 'video') === 'video'): ?>
                <div class="mb-3">
                    <?= \App\Services\VideoService::renderEmbed($lesson['video_url'], $lesson['video_provider'] ?? 'auto', $lesson['title']) ?>
                </div>
                <p class="text-muted small mb-0">
                    <i class="bi bi-shield-check text-success me-1"></i> Video embeds securely into the student classroom player.
                </p>
            <?php else: ?>
                <div class="p-4 bg-light text-center rounded-3 text-muted small">
                    <i class="bi bi-play-circle fs-2 d-block mb-1 opacity-50"></i>
                    Enter a video URL and save to preview the video player here.
                </div>
            <?php endif; ?>

            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold small mb-2 text-dark">Supported Free Platforms</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-muted mb-0" style="font-size:0.8rem;">
                    <li><i class="bi bi-check2 text-success me-1"></i> <strong>YouTube:</strong> Unlisted or Public</li>
                    <li><i class="bi bi-check2 text-success me-1"></i> <strong>Google Drive:</strong> Anyone with link</li>
                    <li><i class="bi bi-check2 text-success me-1"></i> <strong>Loom:</strong> Share links</li>
                    <li><i class="bi bi-check2 text-success me-1"></i> <strong>Vimeo:</strong> Public / Unlisted</li>
                    <li><i class="bi bi-check2 text-success me-1"></i> <strong>Direct MP4:</strong> Protected streaming</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const contentTypeSelect = document.getElementById('contentTypeSelect');
    const videoDiv          = document.getElementById('videoUrlDiv');
    const pdfDiv            = document.getElementById('pdfUploadDiv');
    const textDiv           = document.getElementById('textContentDiv');
    const providerSelect    = document.getElementById('videoProviderSelect');
    const videoUrlInput     = document.getElementById('videoUrlInput');
    const guideText         = document.getElementById('providerGuideText');

    contentTypeSelect.addEventListener('change', () => {
        const v = contentTypeSelect.value;
        videoDiv.classList.toggle('d-none', v !== 'video' && v !== 'audio');
        pdfDiv.classList.toggle('d-none', v !== 'pdf');
        textDiv.classList.toggle('d-none', v !== 'text');
    });

    providerSelect.addEventListener('change', () => {
        const selected = providerSelect.options[providerSelect.selectedIndex];
        const placeholder = selected.getAttribute('data-placeholder');
        const guide = selected.getAttribute('data-guide');
        if (placeholder) videoUrlInput.setAttribute('placeholder', placeholder);
        if (guide) guideText.textContent = guide;
    });

    videoUrlInput.addEventListener('input', () => {
        const val = videoUrlInput.value.trim().toLowerCase();
        if (providerSelect.value === 'auto') {
            if (val.includes('youtube.com') || val.includes('youtu.be')) {
                guideText.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-youtube"></i> YouTube video detected.</span>';
            } else if (val.includes('drive.google.com')) {
                guideText.innerHTML = '<span class="text-primary fw-bold"><i class="bi bi-google"></i> Google Drive video detected.</span>';
            } else if (val.includes('vimeo.com')) {
                guideText.innerHTML = '<span class="text-info fw-bold"><i class="bi bi-vimeo"></i> Vimeo video detected.</span>';
            } else if (val.includes('loom.com')) {
                guideText.innerHTML = '<span class="text-warning fw-bold"><i class="bi bi-camera-video"></i> Loom recording detected.</span>';
            } else if (val.includes('dailymotion.com') || val.includes('dai.ly')) {
                guideText.innerHTML = '<span class="text-primary fw-bold"><i class="bi bi-play-btn"></i> Dailymotion video detected.</span>';
            } else if (val.includes('archive.org')) {
                guideText.innerHTML = '<span class="text-secondary fw-bold"><i class="bi bi-bank"></i> Internet Archive video detected.</span>';
            } else if (val.includes('.mp4') || val.includes('.webm')) {
                guideText.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-file-play"></i> Direct video file detected.</span>';
            }
        }
    });
});
</script>
