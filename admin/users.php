<?php
/**
 * Proper Computer - Users & Permissions Management
 */
$pageTitle = "Users & Permissions";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 1);
    $password = $_POST['password'] ?? 'admin123';

    if (!empty($username) && !empty($email) && !empty($password)) {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role_id, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$username, $email, $hash, $fullName, $roleId]);
            set_flash('success', "User '{$username}' created successfully.");
            header('Location: users.php');
            exit;
        } catch (Exception $e) {
            set_flash('danger', "Failed to create user: " . $e->getMessage());
        }
    }
}

$users = $pdo->query("SELECT u.*, r.name as role_name 
                      FROM users u 
                      JOIN roles r ON u.role_id = r.id 
                      ORDER BY u.id ASC")->fetchAll();

$roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Users &amp; Roles Management</h2>
    <p>Manage administrator credentials, RBAC permissions, and staff login accounts.</p>
</div>

<div class="filter-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-user-shield text-primary me-2"></i>System Users</h5>
    <button type="button" class="btn btn-add-amber" data-bs-toggle="modal" data-bs-target="#addUserModal" title="Add System User">
        <i class="fas fa-plus"></i>
    </button>
</div>

<div class="admin-card-table">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>USER</th>
                    <th>USERNAME</th>
                    <th>EMAIL</th>
                    <th>ROLE</th>
                    <th>STATUS</th>
                    <th>LAST LOGIN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($u['full_name']) ?>&background=4f46e5&color=fff&size=40" class="rounded-circle" style="width:36px; height:36px;">
                                <strong><?= e($u['full_name']) ?></strong>
                            </div>
                        </td>
                        <td><code><?= e($u['username']) ?></code></td>
                        <td><?= e($u['email']) ?></td>
                        <td><span class="badge bg-primary-subtle text-primary fw-semibold"><?= e($u['role_name']) ?></span></td>
                        <td><span class="badge bg-success-subtle text-success"><?= ucfirst(e($u['status'])) ?></span></td>
                        <td><?= format_date($u['last_login'], 'd M Y, h:i A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Create System User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Center Manager" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="e.g. manager1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Role</label>
                            <select name="role_id" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="staff@propercomputer.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Initial Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_user" value="1" class="btn btn-pc-primary">Create User Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
