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

$current_page = 'dashboard';
$page_title = 'Dashboard';
$total_inquiries = $pdo->query('SELECT COUNT(*) FROM customer_inquiries')->fetchColumn();
$total_demos = $pdo->query('SELECT COUNT(*) FROM demo_bookings')->fetchColumn();
$total_events = $pdo->query('SELECT COUNT(*) FROM event_registrations')->fetchColumn();
$total_leads = $pdo->query('SELECT COUNT(*) FROM chatbot_conversations')->fetchColumn();
$total_feedback = $pdo->query('SELECT COUNT(*) FROM visitor_feedback')->fetchColumn();
$feedback_overview = feedback_stats($pdo);
$visit_totals = analytics_totals($pdo);
$daily_visits = analytics_daily_visits($pdo, 7);
$page_counts = analytics_group_counts($pdo, 'page_name', 5);
$device_counts = analytics_group_counts($pdo, 'device_type', 4);
$country_counts = assignment_country_counts($pdo, 5);
$interest_counts = assignment_interest_counts($pdo, 6);
$virtual_assistant_demand = assignment_keyword_count($pdo, ['virtual assistant', 'assistant']);
$prototype_demand = assignment_keyword_count($pdo, ['prototype', 'prototyping']);
$total_customer_demand = (int) $total_inquiries + (int) $total_demos + (int) $total_events + (int) $total_leads;
$activity_metrics = [
    ['label' => 'Visitors', 'value' => $visit_totals['unique_visitors']],
    ['label' => 'Feedback', 'value' => (int) $total_feedback],
    ['label' => 'Inquiries', 'value' => (int) $total_inquiries],
    ['label' => 'Demos', 'value' => (int) $total_demos],
    ['label' => 'Events', 'value' => (int) $total_events],
    ['label' => 'Chatbot leads', 'value' => (int) $total_leads],
];

// Data passed to Chart.js (rendered as proper bar/line/doughnut charts).
$dashboard_charts = [
    'engagement' => [
        'labels' => array_map(fn($m) => $m['label'], $activity_metrics),
        'values' => array_map(fn($m) => (int) $m['value'], $activity_metrics),
    ],
    'daily' => [
        'labels' => array_map(fn($d) => $d['label'], $daily_visits),
        'values' => array_map(fn($d) => (int) $d['visits'], $daily_visits),
    ],
    'pages' => [
        'labels' => array_map(fn($r) => ucfirst($r['label']), $page_counts),
        'values' => array_map(fn($r) => (int) $r['total'], $page_counts),
    ],
    'devices' => [
        'labels' => array_map(fn($r) => $r['label'], $device_counts),
        'values' => array_map(fn($r) => (int) $r['total'], $device_counts),
    ],
    'countries' => [
        'labels' => array_map(fn($r) => $r['label'], $country_counts),
        'values' => array_map(fn($r) => (int) $r['total'], $country_counts),
    ],
    'interests' => [
        'labels' => array_map(fn($r) => $r['label'], $interest_counts),
        'values' => array_map(fn($r) => (int) $r['total'], $interest_counts),
    ],
];

$recent = $pdo->query("SELECT full_name name,'Inquiry' type,created_at FROM customer_inquiries UNION ALL SELECT full_name name,'Demo' type,created_at FROM demo_bookings UNION ALL SELECT full_name name,'Event Registration' type,created_at FROM event_registrations UNION ALL SELECT user_name name,'Chatbot Lead' type,created_at FROM chatbot_conversations UNION ALL SELECT visitor_name name,'Feedback Rating' type,created_at FROM visitor_feedback ORDER BY created_at DESC LIMIT 8")->fetchAll();
include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Admin command center</span>
        <h1>Dashboard</h1>
        <p>Welcome, <?= e(admin_name()) ?>. Review visitors, feedback, demand, demos, and chatbot leads.</p>
    </div>
    <div class="button-row">
        <a class="btn btn-primary" href="visitors.php">Visitor Tracker</a>
        <a class="btn btn-secondary" href="manage-records.php">Manage Records</a>
    </div>
</div>
<div class="admin-grid">
    <div class="stat-card"><span class="number"><?= e($visit_totals['total_visits']) ?></span><p>Total Page Views</p></div>
    <div class="stat-card"><span class="number"><?= e($visit_totals['unique_visitors']) ?></span><p>Visitors Tracked</p></div>
    <div class="stat-card"><span class="number"><?= e($total_customer_demand) ?></span><p>Customer Demand Records</p></div>
    <div class="stat-card"><span class="number"><?= e($feedback_overview['average']) ?></span><p>Average Feedback</p></div>
    <div class="stat-card"><span class="number"><?= e($total_events) ?></span><p>Event Registrations</p></div>
    <div class="stat-card"><span class="number"><?= e($virtual_assistant_demand) ?></span><p>Assistant Interest</p></div>
    <div class="stat-card"><span class="number"><?= e($prototype_demand) ?></span><p>Prototype Interest</p></div>
</div>
<div class="dashboard-visual-grid">
    <section class="chart-panel chart-panel-wide">
        <div class="table-header"><h2>Website Engagement</h2><span class="badge">Totals by channel</span></div>
        <div class="chart-canvas-wrap" style="position:relative;height:280px;">
            <canvas id="chartEngagement" role="img" aria-label="Bar chart of engagement totals by channel"></canvas>
        </div>
    </section>
    <section class="chart-panel">
        <div class="table-header"><h2>7-Day Visitor Trend</h2><span class="badge"><?= e($visit_totals['today_visits']) ?> today</span></div>
        <div class="chart-canvas-wrap" style="position:relative;height:240px;">
            <canvas id="chartDaily" role="img" aria-label="Line chart of visits over the last 7 days"></canvas>
        </div>
    </section>
    <section class="chart-panel">
        <div class="table-header"><h2>Top Pages</h2><span class="badge">By views</span></div>
        <div class="chart-canvas-wrap" style="position:relative;height:240px;">
            <canvas id="chartPages" role="img" aria-label="Bar chart of most viewed pages"></canvas>
        </div>
    </section>
    <section class="chart-panel">
        <div class="table-header"><h2>Device Mix</h2><span class="badge">Visitors</span></div>
        <div class="chart-canvas-wrap" style="position:relative;height:240px;">
            <canvas id="chartDevices" role="img" aria-label="Doughnut chart of visitor device types"></canvas>
        </div>
    </section>
    <section class="chart-panel">
        <div class="table-header"><h2>Demand by Country</h2><span class="badge">Customer data</span></div>
        <div class="chart-canvas-wrap" style="position:relative;height:240px;">
            <canvas id="chartCountries" role="img" aria-label="Bar chart of customer demand by country"></canvas>
        </div>
    </section>
    <section class="chart-panel">
        <div class="table-header"><h2>Interest Mix</h2><span class="badge">Demos, jobs, events</span></div>
        <div class="chart-canvas-wrap" style="position:relative;height:240px;">
            <canvas id="chartInterests" role="img" aria-label="Doughnut chart of customer interest areas"></canvas>
        </div>
    </section>
</div>
<div class="table-panel">
    <div class="table-header"><h2>Recent Submissions</h2><span class="badge">Live database data</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Name</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $row): ?>
                <tr><td><?= e($row['name']) ?></td><td><?= e($row['type']) ?></td><td><?= e($row['created_at']) ?></td><td><a class="action-link" href="manage-records.php">View</a></td></tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="4">No records yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>window.DASH = <?= json_encode($dashboard_charts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="../assets/js/vendor/chart.umd.js?v=<?= filemtime(__DIR__ . '/../assets/js/vendor/chart.umd.js') ?>"></script>
<script>
(function () {
    if (!window.Chart || !window.DASH) { return; }
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#5b6b7a';
    var anim = reduce ? false : { duration: 650 };
    var palette = ['#1ab8c7', '#f26a4f', '#ffb07a', '#2ec4a6', '#6c8cff', '#f5c451', '#8b5cf6', '#34d399'];
    var sum = function (a) { return (a || []).reduce(function (s, n) { return s + (+n || 0); }, 0); };

    function render(id, builder, data) {
        var c = document.getElementById(id);
        if (!c) { return; }
        if (!data.values || !data.values.length || sum(data.values) === 0) {
            var note = document.createElement('p');
            note.className = 'empty-note';
            note.textContent = 'No data tracked yet.';
            c.parentNode.replaceChild(note, c);
            return;
        }
        new Chart(c.getContext('2d'), builder(data));
    }

    var barOpts = { responsive: true, maintainAspectRatio: false, animation: anim, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } };
    var hbarOpts = { responsive: true, maintainAspectRatio: false, animation: anim, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } };
    var lineOpts = { responsive: true, maintainAspectRatio: false, animation: anim, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } };
    var doughOpts = { responsive: true, maintainAspectRatio: false, animation: anim, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } } };

    render('chartEngagement', function (d) {
        return { type: 'bar', data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: d.labels.map(function (_, i) { return palette[i % palette.length]; }), borderRadius: 6, maxBarThickness: 48 }] }, options: barOpts };
    }, window.DASH.engagement);

    render('chartDaily', function (d) {
        return { type: 'line', data: { labels: d.labels, datasets: [{ data: d.values, borderColor: '#1ab8c7', backgroundColor: 'rgba(26,184,199,0.15)', fill: true, tension: 0.35, pointRadius: 3, pointBackgroundColor: '#1ab8c7', borderWidth: 2 }] }, options: lineOpts };
    }, window.DASH.daily);

    render('chartPages', function (d) {
        return { type: 'bar', data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: '#1ab8c7', borderRadius: 6, maxBarThickness: 26 }] }, options: hbarOpts };
    }, window.DASH.pages);

    render('chartDevices', function (d) {
        return { type: 'doughnut', data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: palette, borderWidth: 2, borderColor: '#ffffff' }] }, options: doughOpts };
    }, window.DASH.devices);

    render('chartCountries', function (d) {
        return { type: 'bar', data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: '#2ec4a6', borderRadius: 6, maxBarThickness: 40 }] }, options: barOpts };
    }, window.DASH.countries);

    render('chartInterests', function (d) {
        return { type: 'doughnut', data: { labels: d.labels, datasets: [{ data: d.values, backgroundColor: palette, borderWidth: 2, borderColor: '#ffffff' }] }, options: doughOpts };
    }, window.DASH.interests);
})();
</script>
<?php include 'admin-footer.php'; ?>
