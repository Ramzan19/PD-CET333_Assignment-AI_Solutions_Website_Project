<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/solutions.php';
require_once __DIR__ . '/../includes/report-export.php';
require_admin();
ensure_solution_reviews_table($pdo);

$current_page = 'solution-reviews';
$page_title = 'Solution Reviews';
$notice = '';

if (is_post()) {
    csrf_or_fail();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0 && $action === 'approve') {
        $stmt = $pdo->prepare("UPDATE solution_reviews SET status = 'Approved', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        admin_log_activity($pdo, 'review_approved', 'Solution review #' . $id);
        $notice = 'Review approved and now visible on the Solutions page.';
    } elseif ($id > 0 && $action === 'archive') {
        $stmt = $pdo->prepare("UPDATE solution_reviews SET status = 'Archived', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        admin_log_activity($pdo, 'review_archived', 'Solution review #' . $id);
        $notice = 'Review archived (hidden from the public page).';
    } elseif ($id > 0 && $action === 'delete') {
        require_admin_role(['super_admin', 'manager']);
        $stmt = $pdo->prepare('DELETE FROM solution_reviews WHERE id = ?');
        $stmt->execute([$id]);
        admin_log_activity($pdo, 'review_deleted', 'Solution review #' . $id);
        $notice = 'Review deleted.';
    }
}

$filter = $_GET['status'] ?? '';
$valid_filters = ['New', 'Approved', 'Archived'];
$where = in_array($filter, $valid_filters, true) ? 'WHERE status = ?' : '';
$params = $where !== '' ? [$filter] : [];
$stmt = $pdo->prepare('SELECT * FROM solution_reviews ' . $where . ' ORDER BY created_at DESC');
$stmt->execute($params);
$reviews = $stmt->fetchAll();

$counts = [];
foreach ($pdo->query("SELECT status, COUNT(*) total FROM solution_reviews GROUP BY status")->fetchAll() as $row) {
    $counts[$row['status']] = (int) $row['total'];
}
include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Moderation</span>
        <h1>Solution Reviews</h1>
        <p>Approve authentic customer reviews so they appear (and count toward ratings) on the Solutions page.</p>
    </div>
    <?= render_export_all_toolbar('reviews', 'solution reviews') ?>
</div>
<?php if ($notice): ?><div class="alert alert-success"><?= e($notice) ?></div><?php endif; ?>
<div class="filter-bar">
    <a class="btn <?= $filter === '' ? 'btn-primary' : 'btn-secondary' ?>" href="solution-reviews.php">All</a>
    <a class="btn <?= $filter === 'New' ? 'btn-primary' : 'btn-secondary' ?>" href="solution-reviews.php?status=New">Pending (<?= e($counts['New'] ?? 0) ?>)</a>
    <a class="btn <?= $filter === 'Approved' ? 'btn-primary' : 'btn-secondary' ?>" href="solution-reviews.php?status=Approved">Approved (<?= e($counts['Approved'] ?? 0) ?>)</a>
    <a class="btn <?= $filter === 'Archived' ? 'btn-primary' : 'btn-secondary' ?>" href="solution-reviews.php?status=Archived">Archived (<?= e($counts['Archived'] ?? 0) ?>)</a>
</div>
<div class="table-panel">
    <div class="table-header"><h2>Reviews</h2><span class="badge"><?= count($reviews) ?> records</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Solution</th><th>Reviewer</th><th>Rating</th><th>Comment</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><?= e($r['solution_title']) ?></td>
                    <td><?= e($r['reviewer_name']) ?><br><small><?= e($r['company_name'] ?? '') ?></small></td>
                    <td><?= e(solution_review_stars((int) $r['rating'])) ?> <?= e($r['rating']) ?>/5</td>
                    <td><?= e($r['comment']) ?></td>
                    <td><span class="status <?= e(strtolower($r['status'])) ?>"><?= e($r['status']) ?></span></td>
                    <td><?= e($r['created_at']) ?></td>
                    <td>
                        <div class="table-actions">
                            <?php if ($r['status'] !== 'Approved'): ?>
                                <form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="action-link" type="submit">Approve</button></form>
                            <?php endif; ?>
                            <?php if ($r['status'] !== 'Archived'): ?>
                                <form class="inline-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="archive"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="action-link" type="submit">Archive</button></form>
                            <?php endif; ?>
                            <form class="inline-form" method="post" onsubmit="return confirm('Delete this review permanently?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="action-link action-delete" type="submit">Delete</button></form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$reviews): ?><tr><td colspan="7">No reviews yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
