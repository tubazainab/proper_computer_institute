<?php
/**
 * Proper Computer - Add Enquiry Form
 */
$pageTitle = "Add New Enquiry";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$courses = $pdo->query("SELECT id, name, fee FROM courses WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$employees = $pdo->query("SELECT id, full_name, role FROM employees WHERE status = 'Active'")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $studentName = trim($_POST['student_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $courseId = (int)($_POST['course_id'] ?? 0);
    $mode = $_POST['mode'] ?? 'Offline';
    $leadSource = $_POST['lead_source'] ?? 'Walkin';
    $leadStatus = $_POST['lead_status'] ?? 'Walkin';
    $enquiryDate = $_POST['enquiry_date'] ?? date('Y-m-d');
    $followUpDate = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;
    $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $notes = trim($_POST['notes'] ?? '');

    if (empty($studentName) || empty($phone) || empty($courseId)) {
        $error = "Student name, phone and course are mandatory.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO enquiries 
                (student_name, phone, email, course_id, mode, lead_source, lead_status, enquiry_date, follow_up_date, assigned_to, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$studentName, $phone, $email, $courseId, $mode, $leadSource, $leadStatus, $enquiryDate, $followUpDate, $assignedTo, $notes]);

            set_flash('success', "Enquiry for {$studentName} registered successfully.");
            header('Location: enquiries.php');
            exit;
        } catch (Exception $e) {
            $error = "Failed to add enquiry: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <h2>Record New Enquiry</h2>
    <p>Log walk-in, telephonic or online prospective student enquiries.</p>
</div>

<div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Student Name <span class="text-danger">*</span></label>
                <input type="text" name="student_name" class="form-control" placeholder="e.g. Shafeen Sheikh" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                <input type="tel" name="phone" class="form-control" placeholder="10-digit phone" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="student@example.com">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Course Interested <span class="text-danger">*</span></label>
                <select name="course_id" class="form-select" required>
                    <option value="">Choose Course...</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= format_currency($c['fee']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Preferred Mode</label>
                <select name="mode" class="form-select">
                    <option value="Offline" selected>Offline</option>
                    <option value="Online">Online</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Lead Source</label>
                <select name="lead_source" class="form-select">
                    <option value="Walkin" selected>Walkin</option>
                    <option value="Telephonic">Telephonic</option>
                    <option value="Social Media">Social Media</option>
                    <option value="Website">Website</option>
                    <option value="Referral">Referral</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Enquiry Date</label>
                <input type="date" name="enquiry_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Next Follow-Up Date</label>
                <input type="date" name="follow_up_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Assigned Counselor</label>
                <select name="assigned_to" class="form-select">
                    <option value="">Unassigned</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>"><?= e($emp['full_name']) ?> (<?= e($emp['role']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Discussion Notes / Queries</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Discussion summary, discount requested, preferred timings..."></textarea>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-pc-primary px-4">
                <i class="fas fa-save me-1"></i> Save Enquiry
            </button>
            <a href="enquiries.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
