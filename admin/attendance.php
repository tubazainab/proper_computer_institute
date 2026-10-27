<?php
/**
 * Proper Computer - Employee Attendance
 * Faithfully inspired by reference screenshot Employee/Employee Attendance.png
 */
$pageTitle = "Employee Attendance";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$attendanceDate = $_GET['date'] ?? date('Y-m-d');

// Save / Mark Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    verify_csrf();

    $attDate = $_POST['attendance_date'] ?? date('Y-m-d');
    $statusArr = $_POST['status'] ?? [];
    $inTimeArr = $_POST['in_time'] ?? [];
    $outTimeArr = $_POST['out_time'] ?? [];
    $remarksArr = $_POST['remarks'] ?? [];

    foreach ($statusArr as $empId => $status) {
        $inTime = !empty($inTimeArr[$empId]) ? $inTimeArr[$empId] : null;
        $outTime = !empty($outTimeArr[$empId]) ? $outTimeArr[$empId] : null;
        $remarks = trim($remarksArr[$empId] ?? '');

        // Check if record exists for this employee on this date
        $check = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ?");
        $check->execute([$empId, $attDate]);
        $existing = $check->fetchColumn();

        if ($existing) {
            $upd = $pdo->prepare("UPDATE attendance SET status = ?, in_time = ?, out_time = ?, remarks = ? WHERE id = ?");
            $upd->execute([$status, $inTime, $outTime, $remarks, $existing]);
        } else {
            $ins = $pdo->prepare("INSERT INTO attendance (employee_id, attendance_date, status, in_time, out_time, remarks, is_approved) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $ins->execute([$empId, $attDate, $status, $inTime, $outTime, $remarks]);
        }
    }

    set_flash('success', "Attendance records for {$attDate} saved successfully.");
    header("Location: attendance.php?date={$attDate}");
    exit;
}

// Fetch employees and their current attendance for selected date
$sql = "SELECT e.id as emp_id, e.employee_id, e.full_name, e.role, e.designation, 
               att.id as att_id, att.status, att.in_time, att.out_time, att.remarks, att.is_approved 
        FROM employees e 
        LEFT JOIN attendance att ON e.id = att.employee_id AND att.attendance_date = ? 
        WHERE e.status = 'Active' 
        ORDER BY e.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$attendanceDate]);
$attendanceList = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Employee Attendance</h2>
    <p>Mark daily staff attendance, track timings, and review attendance logs.</p>
</div>

<div class="filter-toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
    <form method="GET" action="" class="d-flex align-items-center gap-2">
        <label class="fw-semibold text-muted small mb-0">Date:</label>
        <input type="date" name="date" class="form-control form-control-sm" value="<?= e($attendanceDate) ?>" onchange="this.form.submit()">
        <button type="submit" class="btn btn-sm btn-primary">Go</button>
    </form>
    <div class="d-flex gap-2">
        <a href="attendance-approval.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-check-double me-1"></i> Attendance Approvals
        </a>
        <a href="leaves.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-calendar-minus me-1"></i> Leaves
        </a>
    </div>
</div>

<form method="POST" action="">
    <?= csrf_field() ?>
    <input type="hidden" name="attendance_date" value="<?= e($attendanceDate) ?>">

    <div class="admin-card-table">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>EMPLOYEE ID</th>
                        <th>NAME</th>
                        <th>ROLE</th>
                        <th>ATTENDANCE STATUS</th>
                        <th>IN TIME</th>
                        <th>OUT TIME</th>
                        <th>REMARKS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceList as $att): ?>
                        <?php $currStatus = $att['status'] ?? 'Present'; ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?= e($att['employee_id']) ?></span></td>
                            <td><strong class="text-dark"><?= e($att['full_name']) ?></strong></td>
                            <td><span class="small text-muted"><?= e($att['role']) ?></span></td>
                            <td>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status[<?= $att['emp_id'] ?>]" id="pres_<?= $att['emp_id'] ?>" value="Present" <?= ($currStatus === 'Present') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-success fw-semibold small" for="pres_<?= $att['emp_id'] ?>">Present</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status[<?= $att['emp_id'] ?>]" id="abs_<?= $att['emp_id'] ?>" value="Absent" <?= ($currStatus === 'Absent') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-danger fw-semibold small" for="abs_<?= $att['emp_id'] ?>">Absent</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status[<?= $att['emp_id'] ?>]" id="half_<?= $att['emp_id'] ?>" value="Half Day" <?= ($currStatus === 'Half Day') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-warning-emphasis fw-semibold small" for="half_<?= $att['emp_id'] ?>">Half Day</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status[<?= $att['emp_id'] ?>]" id="leave_<?= $att['emp_id'] ?>" value="Leave" <?= ($currStatus === 'Leave') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-info fw-semibold small" for="leave_<?= $att['emp_id'] ?>">Leave</label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="time" name="in_time[<?= $att['emp_id'] ?>]" class="form-control form-control-sm" value="<?= e($att['in_time'] ?? '09:30') ?>">
                            </td>
                            <td>
                                <input type="time" name="out_time[<?= $att['emp_id'] ?>]" class="form-control form-control-sm" value="<?= e($att['out_time'] ?? '18:00') ?>">
                            </td>
                            <td>
                                <input type="text" name="remarks[<?= $att['emp_id'] ?>]" class="form-control form-control-sm" placeholder="Notes..." value="<?= e($att['remarks'] ?? '') ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
        <button type="submit" name="save_attendance" value="1" class="btn btn-pc-primary px-4 py-2 fw-bold">
            <i class="fas fa-check-circle me-1"></i> Save Attendance for <?= format_date($attendanceDate) ?>
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
