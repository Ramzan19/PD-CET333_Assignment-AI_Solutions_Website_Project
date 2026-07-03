<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/report-export.php';
require_once __DIR__ . '/../includes/schema.php';
require_admin();
ensure_customer_inquiry_schema($pdo);

$current_page = 'inquiries';
$page_title = 'Customer Inquiries';
$export_message = export_status_message($_GET['export'] ?? '');
$search = trim($_GET['search'] ?? '');
$filter_country = trim($_GET['country'] ?? '');
$filter_job = trim($_GET['job_title'] ?? '');
$filter_status = trim($_GET['status'] ?? '');
$filter_priority = trim($_GET['priority'] ?? '');

// Whitelist of sortable columns -> SQL column (prevents SQL injection via sort key).
$sortable = [
    'full_name' => 'full_name',
    'email' => 'email',
    'company_name' => 'company_name',
    'country' => 'country',
    'solution_interest' => 'solution_interest',
    'job_title' => 'job_title',
    'status' => 'status',
    'priority' => "FIELD(priority, 'High', 'Normal', 'Low')",
    'created_at' => 'created_at',
];
$sort = $_GET['sort'] ?? 'created_at';
if (!isset($sortable[$sort])) {
    $sort = 'created_at';
}
$dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = '(full_name LIKE ? OR email LIKE ? OR company_name LIKE ? OR country LIKE ? OR solution_interest LIKE ?)';
    $needle = '%' . $search . '%';
    array_push($params, $needle, $needle, $needle, $needle, $needle);
}
if ($filter_country !== '') {
    $conditions[] = 'country = ?';
    $params[] = $filter_country;
}
if ($filter_job !== '') {
    $conditions[] = 'job_title = ?';
    $params[] = $filter_job;
}
if ($filter_status !== '') {
    $conditions[] = 'status = ?';
    $params[] = $filter_status;
}
if ($filter_priority !== '') {
    $conditions[] = 'priority = ?';
    $params[] = $filter_priority;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$stmt = $pdo->prepare('SELECT * FROM customer_inquiries ' . $where . ' ORDER BY ' . $sortable[$sort] . ' ' . $dir);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Distinct values for the filter dropdowns.
$countries = $pdo->query("SELECT DISTINCT country FROM customer_inquiries WHERE country <> '' ORDER BY country")->fetchAll(PDO::FETCH_COLUMN);
$job_titles = $pdo->query("SELECT DISTINCT job_title FROM customer_inquiries WHERE job_title <> '' ORDER BY job_title")->fetchAll(PDO::FETCH_COLUMN);
$statuses = $pdo->query("SELECT DISTINCT status FROM customer_inquiries WHERE status <> '' ORDER BY status")->fetchAll(PDO::FETCH_COLUMN);
$priorities = ['High', 'Normal', 'Low'];

// Build a sortable column header link that preserves active filters.
function inquiry_sort_link($column, $label, $sort, $dir, array $context) {
    $next_dir = ($sort === $column && $dir === 'ASC') ? 'desc' : 'asc';
    $query = array_merge($context, ['sort' => $column, 'dir' => $next_dir]);
    $arrow = '';
    if ($sort === $column) {
        $arrow = '<span class="sort-arrow">' . ($dir === 'ASC' ? '&#9650;' : '&#9660;') . '</span>';
    }
    return '<th class="sortable"><a href="inquiries.php?' . e(http_build_query(array_filter($query, fn($v) => $v !== ''))) . '">' . e($label) . ' ' . $arrow . '</a></th>';
}

$filter_context = [
    'search' => $search,
    'country' => $filter_country,
    'job_title' => $filter_job,
    'status' => $filter_status,
    'priority' => $filter_priority,
];
include 'admin-header.php';
?>
<div class="admin-hero">
    <div><h1>Customer Inquiries</h1><p>View, search, sort, filter, update, and safely delete customer inquiry records.</p></div>
    <?= render_export_all_toolbar('inquiries', 'inquiries') ?>
</div>
<form class="filter-bar" method="get">
    <div class="filter-field">
        <label for="search">Search</label>
        <input id="search" name="search" value="<?= e($search) ?>" placeholder="Name, email, or company">
    </div>
    <div class="filter-field">
        <label for="country">Country</label>
        <select id="country" name="country">
            <option value="">All countries</option>
            <?php foreach ($countries as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($filter_country, $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field">
        <label for="job_title">Job title</label>
        <select id="job_title" name="job_title">
            <option value="">All job titles</option>
            <?php foreach ($job_titles as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($filter_job, $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($filter_status, $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field">
        <label for="priority">Priority</label>
        <select id="priority" name="priority">
            <option value="">All priorities</option>
            <?php foreach ($priorities as $option): ?>
                <option value="<?= e($option) ?>" <?= selected($filter_priority, $option) ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <input type="hidden" name="sort" value="<?= e($sort) ?>">
    <input type="hidden" name="dir" value="<?= e(strtolower($dir)) ?>">
    <div class="filter-field">
        <button class="btn btn-secondary" type="submit">Apply</button>
    </div>
    <div class="filter-field">
        <a class="btn btn-primary" href="inquiries.php">Reset</a>
    </div>
</form>
<?php if (($_GET['msg'] ?? '') === 'email_sent'): ?><div class="alert alert-success">Your email was sent to the customer's registered address.</div><?php endif; ?>
<?php if ($export_message): ?><div class="alert alert-<?= e($export_message[0]) ?>"><?= e($export_message[1]) ?></div><?php endif; ?>
<div class="table-panel">
    <div class="table-header"><h2>Customer Inquiries</h2><span class="badge"><?= count($records) ?> records</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr>
                <?= inquiry_sort_link('full_name', 'Name', $sort, $dir, $filter_context) ?>
                <?= inquiry_sort_link('email', 'Email', $sort, $dir, $filter_context) ?>
                <?= inquiry_sort_link('company_name', 'Company', $sort, $dir, $filter_context) ?>
                <?= inquiry_sort_link('country', 'Country', $sort, $dir, $filter_context) ?>
                <?= inquiry_sort_link('solution_interest', 'Interest', $sort, $dir, $filter_context) ?>
                <?= inquiry_sort_link('job_title', 'Job Title', $sort, $dir, $filter_context) ?>
                <?= inquiry_sort_link('priority', 'Priority', $sort, $dir, $filter_context) ?>
                <?= inquiry_sort_link('status', 'Status', $sort, $dir, $filter_context) ?>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?= e($r['full_name'] ?? '') ?></td>
                    <td><?= e($r['email'] ?? '') ?></td>
                    <td><?= e($r['company_name'] ?? '') ?></td>
                    <td><?= e($r['country'] ?? '') ?></td>
                    <td><?= e($r['solution_interest'] ?? 'General AI Solution') ?></td>
                    <td><?= e($r['job_title'] ?? '') ?></td>
                    <td><span class="priority-badge priority-<?= e(strtolower($r['priority'] ?? 'normal')) ?>"><?= e($r['priority'] ?? 'Normal') ?></span></td>
                    <td><span class="status <?= e(strtolower(str_replace(' ', '-', $r['status'] ?? 'new'))) ?>"><?= e($r['status'] ?? 'New') ?></span></td>
                    <td>
                        <div class="table-actions">
                            <a class="action-link" href="view-record.php?type=inquiry&id=<?= e($r['id']) ?>">View</a>
                            <a class="action-link" href="edit-record.php?type=inquiry&id=<?= e($r['id']) ?>">Edit</a>
                            <a class="action-link action-email" href="send-message.php?type=inquiry&id=<?= e($r['id']) ?>">Reply</a>
                            <form class="inline-form" method="post" action="delete-record.php" onsubmit="return confirm('Delete this record?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="type" value="inquiry">
                                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                                <button class="action-link action-delete" type="submit">Delete</button>
                            </form>
                            <a class="action-link action-export" href="export-records.php?action=download&record_type=inquiry&id=<?= e($r['id']) ?>&format=csv">CSV</a>
                            <a class="action-link action-export" href="export-records.php?action=download&record_type=inquiry&id=<?= e($r['id']) ?>&format=pdf">PDF</a>
                            <form class="inline-form" method="post" action="export-records.php">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="email">
                                <input type="hidden" name="record_type" value="inquiry">
                                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                                <input type="hidden" name="format" value="csv">
                                <button class="action-link action-email" type="submit">Email</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$records): ?><tr><td colspan="9">No records found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
