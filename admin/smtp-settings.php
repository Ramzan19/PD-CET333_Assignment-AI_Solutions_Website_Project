<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
require_admin();
require_admin_role(['super_admin']);

$current_page = 'smtp';
$page_title = 'SMTP Settings';
$errors = [];
$success = '';
$settings = smtp_settings($pdo);
$test_email = '';

if (is_post()) {
    csrf_or_fail();

    $action = post_value('action', 30);
    $password = (string) ($_POST['password_value'] ?? '');
    $test_email = post_value('test_email', 180);
    $form = [
        'enabled' => !empty($_POST['enabled']) ? 1 : 0,
        'host' => post_value('host', 180),
        'port' => (int) ($_POST['port'] ?? 587),
        'encryption' => post_value('encryption', 20),
        'username' => post_value('username', 180),
        'from_email' => post_value('from_email', 180),
        'from_name' => post_value('from_name', 180),
    ];

    if ($form['host'] === '') {
        $errors[] = 'SMTP host is required.';
    }
    if ($form['port'] < 1 || $form['port'] > 65535) {
        $errors[] = 'SMTP port must be between 1 and 65535.';
    }
    if (!in_array($form['encryption'], ['tls', 'ssl', 'none'], true)) {
        $errors[] = 'Please select a valid encryption option.';
    }
    if ($form['from_email'] === '' || !valid_email($form['from_email'])) {
        $errors[] = 'A valid from email address is required.';
    }
    if ($form['username'] !== '' && $password === '' && empty($settings['password_value'])) {
        $errors[] = 'Enter the SMTP password or app password for this account.';
    }
    if ($action === 'test' && ($test_email === '' || !valid_email($test_email))) {
        $errors[] = 'Enter a valid test recipient email address.';
    }

    if (!$errors) {
        smtp_save_settings($pdo, $form, $password);
        $settings = smtp_settings($pdo);
        $success = 'SMTP settings saved.';

        if ($action === 'test') {
            $result = smtp_send_mail(
                $pdo,
                $test_email,
                'AI-Solutions SMTP Test',
                'This is a real SMTP test email from the AI-Solutions admin area.',
                []
            );
            $success = $result['sent']
                ? 'SMTP settings saved and the test email was sent successfully.'
                : 'SMTP settings saved, but the test email could not be sent: ' . $result['reason'];
            if (!$result['sent']) {
                $errors[] = $success;
                $success = '';
            }
        }
    }
}

include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Real email delivery</span>
        <h1>SMTP Settings</h1>
        <p>Connect a real email account so admin report emails are delivered through SMTP instead of XAMPP local mail.</p>
    </div>
    <a class="btn btn-secondary" href="email-outbox.php">View Outbox</a>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<section class="form-shell smtp-settings-panel">
    <form method="post" autocomplete="off" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group full">
                <label class="checkbox-label"><input type="checkbox" name="enabled" value="1" <?= !empty($settings['enabled']) ? 'checked' : '' ?>> Enable real SMTP sending</label>
            </div>
            <div class="form-group"><label for="host">SMTP Host *</label><input id="host" name="host" maxlength="180" value="<?= e($settings['host']) ?>" placeholder="smtp.gmail.com" required></div>
            <div class="form-group"><label for="port">SMTP Port *</label><input id="port" type="number" name="port" min="1" max="65535" value="<?= e($settings['port']) ?>" required></div>
            <div class="form-group"><label for="encryption">Encryption *</label><select id="encryption" name="encryption"><option value="tls" <?= selected($settings['encryption'], 'tls') ?>>TLS / STARTTLS</option><option value="ssl" <?= selected($settings['encryption'], 'ssl') ?>>SSL</option><option value="none" <?= selected($settings['encryption'], 'none') ?>>None</option></select></div>
            <div class="form-group"><label for="username">SMTP Username</label><input id="username" name="username" maxlength="180" value="<?= e($settings['username']) ?>" placeholder="your-email@gmail.com"></div>
            <div class="form-group"><label for="password_value">SMTP Password / App Password</label><input id="password_value" type="password" name="password_value" autocomplete="new-password" placeholder="<?= e(smtp_mask_secret($settings['password_value'])) ?>"></div>
            <div class="form-group"><label for="from_email">From Email *</label><input id="from_email" type="email" name="from_email" maxlength="180" value="<?= e($settings['from_email']) ?>" placeholder="your-email@gmail.com" required></div>
            <div class="form-group"><label for="from_name">From Name</label><input id="from_name" name="from_name" maxlength="180" value="<?= e($settings['from_name']) ?>"></div>
            <div class="form-group full"><label for="test_email">Send Test To</label><input id="test_email" type="email" name="test_email" maxlength="180" value="<?= e($test_email) ?>" placeholder="recipient@example.com"></div>
        </div>
        <div class="button-row">
            <button class="btn btn-primary" type="submit" name="action" value="save">Save SMTP Settings</button>
            <button class="btn btn-secondary" type="submit" name="action" value="test">Save and Send Test</button>
        </div>
    </form>
</section>

<section class="table-panel smtp-help-panel">
    <div class="table-header"><h2>Common SMTP Values</h2><span class="badge">Use an app password where required</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Provider</th><th>Host</th><th>Port</th><th>Encryption</th><th>Username</th></tr></thead>
            <tbody>
                <tr><td>Gmail</td><td>smtp.gmail.com</td><td>587</td><td>TLS / STARTTLS</td><td>Your Gmail address</td></tr>
                <tr><td>Outlook / Hotmail</td><td>smtp.office365.com</td><td>587</td><td>TLS / STARTTLS</td><td>Your Outlook address</td></tr>
                <tr><td>Yahoo Mail</td><td>smtp.mail.yahoo.com</td><td>587</td><td>TLS / STARTTLS</td><td>Your Yahoo address</td></tr>
            </tbody>
        </table>
    </div>
</section>
<?php include 'admin-footer.php'; ?>
