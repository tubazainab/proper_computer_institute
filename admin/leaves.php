<?php
/**
 * Proper Computer - Leaves Approval & Management
 * Faithfully inspired by reference screenshot Employee/Leaves Approval.png
 */
$pageTitle = "Leaves Approval";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Approve / Reject Handler
if (isset($_GET['action']) && in_array($_GET['action'], ['approve', 'reject']) && isset($_GET['id'])) {
    $leaveId = (int)$_GET['id'];
    $newStatus = ($_GET['action'] === 'approve') ? 'Approved' : 'Rejected';
    $stmt = $pdo->prepare("UPDATE leave_applications SET status = ?, approved_by = ? WHERE id = ?");
    $stmt->execute([$newStatus, $_SESSION['user_id'], $leaveId]);
    set_flash('success', "Leave application #{$leaveId} marked as {$newStatus}.");
    header('Location: leaves.php');
    exit;
}

// New Leave Application Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_leave'])) {
    verify_csrf();

    $empId = (int)($_POST['employee_id'] ?? 0);
    $leaveType = $_POST['leave_type'] ?? 'Casual Leave';
    $startDate = $_POST['start_date'] ?? date('Y-m-d');
    $endDate = $_POST['end_date'] ?? date('Y-m-d');
    $days = max(1, (int)($_POST['total_days'] ?? 1));
    $reason = trim($_POST['reason'] ?? '');

    if ($empId > 0 && !empty($reason)) {
        $ins = $pdo->prepare("INSERT INTO leave_applications (employee_id, leave_type, start_date, end_date, total_days, reason, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $ins->execute([$empId, $leaveType, $startDate, $endDate, $days, $reason]);
        set_flash('success', 'Leave application registered and submitted for review.');
        header('Location: leaves.php');
        exit;
    }
}

// Filter
$statusFilter = $_GET['status'] ?? 'all';
$sql = "SELECT la.*, e.employee_id, e.full_name as employee_name, e.role, u.full_name as approver_name 
        FROM leave_applications la 
        JOIN employees e ON la.employee_id = e.id 
        LEFT JOIN users u ON la.approved_by = u.id 
        WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND la.status = ?";
    $params[] = ucfirst($statusFilter);
}

$sql .= " ORDER BY la.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leaves = $stmt->fetchAll();

$employees = $pdo->query("SELECT id, full_name, employee_id FROM employees WHERE status = 'Active'")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Leaves Approval</h2>
    <p>Track faculty leave requests, lecture substitution arrangements, and formal approvals.</p>
</div>

<div class="filter-toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex align-items-center gap-2">
        <a href="leaves.php?status=all" class="btn btn-sm <?= ($statusFilter === 'all') ? 'btn-primary' : 'btn-light border' ?> fw-bold">All Requests</a>
        <a href="leaves.php?status=pending" class="btn btn-sm <?= ($statusFilter === 'pending') ? 'btn-primary' : 'btn-light border' ?> fw-bold">Pending</a>
        <a href="leaves.php?status=approved" class="btn btn-sm <?= ($statusFilter === 'approved') ? 'btn-primary' : 'btn-light border' ?> fw-bold">Approved</a>
        <a href="leaves.php?status=rejected" class="btn btn-sm <?= ($statusFilter === 'rejected') ? 'btn-primary' : 'btn-light border' ?> fw-bold">Rejected</a>
    </div>
    <div>
        <button type="button" class="btn btn-add-amber" data-bs-toggle="modal" data-bs-target="#applyLeaveModal" title="Apply for Leave">
            <i class="fas fa-plus"></i>
        </button>
    </div>
</div>

<div class="admin-card-table">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>SR NO</th>
                    <th>EMPLOYEE ID</th>
                    <th>NAME</th>
                    <th>LEAVE TYPE</th>
                    <th>START DATE</th>
                    <th>END DATE</th>
                    <th>DAYS</th>
                    <th>REASON</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaves)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">No leave records found</td></tr>
                <?php else: ?>
                    <?php foreach ($leaves as $idx => $lv): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border"><?= e($lv['employee_id']) ?></span></td>
                            <td><strong><?= e($lv['employee_name']) ?></strong></td>
                            <td><span class="small fw-semibold"><?= e($lv['leave_type']) ?></span></td>
                            <td><?= format_date($lv['start_date']) ?></td>
                            <td><?= format_date($lv['end_date']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $lv['total_days'] ?> Days</span></td>
                            <td><small class="text-muted"><?= e($lv['reason']) ?></small></td>
                            <td>
                                <?php
                                $badge = 'bg-warning-subtle text-warning-emphasis';
                                if ($lv['status'] === 'Approved') $badge = 'bg-success-subtle text-success';
                                elseif ($lv['status'] === 'Rejected') $badge = 'bg-danger-subtle text-danger';
                                ?>
                                <span class="badge <?= $badge ?> px-3 py-1 rounded-pill"><?= e($lv['status']) ?></span>
                            </td>
                            <td>
                                <?php if ($lv['status'] === 'Pending'): ?>
                                    <div class="d-flex gap-1">
                                        <a href="leaves.php?action=approve&id=<?= $lv['id'] ?>" class="btn btn-sm btn-outline-success" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="leaves.php?action=reject&id=<?= $lv['id'] ?>" class="btn btn-sm btn-outline-danger" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted"><i class="fas fa-user-check me-1"></i><?= e($lv['approver_name'] ?? 'Admin') ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Submit Leave Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Choose Employee...</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= e($emp['full_name']) ?> (<?= e($emp['employee_id']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Leave Type</label>
                        <select name="leave_type" class="form-select">
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Earned Leave">Earned Leave</option>
                            <option value="Maternity/Paternity">Maternity/Paternity</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Total Days</label>
                        <input type="number" name="total_days" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason for Leave</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="Specify personal or medical grounds..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit_leave" value="1" class="btn btn-pc-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
