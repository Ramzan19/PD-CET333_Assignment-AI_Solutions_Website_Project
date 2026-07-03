<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$file = basename((string) ($_GET['file'] ?? ''));
$outbox_dir = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'email-outbox');

if ($file === '' || !$outbox_dir) {
    http_response_code(404);
    exit('File not found.');
}

$path = realpath($outbox_dir . DIRECTORY_SEPARATOR . $file);
if (!$path || strpos($path, $outbox_dir . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('File not found.');
}

$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = match ($extension) {
    'eml' => 'message/rfc822',
    'pdf' => 'application/pdf',
    'csv' => 'text/csv; charset=utf-8',
    'json' => 'application/json; charset=utf-8',
    default => 'application/octet-stream',
};

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
