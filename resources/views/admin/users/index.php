<?php $pageTitle = 'User Management'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="font-heading fw-bold mb-0">User Management</h2>
    <div class="d-flex gap-2">
        <input type="text" id="userSearch" class="form-control form-control-sm" style="width:220px;" placeholder="Search users...">
        <a href="<?= url('admin/users/create') ?>" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-person-plus me-1"></i> Add User
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-3 gap-1">
    <li class="nav-item"><a class="nav-link <?= !isset($_GET['role']) ? 'active' : '' ?>" href="<?= url('admin/users') ?>">All</a></li>
    <li class="nav-item"><a class="nav-link <?= ($_GET['role'] ?? '') === 'student' ? 'active' : '' ?>" href="<?= url('admin/users?role=student') ?>">Students</a></li>
    <li class="nav-item"><a class="nav-link <?= ($_GET['role'] ?? '') === 'instructor' ? 'active' : '' ?>" href="<?= url('admin/users?role=instructor') ?>">Instructors</a></li>
    <li class="nav-item"><a class="nav-link <?= ($_GET['role'] ?? '') === 'admin' ? 'active' : '' ?>" href="<?= url('admin/users?role=admin') ?>">Admins</a></li>
</ul>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="usersTable">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Enrollments</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold small"
                                     style="width:36px;height:36px;flex-shrink:0;background-color:<?= $user['role_slug'] === 'admin' ? '#4C3103' : ($user['role_slug'] === 'instructor' ? '#E29578' : '#6366F1') ?>;">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-bold small"><?= e($user['name']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= e($user['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $user['role_slug'] === 'admin' ? 'bg-dark' : ($user['role_slug'] === 'instructor' ? 'bg-warning text-dark' : 'bg-primary') ?>">
                                <?= strtoupper($user['role_slug']) ?>
                            </span>
                        </td>
                        <td class="text-center"><?= e($user['enrollment_count'] ?? 0) ?></td>
                        <td class="text-muted small"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                <?= strtoupper($user['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="<?= url('admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                            <?php if ($user['status'] === 'active'): ?>
                                <form action="<?= url('admin/users/' . $user['id'] . '/suspend') ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Suspend this user?')">Suspend</button>
                                </form>
                            <?php else: ?>
                                <form action="<?= url('admin/users/' . $user['id'] . '/activate') ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $pagination['current_page'] == $i ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('admin/users?page=' . $i . (isset($_GET['role']) ? '&role=' . $_GET['role'] : '')) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<script>
document.getElementById('userSearch').addEventListener('keyup', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
