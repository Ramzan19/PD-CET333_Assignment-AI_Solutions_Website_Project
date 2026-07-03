<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

function analytics_safe_db() {
    try {
        return new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        error_log('Analytics database connection failed: ' . $e->getMessage());
        return null;
    }
}

function ensure_website_visits_table(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS website_visits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            visitor_key VARCHAR(80) NOT NULL,
            session_key VARCHAR(128) NULL,
            page_path VARCHAR(255) NOT NULL,
            page_title VARCHAR(200) NULL,
            page_name VARCHAR(80) NULL,
            referrer VARCHAR(500) NULL,
            ip_hash CHAR(64) NULL,
            user_agent TEXT NULL,
            device_type VARCHAR(30) NOT NULL DEFAULT 'Desktop',
            browser_name VARCHAR(50) NOT NULL DEFAULT 'Unknown',
            visit_date DATE NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_visit_date (visit_date),
            INDEX idx_visitor_key (visitor_key),
            INDEX idx_page_path (page_path)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
}

function analytics_visitor_key() {
    $cookie_name = 'ai_solutions_visitor';
    $existing = (string) ($_COOKIE[$cookie_name] ?? '');
    if (preg_match('/^[a-f0-9]{32}$/', $existing)) {
        return $existing;
    }

    $visitor_key = bin2hex(random_bytes(16));
    $is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie($cookie_name, $visitor_key, [
        'expires' => time() + 60 * 60 * 24 * 365,
        'path' => '/',
        'domain' => '',
        'secure' => $is_secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE[$cookie_name] = $visitor_key;
    return $visitor_key;
}

function analytics_device_type($user_agent) {
    $ua = strtolower((string) $user_agent);
    if (strpos($ua, 'tablet') !== false || strpos($ua, 'ipad') !== false) {
        return 'Tablet';
    }
    if (strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false) {
        return 'Mobile';
    }
    return 'Desktop';
}

function analytics_browser_name($user_agent) {
    $ua = strtolower((string) $user_agent);
    if (strpos($ua, 'edg/') !== false) {
        return 'Edge';
    }
    if (strpos($ua, 'chrome/') !== false) {
        return 'Chrome';
    }
    if (strpos($ua, 'firefox/') !== false) {
        return 'Firefox';
    }
    if (strpos($ua, 'safari/') !== false) {
        return 'Safari';
    }
    return 'Unknown';
}

function track_public_visit($page_title = '', $page_name = '') {
    if (($_COOKIE['ai_solutions_cookie_consent'] ?? '') !== 'accepted') {
        return;
    }

    $pdo = analytics_safe_db();
    if (!$pdo) {
        return;
    }

    try {
        ensure_website_visits_table($pdo);
        $user_agent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1000);
        $page_path = substr((string) ($_SERVER['REQUEST_URI'] ?? ($_SERVER['SCRIPT_NAME'] ?? '')), 0, 255);
        $referrer = substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 500);
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ip_hash = $ip !== '' ? hash('sha256', $ip . '|' . SITE_NAME) : null;

        $stmt = $pdo->prepare('INSERT INTO website_visits(visitor_key,session_key,page_path,page_title,page_name,referrer,ip_hash,user_agent,device_type,browser_name,visit_date,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,CURDATE(),NOW())');
        $stmt->execute([
            analytics_visitor_key(),
            session_id() ?: null,
            $page_path,
            substr((string) $page_title, 0, 200),
            substr((string) $page_name, 0, 80),
            $referrer ?: null,
            $ip_hash,
            $user_agent ?: null,
            analytics_device_type($user_agent),
            analytics_browser_name($user_agent),
        ]);
    } catch (PDOException $e) {
        error_log('Visit tracking failed: ' . $e->getMessage());
    }
}

function analytics_totals(PDO $pdo) {
    ensure_website_visits_table($pdo);
    $totals = $pdo->query('SELECT COUNT(*) total_visits, COUNT(DISTINCT visitor_key) unique_visitors, SUM(visit_date = CURDATE()) today_visits FROM website_visits')->fetch();
    $returning = $pdo->query('SELECT COUNT(*) FROM (SELECT visitor_key FROM website_visits GROUP BY visitor_key HAVING COUNT(*) > 1) repeat_visitors')->fetchColumn();

    return [
        'total_visits' => (int) ($totals['total_visits'] ?? 0),
        'unique_visitors' => (int) ($totals['unique_visitors'] ?? 0),
        'today_visits' => (int) ($totals['today_visits'] ?? 0),
        'returning_visitors' => (int) $returning,
    ];
}

function analytics_daily_visits(PDO $pdo, $days = 7) {
    ensure_website_visits_table($pdo);
    $days = max(1, min(30, (int) $days));
    $interval = $days - 1;
    $stmt = $pdo->query('SELECT visit_date, COUNT(*) visits, COUNT(DISTINCT visitor_key) visitors FROM website_visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ' . $interval . ' DAY) GROUP BY visit_date ORDER BY visit_date ASC');
    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        $rows[$row['visit_date']] = $row;
    }

    $series = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime('-' . $i . ' days'));
        $series[] = [
            'date' => $date,
            'label' => date('M j', strtotime($date)),
            'visits' => (int) ($rows[$date]['visits'] ?? 0),
            'visitors' => (int) ($rows[$date]['visitors'] ?? 0),
        ];
    }
    return $series;
}

function analytics_group_counts(PDO $pdo, $column, $limit = 5) {
    ensure_website_visits_table($pdo);
    $allowed = ['page_name', 'page_path', 'device_type', 'browser_name'];
    if (!in_array($column, $allowed, true)) {
        return [];
    }

    $limit = max(1, min(10, (int) $limit));
    $stmt = $pdo->query("SELECT COALESCE(NULLIF($column, ''), 'Unknown') label, COUNT(*) total FROM website_visits GROUP BY label ORDER BY total DESC LIMIT " . $limit);
    return $stmt->fetchAll();
}

function analytics_recent_visits(PDO $pdo, $limit = 25) {
    ensure_website_visits_table($pdo);
    $limit = max(1, min(100, (int) $limit));
    $stmt = $pdo->query('SELECT * FROM website_visits ORDER BY created_at DESC, id DESC LIMIT ' . $limit);
    return $stmt->fetchAll();
}

function percent_of($value, $max) {
    $max = max(1, (int) $max);
    return max(4, min(100, round(((int) $value / $max) * 100)));
}
