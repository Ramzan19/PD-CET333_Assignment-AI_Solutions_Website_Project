<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
require_once __DIR__ . '/../includes/schema.php';
require_admin();
ensure_event_registrations_table($pdo);

$type = $_GET['type'] ?? '';
$id = (int) ($_GET['id'] ?? 0);
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

$stmt = $pdo->prepare('SELECT * FROM ' . $tables[$type]['table'] . ' WHERE id = ?');
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    http_response_code(404);
    exit('Record not found.');
}

$current_page = 'records';
$page_title = 'View Record';
include 'admin-header.php';
?>
<div class="admin-hero">
    <div><h1>Record Detail</h1><p>Type: <?= e(ucfirst($type)) ?> / ID: <?= e($id) ?></p></div>
    <a class="btn btn-secondary" href="<?= e($tables[$type]['back']) ?>">Back</a>
</div>
<div class="form-shell record-detail">
    <div class="form-grid">
        <?php foreach ($record as $key => $value): ?>
            <div class="form-group <?= strlen((string) $value) > 80 ? 'full' : '' ?>">
                <label><?= e(ucwords(str_replace('_', ' ', $key))) ?></label>
                <div class="data-card"><?= nl2br(e($value)) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="button-row">
        <a class="btn btn-primary" href="edit-record.php?type=<?= e($type) ?>&id=<?= e($id) ?>">Edit Record</a>
        <?php if (!empty($record['email'])): ?>
            <a class="btn btn-secondary" href="send-message.php?type=<?= e($type) ?>&id=<?= e($id) ?>">Email Customer</a>
        <?php endif; ?>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
