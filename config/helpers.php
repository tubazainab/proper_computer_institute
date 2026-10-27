<?php
/**
 * Proper Computer - Helper Functions
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Escape HTML special characters for XSS protection
 */
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Output CSRF Token hidden field
 */
function csrf_field() {
    $token = $_SESSION['csrf_token'] ?? '';
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Verify CSRF Token from POST request
 */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die("CSRF verification failed. Request blocked for security.");
        }
    }
}

/**
 * Flash message management
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function display_flash() {
    $flash = get_flash();
    if ($flash) {
        $type = e($flash['type']);
        $message = $flash['message']; // Can contain safe HTML or plain text
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                <i class='fas fa-info-circle me-2'></i> {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/**
 * Staff / Admin Authentication
 */
function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? 'Staff',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? 'Admin',
        'role_id' => $_SESSION['role_id'] ?? 1
    ];
}

function require_login() {
    if (!is_logged_in()) {
        set_flash('danger', 'You must be logged in to access that page.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Student Authentication
 */
function is_student_logged_in() {
    return !empty($_SESSION['student_id']);
}

function current_student() {
    if (!is_student_logged_in()) return null;
    return [
        'id' => $_SESSION['student_id'],
        'admission_no' => $_SESSION['student_admission_no'] ?? '',
        'full_name' => $_SESSION['student_name'] ?? '',
        'email' => $_SESSION['student_email'] ?? '',
        'phone' => $_SESSION['student_phone'] ?? ''
    ];
}

function require_student_login() {
    if (!is_student_logged_in()) {
        set_flash('warning', 'Please login with your mobile or admission number.');
        header('Location: ' . BASE_URL . '/student-login.php');
        exit;
    }
}

/**
 * Formatting helpers
 */
function format_currency($amount, $decimals = 0) {
    return CURRENCY_SYMBOL . number_format((float)$amount, $decimals);
}

function format_date($date, $format = 'd M Y') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return '-';
    return date($format, strtotime($date));
}

function format_time($time, $format = 'h:i A') {
    if (empty($time)) return '-';
    return date($format, strtotime($time));
}

/**
 * Number generators
 */
function generate_admission_no($pdo) {
    $year = date('Y');
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM admissions");
    $row = $stmt->fetch();
    $next = ($row['max_id'] ?? 0) + 1;
    return 'PC-ADM-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

function generate_receipt_no($pdo) {
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM fee_payments");
    $row = $stmt->fetch();
    $next = ($row['max_id'] ?? 0) + 1;
    return 'PC-REC-' . date('ym') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

function generate_employee_id($pdo) {
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM employees");
    $row = $stmt->fetch();
    $next = ($row['max_id'] ?? 0) + 1;
    return 'PC-EMP-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

/**
 * Activity logger
 */
function log_activity($userId, $action, $module, $recordId = null, $details = null) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, module, record_id, details, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt->execute([$userId, $action, $module, $recordId, $details, $ip]);
    } catch (Exception $e) {
        // Silently fail if log table is unavailable
    }
}

/**
 * Get setting from database
 */
function get_setting($key, $default = '') {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT setting_value FROM website_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}
