<?php
/**
 * Proper Computer - Homepage
 */
$pageTitle = "Premier IT, Coding & Management Institute";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$pdo = getDBConnection();

// Fetch popular courses
$stmt = $pdo->query("SELECT c.*, cat.name as category_name, cat.icon as category_icon 
                    FROM courses c 
                    JOIN course_categories cat ON c.category_id = cat.id 
                    WHERE c.is_popular = 1 AND c.is_active = 1 
                    ORDER BY c.id ASC LIMIT 6");
$popularCourses = $stmt->fetchAll();

// Fetch categories with course count
$catStmt = $pdo->query("SELECT cat.*, COUNT(c.id) as course_count 
                        FROM course_categories cat 
                        LEFT JOIN courses c ON cat.id = c.category_id 
                        GROUP BY cat.id 
                        ORDER BY cat.sort_order ASC");
$categories = $catStmt->fetchAll();

// Fetch active notices
$noticeStmt = $pdo->query("SELECT * FROM notices WHERE is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURRENT_DATE()) ORDER BY priority = 'Urgent' DESC, created_at DESC LIMIT 3");
$notices = $noticeStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Announcement Notice Bar if any -->
<?php if (!empty($notices)): ?>
<div class="bg-primary text-white py-2 px-3 small">
    <div class="container d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark fw-bold"><i class="fas fa-bullhorn me-1"></i> NOTICE</span>
            <span><?= e($notices[0]['title']) ?> — <?= e($notices[0]['content']) ?></span>
        </div>
        <a href="<?= BASE_URL ?>/student-login.php" class="text-white text-decoration-underline ms-auto small">Student Portal &rarr;</a>
    </div>
</div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <i class="fas fa-certificate me-2 text-warning"></i> Government Recognized &amp; ISO Certified IT Training
                </div>
                <h1 class="hero-title">
                    Accelerate Your Tech Career with <span class="text-primary">Proper Computer</span>
                </h1>
                <p class="hero-subtitle">
                    Gain job-ready skills through <strong>65+ hands-on courses</strong> in Full Stack Web Development, Artificial Intelligence, Data Analytics, Cybersecurity, UI/UX Design, and Financial Modeling.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>/courses.php" class="btn btn-pc-primary btn-lg">
                        <i class="fas fa-graduation-cap me-2"></i> Explore All 65+ Courses
                    </a>
                    <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-pc-outline btn-lg">
                        <i class="fas fa-calendar-check me-2"></i> Free Career Counselling
                    </a>
                </div>

                <!-- Stats Counters -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <h3>65+</h3>
                        <p>Specialized Courses</p>
                    </div>
                    <div class="stat-item">
                        <h3>100%</h3>
                        <p>Practical Lab Training</p>
                    </div>
                    <div class="stat-item">
                        <h3>5,000+</h3>
                        <p>Graduated Alumni</p>
                    </div>
                    <div class="stat-item">
                        <h3>94%</h3>
                        <p>Placement Support</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Instant Enquiry Card -->
                <div class="card shadow-xl border-0 rounded-4 overflow-hidden p-4 p-md-4" style="background: #ffffff; border: 1px solid var(--pc-border) !important;">
                    <div class="text-center mb-3">
                        <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-2">QUICK ENQUIRY</div>
                        <h4 class="fw-bold mb-1">Book a Free Demo Class</h4>
                        <p class="text-muted small">Talk to our faculty and get an expert syllabus walkthrough.</p>
                    </div>

                    <form action="<?= BASE_URL ?>/enquiry.php" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Your Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="student_name" class="form-control bg-light border-start-0" placeholder="e.g. Shafeen Sheikh" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                                <input type="tel" name="phone" class="form-control bg-light border-start-0" placeholder="e.g. 9822114455" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Select Course of Interest</label>
                            <select name="course_id" class="form-select bg-light" required>
                                <option value="">Choose a course...</option>
                                <?php
                                $cList = $pdo->query("SELECT id, name FROM courses WHERE is_active = 1 ORDER BY name ASC LIMIT 25")->fetchAll();
                                foreach ($cList as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted">Preferred Mode</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode" id="heroMode1" value="Offline" checked>
                                    <label class="form-check-label small" for="heroMode1">Offline (Classroom)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode" id="heroMode2" value="Online">
                                    <label class="form-check-label small" for="heroMode2">Online Live</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="submit_enquiry" class="btn btn-pc-primary w-100 py-3 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i> Submit Admission Enquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Course Categories Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-header">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-2">CURATED DOMAINS</span>
            <h2 class="section-title">Explore by Domain of Excellence</h2>
            <p class="section-subtitle">Comprehensive training pathways engineered to transition students into industry-ready tech specialists.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-4 col-md-6">
                    <a href="<?= BASE_URL ?>/courses.php?cat=<?= e($cat['slug']) ?>" class="text-decoration-none">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="<?= e($cat['icon']) ?>"></i>
                            </div>
                            <h4 class="fw-bold mb-2 text-dark"><?= e($cat['name']) ?></h4>
                            <p class="text-muted small mb-3"><?= e($cat['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <span class="badge bg-light text-primary border fw-semibold"><?= $cat['course_count'] ?> Courses</span>
                                <span class="text-primary fw-semibold small">Explore Domain <i class="fas fa-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Popular Courses Section -->
<section class="section-padding">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-5">
            <div>
                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-bold mb-2">TRENDING NOW</span>
                <h2 class="section-title mb-0">Most In-Demand Courses</h2>
            </div>
            <a href="<?= BASE_URL ?>/courses.php" class="btn btn-pc-outline">
                View Complete 65+ Catalogue <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($popularCourses as $course): ?>
                <div class="col-lg-4 col-md-6">
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
                                <span><i class="fas fa-laptop-house text-primary me-1"></i> <?= e($course['mode']) ?></span>
                            </div>
                            <p class="text-muted small mb-0 flex-grow-1">
                                <?= e(mb_strimwidth($course['description'] ?? '', 0, 110, '...')) ?>
                            </p>
                        </div>
                        <div class="course-card-footer">
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem;">COURSE FEE</small>
                                <span class="course-price"><?= format_currency($course['fee']) ?></span>
                            </div>
                            <a href="<?= BASE_URL ?>/course-details.php?slug=<?= e($course['slug']) ?>" class="btn btn-sm btn-outline-primary fw-semibold px-3 py-2">
                                Details &amp; Syllabus &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Proper Computer -->
<section class="section-padding bg-dark text-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold mb-3">WHY PROPER COMPUTER</span>
                <h2 class="text-white fw-bold mb-4" style="font-size: 2.4rem;">
                    Engineered for Career Outcomes, Not Just Theory
                </h2>
                <p class="text-white-50 mb-4">
                    At <strong>Proper Computer</strong>, every student works on production-grade projects, receives personalized 1-on-1 mentorship from industry veterans, and graduates with an impressive portfolio.
                </p>
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center text-white" style="width:36px; height:36px;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">State-of-the-Art Computer Labs</h6>
                            <p class="text-white-50 small mb-0">High-speed dual-monitor workstations equipped with professional software suites.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center text-white" style="width:36px; height:36px;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Government Recognized Certifications</h6>
                            <p class="text-white-50 small mb-0">Certificates with verifiable QR codes, recognized by top IT employers and MNCs.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center text-white" style="width:36px; height:36px;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Mock Interviews &amp; Placement Cell</h6>
                            <p class="text-white-50 small mb-0">Resume optimization, GitHub reviews, and interview scheduling with hiring partners.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 h-100">
                            <i class="fas fa-code fs-1 text-primary mb-3"></i>
                            <h5 class="fw-bold text-white mb-2">Live Coding &amp; Projects</h5>
                            <p class="text-white-50 small mb-0">Build full-stack web applications, data dashboards, AI pipelines, and video portfolios from week 1.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 h-100">
                            <i class="fas fa-users fs-1 text-info mb-3"></i>
                            <h5 class="fw-bold text-white mb-2">Small Batch Mentorship</h5>
                            <p class="text-white-50 small mb-0">Strict limit of 15 students per batch to guarantee individual attention and doubt resolution.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 h-100">
                            <i class="fas fa-clock fs-1 text-warning mb-3"></i>
                            <h5 class="fw-bold text-white mb-2">Flexible Batches</h5>
                            <p class="text-white-50 small mb-0">Morning, evening, and weekend batches tailored for college students and working professionals.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 h-100">
                            <i class="fas fa-wallet fs-1 text-success mb-3"></i>
                            <h5 class="fw-bold text-white mb-2">Affordable Installments</h5>
                            <p class="text-white-50 small mb-0">Zero percent interest monthly installment facility with official computer fee receipts.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
    <div class="container text-center text-white py-4">
        <h2 class="fw-bold mb-3" style="font-family:'Outfit',sans-serif; font-size:2.4rem;">Ready to Build Your Future in Technology?</h2>
        <p class="lead mb-4 text-white-50 max-w-600 mx-auto">Visit our campus today or submit an online enquiry for free counseling and scholarship assistance.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= BASE_URL ?>/enquiry.php" class="btn btn-light btn-lg text-primary fw-bold px-4 py-3">
                <i class="fas fa-paper-plane me-2"></i> Submit Admission Enquiry
            </a>
            <a href="tel:<?= e(INSTITUTE_PHONE) ?>" class="btn btn-outline-light btn-lg fw-bold px-4 py-3">
                <i class="fas fa-phone-alt me-2"></i> Call Us: <?= e(INSTITUTE_PHONE) ?>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
