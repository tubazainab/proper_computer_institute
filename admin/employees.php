<?php
/**
 * Proper Computer - Employee Profiles
 * Faithfully inspired by reference screenshot Employee/Employee Profiles.png
 */
$pageTitle = "Employee Profiles";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

$roleFilter = $_GET['role'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM employees WHERE 1=1";
$params = [];

if (!empty($roleFilter)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}
if (!empty($search)) {
    $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR employee_id LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Purple Header Banner (Matching Reference Screenshot) -->
<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Employee Profiles</h2>
    <p>Manage trainers, administrative staff, academic counsellors, and payroll profiles.</p>
</div>

<!-- Filter & Action Toolbar -->
<div class="filter-toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex align-items-center gap-2">
        <a href="employees.php" class="btn btn-sm btn-primary fw-bold">All Employees (<?= count($employees) ?>)</a>
        <a href="employees.php?role=Teacher" class="btn btn-sm btn-light border fw-semibold">Teachers / Trainers</a>
        <a href="employees.php?role=Counselor" class="btn btn-sm btn-light border fw-semibold">Counselors</a>
    </div>
    <div class="d-flex gap-2">
        <a href="attendance.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-clipboard-check me-1"></i> Attendance
        </a>
        <a href="salaries.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-file-invoice-dollar me-1"></i> Payslips
        </a>
        <a href="employee-add.php" class="btn btn-add-amber" title="Add Employee Profile">
            <i class="fas fa-plus"></i>
        </a>
    </div>
</div>

<!-- Employee Cards Grid (Exact Disha Style) -->
<div class="row g-4 mb-4">
    <?php if (empty($employees)): ?>
        <div class="col-12 py-5 text-center text-muted">No employee profiles found.</div>
    <?php else: ?>
        <?php foreach ($employees as $emp): ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card border-0 rounded-4 p-4 shadow-sm text-center h-100 position-relative" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
                    <!-- Active/Inactive Status Badge in top corner -->
                    <span class="position-absolute top-0 start-0 m-3 small fw-bold <?= ($emp['status'] === 'Active') ? 'text-success' : 'text-danger' ?>">
                        <?= e($emp['status']) ?>
                    </span>

                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($emp['full_name']) ?>&background=<?= ($emp['status'] === 'Active') ? '4f46e5' : '64748b' ?>&color=fff&size=120" 
                         alt="<?= e($emp['full_name']) ?>" 
                         class="rounded-circle mx-auto my-3 shadow-sm" style="width: 95px; height: 95px; object-fit: cover;">

                    <h5 class="fw-bold text-dark mb-1 text-uppercase" style="font-size: 1.1rem; letter-spacing: 0.5px;">
                        <?= e($emp['full_name']) ?>
                    </h5>
                    <div class="text-muted small mb-3"><?= e($emp['role']) ?> &bull; <?= e($emp['employee_id']) ?></div>

                    <!-- Skills Tags (e.g. Photoshop, MS Excel, MS Word) -->
                    <div class="d-flex flex-wrap gap-1 justify-content-center mb-4" style="min-height: 48px;">
                        <?php 
                        $skillArray = array_filter(array_map('trim', explode(',', $emp['skills'] ?? '')));
                        foreach ($skillArray as $sk): 
                        ?>
                            <span class="badge border px-2 py-1" style="background:#fff1f2; color:#e11d48; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                <?= e($sk) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <!-- View Profile Button (Lavender/Purple matching screenshot) -->
                    <a href="employee-add.php?id=<?= $emp['id'] ?>" class="btn w-100 py-2 fw-semibold text-white mt-auto" style="background:#6366f1; border-radius: 8px;">
                        View Profile
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
