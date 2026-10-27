<?php
/**
 * Proper Computer - Employee Profile Add & Edit
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$id = (int)($_GET['id'] ?? 0);
$employee = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch();
    $pageTitle = $employee ? "Edit Employee Profile: " . $employee['full_name'] : "Employee Not Found";
} else {
    $pageTitle = "Add New Employee Profile";
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'Teacher';
    $designation = trim($_POST['designation'] ?? 'Instructor');
    $qualification = trim($_POST['qualification'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $baseSalary = (float)($_POST['base_salary'] ?? 25000);
    $joiningDate = $_POST['joining_date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Active';

    if (empty($fullName) || empty($email) || empty($phone)) {
        $error = "Name, email and mobile number are required.";
    } else {
        try {
            if ($employee) {
                // Update
                $upd = $pdo->prepare("UPDATE employees SET 
                    full_name = ?, email = ?, phone = ?, role = ?, designation = ?, 
                    qualification = ?, skills = ?, base_salary = ?, joining_date = ?, status = ? 
                    WHERE id = ?");
                $upd->execute([$fullName, $email, $phone, $role, $designation, $qualification, $skills, $baseSalary, $joiningDate, $status, $id]);
                set_flash('success', "Employee profile for {$fullName} updated successfully.");
            } else {
                // Insert
                $empId = generate_employee_id($pdo);
                $ins = $pdo->prepare("INSERT INTO employees 
                    (employee_id, full_name, email, phone, role, designation, qualification, skills, base_salary, joining_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ins->execute([$empId, $fullName, $email, $phone, $role, $designation, $qualification, $skills, $baseSalary, $joiningDate, $status]);
                set_flash('success', "Employee profile for {$fullName} created with ID {$empId}.");
            }
            header('Location: employees.php');
            exit;
        } catch (Exception $e) {
            $error = "Operation failed: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <h2><?= $employee ? 'Edit Profile: ' . e($employee['full_name']) : 'New Employee Profile' ?></h2>
    <p>Manage trainer credentials, technical skills, and baseline monthly remuneration.</p>
</div>

<div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Employee Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= e($employee['full_name'] ?? '') ?>" placeholder="e.g. Sanjay Saini" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                <input type="tel" name="phone" class="form-control" value="<?= e($employee['phone'] ?? '') ?>" placeholder="10-digit mobile" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Official Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= e($employee['email'] ?? '') ?>" placeholder="employee@propercomputer.com" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Staff Role</label>
                <select name="role" class="form-select">
                    <option value="Teacher" <?= (($employee['role'] ?? '') === 'Teacher') ? 'selected' : '' ?>>Teacher / Trainer</option>
                    <option value="Counselor" <?= (($employee['role'] ?? '') === 'Counselor') ? 'selected' : '' ?>>Counselor</option>
                    <option value="Center Head" <?= (($employee['role'] ?? '') === 'Center Head') ? 'selected' : '' ?>>Center Head</option>
                    <option value="Accountant" <?= (($employee['role'] ?? '') === 'Accountant') ? 'selected' : '' ?>>Accountant</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Designation Title</label>
                <input type="text" name="designation" class="form-control" value="<?= e($employee['designation'] ?? 'IT Instructor') ?>" placeholder="e.g. Senior Full Stack Trainer">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Qualifications</label>
                <input type="text" name="qualification" class="form-control" value="<?= e($employee['qualification'] ?? '') ?>" placeholder="e.g. MCA, B.Tech CSE">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Base Monthly Salary (₹)</label>
                <input type="number" step="0.01" name="base_salary" class="form-control" value="<?= e($employee['base_salary'] ?? '28000.00') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Date of Joining</label>
                <input type="date" name="joining_date" class="form-control" value="<?= e($employee['joining_date'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="Active" <?= (($employee['status'] ?? 'Active') === 'Active') ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= (($employee['status'] ?? '') === 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Key Technical Skills &amp; Subject Specializations (Comma Separated)</label>
                <input type="text" name="skills" class="form-control" value="<?= e($employee['skills'] ?? '') ?>" placeholder="e.g. Photoshop, MS Excel, MS Word, Python, Full Stack">
                <div class="form-text small">These skills appear as badges on the employee profile cards.</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-pc-primary px-4">
                <i class="fas fa-save me-1"></i> <?= $employee ? 'Update Profile' : 'Save Employee Profile' ?>
            </button>
            <a href="employees.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
