<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cms.php';
require_admin();
require_admin_role(['super_admin', 'manager']);

if (!is_post()) {
    http_response_code(405);
    exit('Method not allowed.');
}
csrf_or_fail();

$id = (int) ($_POST['id'] ?? 0);
$type = $_POST['type'] ?? 'article';
if (!cms_type_exists($type)) {
    $type = 'article';
}

if ($id > 0) {
    cms_delete($pdo, $id);
    admin_log_activity($pdo, 'content_delete', $type . ' #' . $id);
}

header('Location: content.php?type=' . urlencode($type) . '&msg=deleted');
exit;
