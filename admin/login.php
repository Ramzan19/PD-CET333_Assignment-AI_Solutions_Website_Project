<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mfa.php';

security_headers();
ensure_admin_security_schema($pdo);

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$username = '';
$blocked_until = (int) ($_SESSION['login_block_until'] ?? 0);
// Stage: 'password' (default) or 'mfa' (awaiting authenticator code).
$stage = !empty($_SESSION['pending_admin_id']) ? 'mfa' : 'password';

function admin_complete_login(PDO $pdo, array $admin) {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_role'] = $admin['role'] ?? 'viewer';
    $_SESSION['login_attempts'] = 0;
    unset(
        $_SESSION['login_block_until'],
        $_SESSION['pending_admin_id'],
        $_SESSION['pending_admin_username'],
        $_SESSION['pending_admin_role']
    );
    $update = $pdo->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
    $update->execute([$admin['id']]);
    admin_log_activity($pdo, 'login', 'Admin login completed');
    redirect('dashboard.php');
}

function admin_register_failed_attempt() {
    $_SESSION['login_attempts'] = (int) ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_block_until'] = time() + 300;
    }
}

if (is_post()) {
    csrf_or_fail();

    if ($blocked_until > time()) {
        $errors[] = 'Too many login attempts. Please wait a few minutes and try again.';
    } elseif (($_POST['stage'] ?? '') === 'mfa' && !empty($_SESSION['pending_admin_id'])) {
        // --- Second factor: verify the authenticator code ---
        $stage = 'mfa';
        $code = preg_replace('/\D/', '', (string) ($_POST['mfa_code'] ?? ''));
        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $_SESSION['pending_admin_id']]);
        $admin = $stmt->fetch();

        if (!$admin || empty($admin['mfa_secret'])) {
            unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_username'], $_SESSION['pending_admin_role']);
            $errors[] = 'Your login session expired. Please sign in again.';
            $stage = 'password';
        } elseif (mfa_verify($admin['mfa_secret'], $code)) {
            admin_complete_login($pdo, $admin);
        } else {
            admin_register_failed_attempt();
            admin_log_activity($pdo, 'mfa_failed', 'Incorrect authenticator code');
            $errors[] = 'That authenticator code was not correct. Please try the current 6-digit code.';
        }
    } else {
        // --- First factor: username + password ---
        $username = post_value('username', 100);
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $errors[] = 'Username and password are required.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                if (!empty($admin['mfa_enabled']) && !empty($admin['mfa_secret'])) {
                    // Hold the session in a pending state until the code is verified.
                    $_SESSION['pending_admin_id'] = $admin['id'];
                    $_SESSION['pending_admin_username'] = $admin['username'];
                    $_SESSION['pending_admin_role'] = $admin['role'] ?? 'viewer';
                    $stage = 'mfa';
                } else {
                    admin_complete_login($pdo, $admin);
                }
            } else {
                admin_register_failed_attempt();
                $errors[] = 'Invalid username or password.';
            }
        }
    }
}

// Allow the user to abandon the pending MFA step and start over.
if (($_GET['restart'] ?? '') === '1') {
    unset($_SESSION['pending_admin_id'], $_SESSION['pending_admin_username'], $_SESSION['pending_admin_role']);
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | AI-Solutions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="admin-body">
<section class="login-page">
    <div class="login-card">
        <a class="brand login-brand" href="../index.php"><span class="brand-logo">A</span><span>AI-Solutions</span></a>
        <span class="section-kicker">Secure admin access</span>
        <?php if ($stage === 'mfa'): ?>
            <h1>Two-Factor Verification</h1>
            <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
            <p class="login-note">Open your authenticator app and enter the current 6-digit code for <strong><?= e($_SESSION['pending_admin_username'] ?? 'your account') ?></strong>.</p>
            <form method="post" autocomplete="off" data-live-validate novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="stage" value="mfa">
                <div class="form-group"><label for="mfa_code">Authenticator Code</label><input id="mfa_code" name="mfa_code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="123456" autofocus required></div>
                <div class="button-row login-actions"><button class="btn btn-primary full-width" type="submit">Verify &amp; Continue</button></div>
            </form>
            <p class="login-note"><a href="login.php?restart=1">Cancel and sign in again</a></p>
        <?php else: ?>
            <h1>Admin Login</h1>
            <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
            <form method="post" autocomplete="off" data-live-validate novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="stage" value="password">
                <div class="form-group"><label for="username">Username</label><input id="username" name="username" maxlength="100" autocomplete="off" autocapitalize="none" spellcheck="false" value="<?= e($username) ?>" required></div>
                <div class="form-group"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="off" autocapitalize="none" spellcheck="false" required></div>
                <div class="button-row login-actions"><button class="btn btn-primary full-width" type="submit">Login</button></div>
            </form>
            <p class="login-note">Authorised access only. Change the default local password before any public deployment.</p>
            <p class="login-note"><a href="../index.php">Back to website</a></p>
        <?php endif; ?>
    </div>
</section>
<script src="../assets/js/site.js?v=<?= filemtime(__DIR__ . '/../assets/js/site.js') ?>"></script>
</body>
</html>
