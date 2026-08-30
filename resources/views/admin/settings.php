<?php $pageTitle = 'System Settings'; ?>
<div class="mb-4">
    <h2 class="font-heading fw-bold mb-1">System Settings</h2>
    <p class="text-muted small mb-0">Configure platform-wide settings and appearance</p>
</div>

<form action="<?= url('admin/settings/update') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs border-0 mb-4" id="settingsTabs">
        <li class="nav-item"><button class="nav-link active fw-bold" type="button" data-bs-toggle="tab" data-bs-target="#generalTab">General</button></li>
        <li class="nav-item"><button class="nav-link fw-bold" type="button" data-bs-toggle="tab" data-bs-target="#emailTab">Email</button></li>
        <li class="nav-item"><button class="nav-link fw-bold" type="button" data-bs-toggle="tab" data-bs-target="#paymentTab">Payments</button></li>
        <li class="nav-item"><button class="nav-link fw-bold" type="button" data-bs-toggle="tab" data-bs-target="#appearanceTab">Appearance</button></li>
    </ul>

    <div class="tab-content">
        <!-- General -->
        <div class="tab-pane fade show active" id="generalTab">
            <div class="card p-4 border-0 shadow-sm rounded-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Platform Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'Beyond Barista Academy') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Platform URL</label>
                        <input type="url" name="site_url" class="form-control" value="<?= e($settings['site_url'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Platform Tagline</label>
                        <input type="text" name="site_tagline" class="form-control" value="<?= e($settings['site_tagline'] ?? 'Rwanda\'s Premier Coffee & Hospitality Academy') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Platform Description (SEO)</label>
                        <textarea name="site_description" class="form-control" rows="3"><?= e($settings['site_description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Default Language</label>
                        <select name="default_language" class="form-select">
                            <option value="en" <?= ($settings['default_language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="fr" <?= ($settings['default_language'] ?? '') === 'fr' ? 'selected' : '' ?>>Français</option>
                            <option value="rw" <?= ($settings['default_language'] ?? '') === 'rw' ? 'selected' : '' ?>>Kinyarwanda</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Default Currency</label>
                        <select name="currency" class="form-select">
                            <option value="RWF" <?= ($settings['currency'] ?? 'RWF') === 'RWF' ? 'selected' : '' ?>>RWF – Rwandan Franc</option>
                            <option value="USD" <?= ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD – US Dollar</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="<?= e($settings['contact_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Contact Phone</label>
                        <input type="tel" name="contact_phone" class="form-control" value="<?= e($settings['contact_phone'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="tab-pane fade" id="emailTab">
            <div class="card p-4 border-0 shadow-sm rounded-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SMTP Host</label>
                        <input type="text" name="mail_host" class="form-control" value="<?= e($settings['mail_host'] ?? 'smtp.gmail.com') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">SMTP Port</label>
                        <input type="number" name="mail_port" class="form-control" value="<?= e($settings['mail_port'] ?? '587') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Encryption</label>
                        <select name="mail_encryption" class="form-select">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SMTP Username</label>
                        <input type="text" name="mail_username" class="form-control" value="<?= e($settings['mail_username'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SMTP Password</label>
                        <input type="password" name="mail_password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">From Name</label>
                        <input type="text" name="mail_from_name" class="form-control" value="<?= e($settings['mail_from_name'] ?? 'Beyond Barista Academy') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">From Email</label>
                        <input type="email" name="mail_from_address" class="form-control" value="<?= e($settings['mail_from_address'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments -->
        <div class="tab-pane fade" id="paymentTab">
            <div class="card p-4 border-0 shadow-sm rounded-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">Platform Fee (%)</label>
                        <input type="number" name="platform_fee_pct" class="form-control" value="<?= e($settings['platform_fee_pct'] ?? '30') ?>" min="0" max="100">
                        <small class="text-muted">Percentage withheld from each course sale for platform costs.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Flutterwave Public Key</label>
                        <input type="text" name="flw_public_key" class="form-control" value="<?= e($settings['flw_public_key'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Flutterwave Secret Key</label>
                        <input type="password" name="flw_secret_key" class="form-control" placeholder="Leave blank to keep">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">MTN MoMo API User</label>
                        <input type="text" name="momo_api_user" class="form-control" value="<?= e($settings['momo_api_user'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">MTN MoMo API Key</label>
                        <input type="password" name="momo_api_key" class="form-control" placeholder="Leave blank to keep">
                    </div>
                </div>
            </div>
        </div>

        <!-- Appearance -->
        <div class="tab-pane fade" id="appearanceTab">
            <div class="card p-4 border-0 shadow-sm rounded-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Primary Brand Color</label>
                        <input type="color" name="brand_primary" class="form-control form-control-color" value="<?= e($settings['brand_primary'] ?? '#4C3103') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Accent Color</label>
                        <input type="color" name="brand_accent" class="form-control form-control-color" value="<?= e($settings['brand_accent'] ?? '#E29578') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Site Logo</label>
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                        <?php if (!empty($settings['site_logo'])): ?>
                            <img src="<?= asset('uploads/' . e($settings['site_logo'])) ?>" alt="Logo" class="mt-2 rounded" style="height:48px;">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Favicon</label>
                        <input type="file" name="favicon" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary fw-bold px-5">Save All Settings</button>
    </div>
</form>
