<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/event-data.php';
require_once 'includes/mailer.php';

$current_page = 'demo';
$page_title = 'Schedule Demo';
$errors = [];
$fields = ['full_name', 'email', 'phone', 'company_name', 'country', 'preferred_date', 'preferred_time', 'demo_type', 'notes'];
$form = array_fill_keys($fields, '');
$today = date('Y-m-d');
$demo_types = ['Virtual Assistant', 'Workflow Automation', 'Data Analytics', 'AI Product Prototyping', 'Full Consultation'];

if (is_post()) {
    csrf_or_fail();

    foreach ($fields as $field) {
        $form[$field] = post_value($field, $field === 'notes' ? 2000 : 150);
    }

    $errors = array_merge(
        required_fields([
            'full_name' => 'Full name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company_name' => 'Company',
            'country' => 'Country',
            'preferred_date' => 'Preferred date',
            'preferred_time' => 'Preferred time',
            'demo_type' => 'Demo type',
        ], $_POST),
        max_length_errors([
            'full_name' => ['Full name', 150],
            'email' => ['Email', 150],
            'phone' => ['Phone', 50],
            'company_name' => ['Company', 150],
            'country' => ['Country', 100],
            'demo_type' => ['Demo type', 150],
            'notes' => ['Additional notes', 2000],
        ], $_POST)
    );

    if (!honeypot_clear()) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if ($form['email'] !== '' && !valid_email($form['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($form['preferred_date'] !== '' && $form['preferred_date'] < $today) {
        $errors[] = 'Preferred date cannot be in the past.';
    }

    if ($form['demo_type'] !== '' && !in_array($form['demo_type'], $demo_types, true)) {
        $errors[] = 'Please select a valid demo type.';
    }

    if (empty($_POST['consent'])) {
        $errors[] = 'Please confirm consent to be contacted.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO demo_bookings(full_name,email,phone,company_name,country,preferred_date,preferred_time,demo_type,notes,status,created_at) VALUES(?,?,?,?,?,?,?,?,?,"Booked",NOW())');
        $stmt->execute([
            $form['full_name'],
            $form['email'],
            $form['phone'],
            $form['company_name'],
            $form['country'],
            $form['preferred_date'],
            $form['preferred_time'],
            $form['demo_type'],
            $form['notes'],
        ]);
        $body = "Hello " . $form['full_name'] . ",\n\n"
            . "Thank you for booking an AI-Solutions demo for " . $form['demo_type'] . ".\n\n"
            . "Requested date: " . $form['preferred_date'] . "\n"
            . "Requested time: " . $form['preferred_time'] . "\n\n"
            . "Our team will confirm the final session details.\n\n"
            . "AI-Solutions";
        mail_send_or_queue($pdo, $form['email'], 'We received your AI-Solutions demo request', $body);
        redirect('success.php?type=demo');
    }
}

include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow"><?= e(tr('Schedule demo')) ?></span>
    <h1><?= e(tr('See the assistant, automation, and analytics flow together.')) ?></h1>
    <p><?= e(tr('Choose a preferred date and time. We will confirm the session and tailor the walkthrough to your goals.')) ?></p>
</section>

<section class="section form-layout">
    <div class="form-intro">
        <span class="section-kicker"><?= e(tr('Demo booking')) ?></span>
        <h2><?= e(tr('Pick the solution area you want to inspect first.')) ?></h2>
        <p><?= e(tr('The more specific your notes, the more relevant the personalised demo will be.')) ?></p>
    </div>
    <div class="form-shell">
        <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', array_map('tr', $errors))) ?></div><?php endif; ?>
        <form method="post" data-live-validate novalidate>
            <?= csrf_field() ?>
            <div class="field-trap" aria-hidden="true">
                <label><?= e(tr('Website')) ?></label>
                <input name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-grid">
                <div class="form-group"><label for="full_name"><?= e(tr('Full Name *')) ?></label><input id="full_name" name="full_name" maxlength="150" autocomplete="name" value="<?= e($form['full_name']) ?>" required></div>
                <div class="form-group"><label for="email"><?= e(tr('Email Address *')) ?></label><input id="email" type="email" name="email" maxlength="150" autocomplete="email" value="<?= e($form['email']) ?>" required></div>
                <div class="form-group"><label for="phone"><?= e(tr('Phone Number *')) ?></label><input id="phone" name="phone" type="tel" inputmode="tel" maxlength="50" autocomplete="tel" pattern="[0-9+()\-\s]{7,30}" placeholder="e.g. +44 7700 900123" title="Use 7-30 characters: digits, spaces, and + ( ) - only." value="<?= e($form['phone']) ?>" required></div>
                <div class="form-group"><label for="company_name"><?= e(tr('Company Name *')) ?></label><input id="company_name" name="company_name" maxlength="150" autocomplete="organization" value="<?= e($form['company_name']) ?>" required></div>
                <div class="form-group"><label for="country"><?= e(tr('Country *')) ?></label><input id="country" name="country" maxlength="100" autocomplete="country-name" value="<?= e($form['country']) ?>" required></div>
                <div class="form-group"><label for="preferred_date"><?= e(tr('Preferred Date *')) ?></label><input id="preferred_date" type="date" name="preferred_date" min="<?= e($today) ?>" value="<?= e($form['preferred_date']) ?>" required></div>
                <div class="form-group"><label for="preferred_time"><?= e(tr('Preferred Time *')) ?></label><input id="preferred_time" type="time" name="preferred_time" value="<?= e($form['preferred_time']) ?>" required></div>
                <div class="form-group"><label for="demo_type"><?= e(tr('Interested Solution *')) ?></label><select id="demo_type" name="demo_type" required><option value=""><?= e(tr('Select solution')) ?></option><?php foreach ($demo_types as $demo_type): ?><option value="<?= e($demo_type) ?>" <?= selected($form['demo_type'], $demo_type) ?>><?= e(tr($demo_type)) ?></option><?php endforeach; ?></select></div>
                <div class="form-group full"><label for="notes"><?= e(tr('Additional Notes')) ?></label><textarea id="notes" name="notes" maxlength="2000"><?= e($form['notes']) ?></textarea></div>
                <div class="form-group full"><label class="checkbox-label"><input type="checkbox" name="consent" value="1" <?= !empty($_POST['consent']) ? 'checked' : '' ?> required> <?= e(tr('I agree to be contacted by AI-Solutions.')) ?></label></div>
            </div>
            <div class="button-row"><button class="btn btn-primary" type="submit"><?= e(tr('Book Demo')) ?></button><a class="btn btn-secondary" href="index.php"><?= e(tr('Cancel')) ?></a></div>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
