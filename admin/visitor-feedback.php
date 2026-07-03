<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
require_once __DIR__ . '/../includes/report-export.php';
require_admin();

ensure_visitor_feedback_table($pdo);

$current_page = 'feedback';
$page_title = 'Visitor Feedback';
$allowed_statuses = ['New', 'Reviewed', 'Featured', 'Archived'];
$errors = [];
$updated = ($_GET['updated'] ?? '') === '1';
$export_message = export_status_message($_GET['export'] ?? '');

if (is_post() && ($_POST['action'] ?? '') === 'update_feedback') {
    csrf_or_fail();

    $id = (int) ($_POST['id'] ?? 0);
    $status = post_value('status', 50);
    $admin_note = post_value('admin_note', 2000);
    $is_featured = !empty($_POST['is_featured']) ? 1 : 0;

    if ($id <= 0) {
        $errors[] = 'Invalid feedback record.';
    }
    if (!in_array($status, $allowed_statuses, true)) {
        $errors[] = 'Please select a valid status.';
    }
    if ($status === 'Featured') {
        $is_featured = 1;
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE visitor_feedback SET status = ?, admin_note = ?, is_featured = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $admin_note, $is_featured, $id]);
        redirect('visitor-feedback.php?updated=1');
    }
}

$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$rating_filter = (int) ($_GET['rating'] ?? 0);
$where = ['1=1'];
$params = [];

if ($search !== '') {
    $where[] = '(visitor_name LIKE ? OR email LIKE ? OR company_name LIKE ? OR role_title LIKE ? OR message LIKE ?)';
    $needle = '%' . $search . '%';
    $params = array_merge($params, [$needle, $needle, $needle, $needle, $needle]);
}

if ($status_filter !== '' && in_array($status_filter, $allowed_statuses, true)) {
    $where[] = 'status = ?';
    $params[] = $status_filter;
}

if ($rating_filter >= 1 && $rating_filter <= 5) {
    $where[] = 'rating = ?';
    $params[] = $rating_filter;
}

$stmt = $pdo->prepare('SELECT * FROM visitor_feedback WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC, id DESC');
$stmt->execute($params);
$records = $stmt->fetchAll();

$stats = $pdo->query('SELECT COUNT(*) total, AVG(rating) average_rating, SUM(status = "New") new_count, SUM(is_featured = 1) featured_count FROM visitor_feedback')->fetch();
$total_feedback = (int) ($stats['total'] ?? 0);
$average_rating = $total_feedback ? number_format((float) $stats['average_rating'], 1) : '0.0';
$new_feedback = (int) ($stats['new_count'] ?? 0);
$featured_feedback = (int) ($stats['featured_count'] ?? 0);
$rating_labels = feedback_rating_labels();

include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Visitor voice</span>
        <h1>Feedback Ratings</h1>
        <p>Review homepage ratings, visitor details, messages, timestamps, and featured testimonials.</p>
    </div>
    <div class="hero-aside">
        <a class="btn btn-primary" href="../index.php#visitor-feedback">View Homepage Wall</a>
        <?= render_export_all_toolbar('feedback', 'feedback') ?>
    </div>
</div>

<div class="admin-grid">
    <div class="stat-card"><span class="number"><?= e($total_feedback) ?></span><p>Total Feedback</p></div>
    <div class="stat-card"><span class="number"><?= e($average_rating) ?></span><p>Average Rating</p></div>
    <div class="stat-card"><span class="number"><?= e($new_feedback) ?></span><p>New Reviews</p></div>
    <div class="stat-card"><span class="number"><?= e($featured_feedback) ?></span><p>Featured</p></div>
</div>

<?php if (($_GET['msg'] ?? '') === 'email_sent'): ?><div class="alert alert-success">Your email was sent to the visitor's registered address.</div><?php endif; ?>
<?php if ($updated): ?><div class="alert alert-success">Feedback record updated successfully.</div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
<?php if ($export_message): ?><div class="alert alert-<?= e($export_message[0]) ?>"><?= e($export_message[1]) ?></div><?php endif; ?>

<form class="search-bar feedback-filter-bar" method="get">
    <input name="search" value="<?= e($search) ?>" placeholder="Search name, email, organization, role, or message">
    <select name="status" aria-label="Filter by status">
        <option value="">All statuses</option>
        <?php foreach ($allowed_statuses as $status_option): ?>
            <option value="<?= e($status_option) ?>" <?= selected($status_filter, $status_option) ?>><?= e($status_option) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="rating" aria-label="Filter by rating">
        <option value="0">All ratings</option>
        <?php foreach ($rating_labels as $rating_value => $rating_label): ?>
            <option value="<?= e($rating_value) ?>" <?= selected($rating_filter, $rating_value) ?>><?= e($rating_value . ' - ' . $rating_label) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-secondary" type="submit">Filter</button>
    <a class="btn btn-primary" href="visitor-feedback.php">Reset</a>
</form>

<div class="table-panel feedback-admin-panel">
    <div class="table-header"><h2>Visitor Feedback Records</h2><span class="badge"><?= count($records) ?> visible</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Rating</th><th>Visitor</th><th>Contact</th><th>Organization</th><th>Feedback</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><span class="rating-badge"><?= e($r['rating']) ?> / 5</span></td>
                    <td><strong><?= e($r['visitor_name'] ?? '') ?></strong><br><span class="muted-text"><?= e($r['role_title'] ?: 'Visitor') ?></span></td>
                    <td><?= e($r['email'] ?? '') ?></td>
                    <td><?= e($r['company_name'] ?: 'Not provided') ?></td>
                    <td class="table-summary"><?= e($r['message'] ?? '') ?></td>
                    <td>
                        <span class="status <?= e(strtolower(str_replace(' ', '-', $r['status'] ?? 'new'))) ?>"><?= e($r['status'] ?? 'New') ?></span>
                        <?php if (!empty($r['is_featured'])): ?><span class="status featured">Featured</span><?php endif; ?>
                    </td>
                    <td><?= e(date('M j, Y g:i A', strtotime($r['created_at'] ?? 'now'))) ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="action-link" href="view-record.php?type=feedback&id=<?= e($r['id']) ?>">View</a>
                            <a class="action-link" href="edit-record.php?type=feedback&id=<?= e($r['id']) ?>">Edit</a>
                            <a class="action-link action-email" href="send-message.php?type=feedback&id=<?= e($r['id']) ?>">Reply</a>
                            <form class="inline-form" method="post" action="delete-record.php" onsubmit="return confirm('Delete this feedback record?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="type" value="feedback">
                                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                                <button class="action-link action-delete" type="submit">Delete</button>
                            </form>
                            <a class="action-link action-export" href="export-records.php?action=download&record_type=feedback&id=<?= e($r['id']) ?>&format=csv">CSV</a>
                            <a class="action-link action-export" href="export-records.php?action=download&record_type=feedback&id=<?= e($r['id']) ?>&format=pdf">PDF</a>
                            <form class="inline-form" method="post" action="export-records.php">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="email">
                                <input type="hidden" name="record_type" value="feedback">
                                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                                <input type="hidden" name="format" value="csv">
                                <button class="action-link action-email" type="submit">Email</button>
                            </form>
                        </div>
                        <form class="feedback-quick-form" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_feedback">
                            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                            <select name="status" aria-label="Update feedback status">
                                <?php foreach ($allowed_statuses as $status_option): ?>
                                    <option value="<?= e($status_option) ?>" <?= selected($r['status'] ?? 'New', $status_option) ?>><?= e($status_option) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="checkbox-label"><input type="checkbox" name="is_featured" value="1" <?= !empty($r['is_featured']) ? 'checked' : '' ?>> Feature</label>
                            <textarea name="admin_note" maxlength="2000" placeholder="Internal note"><?= e($r['admin_note'] ?? '') ?></textarea>
                            <button class="action-link" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$records): ?><tr><td colspan="8">No feedback records found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
