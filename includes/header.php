<?php
/**
 * Proper Computer - Public Website Header
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . APP_NAME : APP_NAME . ' — Premier IT & Coding Institute' ?></title>
    <meta name="description" content="Proper Computer is the premier computer training institute offering 65+ certified courses in Full Stack, Data Science, AI, Cybersecurity, Graphic Design, Video Editing, Digital Marketing, and Finance.">
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
</head>
<body>

<!-- Top Announcement & Contact Bar -->
<div class="top-bar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span><i class="fas fa-phone-alt text-warning me-1"></i> <a href="tel:<?= e(INSTITUTE_PHONE) ?>"><?= e(INSTITUTE_PHONE) ?></a></span>
                <span class="d-none d-md-inline text-white-50">|</span>
                <span class="d-none d-md-inline"><i class="fas fa-envelope text-info me-1"></i> <a href="mailto:<?= e(INSTITUTE_EMAIL) ?>"><?= e(INSTITUTE_EMAIL) ?></a></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-danger rounded-pill px-2 py-1"><i class="fas fa-bolt me-1"></i> Admissions Open 2026</span>
                <a href="<?= BASE_URL ?>/student-login.php" class="text-white"><i class="fas fa-user-graduate me-1"></i> Student Login</a>
                <span class="text-white-50">|</span>
                <a href="<?= BASE_URL ?>/login.php" class="text-white"><i class="fas fa-lock me-1"></i> Staff Login</a>
            </div>
        </div>
    </div>
</div>

<!-- Main Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-main">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/index.php">
            <img src="<?= ASSETS_URL ?>/images/logo.svg" alt="<?= APP_NAME ?>">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMainCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMainCollapse">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'courses.php' || $currentPage === 'course-details.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/courses.php">All Courses (65+)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'enquiry.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/enquiry.php">Enquiry / Admission</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'contact.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>/contact.php">Contact Us</a>
                </li>
                <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                    <a class="btn btn-pc-primary btn-sm" href="<?= BASE_URL ?>/enquiry.php">
                        <i class="fas fa-paper-plane me-1"></i> Apply Online
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
