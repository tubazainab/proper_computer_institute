<?php
/**
 * Proper Computer - Print Receipt Management
 * Faithfully inspired by reference screenshot Transactions/Print Receipt.png
 */
$pageTitle = "Print Receipt";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

$sortBy = $_GET['sort'] ?? 'date_desc';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT r.*, s.full_name as student_name, s.phone as student_phone, 
               c.name as course_name, a.batch_time, a.net_fees as total_fees 
        FROM receipts r 
        JOIN admissions a ON r.admission_id = a.id 
        JOIN students s ON a.student_id = s.id 
        JOIN courses c ON a.course_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.full_name LIKE ? OR s.phone LIKE ? OR r.receipt_no LIKE ? OR c.name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($sortBy === 'amount_desc') {
    $sql .= " ORDER BY r.amount DESC";
} else {
    $sql .= " ORDER BY r.receipt_date DESC, r.id DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$receipts = $stmt->fetchAll();

// Calculate Summary Cards
$totalCollection = 0;
$totalBankCollection = 0;
$totalCashCollection = 0;

foreach ($receipts as $rcp) {
    $totalCollection += $rcp['amount'];
    if (in_array($rcp['payment_mode'], ['Bank Transfer', 'UPI', 'Card'])) {
        $totalBankCollection += $rcp['amount'];
    } elseif ($rcp['payment_mode'] === 'Cash') {
        $totalCashCollection += $rcp['amount'];
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Purple Header Banner (Matching Reference Screenshot) -->
<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Print Receipt</h2>
    <p>Generate, reprint and audit computer-generated Proper Computer fee receipts.</p>
</div>

<!-- Filter Toolbar -->
<div class="filter-toolbar">
    <form method="GET" action="" class="row g-3 align-items-center">
        <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Sort By:</label>
            <select name="sort" class="form-select">
                <option value="date_desc" <?= ($sortBy === 'date_desc') ? 'selected' : '' ?>>Receipt Date (Latest First)</option>
                <option value="amount_desc" <?= ($sortBy === 'amount_desc') ? 'selected' : '' ?>>Amount (Highest First)</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted mb-1">Search Receipt or Student:</label>
            <input type="text" name="search" class="form-control" placeholder="Search receipt no, student, phone..." value="<?= e($search) ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-search-green flex-fill">
                <i class="fas fa-search me-1"></i> Search
            </button>
            <a href="receipts.php" class="btn btn-clear-orange">
                <i class="fas fa-rotate me-1"></i> Clear
            </a>
            <button type="button" class="btn btn-export-purple" id="exportCsvBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </form>
</div>

<!-- Receipts Data Table (Exact Columns as Screenshot) -->
<div class="admin-card-table">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">Show</span>
            <select class="form-select form-select-sm" style="width: 80px;">
                <option>All</option>
                <option>10</option>
                <option>25</option>
            </select>
            <span class="small text-muted">entries</span>
        </div>
        <div>
            <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Live search in table..." style="width: 250px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>S. NO.</th>
                    <th>RECEIPT DATE</th>
                    <th>RECEIPT NO.</th>
                    <th>STUDENT NAME</th>
                    <th>PAYMENT MODE</th>
                    <th>AMOUNT</th>
                    <th>COURSE</th>
                    <th>BATCH</th>
                    <th>TOTAL FEES</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($receipts)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">No data available in table</td></tr>
                <?php else: ?>
                    <?php foreach ($receipts as $idx => $r): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= format_date($r['receipt_date'], 'Y-m-d') ?></td>
                            <td><strong class="text-primary"><?= e($r['receipt_no']) ?></strong></td>
                            <td>
                                <span class="table-student-name"><?= e($r['student_name']) ?></span>
                                <span class="table-student-phone"><?= e($r['student_phone']) ?></span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= e($r['payment_mode']) ?></span></td>
                            <td class="fw-bold text-success fs-6"><?= format_currency($r['amount']) ?></td>
                            <td><span class="small fw-semibold"><?= e($r['course_name']) ?></span></td>
                            <td><span class="small text-muted"><?= e($r['batch_time']) ?></span></td>
                            <td class="fw-bold text-dark"><?= format_currency($r['total_fees']) ?></td>
                            <td>
                                <a href="receipt-print.php?receipt_no=<?= urlencode($r['receipt_no']) ?>" target="_blank" class="action-btn text-primary" title="Print Fee Receipt">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bottom Summary Cards (Exact Disha Screenshot Style) -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="p-3 rounded-3 text-center border fw-bold text-success" style="background:#ecfdf5; border-color:#a7f3d0!important; font-size:1.1rem;">
            Total Collection: <?= format_currency($totalCollection, 2) ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 rounded-3 text-center border fw-bold text-primary" style="background:#ecfdf5; border-color:#a7f3d0!important; font-size:1.1rem;">
            Total Bank Collection: <?= format_currency($totalBankCollection, 2) ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 rounded-3 text-center border fw-bold text-success" style="background:#ecfdf5; border-color:#a7f3d0!important; font-size:1.1rem;">
            Total Cash Collection: <?= format_currency($totalCashCollection, 2) ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 rounded-3 text-center border fw-bold text-success" style="background:#ecfdf5; border-color:#a7f3d0!important; font-size:1.1rem;">
            Total Receipt: <?= count($receipts) ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
