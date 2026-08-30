<?php $pageTitle = 'Blog Management'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="font-heading fw-bold mb-0">Blog Posts</h2>
    <a href="<?= url('admin/blog/create') ?>" class="btn btn-primary btn-sm fw-bold">
        <i class="bi bi-plus-circle me-1"></i> New Post
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Title</th><th>Author</th><th>Published</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <div class="fw-bold small"><?= e($post['title']) ?></div>
                            <small class="text-muted"><?= e(substr($post['excerpt'] ?? '', 0, 60)) ?>...</small>
                        </td>
                        <td class="small"><?= e($post['author_name']) ?></td>
                        <td class="text-muted small"><?= $post['published_at'] ? date('M d, Y', strtotime($post['published_at'])) : 'Not set' ?></td>
                        <td><span class="badge <?= $post['is_published'] ? 'bg-success' : 'bg-secondary' ?>"><?= $post['is_published'] ? 'PUBLISHED' : 'DRAFT' ?></span></td>
                        <td class="text-end">
                            <a href="<?= url('admin/blog/' . $post['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                            <form action="<?= url('admin/blog/' . $post['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete post?')">
                                <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
