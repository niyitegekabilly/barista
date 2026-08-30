<div class="bg-primary-dark text-white py-5" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-3">
        <h6 class="text-accent fw-bold text-uppercase tracking-wider">Community & Hands-On Workshops</h6>
        <h1 class="font-heading text-white fw-bold display-5 mb-2">Events, Masterclasses & Competitions</h1>
        <p class="text-light opacity-80 max-w-700">Join our hands-on sensory cupping sessions, barista bootcamps, and networking seminars in Kigali.</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($events as $ev): ?>
            <div class="col-lg-6">
                <div class="card h-100 p-4 border-0 shadow-sm rounded-4 card-hover-elevate">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-primary text-white"><?= strtoupper(e($ev['event_type'])) ?></span>
                        <span class="fw-bold <?= $ev['is_free'] ? 'text-success' : 'text-dark' ?>">
                            <?= $ev['is_free'] ? 'FREE ENTRY' : format_rwf($ev['price']) ?>
                        </span>
                    </div>

                    <h4 class="font-heading mb-2"><?= e($ev['title']) ?></h4>
                    <p class="text-muted small mb-4 flex-grow-1"><?= e($ev['description']) ?></p>

                    <div class="d-flex flex-column gap-2 small text-muted pt-3 border-top mb-4">
                        <div><i class="bi bi-calendar-event text-accent me-2"></i> <?= date('l, F d, Y @ H:i', strtotime($ev['start_date'])) ?></div>
                        <div><i class="bi bi-geo-alt text-accent me-2"></i> <?= e($ev['location']) ?></div>
                    </div>

                    <form action="<?= url('events/register/' . e($ev['id'])) ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Register for Event</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
