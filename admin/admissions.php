<?php
/**
 * Proper Computer - Admissions Management
 * Faithfully inspired by reference screenshot Transactions/Admissions/All Admission.png
 */
$pageTitle = "Admissions Report";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Status Tab filter
$tab = $_GET['tab'] ?? 'all'; // ongoing, cancelled, completed, all

// Date filters
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT a.*, s.full_name as student_name, s.phone as student_phone, 
               c.name as course_name, emp.full_name as teacher_name 
        FROM admissions a 
        JOIN students s ON a.student_id = s.id 
        JOIN courses c ON a.course_id = c.id 
        LEFT JOIN employees emp ON a.assigned_teacher = emp.id 
        WHERE 1=1";
$params = [];

if ($tab === 'ongoing') {
    $sql .= " AND a.status = 'Ongoing'";
} elseif ($tab === 'cancelled') {
    $sql .= " AND a.status = 'Cancel'";
} elseif ($tab === 'completed') {
    $sql .= " AND a.status = 'Complete'";
}

if (!empty($startDate)) {
    $sql .= " AND a.admission_date >= ?";
    $params[] = $startDate;
}
if (!empty($endDate)) {
    $sql .= " AND a.admission_date <= ?";
    $params[] = $endDate;
}
if (!empty($search)) {
    $sql .= " AND (s.full_name LIKE ? OR s.phone LIKE ? OR a.admission_no LIKE ? OR c.name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY a.admission_date DESC, a.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$admissions = $stmt->fetchAll();

// Calculations for bottom summary cards
$sumTotalFees = 0;
$sumTotalPaid = 0;
$sumTotalDue = 0;

foreach ($admissions as $adm) {
    $sumTotalFees += $adm['net_fees'];
    $sumTotalPaid += $adm['paid_fees'];
    $sumTotalDue += $adm['due_fees'];
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Pill Tabs at Top (Matching Reference Screenshot) -->
<div class="d-flex gap-2 mb-3">
    <a href="admissions.php?tab=ongoing" class="btn btn-sm <?= ($tab === 'ongoing') ? 'btn-primary' : 'btn-light border' ?> px-3 fw-bold">Ongoing Report</a>
    <a href="admissions.php?tab=cancelled" class="btn btn-sm <?= ($tab === 'cancelled') ? 'btn-primary' : 'btn-light border' ?> px-3 fw-bold">Cancelled Report</a>
    <a href="admissions.php?tab=completed" class="btn btn-sm <?= ($tab === 'completed') ? 'btn-primary' : 'btn-light border' ?> px-3 fw-bold">Completed Report</a>
    <a href="admissions.php?tab=all" class="btn btn-sm <?= ($tab === 'all') ? 'btn-primary' : 'btn-light border' ?> px-3 fw-bold">All Admission</a>
</div>

<!-- Purple Header Banner -->
<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Admissions Report</h2>
    <p>Monitor student enrollments, assigned trainers, batch timings, and billing summaries.</p>
</div>

<!-- Filter Toolbar (Exact Disha Layout) -->
<div class="filter-toolbar">
    <form method="GET" action="" class="row g-3 align-items-center">
        <input type="hidden" name="tab" value="<?= e($tab) ?>">
        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Sort By:</label>
            <select name="sort" class="form-select">
                <option value="multi_date">Multi Date</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Start Date:</label>
            <input type="date" name="start_date" class="form-control" value="<?= e($startDate) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">End Date:</label>
            <input type="date" name="end_date" class="form-control" value="<?= e($endDate) ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-search-green flex-fill">
                <i class="fas fa-search me-1"></i> Search
            </button>
            <a href="admissions.php" class="btn btn-clear-orange">
                <i class="fas fa-rotate me-1"></i> Clear
            </a>
            <button type="button" class="btn btn-export-purple" id="exportCsvBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
            <a href="admission-add.php" class="btn btn-add-amber" title="Add Admission">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    </form>
</div>

<!-- Admissions Data Table -->
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
            <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Search student, course, phone..." style="width: 250px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>FORM NO</th>
                    <th>STUDENT NAME</th>
                    <th>TEACHER</th>
                    <th>COURSE</th>
                    <th>BATCH</th>
                    <th>TRACK</th>
                    <th>TOTAL FEES</th>
                    <th>FEES PAID</th>
                    <th>DUE</th>
                    <th>STATUS</th>
                    <th>ADMISSION DATE</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($admissions)): ?>
                    <tr><td colspan="12" class="text-center py-4 text-muted">No admission records found</td></tr>
                <?php else: ?>
                    <?php foreach ($admissions as $adm): ?>
                        <tr>
                            <td>
                                <div class="form-check d-inline-block me-1">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <span class="badge bg-light text-dark border"><?= e($adm['admission_no']) ?></span>
                            </td>
                            <td>
                                <span class="table-student-name"><?= e($adm['student_name']) ?></span>
                                <span class="table-student-phone"><?= e($adm['student_phone']) ?></span>
                            </td>
                            <td><span class="small text-muted fw-semibold"><?= e($adm['teacher_name'] ?? 'Faculty') ?></span></td>
                            <td><span class="small text-primary fw-semibold"><?= e($adm['course_name']) ?></span></td>
                            <td><span class="small"><?= e($adm['batch_time']) ?></span></td>
                            <td><span class="badge bg-light text-dark border"><?= e($adm['track']) ?></span></td>
                            <td class="fw-bold text-dark"><?= format_currency($adm['net_fees']) ?></td>
                            <td class="fw-bold text-success"><?= format_currency($adm['paid_fees']) ?></td>
                            <td class="fw-bold <?= ($adm['due_fees'] > 0) ? 'text-danger' : 'text-muted' ?>">
                                <?= format_currency($adm['due_fees']) ?>
                            </td>
                            <td>
                                <?php
                                $statusBadge = 'bg-primary-subtle text-primary';
                                if ($adm['status'] === 'Complete') $statusBadge = 'bg-success-subtle text-success';
                                elseif ($adm['status'] === 'Cancel') $statusBadge = 'bg-danger-subtle text-danger';
                                ?>
                                <span class="badge <?= $statusBadge ?> px-3 py-1 rounded-pill"><?= e($adm['status']) ?></span>
                            </td>
                            <td><?= format_date($adm['admission_date'], 'Y-m-d') ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="admission-view.php?id=<?= $adm['id'] ?>" class="action-btn" title="View Student Ledger">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    <?php if ($adm['due_fees'] > 0): ?>
                                        <a href="fee-collect.php?admission_id=<?= $adm['id'] ?>" class="action-btn" title="Collect Fees">
                                            <i class="fas fa-money-bill-wave text-success"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bottom Summary Cards (Exact Disha Reference Screenshot Footer) -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="p-3 rounded-3 text-center border fw-bold text-success" style="background:#ecfdf5; border-color:#a7f3d0!important; font-size:1.1rem;">
            Total Fees: <?= format_currency($sumTotalFees) ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 rounded-3 text-center border fw-bold text-primary" style="background:#ede9fe; border-color:#c4b5fd!important; font-size:1.1rem;">
            Total Fees Paid: <?= format_currency($sumTotalPaid) ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 rounded-3 text-center border fw-bold text-danger" style="background:#fff1f2; border-color:#fecdd3!important; font-size:1.1rem;">
            Total Due: <?= format_currency($sumTotalDue) ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 rounded-3 text-center border fw-bold text-success" style="background:#ecfdf5; border-color:#a7f3d0!important; font-size:1.1rem;">
            Total Students: <?= count($admissions) ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
