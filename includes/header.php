<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/analytics.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/cms.php';

security_headers();
start_secure_session();
ai_lang_set();
$active_lang = ai_lang_current();

$current_page = $current_page ?? '';
$page_title = $page_title ?? SITE_NAME;
$body_class = trim((string) ($body_class ?? ''));
// Per-page SEO: a page can set $meta_description / $meta_keywords / $structured_data
// before including this header. Sensible site-wide defaults are used otherwise.
$meta_description = $meta_description ?? 'AI-Solutions builds secure AI assistants, workflow automation, analytics dashboards, and product prototypes for modern teams.';
$meta_keywords = $meta_keywords ?? 'AI solutions, AI assistant, workflow automation, analytics dashboard, AI prototype, digital employee experience';
track_public_visit($page_title, $current_page);
$structured_data = $structured_data ?? [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => SITE_NAME,
    'url' => 'http://localhost/AI_Solutions_Website_Project/',
    'description' => 'Secure AI assistants, workflow automation, analytics dashboards, and product prototypes for modern teams.',
];
?>
<!DOCTYPE html>
<html lang="<?= e($active_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($meta_description) ?>">
    <meta name="keywords" content="<?= e($meta_keywords) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
    <meta property="og:title" content="<?= e($page_title) ?> | <?= e(SITE_NAME) ?>">
    <meta property="og:description" content="<?= e($meta_description) ?>">
    <meta name="twitter:card" content="summary">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="assistant-endpoint" content="chatbot-api.php">
    <title><?= e($page_title) ?> | <?= e(SITE_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <script type="application/ld+json"><?= json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php if (defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID !== ''): ?>
    <!-- Google Analytics 4 (loads only when GA_MEASUREMENT_ID is configured) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(GA_MEASUREMENT_ID) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= e(GA_MEASUREMENT_ID) ?>');
    </script>
<?php endif; ?>
</head>
<body<?= $body_class !== '' ? ' class="' . e($body_class) . '"' : '' ?>>
<div class="site-neural-bg" aria-hidden="true">
    <svg class="site-neural-map" viewBox="0 0 1440 900" preserveAspectRatio="none">
        <defs>
            <linearGradient id="neuralCyan" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#1ab8c7" stop-opacity="0.08" />
                <stop offset="42%" stop-color="#7df8ff" stop-opacity="0.72" />
                <stop offset="100%" stop-color="#1ab8c7" stop-opacity="0.08" />
            </linearGradient>
            <linearGradient id="neuralOrange" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#f26a4f" stop-opacity="0.08" />
                <stop offset="48%" stop-color="#ffb07a" stop-opacity="0.66" />
                <stop offset="100%" stop-color="#f26a4f" stop-opacity="0.08" />
            </linearGradient>
            <filter id="neuralGlow" x="-20%" y="-80%" width="140%" height="260%">
                <feGaussianBlur stdDeviation="4" result="blur" />
                <feMerge>
                    <feMergeNode in="blur" />
                    <feMergeNode in="SourceGraphic" />
                </feMerge>
            </filter>
        </defs>
        <path id="neuralPathCyanA" class="neural-route neural-route-cyan" d="M-40 238 C160 240 240 218 390 246 S690 350 940 318 S1210 282 1485 300" />
        <path id="neuralPathOrangeA" class="neural-route neural-route-orange" d="M40 586 C260 594 390 604 532 554 S754 502 960 508 S1190 536 1480 508" />
        <path id="neuralPathCyanB" class="neural-route neural-route-cyan neural-route-soft" d="M96 826 C330 742 592 722 772 760 S1108 894 1508 778" />
        <path id="neuralPathOrangeB" class="neural-route neural-route-orange neural-route-soft" d="M-65 350 C160 350 282 392 454 372 S760 258 960 336 S1184 432 1510 398" />
        <path class="neural-route-glow neural-glow-cyan" d="M-90 238 C160 240 240 218 390 246 S690 350 940 318 S1210 282 1510 300" />
        <path class="neural-route-glow neural-glow-orange" d="M0 586 C260 594 390 604 532 554 S754 502 960 508 S1190 536 1510 508" />
        <circle class="neural-orb neural-orb-cyan" r="7">
            <animateMotion dur="8.5s" repeatCount="indefinite" rotate="auto">
                <mpath href="#neuralPathCyanA" />
            </animateMotion>
        </circle>
        <circle class="neural-orb neural-orb-orange" r="7">
            <animateMotion dur="10s" begin="-3s" repeatCount="indefinite" rotate="auto">
                <mpath href="#neuralPathOrangeA" />
            </animateMotion>
        </circle>
        <circle class="neural-orb neural-orb-cyan neural-orb-small" r="5">
            <animateMotion dur="11.5s" begin="-5s" repeatCount="indefinite" rotate="auto">
                <mpath href="#neuralPathCyanB" />
            </animateMotion>
        </circle>
    </svg>
    <span class="neural-node neural-node-one"></span>
    <span class="neural-node neural-node-two"></span>
    <span class="neural-node neural-node-three"></span>
    <span class="neural-node neural-node-four"></span>
    <div class="neural-mini-box neural-mini-box-one">
        <span></span><span></span><span></span>
    </div>
    <div class="neural-mini-box neural-mini-box-two">
        <span></span><span></span><span></span>
    </div>
    <div class="neural-mini-box neural-mini-box-three">
        <span></span><span></span><span></span>
    </div>
</div>
<a class="skip-link" href="#mainContent"><?= e(t('skip_to_content')) ?></a>
<header class="site-header">
    <a class="brand" href="index.php" aria-label="AI-Solutions home">
        <span class="brand-logo ai-brand-mark" aria-hidden="true">
            <span class="mark-core">AI</span>
            <span class="mark-node node-a"></span>
            <span class="mark-node node-b"></span>
            <span class="mark-node node-c"></span>
        </span>
        <span class="brand-text">AI-Solutions</span>
    </a>
    <button class="mobile-menu-btn" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="mainNav">
        <span></span><span></span><span></span>
    </button>
    <nav class="main-nav" id="mainNav" aria-label="Primary navigation">
        <a class="<?= $current_page === 'home' ? 'active' : '' ?>" href="index.php"><?= e(t('nav_home')) ?></a>
        <a class="<?= $current_page === 'services' ? 'active' : '' ?>" href="services.php"><?= e(t('nav_solutions')) ?></a>
        <a class="<?= $current_page === 'events' ? 'active' : '' ?>" href="events.php"><?= e(t('nav_events')) ?></a>
        <a class="<?= $current_page === 'articles' ? 'active' : '' ?>" href="articles.php"><?= e(t('nav_articles')) ?></a>
        <a class="<?= $current_page === 'contact' ? 'active' : '' ?>" href="contact.php"><?= e(t('nav_contact')) ?></a>
        <a class="nav-demo <?= $current_page === 'demo' ? 'active' : '' ?>" href="schedule-demo.php"><?= e(t('nav_demo')) ?></a>
        <span class="lang-switcher" aria-label="Language">
            <?php foreach (ai_lang_labels() as $lang_code => $lang_label): ?>
                <a class="<?= $active_lang === $lang_code ? 'active' : '' ?>" href="<?= e(ai_lang_switch_url($lang_code)) ?>" hreflang="<?= e($lang_code) ?>"><?= e($lang_label) ?></a>
            <?php endforeach; ?>
        </span>
    </nav>
</header>
<main id="mainContent">
