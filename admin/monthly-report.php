<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/../includes/schema.php';
require_admin();
ensure_assignment_schema($pdo);
ensure_visitor_feedback_table($pdo);
ensure_website_visits_table($pdo);

$current_page = 'reports';
$page_title = 'Monthly Reports';
$month = trim($_GET['month'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m');
}
$start = $month . '-01';
$end = date('Y-m-t', strtotime($start));

function monthly_count(PDO $pdo, $table, $start, $end) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$start, $end]);
    return (int) $stmt->fetchColumn();
}

$visits_stmt = $pdo->prepare('SELECT COUNT(*) total, COUNT(DISTINCT visitor_key) visitors FROM website_visits WHERE visit_date BETWEEN ? AND ?');
$visits_stmt->execute([$start, $end]);
$visit_row = $visits_stmt->fetch();
$daily_stmt = $pdo->prepare('SELECT visit_date, COUNT(*) visits FROM website_visits WHERE visit_date BETWEEN ? AND ? GROUP BY visit_date ORDER BY visit_date');
$daily_stmt->execute([$start, $end]);
$daily_rows = $daily_stmt->fetchAll();
$daily_map = [];
foreach ($daily_rows as $row) {
    $daily_map[$row['visit_date']] = (int) $row['visits'];
}
$daily_series = [];
$cursor = strtotime($start);
$last = strtotime($end);
while ($cursor <= $last) {
    $date = date('Y-m-d', $cursor);
    $daily_series[] = ['label' => date('M j', $cursor), 'visits' => $daily_map[$date] ?? 0];
    $cursor = strtotime('+1 day', $cursor);
}
$max_daily = max(1, ...array_map(fn($day) => $day['visits'], $daily_series));
$metrics = [
    ['label' => 'Page Views', 'value' => (int) ($visit_row['total'] ?? 0)],
    ['label' => 'Unique Visitors', 'value' => (int) ($visit_row['visitors'] ?? 0)],
    ['label' => 'Inquiries', 'value' => monthly_count($pdo, 'customer_inquiries', $start, $end)],
    ['label' => 'Demo Bookings', 'value' => monthly_count($pdo, 'demo_bookings', $start, $end)],
    ['label' => 'Event RSVPs', 'value' => monthly_count($pdo, 'event_registrations', $start, $end)],
    ['label' => 'Chatbot Leads', 'value' => monthly_count($pdo, 'chatbot_conversations', $start, $end)],
    ['label' => 'Feedback Ratings', 'value' => monthly_count($pdo, 'visitor_feedback', $start, $end)],
];

include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Monthly performance</span>
        <h1>Monthly Reports</h1>
        <p>Review site performance, conversions, and admin-ready demand signals for <?= e(date('F Y', strtotime($start))) ?>.</p>
    </div>
</div>

<form class="search-bar" method="get">
    <input type="month" name="month" value="<?= e($month) ?>" aria-label="Report month">
    <button class="btn btn-secondary" type="submit">View Report</button>
    <a class="btn btn-primary" href="export-records.php?type=all&format=csv">Export All CSV</a>
</form>

<div class="admin-grid">
    <?php foreach ($metrics as $metric): ?>
        <div class="stat-card"><span class="number"><?= e($metric['value']) ?></span><p><?= e($metric['label']) ?></p></div>
    <?php endforeach; ?>
</div>

<section class="chart-panel">
    <div class="table-header"><h2>Daily Page Views</h2><span class="badge"><?= e(date('M Y', strtotime($start))) ?></span></div>
    <div class="daily-chart wide-daily-chart" aria-label="Daily page views for the selected month">
        <?php foreach ($daily_series as $day): ?>
            <div class="daily-bar">
                <span class="daily-fill" style="height: <?= e(percent_of($day['visits'], $max_daily)) ?>%"></span>
                <strong><?= e($day['visits']) ?></strong>
                <small><?= e($day['label']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include 'admin-footer.php'; ?>
