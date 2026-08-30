<div class="bg-primary-dark text-white py-5" style="background: linear-gradient(135deg, #1E1301, #4C3103);">
    <div class="container py-3">
        <h1 class="font-heading text-white fw-bold display-5 mb-2">Get in Touch with Our Team</h1>
        <p class="text-light opacity-80 max-w-700">Have questions about our training programs, corporate workshops, or enrollment?</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-6">
            <div class="card p-4 p-lg-5 border-0 shadow-sm rounded-4">
                <h4 class="font-heading mb-4">Send Us a Message</h4>
                <form action="<?= url('contact/send') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Eric Manzi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. eric@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="e.g. +250 788 123 456">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Your Inquiry</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="How can Beyond Barista Academy help your career or business?" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Send Message</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="d-flex flex-column gap-4">
                <div class="card p-4 border-0 shadow-sm rounded-4">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="stat-icon primary"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <h6 class="font-heading mb-1">Campus Location</h6>
                            <p class="text-muted small mb-0">KG 11 Ave, Kigali Innovation Hub, Kigali, Rwanda</p>
                        </div>
                    </div>
                </div>

                <div class="card p-4 border-0 shadow-sm rounded-4">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="stat-icon success"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <h6 class="font-heading mb-1">Direct Phone & WhatsApp</h6>
                            <p class="text-muted small mb-0">+250 788 000 111 / +250 788 123 456</p>
                        </div>
                    </div>
                </div>

                <div class="card p-4 border-0 shadow-sm rounded-4">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="stat-icon accent"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <h6 class="font-heading mb-1">Email Support</h6>
                            <p class="text-muted small mb-0">info@beyondbarista.rw</p>
                        </div>
                    </div>
                </div>

                <div class="card p-4 border-0 shadow-sm rounded-4">
                    <div class="d-flex gap-3 align-items-center">
                        <div class="stat-icon warning"><i class="bi bi-clock-fill"></i></div>
                        <div>
                            <h6 class="font-heading mb-1">Academy Office Hours</h6>
                            <p class="text-muted small mb-0">Monday – Saturday: 8:00 AM – 6:00 PM (CAT)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
