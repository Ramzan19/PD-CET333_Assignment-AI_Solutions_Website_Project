<?php
require_once 'includes/config.php';
require_once 'includes/content.php';
require_once 'includes/functions.php';

$slug = (string) ($_GET['slug'] ?? '');
$article = ai_solutions_find_article($slug);

if (!$article) {
    http_response_code(404);
    $current_page = 'articles';
    $page_title = 'Article not found';
    $meta_description = 'The requested article could not be found.';
    include 'includes/header.php';
    echo '<section class="page-hero"><span class="eyebrow">' . e(tr('Articles')) . '</span><h1>' . e(tr('Article not found')) . '</h1>'
        . '<p>' . e(tr('That article may have moved.')) . ' <a href="articles.php">' . e(tr('Browse all articles')) . '</a>.</p></section>';
    include 'includes/footer.php';
    exit;
}

$current_page = 'articles';
$page_title = $article['title'];
// Per-article SEO metadata.
$meta_description = $article['summary'];
$meta_keywords = $article['keywords'] ?? '';
$structured_data = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $article['title'],
    'description' => $article['summary'],
    'datePublished' => $article['date'],
    'articleSection' => $article['category'],
    'keywords' => $article['keywords'] ?? '',
    'author' => ['@type' => 'Organization', 'name' => SITE_NAME],
    'publisher' => ['@type' => 'Organization', 'name' => SITE_NAME],
];
include 'includes/header.php';
?>
<section class="page-hero">
    <span class="eyebrow"><?= e(tr($article['category'])) ?></span>
    <h1><?= e(tr($article['title'])) ?></h1>
    <p><small><?= e(date('F j, Y', strtotime($article['date']))) ?></small></p>
</section>

<section class="section article-detail">
    <div class="article-body">
        <p class="article-lede"><?= e(tr($article['summary'])) ?></p>
        <?php foreach (($article['body'] ?? []) as $block): ?>
            <?php if (is_array($block) && isset($block['h'])): ?>
                <h2 class="article-subhead"><?= e(tr($block['h'])) ?></h2>
            <?php elseif (is_array($block) && isset($block['list'])): ?>
                <ul class="article-list">
                    <?php foreach ($block['list'] as $item): ?>
                        <li><?= e(tr($item)) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><?= e(tr((string) $block)) ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="button-row">
        <a class="btn btn-secondary" href="articles.php"><?= e(tr('Back to Articles')) ?></a>
        <a class="btn btn-primary" href="contact.php"><?= e(tr('Talk to AI-Solutions')) ?></a>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
