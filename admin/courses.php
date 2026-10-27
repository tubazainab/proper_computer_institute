<?php
/**
 * Proper Computer - Course Catalogue Management
 */
$pageTitle = "Courses Catalogue";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();

// Handle Delete or Toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("UPDATE courses SET is_active = IF(is_active=1, 0, 1) WHERE id = ?")->execute([$id]);
    set_flash('success', 'Course status updated successfully.');
    header('Location: courses.php');
    exit;
}

// Filters
$categoryFilter = $_GET['category'] ?? '';
$searchFilter = trim($_GET['search'] ?? '');

$sql = "SELECT c.*, cat.name as category_name 
        FROM courses c 
        JOIN course_categories cat ON c.category_id = cat.id 
        WHERE 1=1";
$params = [];

if (!empty($categoryFilter)) {
    $sql .= " AND c.category_id = ?";
    $params[] = (int)$categoryFilter;
}
if (!empty($searchFilter)) {
    $sql .= " AND (c.name LIKE ? OR c.code LIKE ?)";
    $params[] = "%{$searchFilter}%";
    $params[] = "%{$searchFilter}%";
}

$sql .= " ORDER BY c.id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM course_categories ORDER BY sort_order ASC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Purple Header Banner -->
<div class="admin-header-banner">
    <svg class="banner-arcs" viewBox="0 0 100 100">
        <circle class="arc-1" cx="100" cy="100" r="80" />
        <circle class="arc-2" cx="100" cy="100" r="50" />
    </svg>
    <h2>Courses Catalogue (65+ Courses)</h2>
    <p>Manage institute courses, duration, syllabi, tuition fees, and admission modes.</p>
</div>

<!-- Filter Toolbar -->
<div class="filter-toolbar">
    <form method="GET" action="" class="row g-3 align-items-center">
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories (7)</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($categoryFilter == $cat['id']) ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name or course code..." value="<?= e($searchFilter) ?>">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-search-green flex-fill">
                <i class="fas fa-search me-1"></i> Search
            </button>
            <a href="courses.php" class="btn btn-clear-orange">
                <i class="fas fa-rotate me-1"></i> Clear
            </a>
            <button type="button" class="btn btn-export-purple" id="exportCsvBtn">
                <i class="fas fa-download me-1"></i> Export
            </button>
            <a href="course-add.php" class="btn btn-add-amber" title="Add New Course">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    </form>
</div>

<!-- Courses Table Card -->
<div class="admin-card-table">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>SR NO</th>
                    <th>CODE</th>
                    <th>COURSE NAME</th>
                    <th>CATEGORY</th>
                    <th>DURATION</th>
                    <th>FEES</th>
                    <th>LEVEL</th>
                    <th>MODE</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">No courses match the query criteria.</td></tr>
                <?php else: ?>
                    <?php foreach ($courses as $idx => $c): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border"><?= e($c['code']) ?></span></td>
                            <td>
                                <strong><?= e($c['name']) ?></strong>
                                <?php if ($c['is_popular']): ?>
                                    <span class="badge bg-warning text-dark ms-1 small">Popular</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="small text-muted"><?= e($c['category_name']) ?></span></td>
                            <td><?= e($c['duration']) ?></td>
                            <td class="fw-bold text-primary"><?= format_currency($c['fee']) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary border"><?= e($c['level']) ?></span></td>
                            <td><span class="badge bg-info-subtle text-info border"><?= e($c['mode']) ?></span></td>
                            <td>
                                <a href="courses.php?action=toggle&id=<?= $c['id'] ?>" class="badge <?= ($c['is_active']) ? 'bg-success' : 'bg-secondary' ?> text-decoration-none">
                                    <?= ($c['is_active']) ? 'Active' : 'Inactive' ?>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="course-edit.php?id=<?= $c['id'] ?>" class="action-btn" title="Edit Course">
                                        <i class="fas fa-edit text-primary"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/course-details.php?slug=<?= e($c['slug']) ?>" target="_blank" class="action-btn" title="Public Preview">
                                        <i class="fas fa-external-link-alt text-secondary"></i>
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

<!-- Table Summary -->
<div class="table-summary-grid">
    <div class="summary-box">
        Total Catalogue Courses: <?= count($courses) ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
