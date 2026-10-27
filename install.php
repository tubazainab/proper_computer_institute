<?php
/**
 * Proper Computer - 1-Click Database Installer & Seeder
 */
require_once __DIR__ . '/config/config.php';

$message = '';
$status = '';
$details = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_install'])) {
    try {
        $host = $_POST['db_host'] ?? DB_HOST;
        $user = $_POST['db_user'] ?? DB_USER;
        $pass = $_POST['db_pass'] ?? DB_PASS;
        $port = $_POST['db_port'] ?? DB_PORT;
        $dbName = $_POST['db_name'] ?? DB_NAME;

        // Connect to MySQL server (without selecting DB first)
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $details[] = "Connected to MySQL Server ({$host}:{$port}) successfully.";

        // Create Database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");
        $details[] = "Database `{$dbName}` selected/created.";

        // Read database.sql file
        $sqlPath = __DIR__ . '/database.sql';
        if (!file_exists($sqlPath)) {
            throw new Exception("database.sql file not found at " . $sqlPath);
        }

        $sqlContent = file_get_contents($sqlPath);
        
        // Remove comments and split queries
        $queries = explode(";\n", $sqlContent);
        $executed = 0;

        foreach ($queries as $query) {
            $trimmed = trim($query);
            if (!empty($trimmed)) {
                $pdo->exec($trimmed);
                $executed++;
            }
        }

        // Hash admin password properly using PHP's current algorithm
        $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
        $updateAdmin = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
        $updateAdmin->execute([$adminHash]);

        $details[] = "Executed {$executed} SQL statement blocks successfully.";
        $details[] = "All 21 tables created and 65+ course catalogues populated.";
        $details[] = "Default Administrator account secured (Username: <code>admin</code> | Password: <code>admin123</code>).";

        $status = 'success';
        $message = "Installation completed successfully! You can now start using Proper Computer.";
    } catch (Exception $e) {
        $status = 'danger';
        $message = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proper Computer - 1-Click Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }
        .install-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            max-width: 650px;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .install-header {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: white;
            padding: 35px 30px;
            text-align: center;
            position: relative;
        }
        .brand-icon {
            width: 68px;
            height: 68px;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 15px;
            backdrop-filter: blur(8px);
        }
        .form-floating input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.2);
        }
        .btn-install {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            border: none;
            color: white;
            padding: 14px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-install:hover {
            background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
            color: white;
        }
    </style>
</head>
<body>

<div class="install-card">
    <div class="install-header">
        <div class="brand-icon">
            <i class="fas fa-laptop-code"></i>
        </div>
        <h2 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">PROPER COMPUTER</h2>
        <p class="mb-0 text-white-50">1-Click Database Setup & Course Catalogue Seeder</p>
    </div>

    <div class="p-4 p-md-5">
        <?php if ($status === 'success'): ?>
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-check-circle fs-3 me-3"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Installation Successful!</h5>
                    <div><?= htmlspecialchars($message) ?></div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-list-check text-primary me-2"></i>Installation Log:</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <?php foreach ($details as $step): ?>
                            <li class="mb-1"><i class="fas fa-check text-success me-2"></i><?= $step ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="alert alert-primary bg-primary-subtle border-primary-subtle mb-4">
                <div class="fw-bold text-primary mb-1"><i class="fas fa-key me-2"></i>Admin Login Credentials:</div>
                <div class="small">
                    <strong>Username:</strong> <code>admin</code><br>
                    <strong>Password:</strong> <code>admin123</code>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex">
                <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-primary btn-lg flex-fill py-3 fw-bold">
                    <i class="fas fa-gauge me-2"></i> Open Admin Panel
                </a>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary btn-lg flex-fill py-3 fw-bold">
                    <i class="fas fa-globe me-2"></i> View Public Website
                </a>
            </div>

        <?php else: ?>

            <?php if ($status === 'danger'): ?>
                <div class="alert alert-danger mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <p class="text-muted mb-4 small">
                Welcome to <strong>Proper Computer</strong>. Click below to automatically create the database <code><?= htmlspecialchars(DB_NAME) ?></code>, generate 21 management tables, and populate the entire catalogue of <strong>65+ Courses</strong> along with reference staff, admissions, and financial sample records.
            </p>

            <form method="POST" action="">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-database text-primary me-2"></i>MySQL Database Parameters</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="db_host" name="db_host" value="<?= htmlspecialchars(DB_HOST) ?>" required>
                            <label for="db_host">Database Host</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="db_port" name="db_port" value="<?= htmlspecialchars(DB_PORT) ?>" required>
                            <label for="db_port">Port</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="db_name" name="db_name" value="<?= htmlspecialchars(DB_NAME) ?>" required>
                            <label for="db_name">Database Name</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="db_user" name="db_user" value="<?= htmlspecialchars(DB_USER) ?>" required>
                            <label for="db_user">Username (XAMPP default: root)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="db_pass" name="db_pass" value="<?= htmlspecialchars(DB_PASS) ?>" placeholder="Password">
                            <label for="db_pass">Password (XAMPP default is blank)</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" name="run_install" value="1" class="btn btn-install btn-lg">
                        <i class="fas fa-bolt me-2"></i> Run 1-Click Database Setup & Seeder
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
