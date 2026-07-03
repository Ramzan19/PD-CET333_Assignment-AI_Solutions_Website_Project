<?php
require_once 'includes/feedback.php';
require_once 'includes/content.php';

$current_page = 'home';
$page_title = 'AI Strategy, Automation and Assistants';
$feedback_pdo = feedback_safe_db();
$feedback_page_size = 4;
$feedback_total = feedback_total_count($feedback_pdo);
$feedback_total_pages = max(1, (int) ceil($feedback_total / $feedback_page_size));
$feedback_page = max(1, min($feedback_total_pages, (int) ($_GET['ratings_page'] ?? 1)));
$feedback_cards = feedback_fetch_cards($feedback_pdo, $feedback_page_size, ($feedback_page - 1) * $feedback_page_size);
$feedback_stats = feedback_stats($feedback_pdo);
$home_articles = ai_solutions_featured_articles(3);

include 'includes/header.php';
?>
<section class="hero-pro" aria-label="AI-Solutions introduction">
    <div class="hp-bg" aria-hidden="true">
        <span class="hp-glow hp-glow-cyan"></span>
        <span class="hp-glow hp-glow-coral"></span>
        <span class="hp-grid"></span>
    </div>
    <div class="hp-inner">
        <div class="hp-copy">
            <span class="hp-eyebrow"><?= e(tr(cms_text('home_hero_eyebrow', 'Secure AI delivery for growing teams'))) ?></span>
            <h1 class="hp-title"><?= e(tr('AI that earns its place in your')) ?> <span class="hp-accent"><?= e(tr('workflow')) ?></span>.</h1>
            <p class="hp-lede"><?= e(tr(cms_text('home_hero_lede', 'AI-Solutions designs assistants, automation systems, and analytics dashboards that help teams respond faster, reduce manual work, and make sharper decisions.'))) ?></p>
            <div class="hp-actions">
                <a class="btn btn-primary" href="schedule-demo.php"><?= e(tr('Schedule Demo')) ?></a>
                <a class="btn btn-secondary" href="services.php"><?= e(tr('Explore Services')) ?></a>
            </div>
            <dl class="hp-proof">
                <div><dt>24/7</dt><dd><?= e(tr('assistant coverage')) ?></dd></div>
                <div><dt>3&times;</dt><dd><?= e(tr('faster lead routing')) ?></dd></div>
                <div><dt>100%</dt><dd><?= e(tr('team controlled')) ?></dd></div>
            </dl>
        </div>
        <div class="hp-visual" aria-hidden="true">
            <div class="hp-card hp-dashboard">
                <div class="hp-card-head">
                    <span class="hp-dots"><i></i><i></i><i></i></span>
                    <span class="hp-card-title"><?= e(tr('Operations Overview')) ?></span>
                    <span class="hp-live"><i></i>Live</span>
                </div>
                <div class="hp-kpis">
                    <div class="hp-kpi"><strong>1,284</strong><span><?= e(tr('Inquiries')) ?></span></div>
                    <div class="hp-kpi"><strong>96%</strong><span><?= e(tr('Handled')) ?></span></div>
                    <div class="hp-kpi"><strong>1.8s</strong><span><?= e(tr('Avg reply')) ?></span></div>
                </div>
                <div class="hp-chart">
                    <div class="hp-bars">
                        <span style="--h:44%;--d:.20s"></span>
                        <span style="--h:68%;--d:.30s"></span>
                        <span style="--h:54%;--d:.40s"></span>
                        <span style="--h:82%;--d:.50s"></span>
                        <span style="--h:63%;--d:.60s"></span>
                        <span style="--h:92%;--d:.70s"></span>
                        <span style="--h:74%;--d:.80s"></span>
                    </div>
                    <svg class="hp-spark" viewBox="0 0 120 36" preserveAspectRatio="none" focusable="false"><path d="M0 28 L20 22 L40 26 L60 14 L80 18 L100 7 L120 11"/></svg>
                </div>
            </div>
            <div class="hp-float hp-float-chat">
                <span class="hp-bot"></span>
                <div><strong><?= e(tr('Assistant')) ?></strong><span>Routed to sales &middot; 2s</span></div>
            </div>
            <div class="hp-float hp-float-stat">
                <strong>+38%</strong><span><?= e(tr('faster response')) ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section intro-band feature-band">
    <div class="section-header align-left">
        <span class="section-kicker"><?= e(tr('What we build')) ?></span>
        <h2><?= e(tr('AI systems that feel useful from the first interaction.')) ?></h2>
        <p><?= e(tr('Every solution is designed around a business workflow: capture the request, automate the repeatable work, surface the right data, and hand complex cases to people with context intact.')) ?></p>
    </div>
    <div class="grid-4 feature-grid">
        <article class="card feature-card">
            <span class="card-number">01</span>
            <h3><?= e(tr('Customer AI Assistants')) ?></h3>
            <p><?= e(tr('Guided support, qualification, demo routing, and handover flows that keep customer conversations moving.')) ?></p>
        </article>
        <article class="card feature-card">
            <span class="card-number">02</span>
            <h3><?= e(tr('Workflow Automation')) ?></h3>
            <p><?= e(tr('Streamlined intake, approvals, notifications, and operations support built to reduce manual follow-up.')) ?></p>
        </article>
        <article class="card feature-card">
            <span class="card-number">03</span>
            <h3><?= e(tr('Analytics Dashboards')) ?></h3>
            <p><?= e(tr('Business-ready views that turn inquiries, demos, leads, and service data into practical decisions.')) ?></p>
        </article>
        <article class="card feature-card">
            <span class="card-number">04</span>
            <h3><?= e(tr('AI Product Prototypes')) ?></h3>
            <p><?= e(tr('Focused proof-of-concepts that help teams test ideas, validate workflows, and plan the next build.')) ?></p>
        </article>
    </div>
</section>

<section class="section split-section">
    <div>
        <span class="section-kicker"><?= e(tr('Delivery approach')) ?></span>
        <h2><?= e(tr('Professional, secure, and built around your real operations.')) ?></h2>
        <p><?= e(tr('AI should make the business clearer, not more chaotic. We start with your user journey, design the right automation layer, and give your team a controlled follow-up workspace.')) ?></p>
        <div class="button-row">
            <a class="btn btn-primary" href="contact.php"><?= e(tr('Start a Project')) ?></a>
            <a class="btn btn-secondary" href="chatbot.php"><?= e(tr('Try Assistant')) ?></a>
        </div>
    </div>
    <div class="process-list">
        <div><span><?= e(tr('Discover')) ?></span><p><?= e(tr('Map goals, risks, users, and operational bottlenecks.')) ?></p></div>
        <div><span><?= e(tr('Prototype')) ?></span><p><?= e(tr('Launch focused AI flows and dashboards quickly.')) ?></p></div>
        <div><span><?= e(tr('Harden')) ?></span><p><?= e(tr('Improve security, validation, team workflows, and data quality.')) ?></p></div>
        <div><span><?= e(tr('Scale')) ?></span><p><?= e(tr('Extend automation into more teams and customer journeys.')) ?></p></div>
    </div>
</section>

<section class="section news-section">
    <div class="section-header align-left">
        <span class="section-kicker"><?= e(tr('News and insights')) ?></span>
        <h2><?= e(tr('Recent thinking from the AI-Solutions team.')) ?></h2>
        <p><?= e(tr('Short, practical notes for teams planning AI assistants, workflow automation, analytics, and prototypes.')) ?></p>
    </div>
    <div class="grid-3 article-grid">
        <?php foreach ($home_articles as $article): ?>
            <article class="card article-card">
                <span class="card-number"><?= e(tr($article['category'])) ?></span>
                <h3><?= e(tr($article['title'])) ?></h3>
                <p><?= e(tr($article['summary'])) ?></p>
                <small><?= e(date('M j, Y', strtotime($article['date']))) ?></small>
            </article>
        <?php endforeach; ?>
    </div>
    <div class="button-row">
        <a class="btn btn-secondary" href="articles.php"><?= e(tr('View Articles')) ?></a>
    </div>
</section>

<section class="section feedback-section feedback-section-ratings" id="visitor-feedback">
    <div class="feedback-topline">
        <div class="section-header align-left compact">
            <span class="section-kicker"><?= e(tr('Visitor feedback')) ?></span>
            <h2><?= e(tr('Customer ratings')) ?></h2>
            <p><?= e(tr('Browse recent visitor feedback and add your own rating from the Contact Us page.')) ?></p>
            <div class="button-row">
                <a class="btn btn-primary" href="contact.php#visitor-feedback"><?= e(tr('Rate Your Visit')) ?></a>
            </div>
        </div>
        <div class="feedback-score" aria-label="Average visitor rating">
            <span><?= e(tr('Average confidence')) ?></span>
            <strong data-feedback-average><?= e($feedback_stats['average']) ?></strong>
            <div class="star-row" data-feedback-stars aria-label="<?= e($feedback_stats['average']) ?> out of 5 stars">&#9733; &#9733; &#9733; &#9733; &#9733;</div>
            <small data-feedback-total><?= e($feedback_stats['summary']) ?></small>
        </div>
    </div>

    <div class="feedback-wall" data-feedback-wall aria-live="polite" aria-label="Featured customer feedback">
        <?php foreach ($feedback_cards as $index => $card): ?>
            <article class="feedback-card <?= !empty($card['is_live']) ? 'live-feedback' : '' ?>" data-feedback-card>
                <div class="feedback-card-top">
                    <span class="star-row" aria-label="<?= e($card['rating']) ?> out of 5 stars"><?= feedback_star_entities((int) $card['rating']) ?></span>
                    <span class="feedback-tag"><?= e($card['tag']) ?></span>
                </div>
                <p>&ldquo;<?= e($card['message']) ?>&rdquo;</p>
                <div class="feedback-author">
                    <img class="feedback-avatar" src="<?= e($card['avatar_url']) ?>" alt="<?= e($card['display_name']) ?> reviewer photo" loading="lazy" width="58" height="58">
                    <div class="feedback-author-text">
                        <strong><?= e($card['display_name']) ?></strong>
                        <span><?= e($card['role_title']) ?></span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($feedback_total_pages > 1): ?>
        <nav class="feedback-pagination" aria-label="Customer rating pages">
            <?php
            $visible_pages = min(10, $feedback_total_pages);
            $start_page = max(1, min($feedback_page - 4, $feedback_total_pages - $visible_pages + 1));
            $end_page = min($feedback_total_pages, $start_page + $visible_pages - 1);
            for ($page_number = $start_page; $page_number <= $end_page; $page_number++):
                $page_url = 'index.php?ratings_page=' . $page_number . '#visitor-feedback';
            ?>
                <a class="<?= $page_number === $feedback_page ? 'active' : '' ?>" href="<?= e($page_url) ?>" <?= $page_number === $feedback_page ? 'aria-current="page"' : '' ?>><?= e($page_number) ?></a>
            <?php endfor; ?>
            <?php if ($feedback_page < $feedback_total_pages): ?>
                <a class="feedback-pagination-next" href="<?= e('index.php?ratings_page=' . ($feedback_page + 1) . '#visitor-feedback') ?>"><?= e(tr('Next')) ?></a>
            <?php else: ?>
                <span class="feedback-pagination-next disabled" aria-disabled="true"><?= e(tr('Next')) ?></span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>

<section class="section cta-band">
    <span class="section-kicker"><?= e(tr('Ready when you are')) ?></span>
    <h2><?= e(tr('Bring us the workflow that costs your team time.')) ?></h2>
    <p><?= e(tr('We will help turn it into a cleaner AI-assisted system with measurable business value.')) ?></p>
    <div class="hero-actions centered">
        <a class="btn btn-primary" href="schedule-demo.php"><?= e(tr('Book a Demo')) ?></a>
        <a class="btn btn-secondary" href="contact.php"><?= e(tr('Contact Us')) ?></a>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
