<?php
$current_page = 'privacy';
$page_title = 'Privacy Policy';
include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow"><?= e(tr('Privacy policy')) ?></span>
    <h1><?= e(tr('How AI-Solutions handles visitor and inquiry data.')) ?></h1>
    <p><?= e(tr('This prototype stores only the information needed to respond to inquiries, manage events, run demos, and understand site performance.')) ?></p>
</section>

<section class="section">
    <div class="grid-3">
        <article class="card"><span class="card-number"><?= e(tr('Data')) ?></span><h3><?= e(tr('What we collect')) ?></h3><p><?= e(tr('Contact forms, demo bookings, event RSVPs, chatbot handovers, visitor feedback, and consent-based analytics events.')) ?></p></article>
        <article class="card"><span class="card-number"><?= e(tr('Use')) ?></span><h3><?= e(tr('Why we use it')) ?></h3><p><?= e(tr('To respond to requests, improve services, measure conversions, and manage admin follow-up securely.')) ?></p></article>
        <article class="card"><span class="card-number"><?= e(tr('Rights')) ?></span><h3><?= e(tr('Your choices')) ?></h3><p><?= e(tr('You can request correction, export, or deletion of personal data by contacting the AI-Solutions admin team.')) ?></p></article>
    </div>
</section>

<section class="section split-section">
    <div>
        <span class="section-kicker"><?= e(tr('GDPR-oriented handling')) ?></span>
        <h2><?= e(tr('Retention and access are intentionally limited.')) ?></h2>
        <p><?= e(tr('Admin access is password protected, form submissions are validated, and analytics only runs when visitors accept cookies.')) ?></p>
    </div>
    <div class="process-list">
        <div><span><?= e(tr('Retention')) ?></span><p><?= e(tr('Inquiry and event records should be reviewed monthly and removed when no longer needed.')) ?></p></div>
        <div><span><?= e(tr('Security')) ?></span><p><?= e(tr('Production deployments should use HTTPS, strong admin credentials, MFA, and routine backups.')) ?></p></div>
        <div><span><?= e(tr('Cookies')) ?></span><p><?= e(tr('The analytics cookie is optional and can be declined through the site banner.')) ?></p></div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
