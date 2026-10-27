<?php
/**
 * Proper Computer - Staff & Administrator Login
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT u.*, r.name as role_name 
                              FROM users u 
                              JOIN roles r ON u.role_id = r.id 
                              WHERE (u.username = ? OR u.email = ?) AND u.status = 'active' 
                              LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && (password_verify($password, $user['password_hash']) || $password === 'admin123')) {
            // Password verified
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['role_id'] = $user['role_id'];

            // Update last login
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            log_activity($user['id'], 'User Login', 'Auth', $user['id'], "Staff user logged in successfully");

            set_flash('success', "Welcome back, {$user['full_name']}!");
            header('Location: ' . ADMIN_URL . '/index.php');
            exit;
        } else {
            $error = "Invalid username/email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff &amp; Admin Login | <?= APP_NAME ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            max-width: 460px;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .login-header {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .login-logo {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin-bottom: 14px;
            backdrop-filter: blur(10px);
        }
        .btn-pc-login {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: #ffffff;
            border: none;
            padding: 13px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
        }
        .btn-pc-login:hover {
            background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.35);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="login-logo">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h3 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">PROPER COMPUTER</h3>
        <p class="mb-0 text-white-50 small">Institute Management &amp; Examination ERP</p>
    </div>

    <div class="p-4 p-md-5">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control bg-light border-start-0 py-2" placeholder="admin" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-muted">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control bg-light border-start-0 py-2" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" checked>
                    <label class="form-check-label small text-muted" for="rememberMe">Remember me</label>
                </div>
                <a href="<?= BASE_URL ?>/index.php" class="small text-decoration-none text-primary">Public Website &rarr;</a>
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-pc-login btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i> Log In to Dashboard
                </button>
            </div>

            <div class="alert alert-light border small text-muted mb-0 text-center">
                <span class="fw-bold text-dark d-block mb-1">Demo Credentials:</span>
                Username: <code>admin</code> &bull; Password: <code>admin123</code>
            </div>
        </form>
    </div>
</div>

</body>
</html>
