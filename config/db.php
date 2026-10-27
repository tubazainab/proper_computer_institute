<?php
/**
 * Proper Computer - Database Connection (PDO)
 */
require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database doesn't exist yet, try connecting without DB name to auto-install or show friendly page
            try {
                $rootDsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;
                $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
                
                // If accessed directly and DB missing, provide easy installer link
                $installUrl = BASE_URL . '/install.php';
                die("
                <div style='font-family:sans-serif; max-width:600px; margin:60px auto; padding:30px; border:1px solid #e2e8f0; border-radius:12px; background:#fff; box-shadow:0 10px 25px rgba(0,0,0,0.08); text-align:center;'>
                    <h2 style='color:#4f46e5; margin-bottom:12px;'>Proper Computer Setup Required</h2>
                    <p style='color:#64748b; line-height:1.6;'>Database <strong>" . htmlspecialchars(DB_NAME) . "</strong> has not been created or imported yet.</p>
                    <a href='{$installUrl}' style='display:inline-block; margin-top:20px; background:#4f46e5; color:#fff; text-decoration:none; padding:12px 28px; border-radius:8px; font-weight:600;'>Run 1-Click Installer & Seeder</a>
                    <p style='margin-top:20px; font-size:13px; color:#94a3b8;'>Or import <code>database.sql</code> manually into phpMyAdmin.</p>
                </div>");
            } catch (PDOException $ex) {
                die("
                <div style='font-family:sans-serif; max-width:600px; margin:60px auto; padding:30px; border:1px solid #fecaca; border-radius:12px; background:#fef2f2; text-align:center;'>
                    <h2 style='color:#dc2626;'>Database Connection Error</h2>
                    <p style='color:#7f1d1d;'>" . htmlspecialchars($e->getMessage()) . "</p>
                    <p style='color:#991b1b; font-size:14px;'>Please ensure MySQL is running in your XAMPP Control Panel.</p>
                </div>");
            }
        }
    }

    return $pdo;
}
