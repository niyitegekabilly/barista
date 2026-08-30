<?php $pageTitle = 'Student Reviews & Feedback'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-heading fw-bold mb-0">Student Reviews & Ratings</h2>
        <p class="text-muted small mb-0">Manage feedback from enrolled students across your courses</p>
    </div>
</div>

<?php if (empty($reviews)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4">
        <i class="bi bi-star display-4 text-muted mb-3 opacity-50"></i>
        <h4 class="font-heading">No Reviews Received Yet</h4>
        <p class="text-muted small max-w-500 mx-auto mb-0">Once students enroll and complete lessons in your courses, their verified ratings and reviews will appear here.</p>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Student</th>
                        <th>Course</th>
                        <th>Rating</th>
                        <th>Feedback / Review</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                        <?= strtoupper(substr($rev['student_name'] ?? 'S', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold small text-dark"><?= e($rev['student_name'] ?? 'Student') ?></div>
                                        <small class="text-muted"><?= e($rev['student_email'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="<?= url('courses/' . e($rev['course_slug'])) ?>" target="_blank" class="fw-bold small text-decoration-none text-dark hover-primary">
                                    <?= e($rev['course_title']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="text-warning">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="bi bi-star<?= $s <= (int)$rev['rating'] ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                    <span class="text-dark small fw-bold ms-1"><?= (int)$rev['rating'] ?>.0</span>
                                </div>
                            </td>
                            <td>
                                <p class="small text-muted mb-0" style="max-width:380px;">
                                    "<?= e($rev['comment']) ?>"
                                </p>
                            </td>
                            <td class="text-muted small">
                                <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
