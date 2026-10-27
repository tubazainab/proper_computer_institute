<?php
/**
 * Proper Computer - Admission Details & Student Ledger
 */
$pageTitle = "Admission Ledger";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$id = (int)($_GET['id'] ?? 0);

// Status change handler
if (isset($_POST['update_status'])) {
    verify_csrf();
    $newStatus = $_POST['new_status'] ?? 'Ongoing';
    $pdo->prepare("UPDATE admissions SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
    set_flash('success', "Admission status changed to {$newStatus}.");
    header("Location: admission-view.php?id={$id}");
    exit;
}

$stmt = $pdo->prepare("SELECT a.*, s.full_name as student_name, s.phone as student_phone, s.email as student_email, s.address, 
                              c.name as course_name, c.duration, c.code as course_code, 
                              emp.full_name as teacher_name 
                       FROM admissions a 
                       JOIN students s ON a.student_id = s.id 
                       JOIN courses c ON a.course_id = c.id 
                       LEFT JOIN employees emp ON a.assigned_teacher = emp.id 
                       WHERE a.id = ?");
$stmt->execute([$id]);
$admission = $stmt->fetch();

if (!$admission) {
    set_flash('danger', 'Admission record not found.');
    header('Location: admissions.php');
    exit;
}

// Fetch payment transactions
$payStmt = $pdo->prepare("SELECT fp.*, u.full_name as collected_by_name 
                          FROM fee_payments fp 
                          LEFT JOIN users u ON fp.collected_by = u.id 
                          WHERE fp.admission_id = ? 
                          ORDER BY fp.payment_date ASC, fp.id ASC");
$payStmt->execute([$id]);
$payments = $payStmt->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <h2>Student Admission Ledger</h2>
    <p>Form No: <strong><?= e($admission['admission_no']) ?></strong> &bull; Student: <strong><?= e($admission['student_name']) ?></strong></p>
</div>

<div class="row g-4 mb-4">
    <!-- Student & Course Details -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 p-4 shadow-sm mb-4" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="badge bg-primary-subtle text-primary fw-bold mb-1"><?= e($admission['course_code']) ?></span>
                    <h4 class="fw-bold text-dark mb-1"><?= e($admission['course_name']) ?></h4>
                    <span class="text-muted small"><i class="fas fa-chalkboard-teacher me-1"></i> Faculty: <strong><?= e($admission['teacher_name'] ?? 'Unassigned') ?></strong></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge <?= ($admission['status'] === 'Complete') ? 'bg-success' : 'bg-primary' ?> px-3 py-2 rounded-pill">
                        <?= e($admission['status']) ?>
                    </span>
                    <form method="POST" action="" class="d-inline">
                        <?= csrf_field() ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Change Status
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><button class="dropdown-item small" type="submit" name="update_status" value="1" onclick="this.form.new_status.value='Ongoing'">Mark Ongoing</button></li>
                                <li><button class="dropdown-item small text-success fw-bold" type="submit" name="update_status" value="1" onclick="this.form.new_status.value='Complete'">Mark Complete</button></li>
                                <li><button class="dropdown-item small text-danger" type="submit" name="update_status" value="1" onclick="this.form.new_status.value='Cancel'">Mark Cancelled</button></li>
                            </ul>
                            <input type="hidden" name="new_status" value="Ongoing">
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 py-3 border-top border-bottom bg-light rounded-3 px-2 mb-3">
                <div class="col-sm-6 col-md-3">
                    <small class="text-muted d-block">Batch Time</small>
                    <strong class="text-dark"><?= e($admission['batch_time']) ?></strong>
                </div>
                <div class="col-sm-6 col-md-3">
                    <small class="text-muted d-block">Track</small>
                    <strong class="text-dark"><?= e($admission['track']) ?></strong>
                </div>
                <div class="col-sm-6 col-md-3">
                    <small class="text-muted d-block">Admission Date</small>
                    <strong class="text-dark"><?= format_date($admission['admission_date']) ?></strong>
                </div>
                <div class="col-sm-6 col-md-3">
                    <small class="text-muted d-block">Course Duration</small>
                    <strong class="text-dark"><?= e($admission['duration']) ?></strong>
                </div>
            </div>

            <h6 class="fw-bold text-dark mb-2">Student Profile Information</h6>
            <div class="row g-2 small text-muted">
                <div class="col-md-6"><i class="fas fa-phone text-primary me-2"></i><strong>Mobile:</strong> <?= e($admission['student_phone']) ?></div>
                <div class="col-md-6"><i class="fas fa-envelope text-info me-2"></i><strong>Email:</strong> <?= e($admission['student_email'] ?? 'Not provided') ?></div>
                <div class="col-12"><i class="fas fa-map-marker-alt text-danger me-2"></i><strong>Address:</strong> <?= e($admission['address'] ?? 'Not recorded') ?></div>
            </div>
        </div>

        <!-- Payment History Ledger -->
        <div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-receipt text-primary me-2"></i>Fee Payment Transactions</h5>
                <?php if ($admission['due_fees'] > 0): ?>
                    <a href="fee-collect.php?admission_id=<?= $admission['id'] ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i> Collect Fees
                    </a>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>RECEIPT NO</th>
                            <th>DATE</th>
                            <th>AMOUNT</th>
                            <th>MODE</th>
                            <th>REFERENCE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No fee payments recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td><strong><?= e($pay['receipt_no']) ?></strong></td>
                                    <td><?= format_date($pay['payment_date']) ?></td>
                                    <td class="text-success fw-bold"><?= format_currency($pay['amount']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= e($pay['payment_mode']) ?></span></td>
                                    <td><small class="text-muted"><?= e($pay['reference_no'] ?? '-') ?></small></td>
                                    <td>
                                        <a href="receipt-print.php?receipt_no=<?= urlencode($pay['receipt_no']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-print me-1"></i> Print Receipt
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Financial Balance Summary Card -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 p-4 shadow-sm bg-white" style="border:1px solid var(--admin-border)!important;">
            <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Financial Breakdown</h5>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Total Course Fee:</span>
                <strong><?= format_currency($admission['total_fees']) ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Discount / Waiver:</span>
                <span class="text-success">- <?= format_currency($admission['discount']) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-3 pt-2 border-top">
                <span class="fw-bold text-dark">Net Billed Tuition:</span>
                <strong class="text-dark fs-5"><?= format_currency($admission['net_fees']) ?></strong>
            </div>

            <hr>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Total Paid So Far:</span>
                <span class="text-success fw-bold fs-5"><?= format_currency($admission['paid_fees']) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-4">
                <span class="text-muted">Remaining Balance Due:</span>
                <span class="fw-bold fs-5 <?= ($admission['due_fees'] > 0) ? 'text-danger' : 'text-success' ?>">
                    <?= format_currency($admission['due_fees']) ?>
                </span>
            </div>

            <?php if ($admission['due_fees'] > 0): ?>
                <a href="fee-collect.php?admission_id=<?= $admission['id'] ?>" class="btn btn-success w-100 py-3 fw-bold">
                    <i class="fas fa-hand-holding-usd me-1"></i> Collect Pending Fees
                </a>
            <?php else: ?>
                <div class="alert alert-success text-center mb-0 py-2 small fw-bold">
                    <i class="fas fa-check-double me-1"></i> Full Tuition Cleared
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
