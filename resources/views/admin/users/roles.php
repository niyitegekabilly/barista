<?php $pageTitle = 'Roles & Permissions Matrix'; ?>

<!-- Top Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="font-heading fw-bold mb-1 text-primary-dark">Roles & Access Matrix</h2>
        <p class="text-muted small mb-0">Define role definitions, assign system roles, and configure granular permissions across LMS modules.</p>
    </div>
</div>

<!-- Role Cards Grid -->
<div class="row g-3 mb-4">
    <?php foreach ($roles as $role): ?>
        <div class="col-md-6 col-lg-4 col-xl-2-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-surface h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge <?= $role['slug'] === 'super_admin' ? 'bg-dark' : ($role['slug'] === 'instructor' ? 'bg-warning text-dark' : ($role['slug'] === 'admin' ? 'bg-info text-dark' : 'bg-primary')) ?>">
                        <?= strtoupper($role['slug']) ?>
                    </span>
                    <span class="small text-muted fw-bold"><?= $role['users_count'] ?> users</span>
                </div>
                <h5 class="fw-bold mb-1"><?= e($role['name']) ?></h5>
                <p class="text-muted small mb-2" style="font-size:0.75rem; min-height:36px;"><?= e($role['description'] ?? 'System defined role') ?></p>
                <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-auto">
                    <small class="text-primary fw-bold" style="font-size:0.72rem;"><?= $role['permissions_count'] ?> permissions</small>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#editRoleModal_<?= $role['id'] ?>">
                        Configure
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Permissions Modal for this role -->
        <div class="modal fade" id="editRoleModal_<?= $role['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form action="<?= url('admin/roles/' . $role['id'] . '/update') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title font-heading fw-bold">
                                <i class="bi bi-shield-lock me-2 text-primary"></i> Permissions for: <?= e($role['name']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <?php if ($role['slug'] === 'super_admin'): ?>
                                <div class="alert alert-warning border-0 rounded-3 small">
                                    <i class="bi bi-shield-fill-exclamation me-1"></i> <strong>Super Admin</strong> role automatically inherits all permissions across the entire LMS platform.
                                </div>
                            <?php else: ?>
                                <p class="small text-muted mb-3">Select the granular access permissions granted to users with the <strong><?= e($role['name']) ?></strong> role:</p>
                                
                                <?php 
                                    $assignedPerms = array_column(\App\Models\Role::getPermissions($role['id']), 'id');
                                ?>

                                <?php foreach ($permissions as $module => $modPerms): ?>
                                    <div class="mb-4">
                                        <h6 class="text-uppercase fw-bold text-muted border-bottom pb-1 small" style="letter-spacing:1px;">
                                            <i class="bi bi-folder2 me-1"></i> Module: <?= e(ucfirst($module)) ?>
                                        </h6>
                                        <div class="row g-2 mt-1">
                                            <?php foreach ($modPerms as $perm): ?>
                                                <div class="col-md-6">
                                                    <div class="form-check p-2 border rounded-3 bg-light">
                                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="permissions[]" value="<?= $perm['id'] ?>" id="perm_<?= $role['id'] ?>_<?= $perm['id'] ?>" <?= in_array($perm['id'], $assignedPerms) ? 'checked' : '' ?>>
                                                        <label class="form-check-label small fw-bold" for="perm_<?= $role['id'] ?>_<?= $perm['id'] ?>">
                                                            <?= e($perm['name']) ?>
                                                            <span class="d-block text-muted fw-normal" style="font-size:0.72rem;"><?= e($perm['description'] ?? '') ?></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer border-top py-2">
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <?php if ($role['slug'] !== 'super_admin'): ?>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Save Permissions Matrix</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Full Granular Permission Matrix Table -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-surface">
    <h5 class="font-heading fw-bold mb-3 text-primary-dark"><i class="bi bi-table me-2"></i> Comprehensive Permissions Matrix</h5>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th style="width:30%;">Permission / Capability</th>
                    <th class="text-center">Super Admin</th>
                    <th class="text-center">Admin</th>
                    <th class="text-center">Instructor</th>
                    <th class="text-center">Student</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions as $module => $modPerms): ?>
                    <tr class="table-secondary">
                        <td colspan="5" class="fw-bold text-uppercase" style="font-size:0.75rem; letter-spacing:0.5px;">
                            <i class="bi bi-folder-fill me-1 text-primary"></i> <?= e(ucfirst($module)) ?> Module
                        </td>
                    </tr>
                    <?php foreach ($modPerms as $perm): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= e($perm['name']) ?></div>
                                <code class="text-muted" style="font-size:0.7rem;"><?= e($perm['slug']) ?></code>
                            </td>
                            <!-- Super Admin -->
                            <td class="text-center text-success fs-6"><i class="bi bi-check-circle-fill"></i></td>
                            
                            <!-- Admin -->
                            <td class="text-center">
                                <?php if (in_array($perm['slug'], ['users.view', 'users.create', 'users.edit', 'cohorts.manage', 'enrollments.manage', 'certificates.manage', 'finance.view'])): ?>
                                    <i class="bi bi-check-circle-fill text-success fs-6"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash text-muted"></i>
                                <?php endif; ?>
                            </td>

                            <!-- Instructor -->
                            <td class="text-center">
                                <?php if (in_array($perm['slug'], ['courses.create', 'courses.edit', 'quiz.manage'])): ?>
                                    <i class="bi bi-check-circle-fill text-success fs-6"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash text-muted"></i>
                                <?php endif; ?>
                            </td>

                            <!-- Student -->
                            <td class="text-center">
                                <?php if (in_array($perm['slug'], ['courses.learn', 'certificates.view'])): ?>
                                    <i class="bi bi-check-circle-fill text-success fs-6"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash text-muted"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
