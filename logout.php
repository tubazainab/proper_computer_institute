<?php
/**
 * Proper Computer - Logout Handler
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

// Log out user
if (is_logged_in()) {
    log_activity($_SESSION['user_id'] ?? null, 'User Logout', 'Auth', $_SESSION['user_id'] ?? null, 'Staff logged out');
}

// Clear all sessions
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect with notice
header('Location: ' . BASE_URL . '/login.php?logged_out=1');
exit;
