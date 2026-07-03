<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/assistant.php';

security_headers();
start_secure_session();
header('Content-Type: application/json; charset=utf-8');

function assistant_json($payload, $status = 200) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function assistant_json_csrf_valid($token) {
    $expected = (string) ($_SESSION['csrf_token'] ?? '');
    return $token !== '' && $expected !== '' && hash_equals($expected, (string) $token);
}

function assistant_rate_limited() {
    $now = time();
    $window = 60;
    $limit = 24;
    $requests = $_SESSION['assistant_rate'] ?? [];
    if (!is_array($requests)) {
        $requests = [];
    }
    $requests = array_values(array_filter($requests, function ($timestamp) use ($now, $window) {
        return is_int($timestamp) && $timestamp > ($now - $window);
    }));
    if (count($requests) >= $limit) {
        $_SESSION['assistant_rate'] = $requests;
        return true;
    }
    $requests[] = $now;
    $_SESSION['assistant_rate'] = $requests;
    return false;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    assistant_json(['ok' => false, 'error' => 'Use POST to chat with AI-Solutions.'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);
if (!is_array($payload)) {
    assistant_json(['ok' => false, 'error' => 'Invalid chat request.'], 400);
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($payload['csrf_token'] ?? '');
if (!assistant_json_csrf_valid($csrf)) {
    assistant_json(['ok' => false, 'error' => 'Security check failed. Please refresh the page and try again.'], 403);
}

if (assistant_rate_limited()) {
    assistant_json([
        'ok' => false,
        'error' => 'AI-Solutions is receiving too many messages at once. Please wait a moment and try again.',
    ], 429);
}

$message = assistant_clean_text($payload['message'] ?? '', 1000);
if ($message === '') {
    assistant_json(['ok' => false, 'error' => 'Please type a message for AI-Solutions.'], 422);
}

$answer = assistant_reply_payload($message, $payload['history'] ?? []);
assistant_json([
    'ok' => true,
    'reply' => $answer['reply'],
    'topic' => $answer['topic'],
    'actions' => $answer['actions'],
    'source' => $answer['source'],
]);
