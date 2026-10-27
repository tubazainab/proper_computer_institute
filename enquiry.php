<?php
/**
 * Proper Computer - Online Admission Enquiry Form
 */
$pageTitle = "Online Admission & Course Enquiry";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$pdo = getDBConnection();
$selectedCourseId = (int)($_GET['course_id'] ?? 0);

$courses = $pdo->query("SELECT id, name, fee, duration FROM courses WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $studentName = trim($_POST['student_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $courseId = (int)($_POST['course_id'] ?? 0);
    $mode = $_POST['mode'] ?? 'Hybrid';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($studentName) || empty($phone) || empty($courseId)) {
        $errorMsg = "Please fill in all mandatory fields (Name, Mobile, and Course).";
    } elseif (!preg_match('/^[0-9]{10}$/', preg_replace('/[^0-9]/', '', $phone))) {
        $errorMsg = "Please provide a valid 10-digit mobile number.";
    } else {
        try {
            $followupDate = date('Y-m-d', strtotime('+2 days'));
            $stmt = $pdo->prepare("INSERT INTO enquiries 
                (student_name, phone, email, course_id, mode, lead_source, lead_status, enquiry_date, follow_up_date, notes) 
                VALUES (?, ?, ?, ?, ?, 'Website', 'Interested', CURRENT_DATE(), ?, ?)");
            $stmt->execute([$studentName, $phone, $email, $courseId, $mode, $followupDate, $notes]);

            $enquiryId = $pdo->lastInsertId();
            $successMsg = "Thank you, {$studentName}! Your enquiry has been received. Our academic counsellor will contact you shortly.";
        } catch (Exception $e) {
            $errorMsg = "Failed to submit enquiry: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-5 bg-light border-bottom">
    <div class="container text-center">
        <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold mb-2">APPLY ONLINE</span>
        <h1 class="fw-bold mb-2" style="font-size: 2.5rem;">Online Admission &amp; Course Enquiry</h1>
        <p class="text-muted lead max-w-700 mx-auto">
            Take the first step towards your career goals. Fill in the form below and our counselor will assist you with batch schedules, curriculum details, and fee installments.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5">
                <?php if ($successMsg): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4">
                        <i class="fas fa-check-circle fs-2 me-3"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Enquiry Registered!</h5>
                            <div><?= e($successMsg) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($errorMsg): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= e($errorMsg) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrf_field() ?>

                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                        <i class="fas fa-user-edit text-primary me-2"></i> Student Details
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" class="form-control form-control-lg bg-light" placeholder="e.g. Rahul Sharma" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control form-control-lg bg-light" placeholder="10-digit mobile number" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control form-control-lg bg-light" placeholder="name@example.com">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                        <i class="fas fa-graduation-cap text-primary me-2"></i> Course &amp; Mode Preferences
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Interested Course <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select form-select-lg bg-light" required>
                                <option value="">-- Choose Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($selectedCourseId === (int)$c['id']) ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?> (<?= e($c['duration']) ?> - <?= format_currency($c['fee']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Learning Mode</label>
                            <select name="mode" class="form-select form-select-lg bg-light">
                                <option value="Offline">Offline (Classroom Lab)</option>
                                <option value="Online">Online (Live Interactive)</option>
                                <option value="Hybrid" selected>Hybrid (Classroom + Online)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Questions / Specific Batch Timing Preference</label>
                            <textarea name="notes" rows="3" class="form-control bg-light" placeholder="Tell us if you have a preferred time slot or questions about syllabus..."></textarea>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pc-primary btn-lg py-3 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i> Submit Course Enquiry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
