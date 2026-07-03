<?php
require_once 'includes/feedback.php';

security_headers();
start_secure_session();

$wants_json = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
    || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

function feedback_redirect_base() {
    $target = (string) ($_POST['feedback_redirect'] ?? 'contact.php');
    $allowed_targets = ['contact.php', 'index.php'];
    return in_array($target, $allowed_targets, true) ? $target : 'contact.php';
}

function feedback_submit_response(array $payload, int $status, bool $wants_json) {
    http_response_code($status);
    if ($wants_json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    $redirect_base = feedback_redirect_base();
    if (!empty($payload['success'])) {
        redirect($redirect_base . '?feedback=thanks#visitor-feedback');
    }

    $_SESSION['feedback_errors'] = $payload['errors'] ?? ['We could not save your rating right now.'];
    redirect($redirect_base . '#visitor-feedback');
}

if (!is_post()) {
    feedback_submit_response([
        'success' => false,
        'errors' => ['Feedback must be submitted from the form.'],
    ], 405, $wants_json);
}

if (($_POST['form_type'] ?? '') !== 'visitor_feedback') {
    feedback_submit_response([
        'success' => false,
        'errors' => ['Invalid feedback form.'],
    ], 400, $wants_json);
}

if (!verify_csrf()) {
    feedback_submit_response([
        'success' => false,
        'errors' => ['Security check failed. Please refresh the page and try again.'],
    ], 403, $wants_json);
}

[$feedback_form, $feedback_rating, $feedback_errors] = validate_feedback_submission();

if ($feedback_errors) {
    feedback_submit_response([
        'success' => false,
        'errors' => $feedback_errors,
    ], 422, $wants_json);
}

$pdo = feedback_safe_db();
if (!$pdo) {
    feedback_submit_response([
        'success' => false,
        'errors' => ['The rating system is temporarily unavailable. Please try again after the database is running.'],
    ], 503, $wants_json);
}

try {
    $row = save_visitor_feedback($pdo, $feedback_form, $feedback_rating);
    $card = feedback_card_from_row($row);
    feedback_submit_response([
        'success' => true,
        'message' => 'Thank you. Your rating has been received and is now visible in the feedback area.',
        'review' => feedback_card_payload($card),
        'stats' => feedback_stats($pdo),
    ], 200, $wants_json);
} catch (PDOException $e) {
    error_log('Feedback submit failed: ' . $e->getMessage());
    feedback_submit_response([
        'success' => false,
        'errors' => ['We could not save your rating right now. Please try again in a moment.'],
    ], 500, $wants_json);
}
