<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

security_headers();

$current_page = $current_page ?? '';
$page_title = $page_title ?? 'Admin';
$admin_nav_groups = [
    'Overview' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'DB'],
        ['key' => 'visitors', 'label' => 'Visitors', 'href' => 'visitors.php', 'icon' => 'VI'],
        ['key' => 'reports', 'label' => 'Monthly Reports', 'href' => 'monthly-report.php', 'icon' => 'MR'],
    ],
    'Customer Data' => [
        ['key' => 'inquiries', 'label' => 'Inquiries', 'href' => 'inquiries.php', 'icon' => 'IN'],
        ['key' => 'bookings', 'label' => 'Demo Bookings', 'href' => 'demo-bookings.php', 'icon' => 'DM'],
        ['key' => 'events', 'label' => 'Events', 'href' => 'event-registrations.php', 'icon' => 'EV'],
        ['key' => 'leads', 'label' => 'Chatbot Leads', 'href' => 'chatbot-leads.php', 'icon' => 'CL'],
        ['key' => 'feedback', 'label' => 'Feedback', 'href' => 'visitor-feedback.php', 'icon' => 'FB'],
        ['key' => 'solution-reviews', 'label' => 'Solution Reviews', 'href' => 'solution-reviews.php', 'icon' => 'SR'],
    ],
    'Website Content' => [
        ['key' => 'content', 'label' => 'Content Manager', 'href' => 'content.php', 'icon' => 'CM'],
        ['key' => 'content-text', 'label' => 'Site Text', 'href' => 'content-text.php', 'icon' => 'TX'],
    ],
    'Operations' => [
        ['key' => 'smtp', 'label' => 'SMTP', 'href' => 'smtp-settings.php', 'icon' => 'SM'],
        ['key' => 'outbox', 'label' => 'Outbox', 'href' => 'email-outbox.php', 'icon' => 'OB'],
        ['key' => 'records', 'label' => 'Records', 'href' => 'manage-records.php', 'icon' => 'RC'],
        ['key' => 'security', 'label' => 'Security (2FA)', 'href' => 'security.php', 'icon' => 'SC'],
        ['key' => 'activity', 'label' => 'Activity Log', 'href' => 'activity-log.php', 'icon' => 'AL'],
    ],
];
if (isset($pdo) && $pdo instanceof PDO && !defined('ADMIN_ACTIVITY_LOGGED')) {
    define('ADMIN_ACTIVITY_LOGGED', true);
    admin_log_activity($pdo, 'view', $page_title);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | AI-Solutions Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" aria-label="Admin control panel">
        <a class="brand admin-sidebar-brand" href="dashboard.php" aria-label="AI-Solutions admin dashboard">
            <span class="brand-logo ai-brand-mark" aria-hidden="true">
                <span class="mark-core">AI</span>
                <span class="mark-node node-a"></span>
                <span class="mark-node node-b"></span>
                <span class="mark-node node-c"></span>
            </span>
            <span class="admin-brand-copy">
                <span class="admin-brand-name">AI-Solutions</span>
                <span class="admin-brand-label">Admin Panel</span>
            </span>
        </a>

        <div class="admin-user-card">
            <span>Signed in</span>
            <strong><?= e(admin_name()) ?></strong>
            <small><?= e(ucwords(str_replace('_', ' ', admin_role()))) ?></small>
        </div>

        <nav class="admin-sidebar-nav" aria-label="Admin navigation">
            <?php foreach ($admin_nav_groups as $group_label => $items): ?>
                <div class="admin-nav-group">
                    <span class="admin-nav-heading"><?= e($group_label) ?></span>
                    <?php foreach ($items as $item): ?>
                        <a class="<?= $current_page === $item['key'] ? 'active' : '' ?>" href="<?= e($item['href']) ?>" title="<?= e($item['label']) ?>">
                            <span class="admin-nav-icon" aria-hidden="true"><?= e($item['icon']) ?></span>
                            <span><?= e($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <div class="admin-sidebar-footer">
            <a class="admin-site-link" href="../index.php">View website</a>
            <form class="admin-logout-form" method="post" action="logout.php">
                <?= csrf_field() ?>
                <button type="submit">
                    <span class="admin-nav-icon" aria-hidden="true">LO</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="admin-content">
        <main class="admin-main">
