<?php
/**
 * Proper Computer - Contact Us
 */
$pageTitle = "Contact Us — Proper Computer Institute";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

$pdo = getDBConnection();
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($phone)) {
        try {
            // Find default general course or first course
            $firstCourse = $pdo->query("SELECT id FROM courses LIMIT 1")->fetchColumn() ?: 1;
            $stmt = $pdo->prepare("INSERT INTO enquiries (student_name, phone, course_id, lead_source, lead_status, enquiry_date, notes) 
                                  VALUES (?, ?, ?, 'Contact Page', 'Interested', CURRENT_DATE(), ?)");
            $stmt->execute([$name, $phone, $firstCourse, "Message: " . $message]);
            $submitted = true;
        } catch (Exception $e) {}
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-5 bg-light border-bottom">
    <div class="container text-center">
        <span class="badge bg-primary px-3 py-2 rounded-pill fw-bold mb-2">REACH OUT TO US</span>
        <h1 class="fw-bold mb-2" style="font-size: 2.6rem;">Contact Proper Computer</h1>
        <p class="text-muted lead max-w-700 mx-auto">
            Have questions about our syllabus, batches, fee plans or certifications? We are here to guide you.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Contact Information -->
        <div class="col-lg-5">
            <h3 class="fw-bold mb-4">Campus &amp; Help Desk</h3>
            <div class="d-flex flex-column gap-4 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="feature-icon bg-primary text-white" style="width:48px; height:48px; font-size:20px; border-radius:12px; margin:0;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Campus Address</h6>
                        <p class="text-muted small mb-0"><?= e(INSTITUTE_ADDRESS) ?></p>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="feature-icon bg-success text-white" style="width:48px; height:48px; font-size:20px; border-radius:12px; margin:0;">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Phone &amp; WhatsApp</h6>
                        <p class="text-muted small mb-0"><?= e(INSTITUTE_PHONE) ?></p>
                        <small class="text-muted">Monday - Saturday (8:00 AM - 8:00 PM)</small>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <div class="feature-icon bg-info text-white" style="width:48px; height:48px; font-size:20px; border-radius:12px; margin:0;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Email Enquiries</h6>
                        <p class="text-muted small mb-0"><a href="mailto:<?= e(INSTITUTE_EMAIL) ?>"><?= e(INSTITUTE_EMAIL) ?></a></p>
                    </div>
                </div>
            </div>

            <!-- Lab Facilities Card -->
            <div class="p-4 bg-light rounded-4 border">
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-clock text-primary me-2"></i>Visiting Hours</h6>
                <p class="small text-muted mb-2">We welcome parents and students to visit our high-tech coding and multimedia labs.</p>
                <div class="small fw-semibold text-secondary">
                    Weekdays: 8:00 AM – 8:00 PM<br>
                    Sundays: 9:00 AM – 2:00 PM
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="card border rounded-4 p-4 p-md-5 shadow-sm">
                <?php if ($submitted): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4">
                        <i class="fas fa-check-circle fs-3 me-3"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Message Sent!</h5>
                            <div>Thank you! Our front desk executive will contact you shortly.</div>
                        </div>
                    </div>
                <?php endif; ?>

                <h4 class="fw-bold mb-3">Send us a Message</h4>
                <p class="text-muted small mb-4">Fill out the form below and we will get back to you within 24 business hours.</p>

                <form method="POST" action="">
                    <?= csrf_field() ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Your Name</label>
                            <input type="text" name="name" class="form-control bg-light" placeholder="e.g. Amit Patil" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Mobile Number</label>
                            <input type="tel" name="phone" class="form-control bg-light" placeholder="e.g. 9822110000" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Message or Course Query</label>
                            <textarea name="message" rows="4" class="form-control bg-light" placeholder="How can Proper Computer help you?" required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-pc-primary w-100 py-3 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
