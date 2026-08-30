<?php $pageTitle = 'Audit Logs'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1">Audit Logs</h2>
        <p class="text-muted small mb-0">Track all administrative and user actions on the platform</p>
    </div>
    <form action="<?= url('admin/audit-logs') ?>" method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" style="width:200px;" placeholder="Search actions..." value="<?= e($_GET['search'] ?? '') ?>">
        <select name="user_id" class="form-select form-select-sm" style="width:160px;">
            <option value="">All Users</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= ($_GET['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>User</th><th>Action</th><th>Description</th><th>IP Address</th><th>When</th></tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="small fw-bold"><?= e($log['user_name'] ?? 'System') ?></td>
                        <td>
                            <span class="badge <?= str_contains($log['action'], 'delete') ? 'bg-danger' : (str_contains($log['action'], 'create') ? 'bg-success' : 'bg-secondary') ?>" style="font-size:0.7rem;">
                                <?= strtoupper(str_replace('_', ' ', $log['action'])) ?>
                            </span>
                        </td>
                        <td class="small text-muted"><?= e($log['entity_type'] ?? '-') ?></td>
                        <td class="font-monospace small"><?= e($log['ip_address']) ?></td>
                        <td class="text-muted small"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
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
                    <a class="page-link" href="<?= url('admin/audit-logs?page=' . $i . '&search=' . urlencode($_GET['search'] ?? '') . '&user_id=' . urlencode($_GET['user_id'] ?? '')) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
