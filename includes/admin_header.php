<?php
/**
 * Proper Computer - Admin Dashboard Header
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';
require_login();

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . APP_NAME . ' Admin' : APP_NAME . ' — Management Dashboard' ?></title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Admin Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/print.css" media="print">
    
    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar Include -->
    <?php require_once __DIR__ . '/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-welcome">
                    <i class="fas fa-hand-wave text-warning me-1"></i> Welcome <?= e($user['full_name']) ?>!
                </div>
            </div>

            <div class="topbar-actions">
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="topbar-btn text-decoration-none" title="View Public Website">
                    <i class="fas fa-graduation-cap"></i>
                </a>
                <a href="<?= ADMIN_URL ?>/notices.php" class="topbar-btn text-decoration-none" title="Institute Notices">
                    <i class="fas fa-bullhorn"></i>
                </a>
                <div class="dropdown">
                    <button class="topbar-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-2" style="width: 280px;">
                        <li class="dropdown-header text-uppercase small fw-bold text-muted">Recent Notifications</li>
                        <li><a class="dropdown-item small py-2 rounded" href="<?= ADMIN_URL ?>/enquiries.php"><i class="fas fa-user-plus text-primary me-2"></i> New Walkin Enquiry received</a></li>
                        <li><a class="dropdown-item small py-2 rounded" href="<?= ADMIN_URL ?>/fees.php"><i class="fas fa-receipt text-success me-2"></i> Fee payment ₹7,000 recorded</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item small text-center text-primary fw-semibold" href="<?= ADMIN_URL ?>/notices.php">View All Notices</a></li>
                    </ul>
                </div>
                
                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=4f46e5&color=fff&rounded=true" alt="<?= e($user['full_name']) ?>" class="topbar-avatar">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                        <li class="px-3 py-2 border-bottom mb-2">
                            <div class="fw-bold text-dark"><?= e($user['full_name']) ?></div>
                            <small class="text-muted"><?= e($user['role']) ?> &bull; <?= e($user['email']) ?></small>
                        </li>
                        <li><a class="dropdown-item rounded" href="<?= ADMIN_URL ?>/settings.php"><i class="fas fa-cog me-2 text-muted"></i> Website Settings</a></li>
                        <li><a class="dropdown-item rounded" href="<?= ADMIN_URL ?>/users.php"><i class="fas fa-user-shield me-2 text-muted"></i> Users &amp; Roles</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded text-danger" href="<?= BASE_URL ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Admin Main Content Area -->
        <main class="admin-content">
            <?php display_flash(); ?>
