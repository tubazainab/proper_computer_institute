<?php
/**
 * Proper Computer - Fee Collection Ledger
 */
$pageTitle = "Fee Collections";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$modeFilter = $_GET['mode'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT fp.*, a.admission_no, a.batch_time, s.full_name as student_name, s.phone as student_phone, 
               c.name as course_name, u.full_name as collector_name 
        FROM fee_payments fp 
        JOIN admissions a ON fp.admission_id = a.id 
        JOIN students s ON a.student_id = s.id 
        JOIN courses c ON a.course_id = c.id 
        LEFT JOIN users u ON fp.collected_by = u.id 
        WHERE 1=1";
$params = [];

if (!empty($startDate)) {
    $sql .= " AND fp.payment_date >= ?";
    $params[] = $startDate;
}
if (!empty($endDate)) {
    $sql .= " AND fp.payment_date <= ?";
    $params[] = $endDate;
}
if (!empty($modeFilter)) {
    $sql .= " AND fp.payment_mode = ?";
    $params[] = $modeFilter;
}
if (!empty($search)) {
    $sql .= " AND (s.full_name LIKE ? OR s.phone LIKE ? OR fp.receipt_no LIKE ? OR c.name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY fp.payment_date DESC, fp.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

$totalCollection = 0;
foreach ($payments as $p) {
    $totalCollection += $p['amount'];
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Fee Collections</h2>
    <p>Real-time audit log of student tuition installment collections and payment channels.</p>
</div>

<div class="filter-toolbar">
    <form method="GET" action="" class="row g-3 align-items-center">
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= e($startDate) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= e($endDate) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Payment Mode</label>
            <select name="mode" class="form-select">
                <option value="">All Modes</option>
                <option value="Cash" <?= ($modeFilter === 'Cash') ? 'selected' : '' ?>>Cash</option>
                <option value="UPI" <?= ($modeFilter === 'UPI') ? 'selected' : '' ?>>UPI</option>
                <option value="Bank Transfer" <?= ($modeFilter === 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                <option value="Card" <?= ($modeFilter === 'Card') ? 'selected' : '' ?>>Card</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-search-green flex-fill">
                <i class="fas fa-search me-1"></i> Search
            </button>
            <a href="fees.php" class="btn btn-clear-orange">
                <i class="fas fa-rotate me-1"></i> Clear
            </a>
            <button type="button" class="btn btn-export-purple" id="exportCsvBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </form>
</div>

<div class="admin-card-table">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">Show entries:</span>
            <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Filter in table..." style="width: 250px;">
        </div>
        <div>
            <strong class="text-success fs-5">Total: <?= format_currency($totalCollection) ?></strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>RECEIPT NO</th>
                    <th>DATE</th>
                    <th>STUDENT NAME</th>
                    <th>COURSE</th>
                    <th>PAYMENT MODE</th>
                    <th>AMOUNT</th>
                    <th>COLLECTED BY</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No fee payment transactions found</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $pay): ?>
                        <tr>
                            <td><strong><?= e($pay['receipt_no']) ?></strong></td>
                            <td><?= format_date($pay['payment_date']) ?></td>
                            <td>
                                <span class="table-student-name"><?= e($pay['student_name']) ?></span>
                                <span class="table-student-phone"><?= e($pay['student_phone']) ?></span>
                            </td>
                            <td><?= e($pay['course_name']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= e($pay['payment_mode']) ?></span></td>
                            <td class="fw-bold text-success fs-6"><?= format_currency($pay['amount']) ?></td>
                            <td><small class="text-muted"><?= e($pay['collector_name'] ?? 'Staff') ?></small></td>
                            <td>
                                <a href="receipt-print.php?receipt_no=<?= urlencode($pay['receipt_no']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
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

<div class="table-summary-grid">
    <div class="summary-box">
        Total Filtered Collections: <?= format_currency($totalCollection) ?> (<?= count($payments) ?> Transactions)
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
