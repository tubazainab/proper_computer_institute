<?php
/**
 * Proper Computer - Monthly Targets
 * Faithfully inspired by reference screenshot Master/Monthly Target.png
 */
$pageTitle = "Monthly Target";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Delete Target Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM monthly_targets WHERE id = ?")->execute([$id]);
    set_flash('success', 'Monthly target deleted.');
    header('Location: targets.php');
    exit;
}

// Add Target Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_target'])) {
    verify_csrf();
    $month = $_POST['month'] ?? date('F');
    $year = (int)($_POST['year'] ?? date('Y'));
    $admissions = (int)($_POST['target_admissions'] ?? 30);
    $billing = (float)($_POST['target_billing'] ?? 150000);
    $collection = (float)($_POST['target_collection'] ?? 120000);

    $ins = $pdo->prepare("INSERT INTO monthly_targets (month, year, target_admissions, target_billing, target_collection) VALUES (?, ?, ?, ?, ?)");
    $ins->execute([$month, $year, $admissions, $billing, $collection]);
    set_flash('success', "Target for {$month} {$year} created successfully.");
    header('Location: targets.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM monthly_targets WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (month LIKE ? OR year LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY year DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$targets = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Purple Header Banner (Matching Reference Screenshot) -->
<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Monthly Target</h2>
    <p>Define operational performance goals for student enrollments, billing, and gross revenue collections.</p>
</div>

<!-- Filter Toolbar (Exact Disha Layout) -->
<div class="filter-toolbar">
    <form method="GET" action="" class="row g-3 align-items-center">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Search by month or year (e.g. October, 2026)..." value="<?= e($search) ?>">
        </div>
        <div class="col-md-7 d-flex justify-content-md-end gap-2">
            <button type="submit" class="btn btn-search-green">
                <i class="fas fa-search me-1"></i> Search
            </button>
            <a href="targets.php" class="btn btn-clear-orange">
                <i class="fas fa-rotate me-1"></i> Clear
            </a>
            <button type="button" class="btn btn-export-purple" id="exportCsvBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
            <button type="button" class="btn btn-add-amber" data-bs-toggle="modal" data-bs-target="#addTargetModal" title="Add Monthly Target">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </form>
</div>

<!-- Targets Table (Exact Screenshot Columns) -->
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
            <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Live filter..." style="width: 220px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>SR NO.</th>
                    <th>BUDGET</th>
                    <th>MONTH</th>
                    <th>YEAR</th>
                    <th>ADMISSION</th>
                    <th>BILLING</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($targets)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No monthly targets configured yet</td></tr>
                <?php else: ?>
                    <?php foreach ($targets as $idx => $t): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= number_format($t['target_collection']) ?></td>
                            <td><strong><?= e($t['month']) ?></strong></td>
                            <td><?= e($t['year']) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary fs-6"><?= $t['target_admissions'] ?></span></td>
                            <td class="fw-bold text-dark"><?= number_format($t['target_billing']) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="targets.php?action=delete&id=<?= $t['id'] ?>" class="action-btn text-danger confirm-delete" title="Delete Target">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Target Modal -->
<div class="modal fade" id="addTargetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Set Monthly Target</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Target Month</label>
                            <select name="month" class="form-select">
                                <?php foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $m): ?>
                                    <option value="<?= $m ?>" <?= ($m === date('F')) ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Target Year</label>
                            <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Admissions (Number of Students)</label>
                        <input type="number" name="target_admissions" class="form-control" value="40" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Billing Amount (₹)</label>
                        <input type="number" step="0.01" name="target_billing" class="form-control" value="200000.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Cash/Bank Collection (₹)</label>
                        <input type="number" step="0.01" name="target_collection" class="form-control" value="150000.00" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_target" value="1" class="btn btn-pc-primary">Save Target</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
