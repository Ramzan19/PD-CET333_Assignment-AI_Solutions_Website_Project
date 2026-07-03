<?php
require_once __DIR__ . '/functions.php';

function export_record_options() {
    return [
        'all' => 'All Records',
        'inquiries' => 'Customer Inquiries',
        'demos' => 'Demo Bookings',
        'events' => 'Event Registrations',
        'chatbot' => 'Chatbot Leads',
        'feedback' => 'Visitor Feedback',
        'reviews' => 'Solution Reviews',
        'visitors' => 'Visitor Activity',
    ];
}

/**
 * Render a reusable "Export all" toolbar (Excel/CSV + PDF download, plus email)
 * for an admin record listing. $type must be one of export_record_options();
 * $noun is the lowercase plural shown in the label, e.g. "demo bookings".
 */
function render_export_all_toolbar($type, $noun) {
    $type = e($type);
    $noun = e($noun);
    return '<div class="export-all" aria-label="Export all ' . $noun . '">'
        . '<span class="export-all-label">Export all ' . $noun . '</span>'
        . '<div class="export-all-actions">'
        . '<a class="btn btn-primary" href="export-records.php?type=' . $type . '&amp;format=csv">Excel / CSV</a>'
        . '<a class="btn btn-secondary" href="export-records.php?type=' . $type . '&amp;format=pdf">PDF</a>'
        . '<form class="export-all-email" method="post" action="export-records.php">'
        . csrf_field()
        . '<input type="hidden" name="action" value="email">'
        . '<input type="hidden" name="type" value="' . $type . '">'
        . '<input type="hidden" name="format" value="csv">'
        . '<input type="email" name="email" placeholder="Email the CSV to..." maxlength="180" aria-label="Email address to send the full export to">'
        . '<button class="btn btn-secondary" type="submit">Email all</button>'
        . '</form>'
        . '</div>'
        . '</div>';
}

function export_valid_type($type) {
    return array_key_exists($type, export_record_options()) ? $type : 'all';
}

function export_record_options_for_actions() {
    return [
        'inquiry' => ['table' => 'customer_inquiries', 'label' => 'Customer Inquiry'],
        'demo' => ['table' => 'demo_bookings', 'label' => 'Demo Booking'],
        'event' => ['table' => 'event_registrations', 'label' => 'Event Registration'],
        'chatbot' => ['table' => 'chatbot_conversations', 'label' => 'Chatbot Lead'],
        'feedback' => ['table' => 'visitor_feedback', 'label' => 'Visitor Feedback'],
    ];
}

function export_single_record_dataset(PDO $pdo, $type, $id) {
    $options = export_record_options_for_actions();
    if (!isset($options[$type]) || $id <= 0) {
        return null;
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM ' . $options[$type]['table'] . ' WHERE id = ?');
        $stmt->execute([(int) $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Single record export failed: ' . $e->getMessage());
        return null;
    }

    if (!$record) {
        return null;
    }

    $rows = [];
    foreach ($record as $field => $value) {
        $rows[] = [ucwords(str_replace('_', ' ', $field)), $value === null ? '' : preg_replace('/\s+/', ' ', trim((string) $value))];
    }

    return [
        'title' => $options[$type]['label'] . ' #' . (int) $id,
        'columns' => ['Field', 'Value'],
        'rows' => $rows,
        'email' => $record['email'] ?? '',
    ];
}

function export_dataset(PDO $pdo, $type) {
    $type = export_valid_type($type);

    if ($type === 'inquiries') {
        return [
            'title' => 'Customer Inquiries',
            'columns' => ['ID', 'Name', 'Email', 'Phone', 'Company', 'Country', 'Interest', 'Job Title', 'Priority', 'Status', 'Submitted'],
            'rows' => export_fetch($pdo, 'SELECT id, full_name, email, phone, company_name, country, solution_interest, job_title, priority, status, created_at FROM customer_inquiries ORDER BY created_at DESC'),
        ];
    }

    if ($type === 'demos') {
        return [
            'title' => 'Demo Bookings',
            'columns' => ['ID', 'Name', 'Email', 'Phone', 'Company', 'Country', 'Preferred Date', 'Preferred Time', 'Demo Type', 'Status', 'Submitted'],
            'rows' => export_fetch($pdo, 'SELECT id, full_name, email, phone, company_name, country, preferred_date, preferred_time, demo_type, status, created_at FROM demo_bookings ORDER BY created_at DESC'),
        ];
    }

    if ($type === 'events') {
        return [
            'title' => 'Event Registrations',
            'columns' => ['ID', 'Name', 'Email', 'Phone', 'Company', 'Country', 'Event', 'Interest', 'Status', 'Submitted'],
            'rows' => export_fetch($pdo, 'SELECT id, full_name, email, phone, company_name, country, event_name, interest_area, status, created_at FROM event_registrations ORDER BY created_at DESC'),
        ];
    }

    if ($type === 'chatbot') {
        return [
            'title' => 'Chatbot Leads',
            'columns' => ['ID', 'Name', 'Email', 'Phone', 'Company', 'Country', 'Topic', 'Summary', 'Status', 'Submitted'],
            'rows' => export_fetch($pdo, 'SELECT id, user_name, email, phone, company_name, country, topic, chat_summary, status, created_at FROM chatbot_conversations ORDER BY created_at DESC'),
        ];
    }

    if ($type === 'feedback') {
        return [
            'title' => 'Visitor Feedback',
            'columns' => ['ID', 'Visitor', 'Email', 'Organization', 'Role', 'Rating', 'Feedback', 'Status', 'Featured', 'Submitted'],
            'rows' => export_fetch($pdo, 'SELECT id, visitor_name, email, company_name, role_title, rating, message, status, is_featured, created_at FROM visitor_feedback ORDER BY created_at DESC, id DESC'),
        ];
    }

    if ($type === 'reviews') {
        return [
            'title' => 'Solution Reviews',
            'columns' => ['ID', 'Solution', 'Reviewer', 'Email', 'Company', 'Rating', 'Review', 'Status', 'Submitted'],
            'rows' => export_fetch($pdo, 'SELECT id, solution_title, reviewer_name, email, company_name, rating, comment, status, created_at FROM solution_reviews ORDER BY created_at DESC, id DESC'),
        ];
    }

    if ($type === 'visitors') {
        return [
            'title' => 'Visitor Activity',
            'columns' => ['ID', 'Visitor Key', 'Page', 'Page Title', 'Device', 'Browser', 'Referrer', 'Visit Date', 'Visited At'],
            'rows' => export_fetch($pdo, 'SELECT id, visitor_key, page_path, page_title, device_type, browser_name, referrer, visit_date, created_at FROM website_visits ORDER BY created_at DESC, id DESC LIMIT 500'),
        ];
    }

    return [
        'title' => 'Combined Admin Records',
        'columns' => ['Category', 'ID', 'Name', 'Email', 'Detail', 'Status', 'Created'],
        'rows' => export_fetch($pdo, "
            SELECT 'Inquiry' category, id, full_name name, email, job_title detail, status, created_at FROM customer_inquiries
            UNION ALL
            SELECT 'Demo Booking' category, id, full_name name, email, demo_type detail, status, created_at FROM demo_bookings
            UNION ALL
            SELECT 'Event Registration' category, id, full_name name, email, event_name detail, status, created_at FROM event_registrations
            UNION ALL
            SELECT 'Chatbot Lead' category, id, user_name name, email, topic detail, status, created_at FROM chatbot_conversations
            UNION ALL
            SELECT 'Feedback Rating' category, id, visitor_name name, email, CONCAT(rating, '/5 - ', LEFT(message, 120)) detail, status, created_at FROM visitor_feedback
            UNION ALL
            SELECT 'Website Visit' category, id, LEFT(visitor_key, 12) name, '' email, CONCAT(page_name, ' - ', page_path) detail, device_type status, created_at FROM website_visits
            ORDER BY created_at DESC
            LIMIT 500
        "),
    ];
}

function export_status_message($status) {
    $messages = [
        'email_sent' => ['success', 'Record sent by email successfully.'],
        'email_queued' => ['success', 'Live email was not sent, so the report was saved in the local outbox. Add or check SMTP settings to send it for real.'],
        'email_failed' => ['error', 'The record could not be emailed. Please check SMTP settings or download it instead.'],
        'email_invalid' => ['error', 'This record does not have a valid email address.'],
        'record_missing' => ['error', 'The selected record could not be exported.'],
    ];
    return $messages[$status] ?? null;
}

function export_fetch(PDO $pdo, $sql) {
    try {
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);
        return array_map(function ($row) {
            return array_map(function ($value) {
                if ($value === null) {
                    return '';
                }
                return preg_replace('/\s+/', ' ', trim((string) $value));
            }, $row);
        }, $rows);
    } catch (PDOException $e) {
        error_log('Export query failed: ' . $e->getMessage());
        return [];
    }
}

function export_filename($title, $extension) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    $slug = trim($slug, '-') ?: 'records';
    return $slug . '-' . date('Ymd-His') . '.' . $extension;
}

function export_csv_content(array $columns, array $rows) {
    $handle = fopen('php://temp', 'r+');
    fputcsv($handle, $columns);
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    rewind($handle);
    $content = stream_get_contents($handle);
    fclose($handle);
    return $content;
}

function export_send_download($content, $filename, $mime) {
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}

function export_pdf_content($title, array $columns, array $rows) {
    $lines = export_pdf_lines($title, $columns, $rows);
    $pages = array_chunk($lines, 45);
    $objects = [];
    $page_ids = [];

    $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[] = '';

    foreach ($pages as $page) {
        $page_id = count($objects) + 1;
        $content_id = $page_id + 1;
        $page_ids[] = $page_id . ' 0 R';
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 {{FONT_ID}} 0 R >> >> /Contents ' . $content_id . ' 0 R >>';
        $objects[] = export_pdf_stream($page);
    }

    $font_id = count($objects) + 1;
    foreach ($objects as &$object) {
        $object = str_replace('{{FONT_ID}}', (string) $font_id, $object);
    }
    unset($object);
    $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    $objects[1] = '<< /Type /Pages /Kids [' . implode(' ', $page_ids) . '] /Count ' . count($page_ids) . ' >>';

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
    return $pdf;
}

function export_pdf_lines($title, array $columns, array $rows) {
    $lines = [
        $title,
        'Generated: ' . date('Y-m-d H:i:s'),
        'Total records: ' . count($rows),
        '',
        implode(' | ', $columns),
        str_repeat('-', 110),
    ];

    foreach ($rows as $row) {
        $joined = implode(' | ', array_map('export_pdf_cell', $row));
        foreach (str_split($joined, 110) as $line) {
            $lines[] = $line;
        }
        $lines[] = '';
    }

    if (!$rows) {
        $lines[] = 'No records found.';
    }

    return $lines;
}

function export_pdf_cell($value) {
    if (function_exists('mb_substr')) {
        return mb_substr((string) $value, 0, 140);
    }
    return substr((string) $value, 0, 140);
}

function export_pdf_stream(array $lines) {
    $content = "BT\n/F1 9 Tf\n36 760 Td\n13 TL\n";
    foreach ($lines as $line) {
        $content .= '(' . export_pdf_escape($line) . ") Tj\nT*\n";
    }
    $content .= "ET";
    return "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
}

function export_pdf_escape($text) {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string) $text);
}

function export_email_message($to, $subject, $body, $filename, $content, $mime, $boundary) {
    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= $body . "\r\n\r\n";
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: $mime; name=\"$filename\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
    $message .= chunk_split(base64_encode($content));
    $message .= "--$boundary--";
    return $message;
}

function export_send_email($to, $subject, $body, $filename, $content, $mime) {
    $boundary = 'report-' . bin2hex(random_bytes(12));
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
        'From: AI-Solutions Admin <no-reply@ai-solutions.local>',
    ];
    $message = export_email_message($to, $subject, $body, $filename, $content, $mime, $boundary);
    return @mail($to, $subject, $message, implode("\r\n", $headers));
}

function export_queue_local_email($to, $subject, $body, $filename, $content, $mime, $reason = '') {
    $outbox_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'email-outbox';
    if (!is_dir($outbox_dir) && !@mkdir($outbox_dir, 0700, true)) {
        return false;
    }
    if (!is_writable($outbox_dir)) {
        return false;
    }

    $base = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
    $boundary = 'report-' . bin2hex(random_bytes(12));
    $headers = [
        'To: ' . $to,
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
        'From: AI-Solutions Admin <no-reply@ai-solutions.local>',
    ];
    $message = implode("\r\n", $headers) . "\r\n\r\n" . export_email_message($to, $subject, $body, $filename, $content, $mime, $boundary);

    $summary = [
        'queued_at' => date('Y-m-d H:i:s'),
        'to' => $to,
        'subject' => $subject,
        'attachment' => $filename,
        'note' => $reason !== '' ? $reason : 'Created because live email was not available.',
    ];

    $eml_path = $outbox_dir . DIRECTORY_SEPARATOR . $base . '.eml';
    $meta_path = $outbox_dir . DIRECTORY_SEPARATOR . $base . '.json';
    $attachment_path = $outbox_dir . DIRECTORY_SEPARATOR . $base . '-' . preg_replace('/[^A-Za-z0-9._-]/', '-', $filename);

    return file_put_contents($eml_path, $message) !== false
        && file_put_contents($meta_path, json_encode($summary, JSON_PRETTY_PRINT)) !== false
        && file_put_contents($attachment_path, $content) !== false;
}
