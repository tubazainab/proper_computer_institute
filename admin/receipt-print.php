<?php
/**
 * Proper Computer - Printable Official Fee Receipt
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$receiptNo = $_GET['receipt_no'] ?? '';

$stmt = $pdo->prepare("SELECT r.*, a.admission_no, a.batch_time, a.track, a.discount, 
                              s.full_name as student_name, s.phone as student_phone, s.address as student_address, 
                              c.name as course_name, c.code as course_code, 
                              fp.reference_no, fp.remarks 
                       FROM receipts r 
                       JOIN admissions a ON r.admission_id = a.id 
                       JOIN students s ON a.student_id = s.id 
                       JOIN courses c ON a.course_id = c.id 
                       LEFT JOIN fee_payments fp ON r.payment_id = fp.id 
                       WHERE r.receipt_no = ?");
$stmt->execute([$receiptNo]);
$receipt = $stmt->fetch();

if (!$receipt) {
    set_flash('danger', 'Receipt not found.');
    header('Location: receipts.php');
    exit;
}

// Convert amount to words helper
function numberToWords($number) {
    $no = floor($number);
    $words = [
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    ];
    if ($no == 0) return 'Zero Rupees Only';
    $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
    
    // Simple converter for institutional receipts
    return 'Rupees ' . number_format($no) . ' Only';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt <?= e($receipt['receipt_no']) ?> | <?= APP_NAME ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/print.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
        }
    </style>
</head>
<body>

<!-- Action Bar (Hidden on print) -->
<div class="no-print py-3 bg-white border-bottom shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="receipts.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Receipts
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold">
                <i class="fas fa-print me-1"></i> Print / Save as PDF
            </button>
        </div>
    </div>
</div>

<div class="container print-container">
    <div class="receipt-box">
        <div class="doc-watermark"><?= APP_NAME ?></div>

        <!-- Header -->
        <div class="doc-header">
            <div class="d-flex align-items-center gap-3">
                <img src="<?= ASSETS_URL ?>/images/logo.svg" alt="<?= APP_NAME ?>" style="height: 52px;">
                <div>
                    <h3 class="fw-bold mb-0" style="font-family:'Outfit',sans-serif; color:#1e1b4b; letter-spacing:-0.5px;">PROPER COMPUTER</h3>
                    <small class="text-muted d-block"><?= e(INSTITUTE_ADDRESS) ?></small>
                    <small class="text-muted">Phone: <?= e(INSTITUTE_PHONE) ?> &bull; Email: <?= e(INSTITUTE_EMAIL) ?></small>
                </div>
            </div>
            <div class="text-end">
                <div class="doc-badge">FEE RECEIPT</div>
                <div class="small fw-bold text-dark mt-2">ORIGINAL STUDENT COPY</div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="doc-details-grid">
            <div>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Student Name:</td>
                        <td class="fw-bold text-dark"><?= e($receipt['student_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Admission / Form No:</td>
                        <td class="fw-bold text-primary"><?= e($receipt['admission_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Contact Mobile:</td>
                        <td><?= e($receipt['student_phone']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Enrolled Course:</td>
                        <td class="fw-bold"><?= e($receipt['course_name']) ?> (<?= e($receipt['course_code']) ?>)</td>
                    </tr>
                </table>
            </div>

            <div>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Receipt Number:</td>
                        <td class="fw-bold text-danger"><?= e($receipt['receipt_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Receipt Date:</td>
                        <td class="fw-bold"><?= format_date($receipt['receipt_date'], 'd-m-Y') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Batch Timing:</td>
                        <td><?= e($receipt['batch_time']) ?> (<?= e($receipt['track']) ?>)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Channel:</td>
                        <td><span class="badge bg-light text-dark border"><?= e($receipt['payment_mode']) ?></span> <?= e($receipt['reference_no'] ? '(' . $receipt['reference_no'] . ')' : '') ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Financial Statement Table -->
        <table class="doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Particulars &amp; Tuition Breakdown</th>
                    <th class="text-end">Amount (INR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <strong>Tuition Fee Installment &bull; <?= e($receipt['course_name']) ?></strong>
                        <div class="small text-muted"><?= e($receipt['remarks'] ?? 'Installment payment received with thanks.') ?></div>
                    </td>
                    <td class="text-end fw-bold text-dark fs-6"><?= format_currency($receipt['amount']) ?></td>
                </tr>
                <tr class="bg-light">
                    <td colspan="2" class="text-end fw-bold">TOTAL AMOUNT RECEIVED:</td>
                    <td class="text-end fw-bold text-success fs-5"><?= format_currency($receipt['amount']) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Amount In Words -->
        <div class="p-2 mb-3 bg-light border rounded small">
            <strong>Amount in Words:</strong> <?= numberToWords($receipt['amount']) ?>
        </div>

        <!-- Student Ledger Summary -->
        <div class="row g-2 p-3 bg-light rounded border mb-4 small">
            <div class="col-sm-4">
                <span class="text-muted d-block">Total Course Fee:</span>
                <strong><?= format_currency($receipt['total_course_fee']) ?></strong>
            </div>
            <div class="col-sm-4">
                <span class="text-muted d-block">Total Fees Paid To Date:</span>
                <strong class="text-success"><?= format_currency($receipt['fee_paid_so_far']) ?></strong>
            </div>
            <div class="col-sm-4">
                <span class="text-muted d-block">Remaining Balance Due:</span>
                <strong class="<?= ($receipt['remaining_due'] > 0) ? 'text-danger' : 'text-success' ?>">
                    <?= format_currency($receipt['remaining_due']) ?>
                </strong>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="small text-muted mb-4" style="font-size: 0.78rem; line-height: 1.4;">
            <strong>Terms &amp; Conditions:</strong><br>
            1. All fees paid are non-refundable and non-transferable under any circumstances.<br>
            2. Cheque payments are subject to realization.<br>
            3. Please retain this computerized receipt for issuance of final course certificate and examination enrollment.
        </div>

        <!-- Signatures -->
        <div class="doc-signatures">
            <div class="signature-line">
                Student Signature
            </div>
            <div class="signature-line">
                Authorized Signatory / Cashier<br>
                <strong>PROPER COMPUTER</strong>
            </div>
        </div>
    </div>
</div>

</body>
</html>
