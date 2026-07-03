<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/schema.php';

$current_page = 'chatbot';
$page_title = 'Human Support Handover';
$errors = [];
ensure_chatbot_schema($pdo);
$fields = ['user_name', 'email', 'phone', 'company_name', 'country', 'topic', 'chat_summary'];
$form = [
    'user_name' => '',
    'email' => '',
    'phone' => '',
    'company_name' => '',
    'country' => '',
    'topic' => '',
    'chat_summary' => 'Customer requested human support after chatbot conversation.',
];
$topics = ['Virtual Assistant', 'Software Assistance', 'Schedule Demo', 'Events', 'Sales Representative', 'Pricing Question'];

$incoming_summary = trim((string) ($_GET['summary'] ?? ''));
$incoming_topic = trim((string) ($_GET['topic'] ?? ''));

if ($incoming_summary !== '') {
    $form['chat_summary'] = function_exists('mb_substr') ? mb_substr($incoming_summary, 0, 2000) : substr($incoming_summary, 0, 2000);
}

if ($incoming_topic !== '' && in_array($incoming_topic, $topics, true)) {
    $form['topic'] = $incoming_topic;
}

if (is_post()) {
    csrf_or_fail();

    foreach ($fields as $field) {
        $form[$field] = post_value($field, $field === 'chat_summary' ? 2000 : 150);
    }

    $errors = array_merge(
        required_fields([
            'user_name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'country' => 'Country',
            'topic' => 'Topic',
            'chat_summary' => 'Conversation summary',
        ], $_POST),
        max_length_errors([
            'user_name' => ['Name', 150],
            'email' => ['Email', 150],
            'phone' => ['Phone', 50],
            'company_name' => ['Company name', 150],
            'country' => ['Country', 100],
            'topic' => ['Topic', 150],
            'chat_summary' => ['Conversation summary', 2000],
        ], $_POST)
    );

    if (!honeypot_clear()) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if ($form['email'] !== '' && !valid_email($form['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($form['topic'] !== '' && !in_array($form['topic'], $topics, true)) {
        $errors[] = 'Please select a valid topic.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO chatbot_conversations(user_name,email,phone,company_name,country,topic,chat_summary,handover_required,status,created_at) VALUES(?,?,?,?,?,?,?,1,"New",NOW())');
        $stmt->execute([
            $form['user_name'],
            $form['email'],
            $form['phone'],
            $form['company_name'],
            $form['country'],
            $form['topic'],
            $form['chat_summary'],
        ]);
        redirect('success.php?type=handover');
    }
}

include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow">Human handover</span>
    <h1>Send the conversation to a real team member.</h1>
    <p>Submit your details and our team will review the chatbot summary before following up.</p>
</section>

<section class="section form-layout">
    <div class="form-intro">
        <span class="section-kicker">Escalation</span>
        <h2>Clear context helps the team respond quickly.</h2>
        <p>Your handover request is stored separately from general inquiries and demo bookings.</p>
    </div>
    <div class="form-shell">
        <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <div class="alert alert-success">AI Assistant: I can connect you with a human team member. Please share your details.</div>
        <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="field-trap" aria-hidden="true">
                <label>Website</label>
                <input name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-grid">
                <div class="form-group"><label for="user_name">Name *</label><input id="user_name" name="user_name" maxlength="150" autocomplete="name" value="<?= e($form['user_name']) ?>" required></div>
                <div class="form-group"><label for="email">Email Address *</label><input id="email" type="email" name="email" maxlength="150" autocomplete="email" value="<?= e($form['email']) ?>" required></div>
                <div class="form-group"><label for="phone">Phone Number *</label><input id="phone" name="phone" type="tel" inputmode="tel" maxlength="50" autocomplete="tel" pattern="[0-9+()\-\s]{7,30}" placeholder="e.g. +44 7700 900123" title="Use 7-30 characters: digits, spaces, and + ( ) - only." value="<?= e($form['phone']) ?>" required></div>
                <div class="form-group"><label for="company_name">Company Name</label><input id="company_name" name="company_name" maxlength="150" autocomplete="organization" value="<?= e($form['company_name']) ?>"></div>
                <div class="form-group"><label for="country">Country *</label><input id="country" name="country" maxlength="100" autocomplete="country-name" value="<?= e($form['country']) ?>" required></div>
                <div class="form-group"><label for="topic">Topic *</label><select id="topic" name="topic" required><option value="">Select topic</option><?php foreach ($topics as $topic): ?><option value="<?= e($topic) ?>" <?= selected($form['topic'], $topic) ?>><?= e($topic) ?></option><?php endforeach; ?></select></div>
                <div class="form-group full"><label for="chat_summary">Conversation Summary *</label><textarea id="chat_summary" name="chat_summary" maxlength="2000" required><?= e($form['chat_summary']) ?></textarea></div>
            </div>
            <div class="button-row"><button class="btn btn-primary" type="submit">Submit for Follow-up</button><a class="btn btn-secondary" href="chatbot.php">Back to Chat</a></div>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
