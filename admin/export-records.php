<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/../includes/schema.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/report-export.php';

require_admin();
require_admin_role(['super_admin', 'manager']);
ensure_assignment_schema($pdo);
ensure_visitor_feedback_table($pdo);
ensure_website_visits_table($pdo);

$type = export_valid_type($_REQUEST['type'] ?? 'all');
$record_type = $_REQUEST['record_type'] ?? '';
$record_id = (int) ($_REQUEST['id'] ?? 0);
$format = ($_REQUEST['format'] ?? 'csv') === 'pdf' ? 'pdf' : 'csv';
$dataset = $record_id > 0 ? export_single_record_dataset($pdo, $record_type, $record_id) : export_dataset($pdo, $type);
if (!$dataset) {
    export_redirect_back('record_missing');
}
$extension = $format === 'pdf' ? 'pdf' : 'csv';
$mime = $format === 'pdf' ? 'application/pdf' : 'text/csv; charset=utf-8';
$filename = export_filename($dataset['title'], $extension);
$content = $format === 'pdf'
    ? export_pdf_content($dataset['title'], $dataset['columns'], $dataset['rows'])
    : export_csv_content($dataset['columns'], $dataset['rows']);

if (($_REQUEST['action'] ?? 'download') === 'email') {
    if (!is_post()) {
        export_redirect_back('email_invalid');
    }

    csrf_or_fail();
    $email = post_value('email', 180) ?: ($dataset['email'] ?? '');
    if (!valid_email($email)) {
        export_redirect_back('email_invalid');
    }

    $subject = $dataset['title'] . ' Report';
    $body = 'Attached is the requested AI-Solutions admin report: ' . $dataset['title'] . '.';
    $send_result = smtp_send_mail($pdo, $email, $subject, $body, [[
        'filename' => $filename,
        'content' => $content,
        'mime' => $mime,
    ]]);

    if ($send_result['sent']) {
        admin_log_activity($pdo, 'email_export', $dataset['title']);
        export_redirect_back('email_sent');
    }

    $queued = export_queue_local_email(
        $email,
        $subject,
        $body,
        $filename,
        $content,
        $mime,
        $send_result['reason'] ?? ''
    );

    admin_log_activity($pdo, $queued ? 'queue_export_email' : 'email_export_failed', $dataset['title']);
    export_redirect_back($queued ? 'email_queued' : 'email_failed');
}

admin_log_activity($pdo, 'download_export', $dataset['title'] . ' (' . $format . ')');
export_send_download($content, $filename, $mime);

function export_redirect_back($status) {
    $back = $_SERVER['HTTP_REFERER'] ?? 'manage-records.php';
    if (stripos($back, '/admin/') === false) {
        $back = 'manage-records.php';
    }

    $parts = parse_url($back);
    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }
    $query['export'] = $status;
    $path = ($parts['path'] ?? 'manage-records.php');
    if (!empty($parts['scheme']) && !empty($parts['host'])) {
        $path = $parts['scheme'] . '://' . $parts['host'] . $path;
    }
    redirect($path . '?' . http_build_query($query));
}
