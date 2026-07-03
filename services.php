<?php
require_once 'includes/feedback.php';
require_once 'includes/solutions.php';

$current_page = 'services';
$page_title = 'Solutions';
$meta_description = 'Industry-specific AI solutions from AI-Solutions: assistants, workflow automation, analytics dashboards, and prototypes, with customer ratings and case studies.';
$solutions = ai_solutions_catalog();
$case_studies = ai_solutions_case_studies();

$reviews_db = feedback_safe_db();
$rating_map = [];
if ($reviews_db) {
    try {
        $rating_map = solution_review_averages($reviews_db);
    } catch (PDOException $e) {
        error_log('Solution review averages failed: ' . $e->getMessage());
    }
}

$feedback_cards = feedback_fetch_cards($reviews_db, 3, 0);

start_secure_session();
$review_errors = [];
if (!empty($_SESSION['solution_review_errors']) && is_array($_SESSION['solution_review_errors'])) {
    $review_errors = $_SESSION['solution_review_errors'];
    unset($_SESSION['solution_review_errors']);
}
$preselected_key = (string) ($_SESSION['solution_review_key'] ?? ($_GET['rate'] ?? ''));
unset($_SESSION['solution_review_key']);
$review_submitted = ($_GET['review'] ?? '') === 'thanks';

include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow"><?= e(tr('Solutions')) ?></span>
    <h1><?= e(tr(cms_text('services_hero_title', 'Industry-specific AI solutions for practical teams.'))) ?></h1>
    <p><?= e(tr('Filter by industry or capability to see where assistants, automation, analytics, and prototypes can fit into real work.')) ?></p>
</section>

<section class="section solutions-section">
    <div class="solution-filter-bar" data-solution-filters aria-label="Filter solutions">
        <button class="active" type="button" data-solution-filter="all" aria-pressed="true"><?= e(tr('All')) ?></button>
        <button type="button" data-solution-filter="retail" aria-pressed="false"><?= e(tr('Retail')) ?></button>
        <button type="button" data-solution-filter="healthcare" aria-pressed="false"><?= e(tr('Healthcare')) ?></button>
        <button type="button" data-solution-filter="education" aria-pressed="false"><?= e(tr('Education')) ?></button>
        <button type="button" data-solution-filter="services" aria-pressed="false"><?= e(tr('Services')) ?></button>
        <button type="button" data-solution-filter="assistant" aria-pressed="false"><?= e(tr('Assistant')) ?></button>
        <button type="button" data-solution-filter="automation" aria-pressed="false"><?= e(tr('Automation')) ?></button>
        <button type="button" data-solution-filter="analytics" aria-pressed="false"><?= e(tr('Analytics')) ?></button>
        <button type="button" data-solution-filter="prototype" aria-pressed="false"><?= e(tr('Prototype')) ?></button>
    </div>

    <div class="grid-3 solution-grid">
        <?php foreach ($solutions as $solution): ?>
            <?php $rating = $rating_map[$solution['key']] ?? null; ?>
            <article class="card service-card solution-card" data-solution-card data-industry="<?= e($solution['industry']) ?>" data-category="<?= e($solution['category']) ?>">
                <span class="card-number"><?= e($solution['number']) ?></span>
                <h3><?= e(tr($solution['title'])) ?></h3>
                <?php if ($rating): ?>
                    <div class="solution-rating" aria-label="<?= e($rating['average']) ?> out of 5 from <?= e($rating['count']) ?> reviews">
                        <span class="stars" aria-hidden="true"><?= solution_review_stars($rating['average']) ?></span>
                        <strong><?= e($rating['average']) ?></strong>
                        <span class="rating-count">(<?= e($rating['count']) ?>)</span>
                    </div>
                <?php else: ?>
                    <div class="solution-rating is-empty"><?= e(tr('No ratings yet')) ?></div>
                <?php endif; ?>
                <p><?= e(tr($solution['summary'])) ?></p>
                <ul class="service-features">
                    <?php foreach ($solution['features'] as $feature): ?>
                        <li><?= e(tr($feature)) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="button-row">
                    <a class="text-link" href="<?= e($solution['href']) ?>"><?= e(tr($solution['action'])) ?></a>
                    <a class="text-link" href="services.php?rate=<?= e($solution['key']) ?>#rate-solution"><?= e(tr('Rate this')) ?></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section case-studies-section">
    <div class="section-header align-left">
        <span class="section-kicker"><?= e(tr('Case studies')) ?></span>
        <h2><?= e(tr('Past industry projects and measurable results.')) ?></h2>
        <p><?= e(tr('Real-world examples of how AI-Solutions solved a problem and the impact it delivered.')) ?></p>
    </div>
    <div class="case-study-grid">
        <?php foreach ($case_studies as $case): ?>
            <article class="case-study">
                <span class="case-industry"><?= e(tr($case['industry'])) ?></span>
                <h3><?= e(tr($case['title'])) ?></h3>
                <dl>
                    <dt><?= e(tr('Objective')) ?></dt><dd><?= e(tr($case['objective'])) ?></dd>
                    <dt><?= e(tr('Challenge')) ?></dt><dd><?= e(tr($case['challenge'])) ?></dd>
                    <dt><?= e(tr('Solution')) ?></dt><dd><?= e(tr($case['solution'])) ?></dd>
                </dl>
                <span class="case-result"><?= e(tr($case['result'])) ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section form-layout" id="rate-solution">
    <div class="form-intro">
        <span class="section-kicker"><?= e(tr('Rate a solution')) ?></span>
        <h2><?= e(tr('Used one of our solutions? Leave a review.')) ?></h2>
        <p><?= e(tr('Reviews are checked by the AI-Solutions admin team before they appear on the solution, so ratings stay authentic.')) ?></p>
    </div>
    <div class="form-shell">
        <?php if ($review_submitted): ?><div class="alert alert-success"><?= e(tr('Thank you. Your review has been submitted and will appear once the admin team approves it.')) ?></div><?php endif; ?>
        <?php if ($review_errors): ?><div class="alert alert-error"><?= e(implode(' ', $review_errors)) ?></div><?php endif; ?>
        <form method="post" action="solution-review-submit.php" data-live-validate novalidate>
            <?= csrf_field() ?>
            <div class="field-trap" aria-hidden="true">
                <label><?= e(tr('Website')) ?></label>
                <input name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-grid">
                <div class="form-group full">
                    <label for="solution_key"><?= e(tr('Solution *')) ?></label>
                    <select id="solution_key" name="solution_key" required>
                        <option value=""><?= e(tr('Select a solution')) ?></option>
                        <?php foreach ($solutions as $solution): ?>
                            <option value="<?= e($solution['key']) ?>" <?= selected($preselected_key, $solution['key']) ?>><?= e(tr($solution['title'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label for="reviewer_name"><?= e(tr('Your Name *')) ?></label><input id="reviewer_name" name="reviewer_name" maxlength="150" autocomplete="name" required></div>
                <div class="form-group"><label for="review_email"><?= e(tr('Email Address *')) ?></label><input id="review_email" name="email" type="email" maxlength="150" autocomplete="email" required></div>
                <div class="form-group"><label for="review_company"><?= e(tr('Company')) ?></label><input id="review_company" name="company_name" maxlength="150" autocomplete="organization"></div>
                <div class="form-group">
                    <label for="rating"><?= e(tr('Rating *')) ?></label>
                    <select id="rating" name="rating" required>
                        <option value=""><?= e(tr('Select rating')) ?></option>
                        <option value="5"><?= e(tr('5 - Excellent')) ?></option>
                        <option value="4"><?= e(tr('4 - Great')) ?></option>
                        <option value="3"><?= e(tr('3 - Good')) ?></option>
                        <option value="2"><?= e(tr('2 - Fair')) ?></option>
                        <option value="1"><?= e(tr('1 - Needs work')) ?></option>
                    </select>
                </div>
                <div class="form-group full"><label for="comment"><?= e(tr('Your Review *')) ?></label><textarea id="comment" name="comment" maxlength="1500" required></textarea></div>
            </div>
            <div class="button-row"><button class="btn btn-primary" type="submit"><?= e(tr('Submit Review')) ?></button><button class="btn btn-secondary" type="reset"><?= e(tr('Clear')) ?></button></div>
        </form>
    </div>
</section>

<section class="section split-section">
    <div>
        <span class="section-kicker"><?= e(tr('Customer feedback')) ?></span>
        <h2><?= e(tr('Solutions are shaped around real visitor and customer signals.')) ?></h2>
        <p><?= e(tr('Admin analytics, customer inquiries, event registrations, chatbot handovers, and visitor ratings help the team refine what customers need most.')) ?></p>
        <div class="button-row">
            <a class="btn btn-primary" href="contact.php"><?= e(tr('Start an Inquiry')) ?></a>
            <a class="btn btn-secondary" href="contact.php#visitor-feedback"><?= e(tr('Leave Feedback')) ?></a>
        </div>
    </div>
    <div class="case-grid">
        <?php foreach ($feedback_cards as $card): ?>
            <article class="case-row">
                <strong><?= e($card['display_name']) ?><?= e(tr(' rated ')) ?><?= e($card['rating']) ?>/5</strong>
                <span><?= e($card['message']) ?></span>
            </article>
        <?php endforeach; ?>
        <?php if (!$feedback_cards): ?>
            <article class="case-row"><strong><?= e(tr('Feedback-ready')) ?></strong><span><?= e(tr('Customer ratings appear here once visitors submit feedback.')) ?></span></article>
        <?php endif; ?>
    </div>
</section>

<section class="section cta-band">
    <span class="section-kicker"><?= e(tr('Next step')) ?></span>
    <h2><?= e(tr('See how the solution fits your workflow.')) ?></h2>
    <div class="hero-actions centered">
        <a class="btn btn-primary" href="schedule-demo.php"><?= e(tr('Schedule Demo')) ?></a>
        <a class="btn btn-secondary" href="contact.php"><?= e(tr('Contact Us')) ?></a>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
