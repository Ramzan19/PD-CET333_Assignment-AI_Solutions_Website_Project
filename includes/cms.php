<?php
// ---------------------------------------------------------------------------
// Lightweight content management layer.
//
// All managed website content (articles, events, gallery, solutions, case
// studies) lives in a single `cms_content` table. Each row stores the item's
// full public shape as JSON in `data` (the source of truth for rendering),
// plus a few mirror columns (title, summary, event_date, sort_order,
// is_published) used for admin listing and ordering.
//
// Public catalog functions read DB-first and fall back to the original
// hardcoded defaults if the database is unavailable or a type has no rows,
// so the public site keeps working no matter what. On first use each type is
// seeded from those defaults so nothing is lost.
// ---------------------------------------------------------------------------

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Returns a usable PDO connection. Prefers the global $pdo from db.php, but
// lazily opens its own connection when called before db.php has loaded (e.g.
// catalog functions run high up on public pages, before the header include).
// Returns null if the database is genuinely unavailable, so callers fall back
// to the hardcoded defaults instead of crashing.
function cms_pdo() {
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    static $own = null;
    if ($own instanceof PDO) {
        return $own;
    }
    if (!defined('DB_HOST')) {
        return null;
    }
    try {
        $own = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $own;
    } catch (Throwable $e) {
        error_log('CMS could not open a database connection: ' . $e->getMessage());
        return null;
    }
}

function cms_ensure_schema(PDO $pdo) {
    static $done = false;
    if ($done) {
        return;
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cms_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(30) NOT NULL,
            title VARCHAR(255) NOT NULL DEFAULT '',
            summary TEXT NULL,
            image_path VARCHAR(255) NULL,
            event_date DATE NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            data LONGTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            INDEX idx_cms_type (type),
            INDEX idx_cms_sort (type, sort_order)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cms_site_text (
            text_key VARCHAR(80) PRIMARY KEY,
            text_value TEXT NOT NULL,
            updated_at DATETIME NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    $done = true;
}

// ---------------------------------------------------------------------------
// Type registry: labels + the editable field schema that drives the admin
// create/edit form. Each field: [key, label, kind, required].
// kinds: text, textarea, date, body (article markup), list (one item per line).
// ---------------------------------------------------------------------------
function cms_type_defs() {
    return [
        'article' => [
            'label' => 'Article', 'plural' => 'Articles', 'public' => 'articles.php',
            'fields' => [
                ['title', 'Title', 'text', true],
                ['slug', 'Slug (URL id)', 'text', true],
                ['category', 'Category', 'text', true],
                ['date', 'Date', 'date', true],
                ['summary', 'Summary', 'textarea', true],
                ['keywords', 'SEO Keywords', 'text', false],
                ['body', 'Body', 'body', true],
            ],
        ],
        'event' => [
            'label' => 'Event', 'plural' => 'Events', 'public' => 'events.php',
            'fields' => [
                ['name', 'Event Name', 'text', true],
                ['date', 'Date', 'date', true],
                ['time', 'Time', 'text', true],
                ['summary', 'Summary', 'textarea', true],
                ['interest', 'Interest Area', 'text', true],
                ['action', 'Button Label', 'text', false],
            ],
        ],
        'gallery' => [
            'label' => 'Gallery Photo', 'plural' => 'Gallery', 'public' => 'events.php#event-gallery',
            'fields' => [
                ['badge', 'Badge Label', 'text', true],
                ['title', 'Title', 'text', true],
                ['caption', 'Caption', 'textarea', true],
                ['image', 'Image Path', 'text', true],
                ['alt', 'Image Alt Text', 'text', false],
            ],
        ],
        'solution' => [
            'label' => 'Solution', 'plural' => 'Solutions', 'public' => 'services.php',
            'fields' => [
                ['title', 'Title', 'text', true],
                ['key', 'Key (id)', 'text', true],
                ['industry', 'Industry', 'text', true],
                ['category', 'Category', 'text', true],
                ['summary', 'Summary', 'textarea', true],
                ['features', 'Feature Tags (one per line)', 'list', true],
                ['action', 'Button Label', 'text', false],
                ['href', 'Button Link', 'text', false],
            ],
        ],
        'case_study' => [
            'label' => 'Case Study', 'plural' => 'Case Studies', 'public' => 'services.php',
            'fields' => [
                ['title', 'Title', 'text', true],
                ['industry', 'Industry', 'text', true],
                ['objective', 'Objective', 'textarea', true],
                ['challenge', 'Challenge', 'textarea', true],
                ['solution', 'Solution', 'textarea', true],
                ['result', 'Measurable Result', 'textarea', true],
            ],
        ],
    ];
}

function cms_type_exists($type) {
    return array_key_exists($type, cms_type_defs());
}

// ---------------------------------------------------------------------------
// Article body: convert between the structured block array used for rendering
// and a plain-text markup that is easy to edit in a textarea.
//   "## Heading"  -> ['h' => 'Heading']
//   "- item"      -> grouped into ['list' => [...]]
//   blank line    -> paragraph separator
// ---------------------------------------------------------------------------
function cms_body_to_text(array $blocks) {
    $out = [];
    foreach ($blocks as $block) {
        if (is_array($block) && isset($block['h'])) {
            $out[] = '## ' . $block['h'];
        } elseif (is_array($block) && isset($block['list'])) {
            foreach ($block['list'] as $item) {
                $out[] = '- ' . $item;
            }
        } else {
            $out[] = (string) $block;
        }
    }
    return implode("\n\n", $out);
}

function cms_text_to_body($text) {
    $text = str_replace(["\r\n", "\r"], "\n", (string) $text);
    $lines = explode("\n", $text);
    $blocks = [];
    $para = [];
    $list = [];
    $flush_para = function () use (&$para, &$blocks) {
        if ($para) {
            $blocks[] = trim(implode(' ', $para));
            $para = [];
        }
    };
    $flush_list = function () use (&$list, &$blocks) {
        if ($list) {
            $blocks[] = ['list' => $list];
            $list = [];
        }
    };
    foreach ($lines as $line) {
        $t = trim($line);
        if ($t === '') {
            $flush_list();
            $flush_para();
        } elseif (strncmp($t, '## ', 3) === 0) {
            $flush_list();
            $flush_para();
            $blocks[] = ['h' => trim(substr($t, 3))];
        } elseif (strncmp($t, '- ', 2) === 0) {
            $flush_para();
            $list[] = trim(substr($t, 2));
        } else {
            $flush_list();
            $para[] = $t;
        }
    }
    $flush_list();
    $flush_para();
    return $blocks;
}

// One line per item <-> array, used for the "list" field kind (solution tags).
function cms_lines_to_array($text) {
    $text = str_replace(["\r\n", "\r"], "\n", (string) $text);
    return array_values(array_filter(array_map('trim', explode("\n", $text)), fn($v) => $v !== ''));
}

// ---------------------------------------------------------------------------
// Map an editable item (public shape) to mirror columns for the table row.
// ---------------------------------------------------------------------------
function cms_mirror_columns($type, array $item) {
    $title = $item['title'] ?? ($item['name'] ?? '');
    $summary = $item['summary'] ?? ($item['caption'] ?? ($item['objective'] ?? ''));
    $date = $item['date'] ?? null;
    if ($date !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
        $date = null;
    }
    return [
        'title' => (string) $title,
        'summary' => (string) $summary,
        'image_path' => $item['image'] ?? ($item['image_path'] ?? null),
        'event_date' => $date,
    ];
}

// Fetch managed items of a type as their public-shape arrays.
function cms_items(PDO $pdo, $type, $published_only = true) {
    cms_ensure_schema($pdo);
    $sql = 'SELECT * FROM cms_content WHERE type = ?' . ($published_only ? ' AND is_published = 1' : '')
        . ' ORDER BY sort_order ASC, id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$type]);
    $rows = $stmt->fetchAll();
    $items = [];
    foreach ($rows as $row) {
        $item = json_decode((string) $row['data'], true);
        if (!is_array($item)) {
            continue;
        }
        $item['id'] = (int) $row['id'];
        $item['is_published'] = (int) $row['is_published'];
        $items[] = $item;
    }
    return $items;
}

function cms_find(PDO $pdo, $id) {
    cms_ensure_schema($pdo);
    $stmt = $pdo->prepare('SELECT * FROM cms_content WHERE id = ?');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $item = json_decode((string) $row['data'], true);
    $item = is_array($item) ? $item : [];
    $item['id'] = (int) $row['id'];
    $item['type'] = $row['type'];
    $item['is_published'] = (int) $row['is_published'];
    $item['sort_order'] = (int) $row['sort_order'];
    return $item;
}

function cms_insert(PDO $pdo, $type, array $item, $sort_order = 0, $is_published = 1) {
    cms_ensure_schema($pdo);
    $m = cms_mirror_columns($type, $item);
    $stmt = $pdo->prepare('INSERT INTO cms_content (type, title, summary, image_path, event_date, sort_order, is_published, data) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $type, $m['title'], $m['summary'], $m['image_path'], $m['event_date'],
        (int) $sort_order, $is_published ? 1 : 0,
        json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);
    return (int) $pdo->lastInsertId();
}

function cms_update(PDO $pdo, $id, $type, array $item, $sort_order, $is_published) {
    cms_ensure_schema($pdo);
    $m = cms_mirror_columns($type, $item);
    $stmt = $pdo->prepare('UPDATE cms_content SET title=?, summary=?, image_path=?, event_date=?, sort_order=?, is_published=?, data=?, updated_at=NOW() WHERE id=?');
    $stmt->execute([
        $m['title'], $m['summary'], $m['image_path'], $m['event_date'],
        (int) $sort_order, $is_published ? 1 : 0,
        json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        (int) $id,
    ]);
}

function cms_delete(PDO $pdo, $id) {
    cms_ensure_schema($pdo);
    $stmt = $pdo->prepare('DELETE FROM cms_content WHERE id = ?');
    $stmt->execute([(int) $id]);
}

function cms_count(PDO $pdo, $type) {
    cms_ensure_schema($pdo);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM cms_content WHERE type = ?');
    $stmt->execute([$type]);
    return (int) $stmt->fetchColumn();
}

// Seed a type from its hardcoded defaults the first time (idempotent).
function cms_seed_if_empty(PDO $pdo, $type, array $defaults) {
    if (cms_count($pdo, $type) > 0) {
        return;
    }
    $order = 0;
    foreach ($defaults as $item) {
        cms_insert($pdo, $type, $item, $order, 1);
        $order += 10;
    }
}

// DB-first catalog: seed from defaults, then return managed items. Falls back
// to the raw defaults if the database is unavailable for any reason.
function cms_catalog($type, callable $defaults_fn) {
    $pdo = cms_pdo();
    if (!$pdo) {
        return $defaults_fn();
    }
    try {
        cms_ensure_schema($pdo);
        cms_seed_if_empty($pdo, $type, $defaults_fn());
        $items = cms_items($pdo, $type, true);
        return $items ?: $defaults_fn();
    } catch (Throwable $e) {
        error_log('CMS catalog (' . $type . ') failed: ' . $e->getMessage());
        return $defaults_fn();
    }
}

// ---------------------------------------------------------------------------
// Editable site text (hero/section copy). Keyed strings with defaults.
// ---------------------------------------------------------------------------
function cms_site_text_defaults() {
    return [
        'home_hero_eyebrow' => 'Secure AI delivery for growing teams',
        'home_hero_lede' => 'AI-Solutions designs assistants, automation systems, and analytics dashboards that help teams respond faster, reduce manual work, and make sharper decisions.',
        'services_hero_title' => 'Industry-specific AI solutions for practical teams.',
        'articles_hero_title' => 'Company updates and AI implementation insights.',
        'events_hero_title' => 'Join practical sessions that show AI working inside real operations.',
        'contact_hero_title' => 'Tell us what you want AI to improve.',
    ];
}

function cms_all_site_text(PDO $pdo) {
    cms_ensure_schema($pdo);
    $saved = [];
    foreach ($pdo->query('SELECT text_key, text_value FROM cms_site_text')->fetchAll() as $row) {
        $saved[$row['text_key']] = $row['text_value'];
    }
    return array_merge(cms_site_text_defaults(), $saved);
}

function cms_text($key, $default = '') {
    $pdo = cms_pdo();
    $defaults = cms_site_text_defaults();
    $fallback = $default !== '' ? $default : ($defaults[$key] ?? '');
    if (!$pdo) {
        return $fallback;
    }
    try {
        cms_ensure_schema($pdo);
        $stmt = $pdo->prepare('SELECT text_value FROM cms_site_text WHERE text_key = ?');
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false && $val !== null) ? $val : $fallback;
    } catch (Throwable $e) {
        return $fallback;
    }
}

function cms_save_site_text(PDO $pdo, $key, $value) {
    cms_ensure_schema($pdo);
    $stmt = $pdo->prepare('INSERT INTO cms_site_text (text_key, text_value, updated_at) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE text_value = VALUES(text_value), updated_at = NOW()');
    $stmt->execute([$key, (string) $value]);
}
