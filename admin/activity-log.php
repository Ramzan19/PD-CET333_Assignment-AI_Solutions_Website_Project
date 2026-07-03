<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin_role(['super_admin']);
ensure_admin_security_schema($pdo);

$current_page = 'activity';
$page_title = 'Activity Log';
$action = trim($_GET['action'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$where = ['1=1'];
$params = [];

if ($action !== '') {
    $where[] = 'action LIKE ?';
    $params[] = '%' . $action . '%';
}
if ($date_from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) {
    $where[] = 'DATE(created_at) >= ?';
    $params[] = $date_from;
}
if ($date_to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    $where[] = 'DATE(created_at) <= ?';
    $params[] = $date_to;
}

$stmt = $pdo->prepare('SELECT * FROM admin_activity WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT 200');
$stmt->execute($params);
$activities = $stmt->fetchAll();

include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Access control</span>
        <h1>Activity Log</h1>
        <p>Review admin sign-ins, page views, exports, and sensitive actions.</p>
    </div>
</div>

<form class="search-bar visitor-filter-bar" method="get">
    <input name="action" value="<?= e($action) ?>" placeholder="Filter by action">
    <input type="date" name="date_from" value="<?= e($date_from) ?>" aria-label="From date">
    <input type="date" name="date_to" value="<?= e($date_to) ?>" aria-label="To date">
    <button class="btn btn-secondary" type="submit">Filter</button>
    <a class="btn btn-primary" href="activity-log.php">Reset</a>
</form>

<div class="table-panel">
    <div class="table-header"><h2>Recent Admin Activity</h2><span class="badge"><?= e(count($activities)) ?> shown</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>When</th><th>Admin</th><th>Role</th><th>Action</th><th>Detail</th></tr></thead>
            <tbody>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?= e(date('M j, Y g:i A', strtotime($activity['created_at']))) ?></td>
                    <td><?= e($activity['username'] ?? '') ?: '—' ?></td>
                    <td><span class="status"><?= e(ucwords(str_replace('_', ' ', $activity['role'] ?? ''))) ?: '—' ?></span></td>
                    <td><?= e($activity['action']) ?></td>
                    <td class="table-summary"><?= e($activity['detail'] ?: 'No extra detail') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$activities): ?><tr><td colspan="5">No activity found for the selected filters.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
