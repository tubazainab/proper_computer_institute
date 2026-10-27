<?php
/**
 * Proper Computer - Comprehensive Reports Center
 * Inspired by DMR Report.png, Target vs Achievements.png, Expected Collection.png
 */
$pageTitle = "Management Reports";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

$reportType = $_GET['type'] ?? 'dmr'; // dmr, target_vs_achievement, course_admissions, pending_fees, daily_collection, enquiry_conversion

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

// 1. DMR Report Data (Daily Management Summary)
if ($reportType === 'dmr') {
    $reportTitle = "DMR Report (Daily Management Report)";
    $stmt = $pdo->prepare("SELECT 
        COUNT(a.id) as total_admissions,
        COALESCE(SUM(a.net_fees), 0) as total_billing,
        (SELECT COALESCE(SUM(amount), 0) FROM fee_payments WHERE payment_date BETWEEN ? AND ?) as total_collection,
        (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date BETWEEN ? AND ?) as total_expenses
        FROM admissions a 
        WHERE a.admission_date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
    $dmrData = $stmt->fetch();
}

// 2. Target vs Achievements Data (Faithful to Target vs Achievements.png)
if ($reportType === 'target_vs_achievement') {
    $reportTitle = "Target Vs Achievements Report";
    $stmt = $pdo->query("SELECT mt.*, 
        (SELECT COUNT(id) FROM admissions WHERE MONTHNAME(admission_date) = mt.month AND YEAR(admission_date) = mt.year) as actual_admissions,
        (SELECT COALESCE(SUM(net_fees), 0) FROM admissions WHERE MONTHNAME(admission_date) = mt.month AND YEAR(admission_date) = mt.year) as actual_billing,
        (SELECT COALESCE(SUM(amount), 0) FROM fee_payments WHERE MONTHNAME(payment_date) = mt.month AND YEAR(payment_date) = mt.year) as actual_collection
        FROM monthly_targets mt 
        ORDER BY mt.year DESC, mt.id DESC");
    $targetAchData = $stmt->fetchAll();
}

// 3. Course-wise Admissions Data
if ($reportType === 'course_admissions') {
    $reportTitle = "Course-wise Admissions & Fee Breakdown";
    $stmt = $pdo->query("SELECT c.name as course_name, c.code, cat.name as category_name, 
                                COUNT(a.id) as total_students, 
                                COALESCE(SUM(a.net_fees), 0) as total_billing, 
                                COALESCE(SUM(a.paid_fees), 0) as total_collected, 
                                COALESCE(SUM(a.due_fees), 0) as total_due 
                         FROM courses c 
                         JOIN course_categories cat ON c.category_id = cat.id 
                         LEFT JOIN admissions a ON c.id = a.course_id 
                         GROUP BY c.id 
                         HAVING total_students > 0 
                         ORDER BY total_students DESC");
    $courseData = $stmt->fetchAll();
}

// 4. Pending Fees Report
if ($reportType === 'pending_fees') {
    $reportTitle = "Pending Student Fee Dues";
    $stmt = $pdo->query("SELECT a.admission_no, a.due_fees, a.net_fees, a.paid_fees, a.batch_time, 
                                s.full_name as student_name, s.phone as student_phone, c.name as course_name 
                         FROM admissions a 
                         JOIN students s ON a.student_id = s.id 
                         JOIN courses c ON a.course_id = c.id 
                         WHERE a.due_fees > 0 AND a.status = 'Ongoing' 
                         ORDER BY a.due_fees DESC");
    $pendingData = $stmt->fetchAll();
}

// 5. Enquiry Conversion Rate
if ($reportType === 'enquiry_conversion') {
    $reportTitle = "Enquiry Conversion & Lead Effectiveness";
    $totalEnquiries = $pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn() ?: 0;
    $convertedEnquiries = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE is_converted = 1")->fetchColumn() ?: 0;
    $conversionRate = ($totalEnquiries > 0) ? round(($convertedEnquiries / $totalEnquiries) * 100, 1) : 0;
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Purple Header Banner (Matching Reference Screenshot DMR / Target Report) -->
<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2><?= e($reportTitle) ?></h2>
    <p>Audited statistical reporting for institutional governance and performance evaluation.</p>
</div>

<!-- Report Navigation Tabs -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="reports.php?type=dmr" class="btn btn-sm <?= ($reportType === 'dmr') ? 'btn-primary' : 'btn-light border' ?> fw-bold">
        <i class="fas fa-calendar-check me-1"></i> DMR Report
    </a>
    <a href="reports.php?type=target_vs_achievement" class="btn btn-sm <?= ($reportType === 'target_vs_achievement') ? 'btn-primary' : 'btn-light border' ?> fw-bold">
        <i class="fas fa-bullseye me-1"></i> Target vs Achievements
    </a>
    <a href="reports.php?type=course_admissions" class="btn btn-sm <?= ($reportType === 'course_admissions') ? 'btn-primary' : 'btn-light border' ?> fw-bold">
        <i class="fas fa-graduation-cap me-1"></i> Course-wise Admissions
    </a>
    <a href="reports.php?type=pending_fees" class="btn btn-sm <?= ($reportType === 'pending_fees') ? 'btn-primary' : 'btn-light border' ?> fw-bold">
        <i class="fas fa-hand-holding-usd me-1"></i> Pending Fees
    </a>
    <a href="reports.php?type=enquiry_conversion" class="btn btn-sm <?= ($reportType === 'enquiry_conversion') ? 'btn-primary' : 'btn-light border' ?> fw-bold">
        <i class="fas fa-funnel-dollar me-1"></i> Enquiry Conversion
    </a>
</div>

<!-- Filter Toolbar (Matching DMR Report Screenshot) -->
<div class="filter-toolbar">
    <form method="GET" action="" class="row g-3 align-items-center">
        <input type="hidden" name="type" value="<?= e($reportType) ?>">
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
            <a href="reports.php?type=<?= e($reportType) ?>" class="btn btn-clear-orange">
                <i class="fas fa-rotate me-1"></i> Clear
            </a>
            <button type="button" class="btn btn-export-purple" id="exportCsvBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </form>
</div>

<!-- 1. DMR Report Table (Exact Screenshot DMR Report.png) -->
<?php if ($reportType === 'dmr'): ?>
    <div class="admin-card-table">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>SR NO.</th>
                        <th>DATE RANGE</th>
                        <th>BRANCH NAME</th>
                        <th>ADMISSION</th>
                        <th>BILLING</th>
                        <th>COLLECTION</th>
                        <th>EXPENSES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><?= e($startDate) ?> - <?= e($endDate) ?></td>
                        <td><strong class="text-primary"><?= APP_NAME ?> MAIN CAMPUS</strong></td>
                        <td><span class="badge bg-info text-dark fs-6 px-3 py-1"><?= $dmrData['total_admissions'] ?></span></td>
                        <td><span class="badge bg-primary fs-6 px-3 py-1"><?= format_currency($dmrData['total_billing']) ?></span></td>
                        <td><span class="badge bg-success fs-6 px-3 py-1"><?= format_currency($dmrData['total_collection']) ?></span></td>
                        <td><span class="badge bg-danger fs-6 px-3 py-1"><?= format_currency($dmrData['total_expenses']) ?></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- 2. Target vs Achievements (Exact Screenshot Target vs Achievements.png) -->
<?php if ($reportType === 'target_vs_achievement'): ?>
    <div class="admin-card-table">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>SRN NO.</th>
                        <th>MONTH YEAR</th>
                        <th>TARGET ADMISSIONS</th>
                        <th>ACTUAL ADMISSIONS</th>
                        <th>TARGET COLLECTION</th>
                        <th>ACTUAL COLLECTION</th>
                        <th>TARGET BILLING</th>
                        <th>ACTUAL BILLING</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($targetAchData)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No target data available in table</td></tr>
                    <?php else: ?>
                        <?php foreach ($targetAchData as $idx => $ta): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td><strong><?= e($ta['month']) ?>, <?= e($ta['year']) ?></strong></td>
                                <td><?= $ta['target_admissions'] ?></td>
                                <td><span class="badge bg-primary px-3 py-1"><?= $ta['actual_admissions'] ?></span></td>
                                <td><?= format_currency($ta['target_collection']) ?></td>
                                <td><span class="badge bg-success px-3 py-1"><?= format_currency($ta['actual_collection']) ?></span></td>
                                <td><?= format_currency($ta['target_billing']) ?></td>
                                <td><span class="badge bg-info text-dark px-3 py-1"><?= format_currency($ta['actual_billing']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- 3. Course-wise Admissions Table -->
<?php if ($reportType === 'course_admissions'): ?>
    <div class="admin-card-table">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>COURSE NAME</th>
                        <th>CODE</th>
                        <th>DOMAIN CATEGORY</th>
                        <th>TOTAL ENROLLED</th>
                        <th>TOTAL BILLED</th>
                        <th>COLLECTED</th>
                        <th>PENDING DUE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courseData as $c): ?>
                        <tr>
                            <td><strong><?= e($c['course_name']) ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?= e($c['code']) ?></span></td>
                            <td><?= e($c['category_name']) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary fs-6"><?= $c['total_students'] ?></span></td>
                            <td class="fw-bold"><?= format_currency($c['total_billing']) ?></td>
                            <td class="text-success fw-bold"><?= format_currency($c['total_collected']) ?></td>
                            <td class="text-danger fw-bold"><?= format_currency($c['total_due']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- 4. Pending Fees Table -->
<?php if ($reportType === 'pending_fees'): ?>
    <div class="admin-card-table">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>ADMISSION NO</th>
                        <th>STUDENT NAME</th>
                        <th>MOBILE</th>
                        <th>COURSE</th>
                        <th>BATCH</th>
                        <th>TOTAL FEES</th>
                        <th>PAID</th>
                        <th>BALANCE DUE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingData as $pf): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?= e($pf['admission_no']) ?></span></td>
                            <td><strong><?= e($pf['student_name']) ?></strong></td>
                            <td><?= e($pf['student_phone']) ?></td>
                            <td><?= e($pf['course_name']) ?></td>
                            <td><?= e($pf['batch_time']) ?></td>
                            <td><?= format_currency($pf['net_fees']) ?></td>
                            <td class="text-success"><?= format_currency($pf['paid_fees']) ?></td>
                            <td class="text-danger fw-bold fs-6"><?= format_currency($pf['due_fees']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- 5. Conversion Rate Card -->
<?php if ($reportType === 'enquiry_conversion'): ?>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border rounded-4 p-4 shadow-sm bg-white text-center">
                <small class="text-muted fw-bold d-block mb-1">TOTAL PROSPECT ENQUIRIES</small>
                <h2 class="fw-bold text-dark mb-0"><?= $totalEnquiries ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border rounded-4 p-4 shadow-sm bg-white text-center">
                <small class="text-muted fw-bold d-block mb-1">CONVERTED TO ADMISSIONS</small>
                <h2 class="fw-bold text-success mb-0"><?= $convertedEnquiries ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border rounded-4 p-4 shadow-sm bg-white text-center">
                <small class="text-muted fw-bold d-block mb-1">CONVERSION SUCCESS RATE</small>
                <h2 class="fw-bold text-primary mb-0"><?= $conversionRate ?>%</h2>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
