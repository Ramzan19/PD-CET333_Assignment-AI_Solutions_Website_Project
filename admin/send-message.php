<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/schema.php';
require_once __DIR__ . '/../includes/mailer.php';
require_admin();
ensure_event_registrations_table($pdo);

// Record types that carry a customer email we can reply to.
$tables = [
    'inquiry' => ['table' => 'customer_inquiries', 'name' => 'full_name', 'back' => 'inquiries.php', 'label' => 'Inquiry'],
    'demo' => ['table' => 'demo_bookings', 'name' => 'full_name', 'back' => 'demo-bookings.php', 'label' => 'Demo Booking'],
    'event' => ['table' => 'event_registrations', 'name' => 'full_name', 'back' => 'event-registrations.php', 'label' => 'Event Registration'],
    'chatbot' => ['table' => 'chatbot_conversations', 'name' => 'user_name', 'back' => 'chatbot-leads.php', 'label' => 'Chatbot Lead'],
    'feedback' => ['table' => 'visitor_feedback', 'name' => 'visitor_name', 'back' => 'visitor-feedback.php', 'label' => 'Feedback'],
];

$type = $_GET['type'] ?? ($_POST['type'] ?? '');
$id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));

if (!isset($tables[$type]) || $id <= 0) {
    http_response_code(400);
    exit('Invalid record.');
}

$meta = $tables[$type];
if ($type === 'feedback') {
    require_once __DIR__ . '/../includes/feedback.php';
    ensure_visitor_feedback_table($pdo);
}

$stmt = $pdo->prepare('SELECT * FROM ' . $meta['table'] . ' WHERE id = ?');
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    http_response_code(404);
    exit('Record not found.');
}

// The recipient email is taken from the stored record, never from user input.
$customer_email = trim((string) ($record['email'] ?? ''));
$customer_name = trim((string) ($record[$meta['name']] ?? 'there'));

$current_page = 'records';
$page_title = 'Send Email to Customer';
$errors = [];
$status = '';

$default_subject = 'Re: Your AI-Solutions ' . strtolower($meta['label']);
$default_body = "Hello " . $customer_name . ",\n\n"
    . "Thank you for contacting AI-Solutions. We are following up regarding your "
    . strtolower($meta['label']) . ".\n\n"
    . "[Write your reply here]\n\n"
    . "Kind regards,\n" . admin_name() . "\nAI-Solutions";

$subject = $default_subject;
$body = $default_body;

if (is_post()) {
    csrf_or_fail();
    $subject = post_value('subject', 200);
    $body = trim((string) ($_POST['body'] ?? ''));

    if (!valid_email($customer_email)) {
        $errors[] = 'This record does not have a valid email address to send to.';
    }
    if ($subject === '') {
        $errors[] = 'Please enter a subject.';
    }
    if ($body === '') {
        $errors[] = 'Please enter a message.';
    }

    if (!$errors) {
        $result = mail_send_or_queue($pdo, $customer_email, $subject, $body);
        if (!empty($result['sent'])) {
            admin_log_activity($pdo, 'email_customer', $meta['label'] . ' #' . $id . ' -> ' . $customer_email);
            redirect($meta['back'] . '?msg=email_sent');
        }

        admin_log_activity($pdo, 'queue_customer_email', $meta['label'] . ' #' . $id . ' -> ' . $customer_email);
        $status = 'queued';
    }
}

include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker"><?= e($meta['label']) ?></span>
        <h1>Email Customer</h1>
        <p>Send a real email to the address this customer registered when they contacted AI-Solutions.</p>
    </div>
    <a class="btn btn-secondary" href="<?= e($meta['back']) ?>">Back</a>
</div>

<?php if ($status === 'queued'): ?>
    <div class="alert alert-success">Live email was not sent (SMTP not connected), so the message was saved to the local outbox. Configure <a href="smtp-settings.php">SMTP settings</a> to deliver it for real.</div>
<?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<section class="form-shell">
    <form method="post" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="id" value="<?= e($id) ?>">
        <div class="form-grid">
            <div class="form-group"><label>To (registered email)</label><div class="data-card"><?= e($customer_email !== '' ? $customer_email : 'No email on record') ?></div></div>
            <div class="form-group"><label>Customer</label><div class="data-card"><?= e($customer_name) ?></div></div>
            <div class="form-group full"><label for="subject">Subject *</label><input id="subject" name="subject" maxlength="200" value="<?= e($subject) ?>" required></div>
            <div class="form-group full"><label for="body">Message *</label><textarea id="body" name="body" rows="12" required><?= e($body) ?></textarea></div>
        </div>
        <div class="button-row">
            <button class="btn btn-primary" type="submit">Send Email</button>
            <a class="btn btn-secondary" href="<?= e($meta['back']) ?>">Cancel</a>
        </div>
    </form>
</section>
<?php include 'admin-footer.php'; ?>
