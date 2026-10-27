<?php
/**
 * Proper Computer - Add New Course
 */
$pageTitle = "Add New Course";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$categories = $pdo->query("SELECT * FROM course_categories ORDER BY sort_order ASC")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $code = trim($_POST['code'] ?? '');
    $duration = trim($_POST['duration'] ?? '3 Months');
    $fee = (float)($_POST['fee'] ?? 0);
    $level = $_POST['level'] ?? 'Beginner';
    $mode = $_POST['mode'] ?? 'Hybrid';
    $description = trim($_POST['description'] ?? '');
    $syllabus = trim($_POST['syllabus'] ?? '');
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    if (empty($name) || empty($categoryId) || empty($code)) {
        $error = "Please fill in course title, code and category.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses 
                (category_id, name, slug, code, duration, fee, level, mode, description, syllabus, is_popular, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$categoryId, $name, $slug, $code, $duration, $fee, $level, $mode, $description, $syllabus, $isPopular]);

            set_flash('success', "Course '{$name}' created successfully.");
            header('Location: courses.php');
            exit;
        } catch (Exception $e) {
            $error = "Error adding course: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <h2>Add New Course</h2>
    <p>Define course parameters, syllabus modules, fee structure, and learning mode.</p>
</div>

<div class="card border-0 rounded-4 p-4 shadow-sm" style="background:#ffffff; border:1px solid var(--admin-border)!important;">
    <?php if ($error): ?>
        <div class="alert alert-danger mb-4"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrf_field() ?>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Course Title <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Kotlin Multiplatform" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" placeholder="e.g. PC-DEV-20" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Domain Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Domain...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Duration</label>
                <input type="text" name="duration" class="form-control" placeholder="e.g. 3 Months" value="3 Months">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Tuition Fee (₹)</label>
                <input type="number" step="0.01" name="fee" class="form-control" placeholder="e.g. 15000" value="15000">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Course Level</label>
                <select name="level" class="form-select">
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                    <option value="All Levels">All Levels</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Delivery Mode</label>
                <select name="mode" class="form-select">
                    <option value="Hybrid">Hybrid</option>
                    <option value="Offline">Offline</option>
                    <option value="Online">Online</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Short Course Description</label>
                <textarea name="description" rows="3" class="form-control" placeholder="Summary of what the student will learn..."></textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Syllabus Outline (One topic per line)</label>
                <textarea name="syllabus" rows="5" class="form-control" placeholder="Module 1: Foundations&#10;Module 2: Advanced OOP&#10;Module 3: Project Work"></textarea>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_popular" id="isPopular" value="1">
                    <label class="form-check-label fw-semibold" for="isPopular">Featured / Popular on Homepage</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-pc-primary px-4">
                <i class="fas fa-save me-1"></i> Save Course
            </button>
            <a href="courses.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
