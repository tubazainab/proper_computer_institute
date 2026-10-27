<?php
/**
 * Proper Computer - Public Website Footer
 */
?>
<!-- Footer Section -->
<footer class="footer-main">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <div class="mb-3">
                    <img src="<?= ASSETS_URL ?>/images/logo.svg" alt="<?= APP_NAME ?>" style="height: 48px; filter: brightness(0) invert(1);">
                </div>
                <p class="text-white-50 mb-4" style="font-size: 0.95rem;">
                    <strong><?= APP_NAME ?></strong> is a recognized leader in career-oriented computer training. We empower students, job seekers, and working professionals with industry-vetted skills in Software Development, AI, Data Science, Design, and Digital Marketing.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Course Categories -->
            <div class="col-lg-3 col-md-6">
                <h5>Popular Domains</h5>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= BASE_URL ?>/courses.php?cat=programming-web"><i class="fas fa-chevron-right text-primary me-2 small"></i> Programming & Web Dev</a></li>
                    <li><a href="<?= BASE_URL ?>/courses.php?cat=data-ai"><i class="fas fa-chevron-right text-primary me-2 small"></i> Data & Artificial Intelligence</a></li>
                    <li><a href="<?= BASE_URL ?>/courses.php?cat=cybersecurity-it"><i class="fas fa-chevron-right text-primary me-2 small"></i> Cybersecurity & Cloud</a></li>
                    <li><a href="<?= BASE_URL ?>/courses.php?cat=design-creative"><i class="fas fa-chevron-right text-primary me-2 small"></i> UI/UX & Graphic Design</a></li>
                    <li><a href="<?= BASE_URL ?>/courses.php?cat=digital-marketing"><i class="fas fa-chevron-right text-primary me-2 small"></i> Digital Marketing & SEO</a></li>
                    <li><a href="<?= BASE_URL ?>/courses.php?cat=finance-business"><i class="fas fa-chevron-right text-primary me-2 small"></i> Finance & Management</a></li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5>Quick Links</h5>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/about.php">About Proper Computer</a></li>
                    <li><a href="<?= BASE_URL ?>/courses.php">Browse 65+ Courses</a></li>
                    <li><a href="<?= BASE_URL ?>/enquiry.php">Enquiry & Admission</a></li>
                    <li><a href="<?= BASE_URL ?>/student-login.php">Student Portal</a></li>
                    <li><a href="<?= BASE_URL ?>/login.php">Staff / Admin Login</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php">Contact & Location</a></li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-3 col-md-6">
                <h5>Get In Touch</h5>
                <ul class="list-unstyled text-white-50 mb-0 small">
                    <li class="mb-3 d-flex align-items-start">
                        <i class="fas fa-map-marker-alt text-primary mt-1 me-3 fs-6"></i>
                        <span><?= e(INSTITUTE_ADDRESS) ?></span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-phone-alt text-success me-3 fs-6"></i>
                        <span><?= e(INSTITUTE_PHONE) ?></span>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="fas fa-envelope text-info me-3 fs-6"></i>
                        <span><?= e(INSTITUTE_EMAIL) ?></span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="fas fa-clock text-warning me-3 fs-6"></i>
                        <span>Mon - Sat: 8:00 AM – 8:00 PM<br>Sunday: 9:00 AM – 2:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong>. All rights reserved.
            </div>
            <div>
                Designed &amp; Powered for Vocational &amp; Technical Excellence.
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Public Main JS -->
<script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
