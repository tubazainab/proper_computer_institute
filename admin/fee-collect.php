<?php
/**
 * Proper Computer - Collect Fees Form
 */
$pageTitle = "Collect Student Fees";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$admissionId = (int)($_GET['admission_id'] ?? 0);

$stmt = $pdo->prepare("SELECT a.*, s.full_name as student_name, s.phone as student_phone, 
                              c.name as course_name 
                       FROM admissions a 
                       JOIN students s ON a.student_id = s.id 
                       JOIN courses c ON a.course_id = c.id 
                       WHERE a.id = ?");
$stmt->execute([$admissionId]);
$admission = $stmt->fetch();

if (!$admission) {
    set_flash('danger', 'Admission record not found.');
    header('Location: admissions.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMode = $_POST['payment_mode'] ?? 'Cash';
    $referenceNo = trim($_POST['reference_no'] ?? '');
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $remarks = trim($_POST['remarks'] ?? 'Tuition Fee Installment');

    if ($amount <= 0) {
        $error = "Please enter a valid payment amount greater than zero.";
    } elseif ($amount > $admission['due_fees']) {
        $error = "Payment amount cannot exceed the pending balance of " . format_currency($admission['due_fees']) . ".";
    } else {
        try {
            $pdo->beginTransaction();

            $receiptNo = generate_receipt_no($pdo);
            $user = current_user();

            // 1. Record Payment
            $insPay = $pdo->prepare("INSERT INTO fee_payments 
                (admission_id, receipt_no, amount, payment_mode, reference_no, payment_date, remarks, collected_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insPay->execute([$admissionId, $receiptNo, $amount, $paymentMode, $referenceNo, $paymentDate, $remarks, $user['id']]);
            $paymentId = $pdo->lastInsertId();

            // 2. Calculate new totals
            $newPaid = $admission['paid_fees'] + $amount;
            $newDue = max(0, $admission['net_fees'] - $newPaid);

            // 3. Record Receipt
            $insRec = $pdo->prepare("INSERT INTO receipts 
                (receipt_no, payment_id, admission_id, receipt_date, amount, payment_mode, total_course_fee, fee_paid_so_far, remaining_due) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insRec->execute([$receiptNo, $paymentId, $admissionId, $paymentDate, $amount, $paymentMode, $admission['net_fees'], $newPaid, $newDue]);

            // 4. Update Admission record
            $updAdm = $pdo->prepare("UPDATE admissions SET paid_fees = ?, due_fees = ? WHERE id = ?");
            $updAdm->execute([$newPaid, $newDue, $admissionId]);

            $pdo->commit();

            log_activity($user['id'], 'Fee Collected', 'Fees', $paymentId, "Collected " . format_currency($amount) . " for Admission {$admission['admission_no']} (Receipt {$receiptNo})");

            set_flash('success', "Payment of " . format_currency($amount) . " recorded! Receipt {$receiptNo} generated.");
            header('Location: receipt-print.php?receipt_no=' . urlencode($receiptNo));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Payment recording failed: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <h2>Fee Collection</h2>
    <p>Record fee installment payment for <strong><?= e($admission['student_name']) ?></strong> (<?= e($admission['admission_no']) ?>)</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <?php if ($error): ?>
                <div class="alert alert-danger mb-4"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="p-3 bg-light rounded-3 mb-4 border">
                <div class="row g-2">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Student Name</small>
                        <strong class="text-dark fs-6"><?= e($admission['student_name']) ?></strong> (<?= e($admission['student_phone']) ?>)
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Course &amp; Batch</small>
                        <strong class="text-dark fs-6"><?= e($admission['course_name']) ?></strong> (<?= e($admission['batch_time']) ?>)
                    </div>
                    <div class="col-md-4 mt-2">
                        <small class="text-muted d-block">Net Course Fees</small>
                        <strong class="text-dark"><?= format_currency($admission['net_fees']) ?></strong>
                    </div>
                    <div class="col-md-4 mt-2">
                        <small class="text-muted d-block">Already Paid</small>
                        <strong class="text-success"><?= format_currency($admission['paid_fees']) ?></strong>
                    </div>
                    <div class="col-md-4 mt-2">
                        <small class="text-muted d-block">Current Due Balance</small>
                        <strong class="text-danger fs-5"><?= format_currency($admission['due_fees']) ?></strong>
                    </div>
                </div>
            </div>

            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Amount to Collect (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg" max="<?= $admission['due_fees'] ?>" value="<?= $admission['due_fees'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select form-select-lg">
                            <option value="Cash">Cash</option>
                            <option value="UPI" selected>UPI (GPay / PhonePe / Paytm)</option>
                            <option value="Bank Transfer">Bank Transfer (NEFT / IMPS)</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Transaction / UTR Reference No.</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="Optional transaction ID">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Payment Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="Course Installment Payment">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-lg px-4 py-2 fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Receive Payment &amp; Issue Receipt
                    </button>
                    <a href="admission-view.php?id=<?= $admissionId ?>" class="btn btn-outline-secondary btn-lg px-4 py-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
