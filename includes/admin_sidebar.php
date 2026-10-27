<?php
/**
 * Proper Computer - Admin Sidebar Navigation
 */
$currentAdminPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <!-- Brand Logo -->
    <a href="<?= ADMIN_URL ?>/index.php" class="sidebar-brand">
        <img src="<?= ASSETS_URL ?>/images/logo.svg" alt="<?= APP_NAME ?>">
    </a>

    <!-- Sidebar Menu List -->
    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="<?= ADMIN_URL ?>/index.php" class="menu-link <?= ($currentAdminPage === 'index.php') ? 'active' : '' ?>">
                <i class="fas fa-th-large menu-icon"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="<?= ADMIN_URL ?>/courses.php" class="menu-link <?= (in_array($currentAdminPage, ['courses.php', 'course-add.php', 'course-edit.php'])) ? 'active' : '' ?>">
                <i class="fas fa-book-open menu-icon"></i>
                <span>Courses</span>
            </a>
        </li>

        <li class="menu-item">
            <a class="menu-link <?= (in_array($currentAdminPage, ['enquiries.php', 'enquiry-add.php', 'admissions.php', 'admission-add.php', 'admission-view.php', 'fees.php', 'fee-collect.php', 'receipts.php'])) ? 'active' : '' ?>" 
               data-bs-toggle="collapse" href="#menuTransactions" role="button" aria-expanded="<?= (in_array($currentAdminPage, ['enquiries.php', 'enquiry-add.php', 'admissions.php', 'admission-add.php', 'admission-view.php', 'fees.php', 'fee-collect.php', 'receipts.php'])) ? 'true' : 'false' ?>">
                <i class="fas fa-money-check-alt menu-icon"></i>
                <span>Transactions</span>
                <i class="fas fa-chevron-down arrow-icon"></i>
            </a>
            <div class="collapse <?= (in_array($currentAdminPage, ['enquiries.php', 'enquiry-add.php', 'admissions.php', 'admission-add.php', 'admission-view.php', 'fees.php', 'fee-collect.php', 'receipts.php'])) ? 'show' : '' ?>" id="menuTransactions">
                <ul class="submenu">
                    <li><a href="<?= ADMIN_URL ?>/enquiries.php" class="<?= ($currentAdminPage === 'enquiries.php' || $currentAdminPage === 'enquiry-add.php') ? 'active' : '' ?>">Enquiries</a></li>
                    <li><a href="<?= ADMIN_URL ?>/admissions.php" class="<?= ($currentAdminPage === 'admissions.php' || $currentAdminPage === 'admission-add.php' || $currentAdminPage === 'admission-view.php') ? 'active' : '' ?>">Admissions</a></li>
                    <li><a href="<?= ADMIN_URL ?>/fees.php" class="<?= ($currentAdminPage === 'fees.php' || $currentAdminPage === 'fee-collect.php') ? 'active' : '' ?>">Fee Collection</a></li>
                    <li><a href="<?= ADMIN_URL ?>/receipts.php" class="<?= ($currentAdminPage === 'receipts.php') ? 'active' : '' ?>">Receipts</a></li>
                </ul>
            </div>
        </li>

        <li class="menu-item">
            <a class="menu-link <?= (in_array($currentAdminPage, ['employees.php', 'employee-add.php', 'attendance.php', 'attendance-approval.php', 'leaves.php', 'salaries.php'])) ? 'active' : '' ?>" 
               data-bs-toggle="collapse" href="#menuEmployees" role="button" aria-expanded="<?= (in_array($currentAdminPage, ['employees.php', 'employee-add.php', 'attendance.php', 'attendance-approval.php', 'leaves.php', 'salaries.php'])) ? 'true' : 'false' ?>">
                <i class="fas fa-users-cog menu-icon"></i>
                <span>Employees</span>
                <i class="fas fa-chevron-down arrow-icon"></i>
            </a>
            <div class="collapse <?= (in_array($currentAdminPage, ['employees.php', 'employee-add.php', 'attendance.php', 'attendance-approval.php', 'leaves.php', 'salaries.php'])) ? 'show' : '' ?>" id="menuEmployees">
                <ul class="submenu">
                    <li><a href="<?= ADMIN_URL ?>/employees.php" class="<?= ($currentAdminPage === 'employees.php' || $currentAdminPage === 'employee-add.php') ? 'active' : '' ?>">Employee Profiles</a></li>
                    <li><a href="<?= ADMIN_URL ?>/attendance.php" class="<?= ($currentAdminPage === 'attendance.php') ? 'active' : '' ?>">Employee Attendance</a></li>
                    <li><a href="<?= ADMIN_URL ?>/attendance-approval.php" class="<?= ($currentAdminPage === 'attendance-approval.php') ? 'active' : '' ?>">Attendance Approval</a></li>
                    <li><a href="<?= ADMIN_URL ?>/leaves.php" class="<?= ($currentAdminPage === 'leaves.php') ? 'active' : '' ?>">Leaves (Approval)</a></li>
                    <li><a href="<?= ADMIN_URL ?>/salaries.php" class="<?= ($currentAdminPage === 'salaries.php') ? 'active' : '' ?>">Salary &amp; Payslips</a></li>
                </ul>
            </div>
        </li>

        <li class="menu-item">
            <a class="menu-link <?= (in_array($currentAdminPage, ['targets.php', 'budget.php', 'notices.php'])) ? 'active' : '' ?>" 
               data-bs-toggle="collapse" href="#menuMaster" role="button" aria-expanded="<?= (in_array($currentAdminPage, ['targets.php', 'budget.php', 'notices.php'])) ? 'true' : 'false' ?>">
                <i class="fas fa-layer-group menu-icon"></i>
                <span>Master</span>
                <i class="fas fa-chevron-down arrow-icon"></i>
            </a>
            <div class="collapse <?= (in_array($currentAdminPage, ['targets.php', 'budget.php', 'notices.php'])) ? 'show' : '' ?>" id="menuMaster">
                <ul class="submenu">
                    <li><a href="<?= ADMIN_URL ?>/targets.php" class="<?= ($currentAdminPage === 'targets.php') ? 'active' : '' ?>">Monthly Target</a></li>
                    <li><a href="<?= ADMIN_URL ?>/budget.php" class="<?= ($currentAdminPage === 'budget.php') ? 'active' : '' ?>">Monthly Budget &amp; Expense</a></li>
                    <li><a href="<?= ADMIN_URL ?>/notices.php" class="<?= ($currentAdminPage === 'notices.php') ? 'active' : '' ?>">Student Notices</a></li>
                </ul>
            </div>
        </li>

        <li class="menu-item">
            <a href="<?= ADMIN_URL ?>/reports.php" class="menu-link <?= ($currentAdminPage === 'reports.php') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar menu-icon"></i>
                <span>Reports</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="<?= ADMIN_URL ?>/users.php" class="menu-link <?= ($currentAdminPage === 'users.php') ? 'active' : '' ?>">
                <i class="fas fa-user-shield menu-icon"></i>
                <span>Users &amp; Roles</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="<?= ADMIN_URL ?>/settings.php" class="menu-link <?= ($currentAdminPage === 'settings.php') ? 'active' : '' ?>">
                <i class="fas fa-sliders-h menu-icon"></i>
                <span>Website Settings</span>
            </a>
        </li>

        <li class="menu-item mt-3 pt-3 border-top">
            <a href="<?= BASE_URL ?>/logout.php" class="menu-link text-danger">
                <i class="fas fa-sign-out-alt menu-icon text-danger"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>
