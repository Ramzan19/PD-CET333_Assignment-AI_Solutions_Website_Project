<?php
require_once 'includes/functions.php';

$messages = [
    'inquiry' => 'Your inquiry has been saved successfully. Our team will review it and follow up.',
    'demo' => 'Your demo request has been booked successfully. Our team will confirm the details.',
    'event' => 'Your event registration has been saved successfully. Our team will send the joining details.',
    'handover' => 'Your chatbot handover request has been sent to our team.',
    'feedback' => 'Your rating has been received. Thank you for helping us improve the visitor experience.',
    'request' => 'Your request has been saved successfully.',
];

$type = $_GET['type'] ?? 'request';
$message = $messages[$type] ?? $messages['request'];
$page_title = 'Success';
include 'includes/header.php';
?>
<section class="success-page">
    <div class="success-card">
        <div class="success-icon" aria-hidden="true">&#10003;</div>
        <span class="section-kicker"><?= e(tr('Request submitted')) ?></span>
        <h1><?= e(tr('We received it.')) ?></h1>
        <p><?= e(tr($message)) ?></p>
        <p class="ref-badge"><?= e(tr('Reference:')) ?> AI-<?= date('YmdHis') ?></p>
        <div class="hero-actions centered">
            <a class="btn btn-primary" href="index.php"><?= e(tr('Back to Home')) ?></a>
            <a class="btn btn-secondary" href="services.php"><?= e(tr('View Services')) ?></a>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
