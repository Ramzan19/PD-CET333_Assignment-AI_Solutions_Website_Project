<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cms.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/solutions.php';
require_once __DIR__ . '/../includes/event-data.php';
require_admin();
cms_ensure_schema($pdo);

// Make sure each type is seeded from its defaults so the manager is populated.
ai_solutions_article_catalog();
ai_solutions_event_catalog();
ai_solutions_gallery_catalog();
ai_solutions_catalog();
ai_solutions_case_studies();

$defs = cms_type_defs();
$type = $_GET['type'] ?? 'article';
if (!cms_type_exists($type)) {
    $type = 'article';
}
$def = $defs[$type];
// Note: admin-header.php uses $items as its nav loop variable, so use a
// distinct name here to avoid it being clobbered by the include below.
$content_items = cms_items($pdo, $type, false); // include hidden so admin sees everything
$notice = $_GET['msg'] ?? '';

$current_page = 'content';
$page_title = 'Content Manager';
include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Website content</span>
        <h1>Content Manager</h1>
        <p>Create, edit, reorder, and remove the content shown across the public website. Changes appear on the site immediately.</p>
    </div>
    <a class="btn btn-primary" href="content-edit.php?type=<?= e($type) ?>">+ Add <?= e($def['label']) ?></a>
</div>

<?php if ($notice === 'saved'): ?><div class="alert alert-success">Content saved and is now live on the website.</div><?php endif; ?>
<?php if ($notice === 'deleted'): ?><div class="alert alert-success">Item deleted from the website.</div><?php endif; ?>

<div class="content-type-tabs">
    <?php foreach ($defs as $tkey => $tdef): ?>
        <a class="content-tab <?= $tkey === $type ? 'is-active' : '' ?>" href="content.php?type=<?= e($tkey) ?>">
            <?= e($tdef['plural']) ?> <span class="content-tab-count"><?= e(cms_count($pdo, $tkey)) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<div class="table-panel">
    <div class="table-header">
        <h2><?= e($def['plural']) ?></h2>
        <a class="action-link" href="../<?= e($def['public']) ?>" target="_blank" rel="noopener noreferrer">View public page &#8599;</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>#</th><th>Title</th><th>Details</th><th>Visible</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($content_items as $i => $item): ?>
                <?php
                    $title = $item['title'] ?? ($item['name'] ?? '(untitled)');
                    $detail = $item['summary'] ?? ($item['caption'] ?? ($item['objective'] ?? ''));
                    $meta = [];
                    foreach (['date', 'category', 'industry', 'interest', 'badge', 'key'] as $mk) {
                        if (!empty($item[$mk])) { $meta[] = ucfirst($mk) . ': ' . $item[$mk]; }
                    }
                ?>
                <tr>
                    <td><?= e($i + 1) ?></td>
                    <td><strong><?= e($title) ?></strong><?php if ($meta): ?><br><small><?= e(implode(' · ', $meta)) ?></small><?php endif; ?></td>
                    <td class="table-summary"><?= e(mb_strimwidth((string) $detail, 0, 110, '…')) ?></td>
                    <td><span class="status <?= !empty($item['is_published']) ? 'resolved' : 'archived' ?>"><?= !empty($item['is_published']) ? 'Live' : 'Hidden' ?></span></td>
                    <td>
                        <div class="table-actions">
                            <a class="action-link" href="content-edit.php?id=<?= e($item['id']) ?>">Edit</a>
                            <form class="inline-form" method="post" action="content-delete.php" onsubmit="return confirm('Delete this item from the website?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                                <input type="hidden" name="type" value="<?= e($type) ?>">
                                <button class="action-link action-delete" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$content_items): ?><tr><td colspan="5">No <?= e(strtolower($def['plural'])) ?> yet. Use &ldquo;Add <?= e($def['label']) ?>&rdquo; to create one.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
