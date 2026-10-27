<?php
/**
 * Proper Computer - Enquiry Follow-up Management
 */
$pageTitle = "Enquiry Follow-Up History";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT e.*, c.name as course_name 
                      FROM enquiries e 
                      JOIN courses c ON e.course_id = c.id 
                      WHERE e.id = ?");
$stmt->execute([$id]);
$enquiry = $stmt->fetch();

if (!$enquiry) {
    set_flash('danger', 'Enquiry not found.');
    header('Location: enquiries.php');
    exit;
}

// Add follow-up note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $callSummary = trim($_POST['call_summary'] ?? '');
    $nextActionDate = !empty($_POST['next_action_date']) ? $_POST['next_action_date'] : null;
    $leadStatus = $_POST['lead_status'] ?? $enquiry['lead_status'];

    if (!empty($callSummary)) {
        try {
            $user = current_user();
            $insStmt = $pdo->prepare("INSERT INTO enquiry_followups 
                (enquiry_id, followup_date, call_summary, next_action_date, status, created_by) 
                VALUES (?, CURRENT_DATE(), ?, ?, ?, ?)");
            $insStmt->execute([$id, $callSummary, $nextActionDate, $leadStatus, $user['id']]);

            // Update parent enquiry
            $updStmt = $pdo->prepare("UPDATE enquiries SET lead_status = ?, follow_up_date = ? WHERE id = ?");
            $updStmt->execute([$leadStatus, $nextActionDate, $id]);

            set_flash('success', 'Follow-up log saved and lead status updated.');
            header('Location: enquiry-followup.php?id=' . $id);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fetch follow-up history
$histStmt = $pdo->prepare("SELECT ef.*, u.full_name as staff_name 
                          FROM enquiry_followups ef 
                          LEFT JOIN users u ON ef.created_by = u.id 
                          WHERE ef.enquiry_id = ? 
                          ORDER BY ef.created_at DESC");
$histStmt->execute([$id]);
$history = $histStmt->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <h2>Follow-Up: <?= e($enquiry['student_name']) ?></h2>
    <p>Course: <?= e($enquiry['course_name']) ?> &bull; Phone: <?= e($enquiry['phone']) ?> &bull; Current Status: <strong><?= e($enquiry['lead_status']) ?></strong></p>
</div>

<div class="row g-4">
    <!-- Log New Follow-Up Form -->
    <div class="col-lg-5">
        <div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-phone-volume text-primary me-2"></i>Log Follow-Up Call</h5>

            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Lead Status</label>
                    <select name="lead_status" class="form-select">
                        <option value="Interested" <?= ($enquiry['lead_status'] === 'Interested') ? 'selected' : '' ?>>Interested</option>
                        <option value="Followup" <?= ($enquiry['lead_status'] === 'Followup') ? 'selected' : '' ?>>Followup</option>
                        <option value="Walkin" <?= ($enquiry['lead_status'] === 'Walkin') ? 'selected' : '' ?>>Walkin</option>
                        <option value="Telephonic" <?= ($enquiry['lead_status'] === 'Telephonic') ? 'selected' : '' ?>>Telephonic</option>
                        <option value="Cancelled" <?= ($enquiry['lead_status'] === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Next Action / Callback Date</label>
                    <input type="date" name="next_action_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Call Summary &amp; Remarks <span class="text-danger">*</span></label>
                    <textarea name="call_summary" rows="4" class="form-control" placeholder="What did the student say? Batch timing agreed? Discount discussed?" required></textarea>
                </div>

                <button type="submit" class="btn btn-pc-primary w-100 py-2">
                    <i class="fas fa-save me-1"></i> Save Follow-Up Note
                </button>
            </form>

            <?php if (!$enquiry['is_converted']): ?>
                <div class="mt-4 pt-3 border-top text-center">
                    <a href="enquiry-convert.php?id=<?= $enquiry['id'] ?>" class="btn btn-outline-success w-100 py-2 fw-bold">
                        <i class="fas fa-user-check me-1"></i> Convert to Confirmed Admission
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- History Timeline -->
    <div class="col-lg-7">
        <div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-history text-secondary me-2"></i>Follow-Up Interaction History</h5>

            <?php if (empty($history)): ?>
                <p class="text-muted small py-4 text-center">No previous call notes recorded yet.</p>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($history as $h): ?>
                        <div class="p-3 mb-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-primary-subtle text-primary fw-semibold"><?= e($h['status']) ?></span>
                                <small class="text-muted"><?= format_date($h['followup_date']) ?> (<?= e($h['staff_name'] ?? 'Counselor') ?>)</small>
                            </div>
                            <p class="mb-1 text-dark small"><?= nl2br(e($h['call_summary'])) ?></p>
                            <?php if (!empty($h['next_action_date'])): ?>
                                <small class="text-secondary"><i class="far fa-calendar-alt me-1"></i> Next callback: <strong><?= format_date($h['next_action_date']) ?></strong></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
