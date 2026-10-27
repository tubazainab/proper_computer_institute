<?php
/**
 * Proper Computer - Monthly Budget & Operational Expenses
 * Faithfully inspired by reference screenshot Master/Monthly Budget.png & Report/BudgetExpense Report.png
 */
$pageTitle = "Monthly Budget & Expenses";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Add Expense Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_expense'])) {
    verify_csrf();
    $date = $_POST['expense_date'] ?? date('Y-m-d');
    $category = $_POST['category'] ?? 'General';
    $title = trim($_POST['title'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $mode = $_POST['payment_mode'] ?? 'Cash';
    $voucher = trim($_POST['receipt_voucher'] ?? 'VCH-' . rand(1000, 9999));
    $paidTo = trim($_POST['paid_to'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (!empty($title) && $amount > 0) {
        $ins = $pdo->prepare("INSERT INTO expenses (expense_date, category, title, amount, payment_mode, receipt_voucher, paid_to, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$date, $category, $title, $amount, $mode, $voucher, $paidTo, $remarks]);
        set_flash('success', "Expense voucher {$voucher} recorded successfully.");
        header('Location: budget.php');
        exit;
    }
}

// Fetch Expenses
$expenses = $pdo->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC")->fetchAll();

$totalExpenses = 0;
foreach ($expenses as $ex) {
    $totalExpenses += $ex['amount'];
}

// Fetch Monthly Budgets
$budgets = $pdo->query("SELECT * FROM monthly_budgets ORDER BY year DESC, id DESC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Monthly Budget &amp; Expense Report</h2>
    <p>Monitor operational lab costs, software licenses, advertising expenditure, and facility overheads.</p>
</div>

<!-- Quick Metric Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border rounded-4 p-4 shadow-sm bg-white">
            <small class="text-muted fw-bold d-block mb-1">TOTAL EXPENSE VOUCHERS</small>
            <h3 class="fw-bold text-danger mb-0"><?= format_currency($totalExpenses) ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border rounded-4 p-4 shadow-sm bg-white">
            <small class="text-muted fw-bold d-block mb-1">PLANNED MONTHLY BUDGET</small>
            <h3 class="fw-bold text-primary mb-0">₹1,50,000</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border rounded-4 p-4 shadow-sm bg-white">
            <small class="text-muted fw-bold d-block mb-1">BUDGET VARIANCE (HEALTH)</small>
            <h3 class="fw-bold text-success mb-0">+ <?= format_currency(150000 - $totalExpenses) ?></h3>
        </div>
    </div>
</div>

<div class="filter-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Expense Vouchers</h5>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-export-purple btn-sm" id="exportCsvBtn">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <button type="button" class="btn btn-add-amber" data-bs-toggle="modal" data-bs-target="#addExpenseModal" title="Add Expense Voucher">
            <i class="fas fa-plus"></i>
        </button>
    </div>
</div>

<div class="admin-card-table">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>VOUCHER</th>
                    <th>DATE</th>
                    <th>CATEGORY</th>
                    <th>TITLE / PURPOSE</th>
                    <th>PAID TO</th>
                    <th>PAYMENT MODE</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No expense vouchers recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($expenses as $ex): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?= e($ex['receipt_voucher']) ?></span></td>
                            <td><?= format_date($ex['expense_date']) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= e($ex['category']) ?></span></td>
                            <td>
                                <strong><?= e($ex['title']) ?></strong>
                                <small class="text-muted d-block"><?= e($ex['remarks'] ?? '') ?></small>
                            </td>
                            <td><?= e($ex['paid_to']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= e($ex['payment_mode']) ?></span></td>
                            <td class="fw-bold text-danger fs-6"><?= format_currency($ex['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Record Expense Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category" class="form-select">
                                <option value="Rent & Electricity">Rent &amp; Electricity</option>
                                <option value="Lab Equipment">Lab Equipment &amp; Upgrades</option>
                                <option value="Marketing">Marketing &amp; Advertising</option>
                                <option value="Software Licenses">Software Licenses</option>
                                <option value="Office Supplies">Office Supplies &amp; Tea</option>
                                <option value="Miscellaneous">Miscellaneous</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Expense Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Fiber Internet Renewal" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="e.g. 3500" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Payment Mode</label>
                            <select name="payment_mode" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="UPI" selected>UPI</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Paid To (Vendor / Person)</label>
                        <input type="text" name="paid_to" class="form-control" placeholder="e.g. Airtel Broadband Ltd." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Remarks / Voucher Ref</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional notes...">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_expense" value="1" class="btn btn-pc-primary">Save Expense Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
