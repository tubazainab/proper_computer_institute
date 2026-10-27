<?php
/**
 * Proper Computer - Edit Course
 */
$pageTitle = "Edit Course";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$pdo = getDBConnection();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    set_flash('danger', 'Course not found.');
    header('Location: courses.php');
    exit;
}

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
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name) || empty($categoryId) || empty($code)) {
        $error = "Course name, code and category are required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE courses SET 
                category_id = ?, name = ?, code = ?, duration = ?, fee = ?, level = ?, mode = ?, 
                description = ?, syllabus = ?, is_popular = ?, is_active = ? 
                WHERE id = ?");
            $stmt->execute([$categoryId, $name, $code, $duration, $fee, $level, $mode, $description, $syllabus, $isPopular, $isActive, $id]);

            set_flash('success', "Course '{$name}' updated successfully.");
            header('Location: courses.php');
            exit;
        } catch (Exception $e) {
            $error = "Error updating course: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-header-banner">
    <h2>Edit Course: <?= e($course['name']) ?></h2>
    <p>Update course syllabus, duration, tuition pricing, and active status.</p>
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
                <input type="text" name="name" class="form-control" value="<?= e($course['name']) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Course Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" value="<?= e($course['code']) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Domain Category <span class="text-danger">*</span></label>
                <select name="category_id" class="form-select" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($course['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Duration</label>
                <input type="text" name="duration" class="form-control" value="<?= e($course['duration']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Tuition Fee (₹)</label>
                <input type="number" step="0.01" name="fee" class="form-control" value="<?= e($course['fee']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Course Level</label>
                <select name="level" class="form-select">
                    <option value="Beginner" <?= ($course['level'] === 'Beginner') ? 'selected' : '' ?>>Beginner</option>
                    <option value="Intermediate" <?= ($course['level'] === 'Intermediate') ? 'selected' : '' ?>>Intermediate</option>
                    <option value="Advanced" <?= ($course['level'] === 'Advanced') ? 'selected' : '' ?>>Advanced</option>
                    <option value="All Levels" <?= ($course['level'] === 'All Levels') ? 'selected' : '' ?>>All Levels</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Delivery Mode</label>
                <select name="mode" class="form-select">
                    <option value="Hybrid" <?= ($course['mode'] === 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
                    <option value="Offline" <?= ($course['mode'] === 'Offline') ? 'selected' : '' ?>>Offline</option>
                    <option value="Online" <?= ($course['mode'] === 'Online') ? 'selected' : '' ?>>Online</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Short Course Description</label>
                <textarea name="description" rows="3" class="form-control"><?= e($course['description']) ?></textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Syllabus Outline (One topic per line)</label>
                <textarea name="syllabus" rows="6" class="form-control"><?= e($course['syllabus']) ?></textarea>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_popular" id="isPopular" value="1" <?= $course['is_popular'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="isPopular">Featured / Popular on Homepage</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= $course['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold" for="isActive">Course is Active &amp; Enrolling</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-pc-primary px-4">
                <i class="fas fa-save me-1"></i> Update Course
            </button>
            <a href="courses.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
