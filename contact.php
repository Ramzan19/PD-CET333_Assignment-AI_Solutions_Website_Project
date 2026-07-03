<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/feedback.php';
require_once 'includes/schema.php';
require_once 'includes/event-data.php';
require_once 'includes/mailer.php';

$current_page = 'contact';
$page_title = 'Contact Us';
$errors = [];
ensure_customer_inquiry_schema($pdo);
$fields = ['full_name', 'email', 'phone', 'company_name', 'country', 'solution_interest', 'job_title', 'job_details'];
$form = array_fill_keys($fields, '');
$solution_interests = ai_solutions_interest_options();
$feedback_errors = [];
$feedback_submitted = ($_GET['feedback'] ?? '') === 'thanks';
$feedback_form = feedback_form_defaults();
start_secure_session();
if (!empty($_SESSION['feedback_errors']) && is_array($_SESSION['feedback_errors'])) {
    $feedback_errors = $_SESSION['feedback_errors'];
    unset($_SESSION['feedback_errors']);
}
$rating_labels = feedback_rating_labels();

if (is_post()) {
    csrf_or_fail();

    foreach ($fields as $field) {
        $form[$field] = post_value($field, $field === 'job_details' ? 3000 : 150);
    }

    $errors = array_merge(
        required_fields([
            'full_name' => 'Full name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company_name' => 'Company',
            'country' => 'Country',
            'solution_interest' => 'Solution interest',
            'job_title' => 'Job title',
            'job_details' => 'Job details',
        ], $_POST),
        max_length_errors([
            'full_name' => ['Full name', 150],
            'email' => ['Email', 150],
            'phone' => ['Phone', 50],
            'company_name' => ['Company', 150],
            'country' => ['Country', 100],
            'solution_interest' => ['Solution interest', 150],
            'job_title' => ['Job title', 150],
            'job_details' => ['Job details', 3000],
        ], $_POST)
    );

    if (!honeypot_clear()) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if ($form['email'] !== '' && !valid_email($form['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($form['solution_interest'] !== '' && !in_array($form['solution_interest'], $solution_interests, true)) {
        $errors[] = 'Please select a valid solution interest.';
    }

    if (!captcha_verify('contact_inquiry', $_POST['captcha_answer'] ?? '')) {
        $errors[] = 'Please answer the security check correctly.';
    }

    if (empty($_POST['privacy_consent'])) {
        $errors[] = 'Please confirm that AI-Solutions may store and use your details to respond.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO customer_inquiries(full_name,email,phone,company_name,country,solution_interest,job_title,job_details,status,created_at) VALUES(?,?,?,?,?,?,?,?,"New",NOW())');
        $stmt->execute([
            $form['full_name'],
            $form['email'],
            $form['phone'],
            $form['company_name'],
            $form['country'],
            $form['solution_interest'],
            $form['job_title'],
            $form['job_details'],
        ]);
        $body = "Hello " . $form['full_name'] . ",\n\n"
            . "Thank you for contacting AI-Solutions. We received your inquiry about " . $form['solution_interest'] . ".\n\n"
            . "Our team will review your message and follow up with the next step.\n\n"
            . "AI-Solutions";
        mail_send_or_queue($pdo, $form['email'], 'We received your AI-Solutions inquiry', $body);
        captcha_reset('contact_inquiry');
        redirect('success.php?type=inquiry');
    }

    captcha_reset('contact_inquiry');
}

$captcha_question = captcha_question('contact_inquiry');

include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow"><?= e(tr('Contact Us')) ?></span>
    <h1><?= e(tr(cms_text('contact_hero_title', 'Tell us what you want AI to improve.'))) ?></h1>
    <p><?= e(tr('No customer account is required. Share the workflow, customer journey, or reporting gap you want to fix.')) ?></p>
</section>

<section class="section form-layout">
    <div class="form-intro">
        <span class="section-kicker"><?= e(tr('Project intake')) ?></span>
        <h2><?= e(tr('Useful details help us respond with a sharper plan.')) ?></h2>
        <p><?= e(tr('Describe the process, user group, and outcome you care about. We will review it and follow up with next steps.')) ?></p>
    </div>
    <div class="form-shell">
        <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', array_map('tr', $errors))) ?></div><?php endif; ?>
        <form method="post" data-live-validate novalidate>
            <?= csrf_field() ?>
            <div class="field-trap" aria-hidden="true">
                <label><?= e(tr('Website')) ?></label>
                <input name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-grid">
                <div class="form-group"><label for="full_name"><?= e(tr('Full Name *')) ?></label><input id="full_name" name="full_name" maxlength="150" autocomplete="name" value="<?= e($form['full_name']) ?>" required></div>
                <div class="form-group"><label for="email"><?= e(tr('Email Address *')) ?></label><input id="email" name="email" type="email" maxlength="150" autocomplete="email" value="<?= e($form['email']) ?>" required></div>
                <div class="form-group"><label for="phone"><?= e(tr('Phone Number *')) ?></label><input id="phone" name="phone" type="tel" inputmode="tel" maxlength="50" autocomplete="tel" pattern="[0-9+()\-\s]{7,30}" placeholder="e.g. +44 7700 900123" title="Use 7-30 characters: digits, spaces, and + ( ) - only." value="<?= e($form['phone']) ?>" required></div>
                <div class="form-group"><label for="company_name"><?= e(tr('Company Name *')) ?></label><input id="company_name" name="company_name" maxlength="150" autocomplete="organization" value="<?= e($form['company_name']) ?>" required></div>
                <div class="form-group"><label for="country"><?= e(tr('Country *')) ?></label><input id="country" name="country" maxlength="100" autocomplete="country-name" value="<?= e($form['country']) ?>" required></div>
                <div class="form-group"><label for="solution_interest"><?= e(tr('Solution Interest *')) ?></label><select id="solution_interest" name="solution_interest" required><option value=""><?= e(tr('Select interest')) ?></option><?php foreach ($solution_interests as $interest): ?><option value="<?= e($interest) ?>" <?= selected($form['solution_interest'], $interest) ?>><?= e(tr($interest)) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label for="job_title"><?= e(tr('Job Title *')) ?></label><input id="job_title" name="job_title" maxlength="150" autocomplete="organization-title" value="<?= e($form['job_title']) ?>" required></div>
                <div class="form-group full"><label for="job_details"><?= e(tr('Job Details / Message *')) ?></label><textarea id="job_details" name="job_details" maxlength="3000" required><?= e($form['job_details']) ?></textarea></div>
                <div class="form-group"><label for="captcha_answer"><?= e(tr('Security Check:')) ?> <?= e($captcha_question) ?> *</label><input id="captcha_answer" name="captcha_answer" inputmode="numeric" maxlength="2" required></div>
                <div class="form-group full"><label class="checkbox-label"><input type="checkbox" name="privacy_consent" value="1" <?= !empty($_POST['privacy_consent']) ? 'checked' : '' ?> required> <?= e(tr('I agree that AI-Solutions may store and use my details to respond to this inquiry.')) ?></label></div>
            </div>
            <div class="button-row"><button class="btn btn-primary" type="submit"><?= e(tr('Submit Inquiry')) ?></button><button class="btn btn-secondary" type="reset"><?= e(tr('Clear')) ?></button></div>
        </form>
    </div>
</section>

<section class="section contact-feedback-section" id="visitor-feedback">
    <div class="feedback-form-panel form-shell">
        <span class="section-kicker"><?= e(tr('Rate your visit')) ?></span>
        <h2><?= e(tr('Tell us how the experience felt.')) ?></h2>
        <p><?= e(tr('Your feedback goes to the AI-Solutions team for review and improvement.')) ?></p>
        <div data-feedback-alert>
            <?php if ($feedback_submitted): ?><div class="alert alert-success"><?= e(tr('Thank you. Your rating has been received and will help us improve the visitor experience.')) ?></div><?php endif; ?>
            <?php if ($feedback_errors): ?><div class="alert alert-error"><?= e(implode(' ', array_map('tr', $feedback_errors))) ?></div><?php endif; ?>
        </div>
        <form method="post" action="feedback-submit.php" data-feedback-form data-live-validate novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="form_type" value="visitor_feedback">
            <input type="hidden" name="feedback_redirect" value="contact.php">
            <div class="field-trap" aria-hidden="true">
                <label><?= e(tr('Website')) ?></label>
                <input name="feedback_website" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-grid">
                <div class="form-group"><label for="feedback_name"><?= e(tr('Your Name *')) ?></label><input id="feedback_name" name="feedback_name" maxlength="150" autocomplete="name" value="<?= e($feedback_form['feedback_name']) ?>" required></div>
                <div class="form-group"><label for="feedback_email"><?= e(tr('Email Address *')) ?></label><input id="feedback_email" name="feedback_email" type="email" maxlength="150" autocomplete="email" value="<?= e($feedback_form['feedback_email']) ?>" required></div>
                <div class="form-group"><label for="feedback_company"><?= e(tr('Organization')) ?></label><input id="feedback_company" name="feedback_company" maxlength="150" autocomplete="organization" value="<?= e($feedback_form['feedback_company']) ?>"></div>
                <div class="form-group"><label for="feedback_role"><?= e(tr('Role / Title')) ?></label><input id="feedback_role" name="feedback_role" maxlength="150" autocomplete="organization-title" value="<?= e($feedback_form['feedback_role']) ?>"></div>
                <fieldset class="form-group rating-field full">
                    <legend><?= e(tr('Overall Rating *')) ?></legend>
                    <div class="rating-picker">
                        <?php
                        foreach ($rating_labels as $rating_value => $rating_label):
                            $rating_id = 'feedback_rating_' . $rating_value;
                        ?>
                            <input id="<?= e($rating_id) ?>" type="radio" name="feedback_rating" value="<?= e($rating_value) ?>" <?= $feedback_form['feedback_rating'] === (string) $rating_value ? 'checked' : '' ?> required>
                            <label for="<?= e($rating_id) ?>"><strong><?= e($rating_value) ?></strong><span><?= e(tr($rating_label)) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <div class="form-group full"><label for="feedback_message"><?= e(tr('What should we keep improving? *')) ?></label><textarea id="feedback_message" name="feedback_message" maxlength="1200" required><?= e($feedback_form['feedback_message']) ?></textarea></div>
            </div>
            <div class="button-row feedback-submit"><button class="btn btn-primary" type="submit"><?= e(tr('Submit Rating')) ?></button><button class="btn btn-secondary" type="reset"><?= e(tr('Clear')) ?></button></div>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
