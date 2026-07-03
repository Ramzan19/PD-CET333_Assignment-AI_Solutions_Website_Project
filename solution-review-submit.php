<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/solutions.php';

security_headers();
start_secure_session();

if (!is_post()) {
    redirect('services.php');
}

csrf_or_fail();

$catalog = ai_solutions_catalog_map();
$solution_key = (string) ($_POST['solution_key'] ?? '');
$name = post_value('reviewer_name', 150);
$email = post_value('email', 150);
$company = post_value('company_name', 150);
$comment = post_value('comment', 1500);
$rating = (int) ($_POST['rating'] ?? 0);

$errors = [];
if (!isset($catalog[$solution_key])) {
    $errors[] = 'Please choose a valid solution to review.';
}
$errors = array_merge($errors, required_fields([
    'reviewer_name' => 'Your name',
    'email' => 'Email',
    'comment' => 'Review',
], $_POST));

if (!honeypot_clear()) {
    $errors[] = 'Security check failed. Please try again.';
}
if ($email !== '' && !valid_email($email)) {
    $errors[] = 'Please enter a valid email address.';
}
if ($rating < 1 || $rating > 5) {
    $errors[] = 'Please choose a star rating from 1 to 5.';
}

if ($errors) {
    $_SESSION['solution_review_errors'] = $errors;
    $_SESSION['solution_review_key'] = $solution_key;
    redirect('services.php#rate-solution');
}

ensure_solution_reviews_table($pdo);
$stmt = $pdo->prepare('INSERT INTO solution_reviews(solution_key, solution_title, reviewer_name, email, company_name, rating, comment, status, created_at) VALUES(?,?,?,?,?,?,?,"New",NOW())');
$stmt->execute([
    $solution_key,
    $catalog[$solution_key],
    $name,
    $email,
    $company !== '' ? $company : null,
    $rating,
    $comment,
]);

redirect('services.php?review=thanks#rate-solution');
