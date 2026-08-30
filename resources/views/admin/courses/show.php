<?php
$pageTitle = $course['title'];
$statusBadges = [
    'draft' => 'bg-secondary',
    'pending_review' => 'bg-warning-subtle text-warning border border-warning',
    'under_review' => 'bg-info-subtle text-info border border-info',
    'changes_requested' => 'bg-warning text-dark',
    'approved' => 'bg-success-subtle text-success border border-success',
    'scheduled' => 'bg-primary-subtle text-primary border border-primary',
    'published' => 'bg-success',
    'unpublished' => 'bg-secondary-subtle text-secondary border',
    'archived' => 'bg-dark-subtle text-dark border',
    'rejected' => 'bg-danger-subtle text-danger border border-danger',
];
$status = $course['status'];
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="<?= url('admin/courses') ?>">Courses & Approval</a></li>
                <li class="breadcrumb-item active"><?= e($course['title']) ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h2 class="font-heading fw-bold mb-0"><?= e($course['title']) ?></h2>
            <span class="badge <?= $statusBadges[$status] ?? 'bg-secondary' ?> text-capitalize px-3 py-2">
                <?= e(str_replace('_', ' ', $status)) ?>
            </span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            <i class="bi bi-person-fill me-1"></i><?= e($course['instructor_name']) ?>
            <span class="mx-1">•</span><i class="bi bi-tag-fill me-1"></i><?= e($course['category_name'] ?? '—') ?>
            <span class="mx-1">•</span><?= $course['is_free'] ? 'Free' : 'RWF ' . number_format((float)$course['price']) ?>
        </p>
    </div>
    <a href="<?= url('courses/' . e($course['slug'])) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i> View on Site
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">

        <?php if ($course['rejection_reason'] && in_array($status, ['changes_requested', 'rejected'], true)): ?>
            <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4">
                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $status === 'rejected' ? 'Rejection reason' : 'Changes requested' ?>:</strong>
                <?= e($course['rejection_reason']) ?>
            </div>
        <?php endif; ?>

        <!-- Course Information -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="font-heading fw-bold mb-3">Course Information</h5>
            <p class="small text-muted mb-2"><?= e($course['short_description'] ?? '') ?></p>
            <div class="row g-3 small">
                <div class="col-6 col-md-3"><span class="text-muted d-block">Level</span><span class="fw-bold text-capitalize"><?= e(str_replace('_', ' ', $course['level'])) ?></span></div>
                <div class="col-6 col-md-3"><span class="text-muted d-block">Duration</span><span class="fw-bold"><?= e($course['duration_hours']) ?> hrs</span></div>
                <div class="col-6 col-md-3"><span class="text-muted d-block">Passing Score</span><span class="fw-bold"><?= e($course['passing_score']) ?>%</span></div>
                <div class="col-6 col-md-3"><span class="text-muted d-block">Certificate</span><span class="fw-bold"><?= $course['certificate_included'] ? 'Included' : 'None' ?></span></div>
            </div>
        </div>

        <!-- Curriculum -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="font-heading fw-bold mb-3">Curriculum</h5>
            <?php if (empty($course['modules'])): ?>
                <p class="text-muted small mb-0">No modules added yet.</p>
            <?php else: ?>
                <div class="accordion" id="curriculumAccordion">
                    <?php foreach ($course['modules'] as $mi => $mod): ?>
                        <div class="accordion-item border-0 mb-2 rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#mod<?= $mi ?>">
                                    <?= e($mod['title']) ?>
                                    <span class="badge bg-light text-dark border ms-2"><?= count($mod['lessons']) ?> lessons</span>
                                    <?php if (!empty($mod['quizzes'])): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning ms-1"><?= count($mod['quizzes']) ?> quiz</span>
                                    <?php endif; ?>
                                </button>
                            </h2>
                            <div id="mod<?= $mi ?>" class="accordion-collapse collapse" data-bs-parent="#curriculumAccordion">
                                <div class="accordion-body py-2">
                                    <ul class="list-unstyled mb-0 small">
                                        <?php foreach ($mod['lessons'] as $les): ?>
                                            <li class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                                <span><i class="bi bi-play-circle text-primary me-2"></i><?= e($les['title']) ?></span>
                                                <span class="text-muted"><?= e($les['duration_minutes']) ?> min</span>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php foreach ($mod['quizzes'] as $qz): ?>
                                            <li class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                                <span><i class="bi bi-patch-question-fill text-warning me-2"></i><?= e($qz['title']) ?></span>
                                                <span class="text-muted">Pass: <?= e($qz['passing_score']) ?>%</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Approval History -->
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="font-heading fw-bold mb-3">Approval History</h5>
            <?php if (empty($course['approval_history'])): ?>
                <p class="text-muted small mb-0">No history yet — this course hasn't entered the review workflow.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm small align-middle mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Action</th><th>By</th><th>From → To</th><th>Comment</th></tr></thead>
                        <tbody>
                            <?php foreach ($course['approval_history'] as $h): ?>
                                <tr>
                                    <td class="text-muted"><?= date('M d, Y H:i', strtotime($h['created_at'])) ?></td>
                                    <td class="text-capitalize fw-bold"><?= e(str_replace('_', ' ', $h['action'])) ?></td>
                                    <td><?= e($h['performed_by_name']) ?></td>
                                    <td class="text-capitalize"><?= e(str_replace('_', ' ', $h['from_status'] ?? '—')) ?> → <?= e(str_replace('_', ' ', $h['to_status'])) ?></td>
                                    <td><?= e($h['comment'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Stats -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h6 class="font-heading fw-bold mb-3">Performance</h6>
            <div class="d-flex justify-content-between small py-1 border-bottom"><span class="text-muted">Enrollments</span><span class="fw-bold"><?= (int)$course['enrollment_count'] ?></span></div>
            <div class="d-flex justify-content-between small py-1 border-bottom"><span class="text-muted">Completion Rate</span><span class="fw-bold"><?= $course['completion_rate'] !== null ? e($course['completion_rate']) . '%' : '—' ?></span></div>
            <div class="d-flex justify-content-between small py-1 border-bottom"><span class="text-muted">Avg. Progress</span><span class="fw-bold"><?= $course['avg_progress'] !== null ? e($course['avg_progress']) . '%' : '—' ?></span></div>
            <div class="d-flex justify-content-between small py-1"><span class="text-muted">Rating</span><span class="fw-bold"><?= $course['avg_rating'] ? '★ ' . e($course['avg_rating']) . ' (' . $course['review_count'] . ')' : 'No reviews yet' ?></span></div>
        </div>

        <!-- Reviewer Action Panel -->
        <?php if ($canReview && in_array($status, ['pending_review', 'under_review'], true)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="font-heading fw-bold mb-3"><i class="bi bi-clipboard-check-fill text-primary me-1"></i> Review Decision</h6>

                <?php if ($status === 'pending_review'): ?>
                    <form action="<?= url('admin/courses/' . $course['id'] . '/start-review') ?>" method="POST" class="mb-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">Start Review</button>
                    </form>
                <?php else: ?>
                    <form action="<?= url('admin/courses/' . $course['id'] . '/approve') ?>" method="POST" class="mb-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('Approve this course?')">
                            <i class="bi bi-check-lg me-1"></i> Approve
                        </button>
                    </form>

                    <button type="button" class="btn btn-warning btn-sm w-100 mb-2 text-dark" data-bs-toggle="modal" data-bs-target="#requestChangesModal">
                        <i class="bi bi-arrow-repeat me-1"></i> Request Changes
                    </button>

                    <button type="button" class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Publishing Action Panel -->
        <?php if ($canPublish || $canArchive): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="font-heading fw-bold mb-3"><i class="bi bi-rocket-takeoff-fill text-success me-1"></i> Publishing</h6>

                <?php if ($canPublish && $status === 'approved'): ?>
                    <form action="<?= url('admin/courses/' . $course['id'] . '/publish') ?>" method="POST" class="mb-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('Publish this course now?')">Publish Now</button>
                    </form>
                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#scheduleModal">Schedule Publish</button>
                <?php endif; ?>

                <?php if ($canPublish && $status === 'scheduled'): ?>
                    <p class="small text-muted mb-2">Scheduled for <?= e(date('M d, Y H:i', strtotime($course['scheduled_publish_at']))) ?></p>
                    <form action="<?= url('admin/courses/' . $course['id'] . '/publish') ?>" method="POST" class="mb-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success btn-sm w-100">Publish Now Instead</button>
                    </form>
                <?php endif; ?>

                <?php if ($canPublish && $status === 'published'): ?>
                    <form action="<?= url('admin/courses/' . $course['id'] . '/unpublish') ?>" method="POST" class="mb-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-warning btn-sm w-100" onclick="return confirm('Unpublish this course? It will be removed from the public catalog.')">Unpublish</button>
                    </form>
                <?php endif; ?>

                <?php if ($canArchive && in_array($status, ['draft', 'changes_requested', 'rejected', 'approved', 'scheduled', 'unpublished'], true)): ?>
                    <form action="<?= url('admin/courses/' . $course['id'] . '/archive') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-dark btn-sm w-100" onclick="return confirm('Archive this course?')">Archive</button>
                    </form>
                <?php endif; ?>

                <?php if ($canArchive && $status === 'archived'): ?>
                    <form action="<?= url('admin/courses/' . $course['id'] . '/restore') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Restore to Draft</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/courses/' . $course['id'] . '/reject') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold">Reject Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold">Reason for rejection <span class="text-danger">*</span></label>
                    <textarea name="comment" class="form-control" rows="4" required placeholder="Explain why this course is being rejected..."></textarea>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">Reject Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Changes Modal -->
<div class="modal fade" id="requestChangesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/courses/' . $course['id'] . '/request-changes') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold">Request Changes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold">What needs to change? <span class="text-danger">*</span></label>
                    <textarea name="comment" class="form-control" rows="4" required placeholder="Describe the changes the instructor needs to make..."></textarea>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold px-3 text-dark">Send Back for Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?= url('admin/courses/' . $course['id'] . '/schedule') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title font-heading fw-bold">Schedule Publish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label small fw-bold">Publish date & time <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="scheduled_publish_at" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
