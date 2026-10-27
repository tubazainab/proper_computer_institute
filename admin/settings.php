<?php
/**
 * Proper Computer - Website & Institute Settings
 */
$pageTitle = "Website Settings";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    verify_csrf();

    $settings = [
        'institute_name' => trim($_POST['institute_name'] ?? 'Proper Computer'),
        'institute_tagline' => trim($_POST['institute_tagline'] ?? ''),
        'institute_address' => trim($_POST['institute_address'] ?? ''),
        'institute_phone' => trim($_POST['institute_phone'] ?? ''),
        'institute_email' => trim($_POST['institute_email'] ?? ''),
        'institute_website' => trim($_POST['institute_website'] ?? ''),
        'currency_symbol' => trim($_POST['currency_symbol'] ?? '₹'),
        'facebook_url' => trim($_POST['facebook_url'] ?? ''),
        'instagram_url' => trim($_POST['instagram_url'] ?? ''),
        'linkedin_url' => trim($_POST['linkedin_url'] ?? '')
    ];

    foreach ($settings as $k => $v) {
        $stmt = $pdo->prepare("INSERT INTO website_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$k, $v, $v]);
    }

    set_flash('success', 'Institute settings updated successfully.');
    header('Location: settings.php');
    exit;
}

// Fetch all settings
$allSettings = [];
$rows = $pdo->query("SELECT setting_key, setting_value FROM website_settings")->fetchAll();
foreach ($rows as $r) {
    $allSettings[$r['setting_key']] = $r['setting_value'];
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Website &amp; Institute Settings</h2>
    <p>Configure institute branding, contact details, receipt currencies, and public website metadata.</p>
</div>

<div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
    <form method="POST" action="">
        <?= csrf_field() ?>

        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-university text-primary me-2"></i>Institute Branding</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Institute Name <span class="text-danger">*</span></label>
                <input type="text" name="institute_name" class="form-control" value="<?= e($allSettings['institute_name'] ?? 'Proper Computer') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Institute Tagline</label>
                <input type="text" name="institute_tagline" class="form-control" value="<?= e($allSettings['institute_tagline'] ?? 'Premier IT, Coding & Management Institute') ?>">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Physical Campus Address</label>
                <textarea name="institute_address" rows="2" class="form-control"><?= e($allSettings['institute_address'] ?? INSTITUTE_ADDRESS) ?></textarea>
            </div>
        </div>

        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-address-book text-primary me-2"></i>Official Contact Channels</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Phone Numbers</label>
                <input type="text" name="institute_phone" class="form-control" value="<?= e($allSettings['institute_phone'] ?? INSTITUTE_PHONE) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Official Email</label>
                <input type="email" name="institute_email" class="form-control" value="<?= e($allSettings['institute_email'] ?? INSTITUTE_EMAIL) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Website Domain</label>
                <input type="text" name="institute_website" class="form-control" value="<?= e($allSettings['institute_website'] ?? 'www.propercomputer.com') ?>">
            </div>
        </div>

        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-share-alt text-primary me-2"></i>Social Media Links</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Facebook URL</label>
                <input type="url" name="facebook_url" class="form-control" value="<?= e($allSettings['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/...">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Instagram URL</label>
                <input type="url" name="instagram_url" class="form-control" value="<?= e($allSettings['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/...">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">LinkedIn URL</label>
                <input type="url" name="linkedin_url" class="form-control" value="<?= e($allSettings['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/...">
            </div>
        </div>

        <button type="submit" name="save_settings" value="1" class="btn btn-pc-primary px-4 py-2 fw-bold">
            <i class="fas fa-save me-1"></i> Save Institute Settings
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
