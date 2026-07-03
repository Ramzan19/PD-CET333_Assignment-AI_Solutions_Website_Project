<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cms.php';
require_admin();
cms_ensure_schema($pdo);

$defs = cms_type_defs();
$errors = [];

// Determine whether we are editing an existing item or creating a new one.
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$existing = $id > 0 ? cms_find($pdo, $id) : null;

if ($existing) {
    $type = $existing['type'];
} else {
    $type = $_GET['type'] ?? $_POST['type'] ?? 'article';
    $id = 0;
}
if (!cms_type_exists($type)) {
    http_response_code(400);
    exit('Unknown content type.');
}
$def = $defs[$type];
$fields = $def['fields'];

// Build the working values for the form (field key => string value).
$values = [];
foreach ($fields as $f) {
    [$key, $label, $kind, $required] = $f;
    $current = $existing[$key] ?? '';
    if ($kind === 'body' && is_array($current)) {
        $current = cms_body_to_text($current);
    } elseif ($kind === 'list' && is_array($current)) {
        $current = implode("\n", $current);
    }
    $values[$key] = (string) $current;
}
$sort_order = (int) ($existing['sort_order'] ?? (cms_count($pdo, $type) * 10));
$is_published = $existing ? (int) $existing['is_published'] : 1;

if (is_post()) {
    csrf_or_fail();
    foreach ($fields as $f) {
        [$key, $label, $kind, $required] = $f;
        $raw = (string) ($_POST[$key] ?? '');
        $values[$key] = $kind === 'body' || $kind === 'list' || $kind === 'textarea'
            ? trim($raw)
            : trim(post_value($key, 255));
        if ($required && $values[$key] === '') {
            $errors[] = $label . ' is required.';
        }
        if (($key === 'slug' || $key === 'key') && $values[$key] !== '' && !preg_match('/^[a-z0-9-]+$/', $values[$key])) {
            $errors[] = $label . ' may only contain lowercase letters, numbers, and hyphens.';
        }
        if ($kind === 'date' && $values[$key] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $values[$key])) {
            $errors[] = $label . ' must be a valid date.';
        }
    }
    $sort_order = (int) ($_POST['sort_order'] ?? $sort_order);
    $is_published = !empty($_POST['is_published']) ? 1 : 0;

    if (!$errors) {
        // Start from any existing data so non-editable keys (e.g. a solution's
        // display number) are preserved, then overlay the edited fields.
        $item = is_array($existing) ? $existing : [];
        unset($item['id'], $item['type'], $item['is_published'], $item['sort_order']);
        foreach ($fields as $f) {
            [$key, $label, $kind, $required] = $f;
            if ($kind === 'body') {
                $item[$key] = cms_text_to_body($values[$key]);
            } elseif ($kind === 'list') {
                $item[$key] = cms_lines_to_array($values[$key]);
            } else {
                $item[$key] = $values[$key];
            }
        }
        if ($id > 0) {
            cms_update($pdo, $id, $type, $item, $sort_order, $is_published);
            admin_log_activity($pdo, 'content_update', $type . ' #' . $id);
        } else {
            $new_id = cms_insert($pdo, $type, $item, $sort_order, $is_published);
            admin_log_activity($pdo, 'content_create', $type . ' #' . $new_id);
        }
        header('Location: content.php?type=' . urlencode($type) . '&msg=saved');
        exit;
    }
}

$current_page = 'content';
$page_title = ($id > 0 ? 'Edit ' : 'Add ') . $def['label'];
include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker"><?= e($def['plural']) ?></span>
        <h1><?= e($id > 0 ? 'Edit' : 'Add') ?> <?= e($def['label']) ?></h1>
        <p>Fields marked * are required. Saved content appears on the public website right away.</p>
    </div>
    <a class="btn btn-secondary" href="content.php?type=<?= e($type) ?>">Back to list</a>
</div>

<?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<div class="form-shell">
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="id" value="<?= e($id) ?>">
        <div class="form-grid">
            <?php foreach ($fields as $f):
                [$key, $label, $kind, $required] = $f;
                $star = $required ? ' *' : '';
                $full = in_array($kind, ['textarea', 'body', 'list'], true);
            ?>
                <div class="form-group <?= $full ? 'full' : '' ?>">
                    <label for="f_<?= e($key) ?>"><?= e($label) . $star ?></label>
                    <?php if ($kind === 'body'): ?>
                        <textarea id="f_<?= e($key) ?>" name="<?= e($key) ?>" rows="14" class="cms-body-input"><?= e($values[$key]) ?></textarea>
                        <small class="field-hint">Formatting: blank line = new paragraph &nbsp;·&nbsp; <code>## Heading</code> = subheading &nbsp;·&nbsp; <code>- item</code> = bullet list.</small>
                    <?php elseif ($kind === 'list'): ?>
                        <textarea id="f_<?= e($key) ?>" name="<?= e($key) ?>" rows="4"><?= e($values[$key]) ?></textarea>
                        <small class="field-hint">One entry per line.</small>
                    <?php elseif ($kind === 'textarea'): ?>
                        <textarea id="f_<?= e($key) ?>" name="<?= e($key) ?>" rows="3"><?= e($values[$key]) ?></textarea>
                    <?php elseif ($kind === 'date'): ?>
                        <input id="f_<?= e($key) ?>" type="date" name="<?= e($key) ?>" value="<?= e($values[$key]) ?>">
                    <?php else: ?>
                        <input id="f_<?= e($key) ?>" type="text" name="<?= e($key) ?>" maxlength="255" value="<?= e($values[$key]) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="form-group">
                <label for="sort_order">Display Order</label>
                <input id="sort_order" type="number" name="sort_order" value="<?= e($sort_order) ?>">
                <small class="field-hint">Lower numbers appear first.</small>
            </div>
            <div class="form-group">
                <label class="checkbox-label"><input type="checkbox" name="is_published" value="1" <?= $is_published ? 'checked' : '' ?>> Visible on website</label>
            </div>
        </div>
        <div class="button-row">
            <button class="btn btn-primary" type="submit">Save <?= e($def['label']) ?></button>
            <a class="btn btn-secondary" href="content.php?type=<?= e($type) ?>">Cancel</a>
        </div>
    </form>
</div>
<?php include 'admin-footer.php'; ?>
