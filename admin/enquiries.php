<?php
/**
 * Proper Computer - Enquiries Management
 * Faithfully styled after Transactions/All Enquiry.png
 */
$pageTitle = "All Enquiries";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Active tab filter
$tab = $_GET['tab'] ?? 'all'; // all, followups, visits, reminders

// Filter parameters
$leadStatus = $_GET['status'] ?? '';
$courseFilter = $_GET['course'] ?? '';
$sortBy = $_GET['sort'] ?? 'date_desc';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT e.*, c.name as course_name, emp.full_name as assigned_name 
        FROM enquiries e 
        JOIN courses c ON e.course_id = c.id 
        LEFT JOIN employees emp ON e.assigned_to = emp.id 
        WHERE 1=1";
$params = [];

if ($tab === 'followups') {
    $sql .= " AND e.lead_status = 'Followup'";
} elseif ($tab === 'visits') {
    $sql .= " AND e.lead_source = 'Walkin'";
} elseif ($tab === 'reminders') {
    $sql .= " AND e.follow_up_date <= CURRENT_DATE() AND e.lead_status NOT IN ('Admission Done', 'Cancelled')";
}

if (!empty($leadStatus)) {
    $sql .= " AND e.lead_status = ?";
    $params[] = $leadStatus;
}
if (!empty($courseFilter)) {
    $sql .= " AND e.course_id = ?";
    $params[] = (int)$courseFilter;
}
if (!empty($search)) {
    $sql .= " AND (e.student_name LIKE ? OR e.phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($sortBy === 'name_asc') {
    $sql .= " ORDER BY e.student_name ASC";
} else {
    $sql .= " ORDER BY e.enquiry_date DESC, e.id DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

$courses = $pdo->query("SELECT id, name FROM courses WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Reference Pill Tabs at Top -->
<div class="d-flex gap-2 mb-3">
    <a href="enquiries.php?tab=all" class="btn btn-sm <?= ($tab === 'all') ? 'btn-primary' : 'btn-light border' ?> px-3 fw-bold">All Enquiry</a>
    <a href="enquiries.php?tab=followups" class="btn btn-sm <?= ($tab === 'followups') ? 'btn-primary' : 'btn-light border' ?> px-3 fw-bold">FollowUps</a>
    <a href="enquiries.php?tab=visits" class="btn btn-sm <?= ($tab === 'visits') ? 'btn-primary' : 'btn-light border' ?> px-3 fw-bold">Visits</a>
    <a href="enquiries.php?tab=reminders" class="btn btn-sm <?= ($tab === 'reminders') ? 'btn-primary' : 'btn-light border' ?> px-3 fw-bold">Reminders</a>
</div>

<!-- Purple Header Banner -->
<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>All Enquiries</h2>
    <p>Track prospective student interactions, follow-up calls, and conversion to admissions.</p>
</div>

<!-- Filter Toolbar (Exact Disha Layout) -->
<div class="filter-toolbar">
    <form method="GET" action="" class="row g-3 align-items-center">
        <input type="hidden" name="tab" value="<?= e($tab) ?>">
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Lead Status</label>
            <select name="status" class="form-select">
                <option value="">Lead Status</option>
                <option value="Walkin" <?= ($leadStatus === 'Walkin') ? 'selected' : '' ?>>Walkin</option>
                <option value="Telephonic" <?= ($leadStatus === 'Telephonic') ? 'selected' : '' ?>>Telephonic</option>
                <option value="Interested" <?= ($leadStatus === 'Interested') ? 'selected' : '' ?>>Interested</option>
                <option value="Followup" <?= ($leadStatus === 'Followup') ? 'selected' : '' ?>>Followup</option>
                <option value="Admission Done" <?= ($leadStatus === 'Admission Done') ? 'selected' : '' ?>>Admission Done</option>
                <option value="Cancelled" <?= ($leadStatus === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Courses</label>
            <select name="course" class="form-select">
                <option value="">Select Course</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($courseFilter == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted mb-1">Sort By:</label>
            <select name="sort" class="form-select">
                <option value="date_desc" <?= ($sortBy === 'date_desc') ? 'selected' : '' ?>>Date (Recent)</option>
                <option value="name_asc" <?= ($sortBy === 'name_asc') ? 'selected' : '' ?>>Student Name</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-search-green flex-fill">
                <i class="fas fa-search me-1"></i> Search
            </button>
            <a href="enquiries.php" class="btn btn-clear-orange">
                <i class="fas fa-rotate me-1"></i> Clear
            </a>
            <button type="button" class="btn btn-export-purple" id="exportCsvBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
            <a href="enquiry-add.php" class="btn btn-add-amber" title="Add Enquiry">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    </form>
</div>

<!-- Enquiries Table -->
<div class="admin-card-table">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">Show</span>
            <select class="form-select form-select-sm" style="width: 80px;">
                <option>All</option>
                <option>10</option>
                <option>25</option>
            </select>
            <span class="small text-muted">entries</span>
        </div>
        <div>
            <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Search in table..." style="width: 220px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>SRNO</th>
                    <th>STUDENT NAME</th>
                    <th>COURSE NAME</th>
                    <th>DATE</th>
                    <th>LEAD STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enquiries)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No data available in table</td></tr>
                <?php else: ?>
                    <?php foreach ($enquiries as $idx => $enq): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td>
                                <span class="table-student-name"><?= e($enq['student_name']) ?></span>
                                <span class="table-student-phone"><?= e($enq['phone']) ?></span>
                            </td>
                            <td><?= e($enq['course_name']) ?></td>
                            <td><?= format_date($enq['enquiry_date'], 'Y-m-d') ?></td>
                            <td>
                                <?php
                                $badgeClass = 'bg-secondary-subtle text-secondary';
                                if ($enq['lead_status'] === 'Admission Done') $badgeClass = 'bg-success-subtle text-success';
                                elseif ($enq['lead_status'] === 'Walkin') $badgeClass = 'bg-light text-dark border';
                                elseif ($enq['lead_status'] === 'Telephonic') $badgeClass = 'bg-info-subtle text-info';
                                elseif ($enq['lead_status'] === 'Interested') $badgeClass = 'bg-warning-subtle text-warning-emphasis';
                                elseif ($enq['lead_status'] === 'Cancelled') $badgeClass = 'bg-danger-subtle text-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?> px-3 py-1 rounded-pill"><?= e($enq['lead_status']) ?></span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="action-btn" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item small" href="enquiry-followup.php?id=<?= $enq['id'] ?>"><i class="fas fa-phone-alt text-primary me-2"></i> Follow-up Notes</a></li>
                                        <?php if (!$enq['is_converted'] && $enq['lead_status'] !== 'Cancelled'): ?>
                                            <li><a class="dropdown-item small fw-bold text-success" href="enquiry-convert.php?id=<?= $enq['id'] ?>"><i class="fas fa-user-check me-2"></i> Convert to Admission</a></li>
                                        <?php endif; ?>
                                        <li><a class="dropdown-item small text-danger confirm-delete" href="enquiries.php?action=delete&id=<?= $enq['id'] ?>"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="table-summary-grid">
    <div class="summary-box">
        Total Filtered Enquiries: <?= count($enquiries) ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
