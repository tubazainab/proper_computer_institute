<?php
/**
 * Proper Computer - Student Dashboard
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';
require_student_login();

$student = current_student();
$pdo = getDBConnection();

// Fetch Student Admissions
$admStmt = $pdo->prepare("SELECT a.*, c.name as course_name, c.duration, c.level, c.code as course_code, 
                                 emp.full_name as teacher_name 
                          FROM admissions a 
                          JOIN courses c ON a.course_id = c.id 
                          LEFT JOIN employees emp ON a.assigned_teacher = emp.id 
                          WHERE a.student_id = ? 
                          ORDER BY a.admission_date DESC");
$admStmt->execute([$student['id']]);
$admissions = $admStmt->fetchAll();

// Fetch Fee Payments
$payStmt = $pdo->prepare("SELECT fp.*, c.name as course_name, a.admission_no 
                          FROM fee_payments fp 
                          JOIN admissions a ON fp.admission_id = a.id 
                          JOIN courses c ON a.course_id = c.id 
                          WHERE a.student_id = ? 
                          ORDER BY fp.payment_date DESC");
$payStmt->execute([$student['id']]);
$payments = $payStmt->fetchAll();

// Calculate total metrics
$totalFee = 0;
$totalPaid = 0;
$totalDue = 0;
foreach ($admissions as $adm) {
    $totalFee += $adm['net_fees'];
    $totalPaid += $adm['paid_fees'];
    $totalDue += $adm['due_fees'];
}

// Fetch active notices
$notices = $pdo->query("SELECT * FROM notices WHERE is_active = 1 AND target_audience IN ('All', 'Students') ORDER BY priority = 'Urgent' DESC, created_at DESC LIMIT 4")->fetchAll();

$pageTitle = "Student Portal — " . $student['full_name'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Student Header Banner -->
<div class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold mb-1">STUDENT PORTAL</span>
                <h2 class="fw-bold mb-0">Welcome, <?= e($student['full_name']) ?>!</h2>
                <div class="text-muted small mt-1">
                    <span class="me-3"><i class="fas fa-id-badge text-primary me-1"></i> Form No: <strong><?= e($student['admission_no']) ?></strong></span>
                    <span><i class="fas fa-phone text-muted me-1"></i> <?= e($student['phone']) ?></span>
                </div>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <?php display_flash(); ?>

    <!-- Summary Metrics -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border rounded-4 p-4 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">TOTAL BILLED FEES</span>
                    <i class="fas fa-file-invoice-dollar fs-4 text-primary"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark"><?= format_currency($totalFee) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border rounded-4 p-4 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">TOTAL FEES PAID</span>
                    <i class="fas fa-check-circle fs-4 text-success"></i>
                </div>
                <h3 class="fw-bold mb-0 text-success"><?= format_currency($totalPaid) ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border rounded-4 p-4 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">REMAINING BALANCE DUE</span>
                    <i class="fas fa-hourglass-half fs-4 text-danger"></i>
                </div>
                <h3 class="fw-bold mb-0 <?= ($totalDue > 0) ? 'text-danger' : 'text-muted' ?>"><?= format_currency($totalDue) ?></h3>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Enrolled Courses -->
        <div class="col-lg-8">
            <h4 class="fw-bold text-dark mb-4"><i class="fas fa-graduation-cap text-primary me-2"></i>My Enrolled Courses</h4>
            
            <?php if (empty($admissions)): ?>
                <div class="card p-4 text-center text-muted">No course admissions recorded yet.</div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3 mb-5">
                    <?php foreach ($admissions as $adm): ?>
                        <div class="card border rounded-4 p-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary fw-bold mb-1"><?= e($adm['course_code']) ?></span>
                                    <h5 class="fw-bold mb-1 text-dark"><?= e($adm['course_name']) ?></h5>
                                    <span class="text-muted small"><i class="fas fa-chalkboard-teacher me-1"></i> Trainer: <strong><?= e($adm['teacher_name'] ?? 'Faculty Assigned') ?></strong></span>
                                </div>
                                <span class="badge <?= ($adm['status'] === 'Complete') ? 'bg-success' : 'bg-primary' ?> px-3 py-2 rounded-pill">
                                    <?= e($adm['status']) ?>
                                </span>
                            </div>

                            <div class="row g-3 py-3 border-top border-bottom my-2 bg-light rounded-3 px-2">
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">Batch Time</small>
                                    <strong class="text-dark small"><?= e($adm['batch_time']) ?></strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">Track</small>
                                    <strong class="text-dark small"><?= e($adm['track']) ?></strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">Net Fees</small>
                                    <strong class="text-dark small"><?= format_currency($adm['net_fees']) ?></strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">Balance Due</small>
                                    <strong class="<?= ($adm['due_fees'] > 0) ? 'text-danger' : 'text-success' ?> small"><?= format_currency($adm['due_fees']) ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Payment Receipts History -->
            <h4 class="fw-bold text-dark mb-4"><i class="fas fa-receipt text-primary me-2"></i>Official Fee Payment Receipts</h4>
            <div class="card border rounded-4 shadow-sm overflow-hidden mb-5">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt No</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Course</th>
                                <th>Action</th>
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
                                        <td><?= e($pay['course_name']) ?></td>
                                        <td>
                                            <a href="<?= ADMIN_URL ?>/receipt-print.php?receipt_no=<?= urlencode($pay['receipt_no']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-print me-1"></i> Print
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

        <!-- Sidebar Notices -->
        <div class="col-lg-4">
            <div class="card border rounded-4 p-4 shadow-sm bg-white mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-bullhorn text-warning me-2"></i>Institute Notices</h5>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($notices as $nt): ?>
                        <div class="border-bottom pb-3">
                            <span class="badge <?= ($nt['priority'] === 'Urgent') ? 'bg-danger' : 'bg-primary' ?> mb-1 small"><?= e($nt['priority']) ?></span>
                            <h6 class="fw-bold text-dark mb-1"><?= e($nt['title']) ?></h6>
                            <p class="text-muted small mb-1"><?= e($nt['content']) ?></p>
                            <small class="text-secondary"><?= format_date($nt['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
