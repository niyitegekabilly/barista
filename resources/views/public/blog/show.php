<div class="bg-primary-dark text-white py-5" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-3">
        <span class="badge bg-warning text-dark px-3 py-1 fw-bold mb-3"><?= e($post['category_name']) ?></span>
        <h1 class="font-heading text-white fw-bold display-6 mb-3"><?= e($post['title']) ?></h1>
        <div class="d-flex align-items-center gap-4 text-light opacity-80 small">
            <span><i class="bi bi-person-fill me-1"></i> <?= e($post['author_name']) ?></span>
            <span><i class="bi bi-calendar3 me-1"></i> <?= date('F d, Y', strtotime($post['published_at'])) ?></span>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4 mb-5">
                <div class="blog-content lh-lg text-dark">
                    <?= $post['content'] ?>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="<?= url('blog') ?>" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i> Back to All Articles</a>
                <a href="<?= url('courses') ?>" class="btn btn-primary">Explore Academy Courses <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>
