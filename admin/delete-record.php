<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
require_once __DIR__ . '/../includes/schema.php';
require_admin();
require_admin_role(['super_admin', 'manager']);

if (!is_post()) {
    http_response_code(405);
    exit('Delete requests must use POST.');
}

csrf_or_fail();

$type = $_POST['type'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$tables = [
    'inquiry' => ['table' => 'customer_inquiries', 'back' => 'inquiries.php'],
    'demo' => ['table' => 'demo_bookings', 'back' => 'demo-bookings.php'],
    'event' => ['table' => 'event_registrations', 'back' => 'event-registrations.php'],
    'chatbot' => ['table' => 'chatbot_conversations', 'back' => 'chatbot-leads.php'],
    'feedback' => ['table' => 'visitor_feedback', 'back' => 'visitor-feedback.php'],
];

if (!isset($tables[$type]) || $id <= 0) {
    http_response_code(400);
    exit('Invalid record.');
}

if ($type === 'feedback') {
    ensure_visitor_feedback_table($pdo);
}
if ($type === 'event') {
    ensure_event_registrations_table($pdo);
}

$stmt = $pdo->prepare('DELETE FROM ' . $tables[$type]['table'] . ' WHERE id = ?');
$stmt->execute([$id]);
admin_log_activity($pdo, 'delete', $type . ' #' . $id);
header('Location: ' . $tables[$type]['back']);
exit;
