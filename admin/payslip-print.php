<?php
/**
 * Proper Computer - Printable Employee Payslip
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT sr.*, e.employee_id, e.full_name as employee_name, e.phone, e.email, 
                              e.role, e.designation, e.qualification, e.joining_date 
                       FROM salary_records sr 
                       JOIN employees e ON sr.employee_id = e.id 
                       WHERE sr.id = ?");
$stmt->execute([$id]);
$slip = $stmt->fetch();

if (!$slip) {
    set_flash('danger', 'Payslip record not found.');
    header('Location: salaries.php');
    exit;
}

$monthName = date('F', mktime(0, 0, 0, $slip['month'], 10));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip_<?= e($slip['employee_id']) ?>_<?= $monthName ?>_<?= $slip['year'] ?> | <?= APP_NAME ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/print.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
    </style>
</head>
<body>

<div class="no-print py-3 bg-white border-bottom shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="salaries.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Payslips
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold">
            <i class="fas fa-print me-1"></i> Print / Save PDF
        </button>
    </div>
</div>

<div class="container print-container">
    <div class="payslip-box">
        <div class="doc-watermark"><?= APP_NAME ?></div>

        <div class="doc-header">
            <div class="d-flex align-items-center gap-3">
                <img src="<?= ASSETS_URL ?>/images/logo.svg" alt="<?= APP_NAME ?>" style="height: 52px;">
                <div>
                    <h3 class="fw-bold mb-0" style="font-family:'Outfit',sans-serif; color:#1e1b4b;">PROPER COMPUTER</h3>
                    <small class="text-muted d-block"><?= e(INSTITUTE_ADDRESS) ?></small>
                    <small class="text-muted">Phone: <?= e(INSTITUTE_PHONE) ?> &bull; Email: <?= e(INSTITUTE_EMAIL) ?></small>
                </div>
            </div>
            <div class="text-end">
                <div class="doc-badge">PAYSLIP</div>
                <div class="small fw-bold text-dark mt-2"><?= $monthName ?>, <?= $slip['year'] ?></div>
            </div>
        </div>

        <!-- Employee Info Grid -->
        <div class="doc-details-grid">
            <div>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Employee Name:</td>
                        <td class="fw-bold text-dark"><?= e($slip['employee_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Employee ID:</td>
                        <td class="fw-bold text-primary"><?= e($slip['employee_id']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Designation:</td>
                        <td><?= e($slip['designation']) ?> (<?= e($slip['role']) ?>)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Joining Date:</td>
                        <td><?= format_date($slip['joining_date']) ?></td>
                    </tr>
                </table>
            </div>

            <div>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Payment Date:</td>
                        <td class="fw-bold"><?= format_date($slip['payment_date']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Working Days:</td>
                        <td><?= $slip['working_days'] ?> Days</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Days Attended:</td>
                        <td class="fw-bold text-success"><?= $slip['attended_days'] ?> Days</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Mode:</td>
                        <td><span class="badge bg-light text-dark border"><?= e($slip['payment_mode']) ?></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Salary Earnings & Deductions Table -->
        <div class="row g-0 border rounded overflow-hidden mb-4">
            <div class="col-6 border-end">
                <div class="p-2 bg-light fw-bold text-dark border-bottom">EARNINGS</div>
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="py-2">Basic Remuneration:</td>
                        <td class="text-end py-2 fw-semibold"><?= format_currency($slip['basic_salary']) ?></td>
                    </tr>
                    <tr>
                        <td class="py-2">Performance &amp; Special Allowance:</td>
                        <td class="text-end py-2 fw-semibold"><?= format_currency($slip['allowances']) ?></td>
                    </tr>
                    <tr class="bg-light">
                        <td class="fw-bold">Total Gross Earnings:</td>
                        <td class="text-end fw-bold text-dark"><?= format_currency($slip['basic_salary'] + $slip['allowances']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="col-6">
                <div class="p-2 bg-light fw-bold text-dark border-bottom">DEDUCTIONS</div>
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="py-2">Leave / Absence Deductions:</td>
                        <td class="text-end py-2 fw-semibold"><?= format_currency($slip['deductions']) ?></td>
                    </tr>
                    <tr>
                        <td class="py-2">TDS / Professional Tax:</td>
                        <td class="text-end py-2 fw-semibold">₹0.00</td>
                    </tr>
                    <tr class="bg-light">
                        <td class="fw-bold">Total Deductions:</td>
                        <td class="text-end fw-bold text-danger"><?= format_currency($slip['deductions']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Net Salary Bar -->
        <div class="d-flex justify-content-between align-items-center p-3 rounded bg-light border mb-4">
            <div>
                <small class="text-muted fw-bold d-block">TAKE HOME NET REMUNERATION</small>
                <span class="fs-4 fw-bold text-success"><?= format_currency($slip['net_salary']) ?></span>
            </div>
            <div class="text-end">
                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 border border-success-subtle">
                    STATUS: <?= strtoupper(e($slip['status'])) ?>
                </span>
            </div>
        </div>

        <div class="small text-muted mb-4" style="font-size: 0.78rem;">
            * This is a computerized monthly remuneration slip issued by Proper Computer Institute.
        </div>

        <!-- Signatures -->
        <div class="doc-signatures">
            <div class="signature-line">
                Employee Signature
            </div>
            <div class="signature-line">
                Director / Center Head<br>
                <strong>PROPER COMPUTER</strong>
            </div>
        </div>
    </div>
</div>

</body>
</html>
