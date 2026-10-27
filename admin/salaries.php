<?php
/**
 * Proper Computer - Salary & Payslips Management
 * Faithfully inspired by reference screenshot Employee/Payslip.png
 */
$pageTitle = "Payslip & Salary Details";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

$selectedMonthYear = $_GET['month_year'] ?? date('Y-m');
$parts = explode('-', $selectedMonthYear);
$year = (int)($parts[0] ?? date('Y'));
$month = (int)($parts[1] ?? date('m'));

// Auto-Generate Monthly Salaries Action
if (isset($_POST['generate_salaries'])) {
    verify_csrf();
    $employees = $pdo->query("SELECT * FROM employees WHERE status = 'Active'")->fetchAll();

    foreach ($employees as $emp) {
        // Check if salary record already exists
        $chk = $pdo->prepare("SELECT id FROM salary_records WHERE employee_id = ? AND month = ? AND year = ?");
        $chk->execute([$emp['id'], $month, $year]);
        if (!$chk->fetchColumn()) {
            $workingDays = 26;
            $attendedDays = 26; // Default to full or calculate from attendance table
            $leaveDays = 0;
            $basic = $emp['base_salary'];
            $allowance = 1000.00;
            $deduction = 500.00;
            $net = $basic + $allowance - $deduction;

            $ins = $pdo->prepare("INSERT INTO salary_records 
                (employee_id, month, year, working_days, attended_days, leave_days, basic_salary, allowances, deductions, net_salary, status, payment_date, payment_mode) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Paid', CURRENT_DATE(), 'Bank Transfer')");
            $ins->execute([$emp['id'], $month, $year, $workingDays, $attendedDays, $leaveDays, $basic, $allowance, $deduction, $net]);
        }
    }
    set_flash('success', "Salaries generated for {$selectedMonthYear}.");
    header("Location: salaries.php?month_year={$selectedMonthYear}");
    exit;
}

// Fetch salaries for selected month/year
$sql = "SELECT sr.*, e.employee_id, e.full_name as employee_name, e.role, e.designation 
        FROM salary_records sr 
        JOIN employees e ON sr.employee_id = e.id 
        WHERE sr.month = ? AND sr.year = ? 
        ORDER BY sr.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$month, $year]);
$salaries = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Purple Header Banner (Matching Reference Screenshot) -->
<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Payslip</h2>
    <p>Monthly payroll calculation, deductions breakdown, and printable institutional payslips.</p>
</div>

<!-- Month Filter Toolbar -->
<div class="filter-toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
    <form method="GET" action="" class="d-flex align-items-center gap-2">
        <label class="fw-semibold text-muted small mb-0">Select Payroll Month:</label>
        <input type="month" name="month_year" class="form-control form-control-sm" value="<?= e($selectedMonthYear) ?>" onchange="this.form.submit()">
        <button type="submit" class="btn btn-sm btn-primary">Go</button>
    </form>
    <div class="d-flex gap-2">
        <form method="POST" action="">
            <?= csrf_field() ?>
            <button type="submit" name="generate_salaries" value="1" class="btn btn-sm btn-success fw-bold">
                <i class="fas fa-calculator me-1"></i> Compute Monthly Payroll
            </button>
        </form>
        <button type="button" class="btn btn-export-purple btn-sm" id="exportCsvBtn">
            <i class="fas fa-download me-1"></i> Export
        </button>
    </div>
</div>

<!-- Payslips Table (Exact Disha Screenshot Columns) -->
<div class="admin-card-table">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">Show</span>
            <select class="form-select form-select-sm" style="width: 80px;">
                <option>All</option>
                <option>7</option>
                <option>10</option>
            </select>
            <span class="small text-muted">entries</span>
        </div>
        <div>
            <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Search employee..." style="width: 220px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>SR NO.</th>
                    <th>MONTH YEAR</th>
                    <th>EMPLOYEE NAME</th>
                    <th>ROLE</th>
                    <th>ATTENDANT DAYS</th>
                    <th>WORKING DAYS</th>
                    <th>SALARY</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($salaries)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No data available in table for <?= e($selectedMonthYear) ?>. Click 'Compute Monthly Payroll' to generate.</td></tr>
                <?php else: ?>
                    <?php foreach ($salaries as $idx => $s): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= date('F, Y', strtotime("{$s['year']}-{$s['month']}-01")) ?></td>
                            <td>
                                <strong><?= e($s['employee_name']) ?></strong>
                                <small class="text-muted d-block"><?= e($s['employee_id']) ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= e($s['role']) ?></span></td>
                            <td><span class="badge bg-success-subtle text-success"><?= $s['attended_days'] ?> Days</span></td>
                            <td><?= $s['working_days'] ?> Days</td>
                            <td class="fw-bold text-success fs-6"><?= format_currency($s['net_salary']) ?></td>
                            <td>
                                <a href="payslip-print.php?id=<?= $s['id'] ?>" target="_blank" class="action-btn text-primary" title="Print Payslip">
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

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
