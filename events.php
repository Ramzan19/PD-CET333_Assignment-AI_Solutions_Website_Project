<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/schema.php';
require_once 'includes/event-data.php';
require_once 'includes/mailer.php';

$current_page = 'events';
$page_title = 'Events and Insights';
$errors = [];
$events = ai_solutions_event_catalog();
$today = date('Y-m-d');
$all_event_names = ai_solutions_event_names();
$registration_events = array_values(array_filter($events, fn($event) => !event_has_finished($event, $today)));
$event_names = array_map(fn($event) => $event['name'], $registration_events);
$interest_options = ai_solutions_interest_options();
$event_filter_options = array_values(array_unique(array_map(fn($event) => $event['interest'], $events)));
ensure_event_registrations_table($pdo);

$fields = ['full_name', 'email', 'phone', 'company_name', 'country', 'event_name', 'interest_area', 'notes'];
$form = array_fill_keys($fields, '');
$event_notice = '';
$requested_event = trim((string) ($_GET['event'] ?? ''));
if ($requested_event !== '' && in_array($requested_event, $event_names, true)) {
    $form['event_name'] = $requested_event;
    foreach ($registration_events as $event) {
        if ($event['name'] === $requested_event) {
            $form['interest_area'] = $event['interest'];
            break;
        }
    }
} elseif ($requested_event !== '' && in_array($requested_event, $all_event_names, true)) {
    $event_notice = 'That session has already happened. Choose one of the upcoming events below.';
}

if (is_post()) {
    csrf_or_fail();

    foreach ($fields as $field) {
        $form[$field] = post_value($field, $field === 'notes' ? 2000 : 200);
    }

    $errors = array_merge(
        required_fields([
            'full_name' => 'Full name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company_name' => 'Company',
            'country' => 'Country',
            'event_name' => 'Event',
            'interest_area' => 'Interest area',
        ], $_POST),
        max_length_errors([
            'full_name' => ['Full name', 150],
            'email' => ['Email', 150],
            'phone' => ['Phone', 50],
            'company_name' => ['Company', 150],
            'country' => ['Country', 100],
            'event_name' => ['Event', 200],
            'interest_area' => ['Interest area', 150],
            'notes' => ['Notes', 2000],
        ], $_POST)
    );

    if (!honeypot_clear()) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if ($form['email'] !== '' && !valid_email($form['email'])) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($form['event_name'] !== '' && !in_array($form['event_name'], $event_names, true)) {
        $errors[] = 'Please select an upcoming event.';
    }

    if ($form['interest_area'] !== '' && !in_array($form['interest_area'], $interest_options, true)) {
        $errors[] = 'Please select a valid interest area.';
    }

    if (empty($_POST['consent'])) {
        $errors[] = 'Please confirm consent to be contacted about the event.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO event_registrations(full_name,email,phone,company_name,country,event_name,interest_area,notes,status,created_at) VALUES(?,?,?,?,?,?,?,?, "Registered", NOW())');
        $stmt->execute([
            $form['full_name'],
            $form['email'],
            $form['phone'],
            $form['company_name'],
            $form['country'],
            $form['event_name'],
            $form['interest_area'],
            $form['notes'],
        ]);
        $body = "Hello " . $form['full_name'] . ",\n\n"
            . "Thank you for registering for " . $form['event_name'] . ".\n\n"
            . "We will send joining details before the event. You can also download the calendar file from the Events page.\n\n"
            . "AI-Solutions";
        mail_send_or_queue($pdo, $form['email'], 'We received your AI-Solutions event RSVP', $body);
        redirect('success.php?type=event');
    }
}

$body_class = 'events-page';
include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow"><?= e(tr('Events and insights')) ?></span>
    <h1><?= e(tr(cms_text('events_hero_title', 'Join practical sessions that show AI working inside real operations.'))) ?></h1>
    <p><?= e(tr('Customers can view upcoming technical demonstrations, register interest, and help AI-Solutions understand demand for assistants, automation, dashboards, and prototypes.')) ?></p>
</section>

<section class="section events-layout">
    <div class="events-column events-live-column">
        <div class="section-header align-left compact">
            <span class="section-kicker"><?= e(tr('Upcoming events')) ?></span>
            <h2><?= e(tr('Live sessions')) ?></h2>
            <p><?= e(tr('Filter by solution area, reserve a seat, or add a session to your calendar.')) ?></p>
        </div>
        <div class="event-filter-bar" data-event-filters aria-label="Filter live sessions">
            <button class="active" type="button" data-event-filter="all" aria-pressed="true"><?= e(tr('All')) ?></button>
            <?php foreach ($event_filter_options as $filter): ?>
                <button type="button" data-event-filter="<?= e($filter) ?>" aria-pressed="false"><?= e(tr($filter)) ?></button>
            <?php endforeach; ?>
        </div>
        <?php foreach ($events as $event): ?>
            <?php $date_parts = event_date_parts($event['date']); ?>
            <?php $finished = event_has_finished($event, $today); ?>
            <article class="card event-card <?= $finished ? 'is-past' : 'is-open' ?>" data-event-card data-event-interest="<?= e($event['interest']) ?>" data-event-status="<?= $finished ? 'finished' : 'open' ?>">
                <div class="event-date"><span><?= e($date_parts['month']) ?></span><strong><?= e($date_parts['day']) ?></strong><small><?= e($date_parts['year']) ?></small></div>
                <div class="event-card-copy">
                    <div class="event-meta">
                        <span class="event-pill <?= $finished ? 'is-finished' : 'is-open' ?>"><?= $finished ? e(tr('Completed')) : e(tr('Open')) ?></span>
                        <span><?= e(event_display_date($event['date'])) ?></span>
                        <span><?= e($event['time']) ?></span>
                    </div>
                    <h3><?= e(tr($event['name'])) ?></h3>
                    <p><?= e(tr($event['summary'])) ?></p>
                </div>
                <div class="event-card-actions">
                    <?php if ($finished): ?>
                        <span class="btn btn-secondary is-disabled" aria-disabled="true"><?= e(tr('Closed')) ?></span>
                    <?php else: ?>
                        <a class="btn btn-secondary" href="<?= e('events.php?event=' . urlencode($event['name']) . '#join-events') ?>"><?= e(tr($event['action'])) ?></a>
                        <a class="action-link" href="<?= e('calendar.php?event=' . urlencode($event['name'])) ?>"><?= e(tr('Add calendar')) ?></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
        <p class="event-empty" data-event-empty hidden><?= e(tr('No sessions match this filter.')) ?></p>
    </div>

    <aside class="insight-panel event-spotlight-panel">
        <span class="section-kicker"><?= e(tr('Event snapshot')) ?></span>
        <h2><?= e(tr('Built for practical learning')) ?></h2>
        <div class="event-stat-row" aria-label="Event summary">
            <div><strong><?= count($registration_events) ?></strong><span><?= e(tr('Upcoming')) ?></span></div>
            <div><strong><?= count($event_filter_options) ?></strong><span><?= e(tr('Tracks')) ?></span></div>
            <div><strong><?= e(tr('Online')) ?></strong><span><?= e(tr('Delivery')) ?></span></div>
        </div>
        <div class="event-snapshot">
            <div><span class="snapshot-index">01</span><strong><?= e(tr('Live demos')) ?></strong><span><?= e(tr('Assistants, dashboards, and automation walkthroughs.')) ?></span></div>
            <div><span class="snapshot-index">02</span><strong><?= e(tr('Team-ready')) ?></strong><span><?= e(tr('Sessions designed for managers, analysts, and operators.')) ?></span></div>
            <div><span class="snapshot-index">03</span><strong><?= e(tr('Actionable')) ?></strong><span><?= e(tr('Clear next steps after every promotional event.')) ?></span></div>
        </div>
        <a class="btn btn-primary full-width" href="#join-events"><?= e(tr('Reserve a seat')) ?></a>
        <article class="event-readout">
            <span class="card-number"><?= e(tr('Read')) ?></span>
            <h3><?= e(tr('5 ways AI transforms customer support')) ?></h3>
            <p><?= e(tr('How smart intake, routing, and context-aware handover improve customer experience without overwhelming staff.')) ?></p>
            <a class="text-link" href="articles.php"><?= e(tr('Read articles')) ?></a>
        </article>
    </aside>
</section>

<section class="section event-gallery-section" id="event-gallery">
    <div class="section-header align-left gallery-header">
        <span class="section-kicker"><?= e(tr('Gallery')) ?></span>
        <h2><?= e(tr('Moments from AI-Solutions events and delivery sessions.')) ?></h2>
        <p><?= e(tr('Explore professional workshop, dashboard, assistant, automation, and prototype scenes that show how technical solution events translate into real operational outcomes.')) ?></p>
    </div>
    <div class="event-gallery-grid" aria-label="AI-Solutions event gallery">
        <?php foreach (ai_solutions_gallery_catalog() as $g_index => $photo): ?>
            <article class="gallery-card<?= $g_index === 0 ? ' gallery-card-featured' : '' ?>">
                <img src="<?= e($photo['image'] ?? '') ?>" alt="<?= e($photo['alt'] ?? '') ?>" loading="lazy" width="1280" height="820">
                <div class="gallery-card-content">
                    <span><?= e(tr($photo['badge'] ?? '')) ?></span>
                    <h3><?= e(tr($photo['title'] ?? '')) ?></h3>
                    <p><?= e(tr($photo['caption'] ?? '')) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section form-layout event-registration-section" id="join-events">
    <div class="form-intro">
        <span class="section-kicker"><?= e(tr('Join our events')) ?></span>
        <h2><?= e(tr('Register for a promotional event.')) ?></h2>
        <p><?= e(tr('Your details help the admin team measure event demand by country, company, and solution interest.')) ?></p>
    </div>
    <div class="form-shell">
        <?php if ($event_notice): ?><div class="alert alert-error"><?= e(tr($event_notice)) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', array_map('tr', $errors))) ?></div><?php endif; ?>
        <form method="post" data-live-validate data-event-interest-sync novalidate>
            <?= csrf_field() ?>
            <div class="field-trap" aria-hidden="true">
                <label><?= e(tr('Website')) ?></label>
                <input name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-grid">
                <div class="form-group"><label for="full_name"><?= e(tr('Full Name *')) ?></label><input id="full_name" name="full_name" maxlength="150" autocomplete="name" value="<?= e($form['full_name']) ?>" required></div>
                <div class="form-group"><label for="email"><?= e(tr('Email Address *')) ?></label><input id="email" type="email" name="email" maxlength="150" autocomplete="email" value="<?= e($form['email']) ?>" required></div>
                <div class="form-group"><label for="phone"><?= e(tr('Phone Number *')) ?></label><input id="phone" name="phone" type="tel" inputmode="tel" maxlength="50" autocomplete="tel" pattern="[0-9+()\-\s]{7,30}" placeholder="e.g. +44 7700 900123" title="Use 7-30 characters: digits, spaces, and + ( ) - only." value="<?= e($form['phone']) ?>" required></div>
                <div class="form-group"><label for="company_name"><?= e(tr('Company Name *')) ?></label><input id="company_name" name="company_name" maxlength="150" autocomplete="organization" value="<?= e($form['company_name']) ?>" required></div>
                <div class="form-group"><label for="country"><?= e(tr('Country *')) ?></label><input id="country" name="country" maxlength="100" autocomplete="country-name" value="<?= e($form['country']) ?>" required></div>
                <div class="form-group"><label for="event_name"><?= e(tr('Event *')) ?></label><select id="event_name" name="event_name" data-event-select required><option value=""><?= e(tr('Select event')) ?></option><?php foreach ($registration_events as $event_option): ?><option value="<?= e($event_option['name']) ?>" data-interest="<?= e($event_option['interest']) ?>" data-date="<?= e(event_display_date($event_option['date'])) ?>" data-time="<?= e($event_option['time']) ?>" data-summary="<?= e(tr($event_option['summary'])) ?>" <?= selected($form['event_name'], $event_option['name']) ?>><?= e(tr($event_option['name'])) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label for="interest_area"><?= e(tr('Interest Area *')) ?></label><select id="interest_area" name="interest_area" data-interest-select required><option value=""><?= e(tr('Select interest')) ?></option><?php foreach ($interest_options as $interest): ?><option value="<?= e($interest) ?>" <?= selected($form['interest_area'], $interest) ?>><?= e(tr($interest)) ?></option><?php endforeach; ?></select></div>
                <div class="event-picked full" data-event-summary aria-live="polite" hidden></div>
                <div class="form-group full"><label for="notes"><?= e(tr('Questions or Notes')) ?></label><textarea id="notes" name="notes" maxlength="2000"><?= e($form['notes']) ?></textarea></div>
                <div class="form-group full"><label class="checkbox-label"><input type="checkbox" name="consent" value="1" <?= !empty($_POST['consent']) ? 'checked' : '' ?>> <?= e(tr('I agree to be contacted about this AI-Solutions event.')) ?></label></div>
            </div>
            <div class="button-row"><button class="btn btn-primary" type="submit"><?= e(tr('Join Event')) ?></button><a class="btn btn-secondary" href="events.php"><?= e(tr('Clear')) ?></a></div>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
