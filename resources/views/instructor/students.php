<?php $pageTitle = 'My Students'; ?>
<div class="mb-4">
    <h2 class="font-heading fw-bold mb-1">My Students</h2>
    <p class="text-muted small mb-0">Students enrolled across all your courses</p>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
        <span class="text-muted small fw-bold"><?= count($students) ?> Total Students</span>
        <input type="text" id="studentSearch" class="form-control form-control-sm" style="max-width:250px;" placeholder="Search by name or email...">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="studentsTable">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Progress</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold small" style="width:36px;height:36px;flex-shrink:0;">
                                    <?= strtoupper(substr($s['student_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-bold small"><?= e($s['student_name']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= e($s['student_email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="small"><?= e($s['course_title']) ?></td>
                        <td style="min-width:120px;">
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar bg-success" style="width:<?= e($s['progress_percentage'] ?? 0) ?>%"></div>
                            </div>
                            <small class="text-muted"><?= e($s['progress_percentage'] ?? 0) ?>%</small>
                        </td>
                        <td class="text-muted small"><?= date('M d, Y', strtotime($s['enrolled_at'])) ?></td>
                        <td>
                            <?php if ($s['completed_at']): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark">In Progress</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('studentSearch').addEventListener('keyup', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#studentsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
