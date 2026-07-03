<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/analytics.php';
require_admin();
ensure_website_visits_table($pdo);

$current_page = 'visitors';
$page_title = 'Visitor Tracker';
$search = trim($_GET['search'] ?? '');
$device = trim($_GET['device'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$allowed_devices = ['Desktop', 'Mobile', 'Tablet'];
$where = ['1=1'];
$params = [];

if ($search !== '') {
    $where[] = '(visitor_key LIKE ? OR page_path LIKE ? OR page_title LIKE ? OR page_name LIKE ? OR browser_name LIKE ? OR referrer LIKE ?)';
    $needle = '%' . $search . '%';
    $params = array_merge($params, [$needle, $needle, $needle, $needle, $needle, $needle]);
}

if ($device !== '' && in_array($device, $allowed_devices, true)) {
    $where[] = 'device_type = ?';
    $params[] = $device;
}

if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $where[] = 'visit_date >= ?';
    $params[] = $date_from;
}

if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $where[] = 'visit_date <= ?';
    $params[] = $date_to;
}

$stmt = $pdo->prepare('SELECT * FROM website_visits WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC, id DESC LIMIT 100');
$stmt->execute($params);
$visits = $stmt->fetchAll();

$visit_totals = analytics_totals($pdo);
$daily_visits = analytics_daily_visits($pdo, 14);
$page_counts = analytics_group_counts($pdo, 'page_name', 6);
$device_counts = analytics_group_counts($pdo, 'device_type', 4);
$browser_counts = analytics_group_counts($pdo, 'browser_name', 5);
$max_daily = max(1, ...array_map(fn($day) => (int) $day['visits'], $daily_visits));
$max_pages = max(1, ...array_map(fn($row) => (int) $row['total'], $page_counts ?: [['total' => 0]]));
$max_devices = max(1, ...array_map(fn($row) => (int) $row['total'], $device_counts ?: [['total' => 0]]));
$max_browsers = max(1, ...array_map(fn($row) => (int) $row['total'], $browser_counts ?: [['total' => 0]]));

include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Separate visitor tracker</span>
        <h1>Visitors</h1>
        <p>Track anonymous page views, visitor keys, devices, browsers, referrers, and timestamps.</p>
    </div>
    <a class="btn btn-primary" href="dashboard.php">Back to Dashboard</a>
</div>

<div class="admin-grid">
    <div class="stat-card"><span class="number"><?= e($visit_totals['total_visits']) ?></span><p>Total Page Views</p></div>
    <div class="stat-card"><span class="number"><?= e($visit_totals['unique_visitors']) ?></span><p>Unique Visitors</p></div>
    <div class="stat-card"><span class="number"><?= e($visit_totals['today_visits']) ?></span><p>Today</p></div>
    <div class="stat-card"><span class="number"><?= e($visit_totals['returning_visitors']) ?></span><p>Returning Visitors</p></div>
</div>

<div class="dashboard-visual-grid tracker-visual-grid">
    <section class="chart-panel chart-panel-wide">
        <div class="table-header"><h2>14-Day Visit Pattern</h2><span class="badge">Page views</span></div>
        <div class="daily-chart wide-daily-chart" aria-label="Visits over the last 14 days">
            <?php foreach ($daily_visits as $day): ?>
                <div class="daily-bar">
                    <span class="daily-fill" style="height: <?= e(percent_of($day['visits'], $max_daily)) ?>%"></span>
                    <strong><?= e($day['visits']) ?></strong>
                    <small><?= e($day['label']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="chart-panel">
        <div class="table-header"><h2>Pages Visited</h2><span class="badge">Top pages</span></div>
        <div class="progress-stack compact-stack">
            <?php foreach ($page_counts as $row): ?>
                <div class="progress-metric teal">
                    <div><strong><?= e(ucfirst($row['label'])) ?></strong><span><?= e($row['total']) ?> views</span></div>
                    <div class="progress-track"><span style="width: <?= e(percent_of($row['total'], $max_pages)) ?>%"></span></div>
                </div>
            <?php endforeach; ?>
            <?php if (!$page_counts): ?><p class="empty-note">No page visits tracked yet.</p><?php endif; ?>
        </div>
    </section>
    <section class="chart-panel">
        <div class="table-header"><h2>Device Split</h2><span class="badge">Device type</span></div>
        <div class="progress-stack compact-stack">
            <?php foreach ($device_counts as $row): ?>
                <div class="progress-metric amber">
                    <div><strong><?= e($row['label']) ?></strong><span><?= e($row['total']) ?> visits</span></div>
                    <div class="progress-track"><span style="width: <?= e(percent_of($row['total'], $max_devices)) ?>%"></span></div>
                </div>
            <?php endforeach; ?>
            <?php if (!$device_counts): ?><p class="empty-note">No device data tracked yet.</p><?php endif; ?>
        </div>
    </section>
    <section class="chart-panel">
        <div class="table-header"><h2>Browsers</h2><span class="badge">Detected</span></div>
        <div class="progress-stack compact-stack">
            <?php foreach ($browser_counts as $row): ?>
                <div class="progress-metric coral">
                    <div><strong><?= e($row['label']) ?></strong><span><?= e($row['total']) ?> visits</span></div>
                    <div class="progress-track"><span style="width: <?= e(percent_of($row['total'], $max_browsers)) ?>%"></span></div>
                </div>
            <?php endforeach; ?>
            <?php if (!$browser_counts): ?><p class="empty-note">No browser data tracked yet.</p><?php endif; ?>
        </div>
    </section>
</div>

<form class="search-bar visitor-filter-bar" method="get">
    <input name="search" value="<?= e($search) ?>" placeholder="Search visitor, page, browser, or referrer">
    <select name="device" aria-label="Filter by device">
        <option value="">All devices</option>
        <?php foreach ($allowed_devices as $device_option): ?>
            <option value="<?= e($device_option) ?>" <?= selected($device, $device_option) ?>><?= e($device_option) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="date_from" value="<?= e($date_from) ?>" aria-label="From date">
    <input type="date" name="date_to" value="<?= e($date_to) ?>" aria-label="To date">
    <button class="btn btn-secondary" type="submit">Filter</button>
    <a class="btn btn-primary" href="visitors.php">Reset</a>
</form>

<div class="table-panel visitor-table-panel">
    <div class="table-header"><h2>Recent Visitor Activity</h2><span class="badge"><?= e(count($visits)) ?> shown</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Visitor</th><th>Page</th><th>Device</th><th>Browser</th><th>Referrer</th><th>Visited At</th></tr></thead>
            <tbody>
            <?php foreach ($visits as $visit): ?>
                <tr>
                    <td><span class="visitor-key"><?= e(substr($visit['visitor_key'], 0, 10)) ?></span></td>
                    <td><strong><?= e(ucfirst($visit['page_name'] ?: 'Page')) ?></strong><br><span class="muted-text"><?= e($visit['page_path']) ?></span></td>
                    <td><span class="status <?= e(strtolower($visit['device_type'])) ?>"><?= e($visit['device_type']) ?></span></td>
                    <td><?= e($visit['browser_name']) ?></td>
                    <td class="table-summary"><?= e($visit['referrer'] ?: 'Direct visit') ?></td>
                    <td><?= e(date('M j, Y g:i A', strtotime($visit['created_at'] ?? 'now'))) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$visits): ?><tr><td colspan="6">No visits found for the selected filters.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
