<?php
$current_page = 'terms';
$page_title = 'Terms of Service';
include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow"><?= e(tr('Terms of service')) ?></span>
    <h1><?= e(tr('Responsible use of the AI-Solutions website.')) ?></h1>
    <p><?= e(tr('These terms explain how visitors should use the prototype website, forms, event RSVPs, and assistant experience.')) ?></p>
</section>

<section class="section">
    <div class="grid-3">
        <article class="card"><span class="card-number">01</span><h3><?= e(tr('Website use')) ?></h3><p><?= e(tr('Use the forms and assistant for genuine business inquiries, event registrations, demo requests, and feedback.')) ?></p></article>
        <article class="card"><span class="card-number">02</span><h3><?= e(tr('Information accuracy')) ?></h3><p><?= e(tr('Visitors are responsible for providing accurate contact and company information when submitting forms.')) ?></p></article>
        <article class="card"><span class="card-number">03</span><h3><?= e(tr('Prototype limits')) ?></h3><p><?= e(tr('Content is provided for demonstration and planning purposes. Production deployments require final legal, security, and hosting review.')) ?></p></article>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
