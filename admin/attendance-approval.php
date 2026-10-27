<?php
/**
 * Proper Computer - Attendance Approval
 * Faithfully inspired by reference screenshot Employee/Attendance Approval.png
 */
$pageTitle = "Attendance Approval";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Approve Action
if (isset($_GET['approve_id'])) {
    $attId = (int)$_GET['approve_id'];
    $pdo->prepare("UPDATE attendance SET is_approved = 1, approved_by = ? WHERE id = ?")->execute([$_SESSION['user_id'], $attId]);
    set_flash('success', 'Attendance record approved.');
    header('Location: attendance-approval.php');
    exit;
}

// Approve All Action
if (isset($_POST['approve_all'])) {
    verify_csrf();
    $pdo->prepare("UPDATE attendance SET is_approved = 1, approved_by = ? WHERE is_approved = 0")->execute([$_SESSION['user_id']]);
    set_flash('success', 'All pending attendance records approved.');
    header('Location: attendance-approval.php');
    exit;
}

$pendingAttendance = $pdo->query("SELECT att.*, e.employee_id, e.full_name, e.designation 
                                  FROM attendance att 
                                  JOIN employees e ON att.employee_id = e.id 
                                  WHERE att.is_approved = 0 
                                  ORDER BY att.attendance_date DESC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Attendance Approval</h2>
    <p>Review and authorize daily staff attendance logs, exceptions, and overtime hours.</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold text-dark mb-0">Pending Attendance Approvals (<?= count($pendingAttendance) ?>)</h5>
    <?php if (!empty($pendingAttendance)): ?>
        <form method="POST" action="">
            <?= csrf_field() ?>
            <button type="submit" name="approve_all" value="1" class="btn btn-success btn-sm fw-bold">
                <i class="fas fa-check-double me-1"></i> Approve All Pending
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="admin-card-table">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>EMPLOYEE ID</th>
                    <th>NAME</th>
                    <th>STATUS</th>
                    <th>IN TIME</th>
                    <th>OUT TIME</th>
                    <th>REMARKS</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingAttendance)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No attendance records currently pending approval. All caught up!</td></tr>
                <?php else: ?>
                    <?php foreach ($pendingAttendance as $p): ?>
                        <tr>
                            <td><?= format_date($p['attendance_date']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= e($p['employee_id']) ?></span></td>
                            <td><strong><?= e($p['full_name']) ?></strong></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= e($p['status']) ?></span></td>
                            <td><?= e($p['in_time'] ?? '-') ?></td>
                            <td><?= e($p['out_time'] ?? '-') ?></td>
                            <td><small class="text-muted"><?= e($p['remarks'] ?? '-') ?></small></td>
                            <td>
                                <a href="attendance-approval.php?approve_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-check me-1"></i> Approve
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
