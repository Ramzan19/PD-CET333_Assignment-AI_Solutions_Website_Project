<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mfa.php';

require_admin();
ensure_admin_security_schema($pdo);

$current_page = 'security';
$page_title = 'Security';
$errors = [];
$notice = '';

$admin_id = (int) ($_SESSION['admin_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
$mfa_active = $admin && !empty($admin['mfa_enabled']) && !empty($admin['mfa_secret']);

if (is_post()) {
    csrf_or_fail();
    $action = $_POST['action'] ?? '';

    if ($action === 'begin') {
        // Generate a fresh secret and hold it in the session until confirmed.
        $_SESSION['mfa_setup_secret'] = mfa_generate_secret();
        $notice = 'Scan or enter the key below in your authenticator app, then confirm with a code.';
    } elseif ($action === 'confirm') {
        $secret = (string) ($_SESSION['mfa_setup_secret'] ?? '');
        $code = $_POST['mfa_code'] ?? '';
        if ($secret === '') {
            $errors[] = 'Your setup session expired. Please start again.';
        } elseif (!mfa_verify($secret, $code)) {
            $errors[] = 'That code did not match. Make sure the app clock is correct and try the current code.';
        } else {
            $update = $pdo->prepare('UPDATE admin_users SET mfa_enabled = 1, mfa_secret = ? WHERE id = ?');
            $update->execute([$secret, $admin_id]);
            unset($_SESSION['mfa_setup_secret']);
            admin_log_activity($pdo, 'mfa_enabled', 'Two-factor authentication enabled');
            redirect('security.php?status=enabled');
        }
    } elseif ($action === 'disable') {
        $code = $_POST['mfa_code'] ?? '';
        if (!$mfa_active) {
            $errors[] = 'Two-factor authentication is not currently enabled.';
        } elseif (!mfa_verify($admin['mfa_secret'], $code)) {
            $errors[] = 'Enter a valid current code to confirm turning off two-factor authentication.';
        } else {
            $update = $pdo->prepare('UPDATE admin_users SET mfa_enabled = 0, mfa_secret = NULL WHERE id = ?');
            $update->execute([$admin_id]);
            admin_log_activity($pdo, 'mfa_disabled', 'Two-factor authentication disabled');
            redirect('security.php?status=disabled');
        }
    }

    // Refresh admin row after any change.
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
    $mfa_active = $admin && !empty($admin['mfa_enabled']) && !empty($admin['mfa_secret']);
}

if (($_GET['status'] ?? '') === 'enabled') {
    $notice = 'Two-factor authentication is now active on your account.';
}
if (($_GET['status'] ?? '') === 'disabled') {
    $notice = 'Two-factor authentication has been turned off.';
}

$setup_secret = (string) ($_SESSION['mfa_setup_secret'] ?? '');
$otpauth_uri = $setup_secret !== ''
    ? mfa_otpauth_uri($setup_secret, $admin['username'] ?? 'admin', SITE_NAME)
    : '';

include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Account security</span>
        <h1>Two-Factor Authentication</h1>
        <p>Add an authenticator-app code on top of your password for the admin area.</p>
    </div>
    <div class="button-row">
        <span class="badge"><?= $mfa_active ? '2FA Enabled' : '2FA Disabled' ?></span>
    </div>
</div>

<?php if ($notice): ?><div class="alert alert-success"><?= e($notice) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<div class="table-panel">
    <div class="table-header"><h2>Status</h2><span class="badge"><?= e(admin_name()) ?></span></div>
    <div style="padding:1.25rem;">
        <?php if ($mfa_active): ?>
            <p>Two-factor authentication is <strong>enabled</strong>. You will be asked for a 6-digit code from your authenticator app each time you sign in.</p>
            <form method="post" autocomplete="off" data-live-validate novalidate style="margin-top:1rem;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="disable">
                <div class="form-group" style="max-width:260px;"><label for="mfa_code">Enter a current code to disable</label><input id="mfa_code" name="mfa_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="123456" required></div>
                <div class="button-row"><button class="btn btn-secondary" type="submit">Disable 2FA</button></div>
            </form>
        <?php elseif ($setup_secret !== ''): ?>
            <p>Add this account to <strong>Google Authenticator</strong>, <strong>Microsoft Authenticator</strong>, or <strong>Authy</strong> using the key below, then enter the current 6-digit code to confirm.</p>
            <div class="mfa-setup-grid">
                <div>
                    <p class="mfa-setup-label">Setup key (manual entry)</p>
                    <p class="mfa-secret-key"><?= e(mfa_format_secret($setup_secret)) ?></p>
                    <p class="mfa-setup-label">Or paste this into a QR generator / app link</p>
                    <code class="mfa-otpauth"><?= e($otpauth_uri) ?></code>
                </div>
                <form method="post" autocomplete="off" data-live-validate novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="confirm">
                    <div class="form-group"><label for="mfa_code">Confirmation code</label><input id="mfa_code" name="mfa_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="123456" autofocus required></div>
                    <div class="button-row"><button class="btn btn-primary" type="submit">Confirm &amp; Enable</button></div>
                </form>
            </div>
        <?php else: ?>
            <p>Two-factor authentication is <strong>not enabled</strong>. Enabling it means every admin sign-in will need a code from your phone, even if your password is known.</p>
            <form method="post" style="margin-top:1rem;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="begin">
                <div class="button-row"><button class="btn btn-primary" type="submit">Set Up 2FA</button></div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
