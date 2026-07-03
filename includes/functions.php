<?php
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        $save_path = session_save_path();
        $test_path = $save_path;
        if (strpos($save_path, ';') !== false) {
            $parts = explode(';', $save_path);
            $test_path = end($parts);
        }
        if ($test_path === '' || !is_writable($test_path)) {
            $fallback_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
            if (!is_dir($fallback_path)) {
                @mkdir($fallback_path, 0700, true);
            }
            if (is_dir($fallback_path) && is_writable($fallback_path)) {
                session_save_path($fallback_path);
            }
        }

        $is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $is_secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function security_headers() {
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function is_post() {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function post_value($field, $max_length = 5000) {
    $value = trim((string) ($_POST[$field] ?? ''));
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max_length);
    }
    return substr($value, 0, $max_length);
}

function required_fields($fields, $source) {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty(trim((string) ($source[$field] ?? '')))) {
            $errors[] = $label . ' is required.';
        }
    }
    return $errors;
}

function max_length_errors($fields, $source) {
    $errors = [];
    foreach ($fields as $field => $rule) {
        [$label, $max] = $rule;
        if (strlen((string) ($source[$field] ?? '')) > $max) {
            $errors[] = $label . ' must be ' . $max . ' characters or fewer.';
        }
    }
    return $errors;
}

function csrf_token() {
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf() {
    start_secure_session();
    $token = (string) ($_POST['csrf_token'] ?? '');
    return $token !== '' && hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token);
}

function csrf_or_fail() {
    if (!verify_csrf()) {
        security_headers();
        http_response_code(403);
        exit('Security check failed. Please go back, refresh the page, and try again.');
    }
}

function honeypot_clear($field = 'website') {
    return trim((string) ($_POST[$field] ?? '')) === '';
}

function selected($current, $expected) {
    return (string) $current === (string) $expected ? 'selected' : '';
}

function captcha_question($key = 'default') {
    start_secure_session();
    if (empty($_SESSION['captcha'][$key]['answer'])) {
        captcha_reset($key);
    }
    return $_SESSION['captcha'][$key]['question'];
}

function captcha_reset($key = 'default') {
    start_secure_session();
    $left = random_int(2, 9);
    $right = random_int(2, 9);
    $_SESSION['captcha'][$key] = [
        'question' => $left . ' + ' . $right . ' = ?',
        'answer' => (string) ($left + $right),
    ];
}

function captcha_verify($key, $answer) {
    start_secure_session();
    $expected = (string) ($_SESSION['captcha'][$key]['answer'] ?? '');
    return $expected !== '' && hash_equals($expected, trim((string) $answer));
}

function mask_email($email) {
    $email = trim((string) $email);
    if (!valid_email($email)) {
        return 'the configured admin email';
    }

    [$local, $domain] = explode('@', $email, 2);
    $visible = substr($local, 0, 1);
    return $visible . str_repeat('*', max(2, strlen($local) - 1)) . '@' . $domain;
}
