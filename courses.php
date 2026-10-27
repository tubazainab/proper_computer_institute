<?php
/**
 * Proper Computer - Complete Course Catalogue (65+ Courses)
 */
$pageTitle = "All Courses (65+) — Comprehensive Tech Catalogue";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$pdo = getDBConnection();

// Fetch all categories
$catStmt = $pdo->query("SELECT * FROM course_categories ORDER BY sort_order ASC");
$categories = $catStmt->fetchAll();

// Get query params
$selectedCatSlug = $_GET['cat'] ?? 'all';
$selectedLevel = $_GET['level'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

// Build query
$sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug, cat.icon as category_icon 
        FROM courses c 
        JOIN course_categories cat ON c.category_id = cat.id 
        WHERE c.is_active = 1";
$params = [];

if ($selectedCatSlug !== 'all') {
    $sql .= " AND cat.slug = ?";
    $params[] = $selectedCatSlug;
}
if ($selectedLevel !== 'all') {
    $sql .= " AND c.level = ?";
    $params[] = $selectedLevel;
}
if (!empty($searchQuery)) {
    $sql .= " AND (c.name LIKE ? OR c.description LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$sql .= " ORDER BY cat.sort_order ASC, c.name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header Banner -->
<div class="py-5 bg-light border-bottom">
    <div class="container text-center">
        <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold mb-2">COMPLETE DIRECTORY</span>
        <h1 class="fw-bold mb-2" style="font-size: 2.6rem;">Explore Our 65+ Professional Courses</h1>
        <p class="text-muted lead max-w-700 mx-auto">
            From Software Engineering to Artificial Intelligence, UI/UX Design, and Quantitative Finance. Every course is taught by industry professionals.
        </p>
    </div>
</div>

<div class="container py-5">
    <!-- Filter and Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background:#ffffff; border:1px solid var(--pc-border)!important;">
        <div class="row g-3 align-items-center">
            <!-- Search Input -->
            <div class="col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="courseSearchInput" class="form-control bg-light border-start-0" 
                           placeholder="Search courses (e.g. MERN, Python, Canva, AWS, Flutter)..." 
                           value="<?= e($searchQuery) ?>">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="col-md-4 col-lg-3">
                <select class="form-select bg-light" id="categorySelect" onchange="window.location.href=this.value;">
                    <option value="<?= BASE_URL ?>/courses.php?level=<?= urlencode($selectedLevel) ?>">All Categories (7)</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= BASE_URL ?>/courses.php?cat=<?= urlencode($cat['slug']) ?>&level=<?= urlencode($selectedLevel) ?>" 
                                <?= ($selectedCatSlug === $cat['slug']) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Level Filter -->
            <div class="col-md-4 col-lg-2">
                <select class="form-select bg-light" id="levelSelect" onchange="window.location.href=this.value;">
                    <option value="<?= BASE_URL ?>/courses.php?cat=<?= urlencode($selectedCatSlug) ?>&level=all">All Levels</option>
                    <option value="<?= BASE_URL ?>/courses.php?cat=<?= urlencode($selectedCatSlug) ?>&level=Beginner" <?= ($selectedLevel === 'Beginner') ? 'selected' : '' ?>>Beginner</option>
                    <option value="<?= BASE_URL ?>/courses.php?cat=<?= urlencode($selectedCatSlug) ?>&level=Intermediate" <?= ($selectedLevel === 'Intermediate') ? 'selected' : '' ?>>Intermediate</option>
                    <option value="<?= BASE_URL ?>/courses.php?cat=<?= urlencode($selectedCatSlug) ?>&level=Advanced" <?= ($selectedLevel === 'Advanced') ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>

            <div class="col-md-4 col-lg-2 text-md-end">
                <a href="<?= BASE_URL ?>/courses.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-rotate-left me-1"></i> Reset Filters
                </a>
            </div>
        </div>

        <!-- Category Pills Row -->
        <div class="category-pills mt-4 mb-0 pt-3 border-top">
            <a href="<?= BASE_URL ?>/courses.php?level=<?= urlencode($selectedLevel) ?>" 
               class="category-pill-btn <?= ($selectedCatSlug === 'all') ? 'active' : '' ?>">
                All Categories (<?= count($courses) ?>)
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= BASE_URL ?>/courses.php?cat=<?= urlencode($cat['slug']) ?>&level=<?= urlencode($selectedLevel) ?>" 
                   class="category-pill-btn <?= ($selectedCatSlug === $cat['slug']) ? 'active' : '' ?>">
                    <i class="<?= e($cat['icon']) ?> me-1"></i> <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Active Courses Grid -->
    <div class="row g-4" id="coursesGrid">
        <?php if (empty($courses)): ?>
            <div class="col-12 py-5 text-center text-muted" id="noCoursesFound">
                <i class="fas fa-book-reader fs-1 mb-3 text-secondary"></i>
                <h4>No courses matched your search criteria</h4>
                <p>Try modifying your filter or <a href="<?= BASE_URL ?>/courses.php">browse all 65+ courses</a>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($courses as $course): ?>
                <div class="col-lg-4 col-md-6 course-item-col" 
                     data-title="<?= e($course['name']) ?>" 
                     data-category="<?= e($course['category_slug']) ?>" 
                     data-desc="<?= e($course['description']) ?>">
                    <div class="course-card">
                        <div class="course-card-header">
                            <span class="course-badge-level text-primary"><?= e($course['level']) ?></span>
                            <div class="course-icon-wrap">
                                <i class="<?= e($course['category_icon']) ?>"></i>
                            </div>
                            <span class="badge bg-white text-muted border px-2 py-1 small"><?= e($course['category_name']) ?></span>
                        </div>
                        <div class="course-card-body">
                            <h3 class="course-card-title"><?= e($course['name']) ?></h3>
                            <div class="course-meta">
                                <span><i class="far fa-clock text-primary me-1"></i> <?= e($course['duration']) ?></span>
                                <span><i class="fas fa-chalkboard-teacher text-primary me-1"></i> <?= e($course['mode']) ?></span>
                            </div>
                            <p class="text-muted small mb-0 flex-grow-1">
                                <?= e(mb_strimwidth($course['description'] ?? '', 0, 115, '...')) ?>
                            </p>
                        </div>
                        <div class="course-card-footer">
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem;">COURSE FEE</small>
                                <span class="course-price"><?= format_currency($course['fee']) ?></span>
                            </div>
                            <a href="<?= BASE_URL ?>/course-details.php?slug=<?= e($course['slug']) ?>" class="btn btn-sm btn-pc-primary fw-semibold px-3 py-2">
                                Syllabus &amp; Enroll &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
