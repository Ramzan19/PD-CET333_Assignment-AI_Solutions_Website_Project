<?php
require_once 'includes/content.php';
require_once 'includes/functions.php';

$current_page = 'articles';
$page_title = 'Articles and News';
$meta_description = 'AI implementation insights from AI-Solutions: support routing, workflow automation readiness, analytics dashboards, and prototype planning.';
$meta_keywords = 'AI articles, AI blog, workflow automation, AI analytics, AI prototypes, digital employee experience';
$query = trim((string) ($_GET['q'] ?? ''));
$articles = ai_solutions_search_articles($query);
include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow"><?= e(tr('Articles and news')) ?></span>
    <h1><?= e(tr(cms_text('articles_hero_title', 'Company updates and AI implementation insights.'))) ?></h1>
    <p><?= e(tr('Read practical notes from AI-Solutions on assistants, workflow automation, analytics dashboards, and prototype planning.')) ?></p>
</section>

<section class="section">
    <form class="article-search" method="get" role="search" aria-label="Search articles">
        <input type="search" name="q" value="<?= e($query) ?>" placeholder="<?= e(tr('Search articles by topic or keyword')) ?>" aria-label="Search articles">
        <button class="btn btn-primary" type="submit"><?= e(tr('Search')) ?></button>
        <?php if ($query !== ''): ?><a class="btn btn-secondary" href="articles.php"><?= e(tr('Clear')) ?></a><?php endif; ?>
    </form>

    <?php if ($query !== ''): ?>
        <p class="search-summary"><?= count($articles) ?> <?= e(count($articles) === 1 ? tr('result for') : tr('results for')) ?> &ldquo;<?= e($query) ?>&rdquo;.</p>
    <?php endif; ?>

    <?php if ($articles): ?>
        <div class="grid-3 article-grid">
            <?php foreach ($articles as $article): ?>
                <article class="card article-card" id="<?= e($article['slug']) ?>">
                    <span class="card-number"><?= e(tr($article['category'])) ?></span>
                    <h3><a class="text-link" href="article.php?slug=<?= e($article['slug']) ?>"><?= e(tr($article['title'])) ?></a></h3>
                    <p><?= e(tr($article['summary'])) ?></p>
                    <small><?= e(date('M j, Y', strtotime($article['date']))) ?></small>
                    <div class="button-row"><a class="text-link" href="article.php?slug=<?= e($article['slug']) ?>"><?= e(tr('Read article')) ?></a></div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="empty-note"><?= e(tr('No articles match that search. Try a different keyword.')) ?></p>
    <?php endif; ?>
</section>

<section class="section cta-band">
    <span class="section-kicker"><?= e(tr('Stay connected')) ?></span>
    <h2><?= e(tr('Have a workflow question for the next article?')) ?></h2>
    <div class="hero-actions centered">
        <a class="btn btn-primary" href="contact.php"><?= e(tr('Send a Question')) ?></a>
        <a class="btn btn-secondary" href="events.php"><?= e(tr('View Events')) ?></a>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
