<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cms.php';
require_admin();
cms_ensure_schema($pdo);

// Friendly labels for each editable site-text key.
$labels = [
    'home_hero_eyebrow' => 'Home — Hero Eyebrow',
    'home_hero_lede' => 'Home — Hero Intro Paragraph',
    'services_hero_title' => 'Solutions — Hero Heading',
    'articles_hero_title' => 'Articles — Hero Heading',
    'events_hero_title' => 'Events — Hero Heading',
    'contact_hero_title' => 'Contact — Hero Heading',
];

$saved = false;
if (is_post()) {
    csrf_or_fail();
    foreach (array_keys(cms_site_text_defaults()) as $key) {
        if (array_key_exists($key, $_POST)) {
            cms_save_site_text($pdo, $key, trim((string) $_POST[$key]));
        }
    }
    admin_log_activity($pdo, 'content_text_update', 'Site text');
    $saved = true;
}

$values = cms_all_site_text($pdo);

$current_page = 'content-text';
$page_title = 'Site Text';
include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Website content</span>
        <h1>Site Text</h1>
        <p>Edit the key headings and intro copy shown on the public website. Changes go live immediately.</p>
    </div>
    <a class="btn btn-secondary" href="content.php">Content Manager</a>
</div>

<?php if ($saved): ?><div class="alert alert-success">Site text updated and now live on the website.</div><?php endif; ?>

<div class="form-shell">
    <form method="post">
        <?= csrf_field() ?>
        <div class="form-grid">
            <?php foreach (cms_site_text_defaults() as $key => $default): ?>
                <?php $is_long = strlen((string) ($values[$key] ?? '')) > 70; ?>
                <div class="form-group full">
                    <label for="t_<?= e($key) ?>"><?= e($labels[$key] ?? ucwords(str_replace('_', ' ', $key))) ?></label>
                    <?php if ($is_long): ?>
                        <textarea id="t_<?= e($key) ?>" name="<?= e($key) ?>" rows="2"><?= e($values[$key] ?? $default) ?></textarea>
                    <?php else: ?>
                        <input id="t_<?= e($key) ?>" type="text" name="<?= e($key) ?>" maxlength="500" value="<?= e($values[$key] ?? $default) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="button-row">
            <button class="btn btn-primary" type="submit">Save Site Text</button>
        </div>
    </form>
</div>
<?php include 'admin-footer.php'; ?>
