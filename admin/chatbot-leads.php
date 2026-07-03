<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/report-export.php';
require_once __DIR__ . '/../includes/schema.php';
require_admin();
ensure_chatbot_schema($pdo);

$current_page = 'leads';
$page_title = 'Chatbot Leads';
$export_message = export_status_message($_GET['export'] ?? '');
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = 'WHERE user_name LIKE ? OR email LIKE ? OR company_name LIKE ? OR phone LIKE ? OR country LIKE ?';
    $needle = '%' . $search . '%';
    $params = [$needle, $needle, $needle, $needle, $needle];
}

$stmt = $pdo->prepare('SELECT * FROM chatbot_conversations ' . $where . ' ORDER BY created_at DESC');
$stmt->execute($params);
$records = $stmt->fetchAll();
include 'admin-header.php';
?>
<div class="admin-hero">
    <div><h1>Chatbot Leads</h1><p>Review chatbot handover requests and sales or support leads.</p></div>
    <?= render_export_all_toolbar('chatbot', 'chatbot leads') ?>
</div>
<form class="search-bar" method="get">
    <input name="search" value="<?= e($search) ?>" placeholder="Search by name, email, or company">
    <button class="btn btn-secondary" type="submit">Search</button>
    <a class="btn btn-primary" href="chatbot-leads.php">Reset</a>
</form>
<?php if (($_GET['msg'] ?? '') === 'email_sent'): ?><div class="alert alert-success">Your email was sent to the customer's registered address.</div><?php endif; ?>
<?php if ($export_message): ?><div class="alert alert-<?= e($export_message[0]) ?>"><?= e($export_message[1]) ?></div><?php endif; ?>
<div class="table-panel">
    <div class="table-header"><h2>Chatbot Leads</h2><span class="badge"><?= count($records) ?> records</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Company</th><th>Country</th><th>Topic</th><th>Summary</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?= e($r['user_name'] ?? '') ?></td>
                    <td><?= e($r['email'] ?? '') ?></td>
                    <td><?= e($r['phone'] ?? '') ?></td>
                    <td><?= e($r['company_name'] ?? '') ?></td>
                    <td><?= e($r['country'] ?? '') ?></td>
                    <td><?= e($r['topic'] ?? '') ?></td>
                    <td class="table-summary"><?= e($r['chat_summary'] ?? '') ?></td>
                    <td><span class="status <?= e(strtolower(str_replace(' ', '-', $r['status'] ?? 'new'))) ?>"><?= e($r['status'] ?? 'New') ?></span></td>
                    <td>
                        <div class="table-actions">
                            <a class="action-link" href="view-record.php?type=chatbot&id=<?= e($r['id']) ?>">View</a>
                            <a class="action-link" href="edit-record.php?type=chatbot&id=<?= e($r['id']) ?>">Edit</a>
                            <a class="action-link action-email" href="send-message.php?type=chatbot&id=<?= e($r['id']) ?>">Reply</a>
                            <form class="inline-form" method="post" action="delete-record.php" onsubmit="return confirm('Delete this record?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="type" value="chatbot">
                                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                                <button class="action-link action-delete" type="submit">Delete</button>
                            </form>
                            <a class="action-link action-export" href="export-records.php?action=download&record_type=chatbot&id=<?= e($r['id']) ?>&format=csv">CSV</a>
                            <a class="action-link action-export" href="export-records.php?action=download&record_type=chatbot&id=<?= e($r['id']) ?>&format=pdf">PDF</a>
                            <form class="inline-form" method="post" action="export-records.php">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="email">
                                <input type="hidden" name="record_type" value="chatbot">
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
