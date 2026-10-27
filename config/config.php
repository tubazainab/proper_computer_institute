<?php
/**
 * Proper Computer - Configuration File
 * Author: Proper Computer Engineering Team
 */

// Error reporting (Display errors in development, log in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Start PHP Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Institute & Application Constants
define('APP_NAME', 'Proper Computer');
define('APP_TAGLINE', 'Institute of Computer Education & Advanced Technologies');
define('INSTITUTE_ADDRESS', 'Proper Computer Academy, 2nd Floor, Main IT Hub, Kamptee / Nagpur - 441001');
define('INSTITUTE_PHONE', '+91 98223 34455 / +91 70200 31922');
define('INSTITUTE_EMAIL', 'info@propercomputer.com');
define('INSTITUTE_WEBSITE', 'www.propercomputer.com');
define('CURRENCY_SYMBOL', '₹');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'proper_computer_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// Dynamic Base URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Detect if we're inside /admin or root
$basePath = preg_replace('#/(admin|includes|config|assets)$#', '', $scriptName);
$basePath = rtrim($basePath, '/');

define('BASE_URL', $protocol . $host . $basePath);
define('ADMIN_URL', BASE_URL . '/admin');
define('ASSETS_URL', BASE_URL . '/assets');

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads');

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
