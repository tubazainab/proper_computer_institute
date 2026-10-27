<?php
/**
 * Proper Computer - Course Details Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$pdo = getDBConnection();
$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT c.*, cat.name as category_name, cat.slug as category_slug, cat.icon as category_icon 
                      FROM courses c 
                      JOIN course_categories cat ON c.category_id = cat.id 
                      WHERE (c.slug = ? OR c.id = ?) AND c.is_active = 1");
$stmt->execute([$slug, (int)$id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: ' . BASE_URL . '/courses.php');
    exit;
}

$pageTitle = $course['name'] . " — Course Details & Syllabus";

// Fetch related courses
$relStmt = $pdo->prepare("SELECT * FROM courses WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 3");
$relStmt->execute([$course['category_id'], $course['id']]);
$relatedCourses = $relStmt->fetchAll();

// Parse syllabus lines
$syllabusLines = array_filter(array_map('trim', explode("\n", $course['syllabus'] ?? '')));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Header Banner -->
<div class="py-5" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff;">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/courses.php" class="text-white-50">Courses</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/courses.php?cat=<?= e($course['category_slug']) ?>" class="text-white-50"><?= e($course['category_name']) ?></a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page"><?= e($course['name']) ?></li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1"><?= e($course['code']) ?></span>
                    <span class="badge bg-primary px-3 py-1"><?= e($course['level']) ?> Level</span>
                    <span class="badge bg-info text-dark px-3 py-1"><?= e($course['mode']) ?> Mode</span>
                </div>
                <h1 class="fw-bold mb-3" style="font-size: 2.8rem;"><?= e($course['name']) ?></h1>
                <p class="lead text-white-50 mb-0"><?= e($course['description']) ?></p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="card border-0 rounded-4 p-4 text-dark text-start shadow-lg">
                    <small class="text-muted fw-bold">TOTAL TUITION FEE</small>
                    <div class="d-flex align-items-baseline gap-2 my-2">
                        <span class="fs-1 fw-bold text-primary"><?= format_currency($course['fee']) ?></span>
                        <span class="text-muted small">/ one-time or EMI</span>
                    </div>
                    <ul class="list-unstyled small text-muted mb-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Duration: <strong><?= e($course['duration']) ?></strong></li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> 100% Practical lab assignments</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Proper Computer Certified Credential</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Guaranteed interview coordination</li>
                    </ul>
                    <a href="<?= BASE_URL ?>/enquiry.php?course_id=<?= $course['id'] ?>" class="btn btn-pc-primary w-100 py-3 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i> Enroll in this Course
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Course Overview -->
            <div class="mb-5">
                <h3 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Course Overview</h3>
                <p class="text-muted lead" style="font-size: 1.05rem;">
                    The <strong><?= e($course['name']) ?></strong> program at Proper Computer is engineered by senior developers and industry leaders to take you from foundational understanding to production-level competency. Through real-world coding assignments and live projects, you build deep, actionable mastery.
                </p>
            </div>

            <!-- Complete Syllabus Breakdown -->
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0"><i class="fas fa-list-ol text-primary me-2"></i>Comprehensive Syllabus &amp; Modules</h3>
                    <span class="badge bg-light text-dark border px-3 py-2"><?= count($syllabusLines) ?> Modules</span>
                </div>

                <div class="accordion" id="syllabusAccordion">
                    <?php foreach ($syllabusLines as $idx => $line): ?>
                        <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="heading<?= $idx ?>">
                                <button class="accordion-button <?= ($idx !== 0) ? 'collapsed' : '' ?> fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $idx ?>">
                                    <span class="badge bg-primary-subtle text-primary me-3 px-2 py-1">Module <?= $idx + 1 ?></span>
                                    <?= e($line) ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $idx ?>" class="accordion-collapse collapse <?= ($idx === 0) ? 'show' : '' ?>" data-bs-parent="#syllabusAccordion">
                                <div class="accordion-body bg-light text-muted small">
                                    <p class="mb-2">Topics covered in this module:</p>
                                    <ul class="mb-0">
                                        <li>Core architectural principles and hands-on coding drills.</li>
                                        <li>Debugging, optimization techniques, and best practices.</li>
                                        <li>Real-world lab task and mentor review.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- What You Will Receive -->
            <div class="p-4 rounded-4 bg-light border mb-5">
                <h4 class="fw-bold mb-3"><i class="fas fa-award text-warning me-2"></i>Certification &amp; Career Benefits</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <i class="fas fa-check text-success mt-1"></i>
                            <span class="small">Verifiable Proper Computer Course Certificate</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <i class="fas fa-check text-success mt-1"></i>
                            <span class="small">Live Production Capstone Projects</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <i class="fas fa-check text-success mt-1"></i>
                            <span class="small">Resume &amp; LinkedIn Profile Revamp</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <i class="fas fa-check text-success mt-1"></i>
                            <span class="small">Lifetime Lab &amp; Community Access</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contact / Enquiry Box -->
            <div class="card border rounded-4 p-4 shadow-sm mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-headset text-primary me-2"></i>Need Help Choosing?</h5>
                <p class="text-muted small mb-4">Speak directly with our academic counselor for batch timings and scholarship eligibility.</p>
                <a href="tel:<?= e(INSTITUTE_PHONE) ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-phone-alt me-2"></i> Call: <?= e(INSTITUTE_PHONE) ?>
                </a>
                <a href="<?= BASE_URL ?>/enquiry.php?course_id=<?= $course['id'] ?>" class="btn btn-pc-primary w-100">
                    <i class="fas fa-envelope me-2"></i> Request Callback
                </a>
            </div>

            <!-- Related Courses -->
            <?php if (!empty($relatedCourses)): ?>
                <div class="card border rounded-4 p-4 shadow-sm">
                    <h5 class="fw-bold mb-3"><i class="fas fa-graduation-cap text-primary me-2"></i>Related Courses</h5>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($relatedCourses as $rc): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <div>
                                    <a href="<?= BASE_URL ?>/course-details.php?slug=<?= e($rc['slug']) ?>" class="fw-bold text-dark text-decoration-none small d-block">
                                        <?= e($rc['name']) ?>
                                    </a>
                                    <span class="text-muted small"><?= e($rc['duration']) ?> &bull; <?= format_currency($rc['fee']) ?></span>
                                </div>
                                <a href="<?= BASE_URL ?>/course-details.php?slug=<?= e($rc['slug']) ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
