<?php
/**
 * Proper Computer - Direct Admission Entry
 */
$pageTitle = "Direct Student Admission";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$courses = $pdo->query("SELECT id, name, fee FROM courses WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$employees = $pdo->query("SELECT id, full_name FROM employees WHERE status = 'Active'")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $studentName = trim($_POST['student_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $courseId = (int)($_POST['course_id'] ?? 0);
    $batchTime = trim($_POST['batch_time'] ?? '10:00 am-11:00 am');
    $track = $_POST['track'] ?? 'Daily';
    $assignedTeacher = !empty($_POST['assigned_teacher']) ? (int)$_POST['assigned_teacher'] : null;
    $totalFees = (float)($_POST['total_fees'] ?? 0);
    $discount = (float)($_POST['discount'] ?? 0);
    $initialPayment = (float)($_POST['initial_payment'] ?? 0);
    $paymentMode = $_POST['payment_mode'] ?? 'Cash';
    $referenceNo = trim($_POST['reference_no'] ?? '');
    $admissionDate = $_POST['admission_date'] ?? date('Y-m-d');

    $netFees = max(0, $totalFees - $discount);
    $paidFees = min($netFees, max(0, $initialPayment));
    $dueFees = max(0, $netFees - $paidFees);

    if (empty($studentName) || empty($phone) || empty($courseId)) {
        $error = "Student name, mobile number and course are required.";
    } else {
        try {
            $pdo->beginTransaction();

            // Check or Create Student
            $chkStudent = $pdo->prepare("SELECT id, admission_no FROM students WHERE phone = ? LIMIT 1");
            $chkStudent->execute([$phone]);
            $studentRow = $chkStudent->fetch();

            if ($studentRow) {
                $studentId = $studentRow['id'];
                $admissionNo = $studentRow['admission_no'];
            } else {
                $admissionNo = generate_admission_no($pdo);
                $insStudent = $pdo->prepare("INSERT INTO students 
                    (admission_no, full_name, email, phone, status) 
                    VALUES (?, ?, ?, ?, 'Active')");
                $insStudent->execute([$admissionNo, $studentName, $email, $phone]);
                $studentId = $pdo->lastInsertId();
            }

            // Insert Admission
            $insAdm = $pdo->prepare("INSERT INTO admissions 
                (admission_no, student_id, course_id, batch_time, track, total_fees, discount, net_fees, paid_fees, due_fees, admission_date, status, assigned_teacher) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Ongoing', ?)");
            $insAdm->execute([$admissionNo, $studentId, $courseId, $batchTime, $track, $totalFees, $discount, $netFees, $paidFees, $dueFees, $admissionDate, $assignedTeacher]);
            $admissionId = $pdo->lastInsertId();

            // Record Initial Payment & Receipt
            $receiptNo = null;
            if ($paidFees > 0) {
                $receiptNo = generate_receipt_no($pdo);
                $user = current_user();

                $insPay = $pdo->prepare("INSERT INTO fee_payments 
                    (admission_id, receipt_no, amount, payment_mode, reference_no, payment_date, remarks, collected_by) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Direct Admission Down Payment', ?)");
                $insPay->execute([$admissionId, $receiptNo, $paidFees, $paymentMode, $referenceNo, $admissionDate, $user['id']]);
                $paymentId = $pdo->lastInsertId();

                $insRec = $pdo->prepare("INSERT INTO receipts 
                    (receipt_no, payment_id, admission_id, receipt_date, amount, payment_mode, total_course_fee, fee_paid_so_far, remaining_due) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insRec->execute([$receiptNo, $paymentId, $admissionId, $admissionDate, $paidFees, $paymentMode, $netFees, $paidFees, $dueFees]);
            }

            $pdo->commit();

            log_activity(current_user()['id'], 'Admission Created', 'Admissions', $admissionId, "Direct student admission {$admissionNo} completed");

            if ($receiptNo) {
                set_flash('success', "Admission {$admissionNo} registered! Fee receipt {$receiptNo} generated.");
                header('Location: receipt-print.php?receipt_no=' . urlencode($receiptNo));
            } else {
                set_flash('success', "Admission {$admissionNo} registered successfully.");
                header('Location: admissions.php');
            }
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to record admission: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <h2>Direct Student Admission</h2>
    <p>Enroll student directly into a course, configure fee installments, and generate receipt.</p>
</div>

<div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>

        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-user-circle text-primary me-2"></i>Personal Information</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Student Full Name <span class="text-danger">*</span></label>
                <input type="text" name="student_name" class="form-control" placeholder="e.g. Rashi Biturwar" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@domain.com">
            </div>
        </div>

        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-chalkboard text-primary me-2"></i>Batch &amp; Course Selection</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                <select name="course_id" id="courseSelect" class="form-select" required onchange="updateCourseFee(this)">
                    <option value="">Select Course...</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" data-fee="<?= $c['fee'] ?>"><?= e($c['name']) ?> (<?= format_currency($c['fee']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Batch Time</label>
                <input type="text" name="batch_time" class="form-control" value="10:00 am-11:00 am">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Track</label>
                <select name="track" class="form-select">
                    <option value="Daily" selected>Daily</option>
                    <option value="Alternate">Alternate</option>
                    <option value="Weekend">Weekend</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Assigned Faculty</label>
                <select name="assigned_teacher" class="form-select">
                    <option value="">Choose Teacher</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>"><?= e($emp['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="fas fa-money-check-alt text-primary me-2"></i>Fee Payment Details</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Total Course Fees (₹)</label>
                <input type="number" step="0.01" name="total_fees" id="totalFeesInput" class="form-control" value="15000.00" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Discount / Waiver (₹)</label>
                <input type="number" step="0.01" name="discount" class="form-control" value="0.00">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Down Payment (₹)</label>
                <input type="number" step="0.01" name="initial_payment" class="form-control" value="5000.00">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Payment Mode</label>
                <select name="payment_mode" class="form-select">
                    <option value="Cash">Cash</option>
                    <option value="UPI" selected>UPI</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Card">Card</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Reference / UTR Number</label>
                <input type="text" name="reference_no" class="form-control" placeholder="UPI ref no or cheque number">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Admission Date</label>
                <input type="date" name="admission_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-pc-primary px-4 py-2">
                <i class="fas fa-check-circle me-1"></i> Register Student Admission
            </button>
            <a href="admissions.php" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
        </div>
    </form>
</div>

<script>
function updateCourseFee(select) {
    const opt = select.options[select.selectedIndex];
    const fee = opt.getAttribute('data-fee');
    if (fee) {
        document.getElementById('totalFeesInput').value = fee;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
