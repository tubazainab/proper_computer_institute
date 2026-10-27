<?php
/**
 * Proper Computer - Student Portal Login
 */
$pageTitle = "Student Portal Login";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$error = '';

if (is_student_logged_in()) {
    header('Location: ' . BASE_URL . '/student-portal.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $loginId = trim($_POST['login_id'] ?? '');

    if (empty($loginId)) {
        $error = "Please enter your registered mobile number or admission number.";
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM students WHERE phone = ? OR admission_no = ? LIMIT 1");
        $stmt->execute([$loginId, $loginId]);
        $student = $stmt->fetch();

        if ($student) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_admission_no'] = $student['admission_no'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['student_email'] = $student['email'];
            $_SESSION['student_phone'] = $student['phone'];

            set_flash('success', "Welcome back, {$student['full_name']}!");
            header('Location: ' . BASE_URL . '/student-portal.php');
            exit;
        } else {
            $error = "No student record found matching '{$loginId}'. Please verify with the administration office.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-5" style="background: linear-gradient(135deg, #f8fafc 0%, #ede9fe 100%); min-height: 70vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                    <div class="text-center mb-4">
                        <div class="bg-primary-subtle text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 28px;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Student Portal</h3>
                        <p class="text-muted small">Access your enrolled courses, fee receipts and notices.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="fas fa-exclamation-circle me-1"></i> <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?= csrf_field() ?>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted">Registered Mobile / Admission No.</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-id-card text-muted"></i></span>
                                <input type="text" name="login_id" class="form-control bg-light border-start-0 py-2" placeholder="e.g. 7020031922 or PC-ADM-..." required autofocus>
                            </div>
                            <div class="form-text small">Try demo: <code>7020031922</code> or <code>8379066454</code></div>
                        </div>

                        <button type="submit" class="btn btn-pc-primary w-100 py-3 fw-bold mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i> Access Student Dashboard
                        </button>
                    </form>

                    <div class="text-center pt-3 border-top">
                        <a href="<?= BASE_URL ?>/enquiry.php" class="text-decoration-none small text-primary fw-semibold">New student? Submit an admission enquiry &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
