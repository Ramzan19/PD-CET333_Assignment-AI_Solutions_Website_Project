<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/schema.php';

start_secure_session();

function require_admin() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function admin_name() { return $_SESSION['admin_username'] ?? 'Admin'; }

function admin_role() { return $_SESSION['admin_role'] ?? 'viewer'; }

function require_admin_role(array $roles) {
    require_admin();
    if (!in_array(admin_role(), $roles, true)) {
        http_response_code(403);
        exit('You do not have permission to perform this admin action.');
    }
}

function ensure_admin_security_schema(PDO $pdo) {
    db_add_column_if_missing($pdo, 'admin_users', 'email', "VARCHAR(180) NULL", 'username');
    db_add_column_if_missing($pdo, 'admin_users', 'role', "VARCHAR(40) NOT NULL DEFAULT 'super_admin'", 'email');
    db_add_column_if_missing($pdo, 'admin_users', 'mfa_enabled', 'TINYINT(1) NOT NULL DEFAULT 0', 'role');
    db_add_column_if_missing($pdo, 'admin_users', 'mfa_secret', 'VARCHAR(64) NULL', 'mfa_enabled');
    $pdo->exec("UPDATE admin_users SET role = 'super_admin' WHERE role = '' OR role IS NULL");
    $pdo->exec("UPDATE admin_users SET email = 'admin@example.com' WHERE (email IS NULL OR email = '') AND username = 'admin'");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_activity (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NULL,
            username VARCHAR(100) NOT NULL DEFAULT '',
            role VARCHAR(40) NOT NULL DEFAULT '',
            action VARCHAR(80) NOT NULL,
            detail VARCHAR(255) NULL,
            ip_hash CHAR(64) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_activity_created (created_at),
            INDEX idx_admin_activity_action (action)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");

    // Older/normalized databases created admin_activity without these columns
    // (PK named activity_id, no username/role). Backfill them so activity
    // logging and the Activity Log page work regardless of how the table began.
    db_add_column_if_missing($pdo, 'admin_activity', 'username', "VARCHAR(100) NOT NULL DEFAULT ''", 'admin_id');
    db_add_column_if_missing($pdo, 'admin_activity', 'role', "VARCHAR(40) NOT NULL DEFAULT ''", 'username');
}

function admin_log_activity(PDO $pdo, $action, $detail = '') {
    try {
        ensure_admin_security_schema($pdo);
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ip_hash = $ip !== '' ? hash('sha256', $ip . '|admin|' . SITE_NAME) : null;
        $stmt = $pdo->prepare('INSERT INTO admin_activity(admin_id, username, role, action, detail, ip_hash, created_at) VALUES(?,?,?,?,?,?,NOW())');
        $stmt->execute([
            $_SESSION['admin_id'] ?? null,
            admin_name(),
            admin_role(),
            substr((string) $action, 0, 80),
            substr((string) $detail, 0, 255),
            $ip_hash,
        ]);
    } catch (Throwable $e) {
        error_log('Admin activity logging failed: ' . $e->getMessage());
    }
}
