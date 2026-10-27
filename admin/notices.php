<?php
/**
 * Proper Computer - Student Notices Management
 * Faithfully inspired by reference screenshot Master/Student Notices.png
 */
$pageTitle = "Student Notices";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Toggle Notice Status Action
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("UPDATE notices SET is_active = IF(is_active=1, 0, 1) WHERE id = ?")->execute([$id]);
    set_flash('success', 'Notice status updated.');
    header('Location: notices.php');
    exit;
}

// Delete Notice Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM notices WHERE id = ?")->execute([$id]);
    set_flash('success', 'Notice removed.');
    header('Location: notices.php');
    exit;
}

// Add Notice Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notice'])) {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $audience = $_POST['target_audience'] ?? 'All';
    $priority = $_POST['priority'] ?? 'Normal';
    $expiry = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    if (!empty($title) && !empty($content)) {
        $ins = $pdo->prepare("INSERT INTO notices (title, content, target_audience, priority, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $ins->execute([$title, $content, $audience, $priority, $expiry]);
        set_flash('success', "Notice '{$title}' published successfully.");
        header('Location: notices.php');
        exit;
    }
}

$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Student Notices</h2>
    <p>Publish holiday circulars, exam schedules, and new batch announcements to student portals.</p>
</div>

<div class="filter-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-bullhorn text-warning me-2"></i>Active Circulars &amp; Announcements</h5>
    <button type="button" class="btn btn-add-amber" data-bs-toggle="modal" data-bs-target="#addNoticeModal" title="Add Student Notice">
        <i class="fas fa-plus"></i>
    </button>
</div>

<div class="admin-card-table">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>TITLE</th>
                    <th>CONTENT</th>
                    <th>AUDIENCE</th>
                    <th>PRIORITY</th>
                    <th>EXPIRY</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notices)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No notices currently published.</td></tr>
                <?php else: ?>
                    <?php foreach ($notices as $n): ?>
                        <tr>
                            <td><?= format_date($n['created_at']) ?></td>
                            <td><strong class="text-dark"><?= e($n['title']) ?></strong></td>
                            <td><small class="text-muted"><?= e(mb_strimwidth($n['content'], 0, 110, '...')) ?></small></td>
                            <td><span class="badge bg-light text-dark border"><?= e($n['target_audience']) ?></span></td>
                            <td>
                                <?php
                                $badge = 'bg-primary-subtle text-primary';
                                if ($n['priority'] === 'Urgent') $badge = 'bg-danger-subtle text-danger';
                                elseif ($n['priority'] === 'High') $badge = 'bg-warning-subtle text-warning-emphasis';
                                ?>
                                <span class="badge <?= $badge ?> px-2 py-1"><?= e($n['priority']) ?></span>
                            </td>
                            <td><?= $n['expiry_date'] ? format_date($n['expiry_date']) : 'Never' ?></td>
                            <td>
                                <a href="notices.php?action=toggle&id=<?= $n['id'] ?>" class="badge <?= ($n['is_active']) ? 'bg-success' : 'bg-secondary' ?> text-decoration-none">
                                    <?= ($n['is_active']) ? 'Active' : 'Inactive' ?>
                                </a>
                            </td>
                            <td>
                                <a href="notices.php?action=delete&id=<?= $n['id'] ?>" class="action-btn text-danger confirm-delete" title="Delete Notice">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Notice Modal -->
<div class="modal fade" id="addNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Publish Notice / Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Notice Heading / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Assessment Project Submissions" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Target Audience</label>
                            <select name="target_audience" class="form-select">
                                <option value="All">All Campus</option>
                                <option value="Students" selected>Students Only</option>
                                <option value="Faculty">Faculty Only</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Priority Level</label>
                            <select name="priority" class="form-select">
                                <option value="Normal">Normal</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Notice Content <span class="text-danger">*</span></label>
                        <textarea name="content" rows="4" class="form-control" placeholder="Full announcement text..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_notice" value="1" class="btn btn-pc-primary">Publish Notice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
