<?php
/**
 * Proper Computer - About Us
 */
$pageTitle = "About Us — Proper Computer Institute";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$pdo = getDBConnection();
$teachers = $pdo->query("SELECT * FROM employees WHERE status = 'Active' ORDER BY id ASC LIMIT 4")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- About Banner -->
<div class="py-5 bg-light border-bottom">
    <div class="container text-center">
        <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold mb-2">ABOUT PROPER COMPUTER</span>
        <h1 class="fw-bold mb-2" style="font-size: 2.6rem;">Empowering Careers Through Applied IT Education</h1>
        <p class="text-muted lead max-w-700 mx-auto">
            Proper Computer is a premier computer technology institute committed to delivering vocational training, deep technical skills, and high-impact career outcomes.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-3">OUR MISSION</span>
            <h2 class="fw-bold mb-4">Bridging the Gap Between Academia &amp; Global Industry Requirements</h2>
            <p class="text-muted mb-3" style="font-size: 1.05rem;">
                Founded with the belief that practical execution supersedes passive theory, <strong>Proper Computer</strong> delivers industry-aligned training programs across 65+ specialized tech domains.
            </p>
            <p class="text-muted mb-4">
                Whether you are an undergraduate building foundational coding skills in C++ and Java, an aspiring Data Scientist mastering machine learning models, or a creative designer working on motion graphics and UI/UX in Figma, Proper Computer provides the computing power, mentorship, and project portfolio you need to stand out.
            </p>
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <h4 class="fw-bold text-primary mb-1">5,000+</h4>
                        <p class="text-muted small mb-0">Certified Graduates</p>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <h4 class="fw-bold text-primary mb-1">65+</h4>
                        <p class="text-muted small mb-0">Cutting-Edge Courses</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden p-4 bg-primary text-white">
                <h3 class="fw-bold mb-3">Our Core Educational Values</h3>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle fs-4 text-warning"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Practical-First Pedagogy</h5>
                            <p class="text-white-50 small mb-0">80% lab time and 20% concept discussions to build muscle memory and coding confidence.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle fs-4 text-warning"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Continuous Assessment &amp; Feedback</h5>
                            <p class="text-white-50 small mb-0">Weekly module challenges, project reviews, and individual code walkthroughs.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <i class="fas fa-check-circle fs-4 text-warning"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Industry Recognized Certification</h5>
                            <p class="text-white-50 small mb-0">Government-registered certificates valued across MNCs, startups, and IT agencies.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Faculty & Mentors -->
    <div class="mt-5 pt-4">
        <div class="text-center mb-5">
            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-bold mb-2">OUR FACULTY</span>
            <h2 class="fw-bold">Learn from Experienced Industry Mentors</h2>
            <p class="text-muted">Our dedicated trainers bring real software engineering, design, and accounting expertise to every session.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($teachers as $emp): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card border rounded-4 text-center p-4 h-100 shadow-sm">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($emp['full_name']) ?>&background=4f46e5&color=fff&size=120" 
                             alt="<?= e($emp['full_name']) ?>" 
                             class="rounded-circle mx-auto mb-3 shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                        <h5 class="fw-bold mb-1 text-dark"><?= e($emp['full_name']) ?></h5>
                        <p class="text-primary small fw-semibold mb-2"><?= e($emp['designation']) ?></p>
                        <p class="text-muted small mb-3"><?= e($emp['qualification']) ?></p>
                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                            <?php foreach (explode(',', $emp['skills'] ?? '') as $sk): ?>
                                <span class="badge bg-light text-muted border small"><?= trim(e($sk)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
