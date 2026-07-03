<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

function feedback_form_defaults() {
    return [
        'feedback_name' => '',
        'feedback_email' => '',
        'feedback_company' => '',
        'feedback_role' => '',
        'feedback_rating' => '',
        'feedback_message' => '',
    ];
}

function feedback_rating_labels() {
    return [
        5 => 'Excellent',
        4 => 'Great',
        3 => 'Good',
        2 => 'Fair',
        1 => 'Needs work',
    ];
}

function feedback_reviewer_avatar_urls() {
    return [
        'assets/images/review-card-avatar.jpg',
        'assets/images/review-card-avatar-2.jpg',
        'assets/images/review-card-avatar-3.jpg',
        'assets/images/review-card-avatar-4.jpg',
    ];
}

function feedback_reviewer_avatar_url($seed = 0) {
    $avatars = feedback_reviewer_avatar_urls();
    $index = is_numeric($seed) ? (int) $seed : (int) sprintf('%u', crc32((string) $seed));
    return $avatars[abs($index) % count($avatars)];
}

function feedback_safe_db() {
    try {
        return new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        error_log('Feedback database connection failed: ' . $e->getMessage());
        return null;
    }
}

function ensure_visitor_feedback_table(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS visitor_feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            visitor_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            company_name VARCHAR(150) NULL,
            role_title VARCHAR(150) NULL,
            rating TINYINT UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'New',
            admin_note TEXT NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");

    $columns = [
        'admin_note' => 'ALTER TABLE visitor_feedback ADD COLUMN admin_note TEXT NULL AFTER status',
        'is_featured' => 'ALTER TABLE visitor_feedback ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER admin_note',
        'updated_at' => 'ALTER TABLE visitor_feedback ADD COLUMN updated_at DATETIME NULL',
    ];

    foreach ($columns as $column => $alter_sql) {
        $stmt = $pdo->query('SHOW COLUMNS FROM visitor_feedback LIKE ' . $pdo->quote($column));
        if (!$stmt->fetch()) {
            $pdo->exec($alter_sql);
        }
    }
}

function validate_feedback_submission() {
    $form = feedback_form_defaults();
    foreach (array_keys($form) as $field) {
        $form[$field] = post_value($field, $field === 'feedback_message' ? 1200 : 150);
    }

    $rating = (int) ($_POST['feedback_rating'] ?? 0);
    $errors = array_merge(
        required_fields([
            'feedback_name' => 'Your name',
            'feedback_email' => 'Email address',
            'feedback_message' => 'Feedback message',
        ], $_POST),
        max_length_errors([
            'feedback_name' => ['Your name', 150],
            'feedback_email' => ['Email address', 150],
            'feedback_company' => ['Organization', 150],
            'feedback_role' => ['Role', 150],
            'feedback_message' => ['Feedback message', 1200],
        ], $_POST)
    );

    if (!honeypot_clear('feedback_website')) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if ($form['feedback_email'] !== '' && !valid_email($form['feedback_email'])) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Please choose a rating from 1 to 5.';
    } else {
        $form['feedback_rating'] = (string) $rating;
    }

    return [$form, $rating, $errors];
}

function save_visitor_feedback(PDO $pdo, array $form, int $rating) {
    ensure_visitor_feedback_table($pdo);
    $stmt = $pdo->prepare('INSERT INTO visitor_feedback(visitor_name,email,company_name,role_title,rating,message,status,created_at) VALUES(?,?,?,?,?,?,"New",NOW())');
    $stmt->execute([
        $form['feedback_name'],
        $form['feedback_email'],
        $form['feedback_company'] ?: null,
        $form['feedback_role'] ?: null,
        $rating,
        $form['feedback_message'],
    ]);

    $id = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT * FROM visitor_feedback WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function feedback_default_cards() {
    return [
        [
            'id' => 0,
            'rating' => 5,
            'message' => 'The AI-Solutions homepage makes the offer feel clear, modern, and easy for our team to understand.',
            'display_name' => 'Sunderland Tech Ltd',
            'role_title' => 'Operations Manager',
            'tag' => 'Prototype review',
            'avatar_url' => feedback_reviewer_avatar_url(0),
            'created_at' => null,
            'is_live' => false,
        ],
        [
            'id' => 0,
            'rating' => 5,
            'message' => 'The assistant, demo request, and contact path feel simple enough for customers to act without confusion.',
            'display_name' => 'NorthCloud Services',
            'role_title' => 'IT Support Lead',
            'tag' => 'Assistant flow',
            'avatar_url' => feedback_reviewer_avatar_url(1),
            'created_at' => null,
            'is_live' => false,
        ],
        [
            'id' => 0,
            'rating' => 5,
            'message' => 'The service sections explain business value clearly and give visitors a professional reason to start a project.',
            'display_name' => 'BridgeWorks Group',
            'role_title' => 'Business Analyst',
            'tag' => 'Service clarity',
            'avatar_url' => feedback_reviewer_avatar_url(2),
            'created_at' => null,
            'is_live' => false,
        ],
        [
            'id' => 0,
            'rating' => 5,
            'message' => 'The contact form, demo path, and assistant make it easy to see how AI-Solutions can support real business needs.',
            'display_name' => 'MetroWorks Digital',
            'role_title' => 'Project Lead',
            'tag' => 'Contact journey',
            'avatar_url' => feedback_reviewer_avatar_url(3),
            'created_at' => null,
            'is_live' => false,
        ],
    ];
}

function feedback_card_from_row(array $row, bool $is_live = true, $avatar_seed = null) {
    $labels = feedback_rating_labels();
    $name = trim((string) ($row['visitor_name'] ?? 'Visitor'));
    $company = trim((string) ($row['company_name'] ?? ''));
    $role = trim((string) ($row['role_title'] ?? ''));
    $rating = max(1, min(5, (int) ($row['rating'] ?? 5)));

    return [
        'id' => (int) ($row['id'] ?? 0),
        'rating' => $rating,
        'message' => trim((string) ($row['message'] ?? '')),
        'display_name' => $company !== '' ? $company : $name,
        'role_title' => $role !== '' ? $role : $name,
        'tag' => $is_live ? $labels[$rating] . ' rating' : 'Visitor review',
        'avatar_url' => feedback_reviewer_avatar_url($avatar_seed ?? ($row['id'] ?? $name)),
        'created_at' => $row['created_at'] ?? null,
        'is_live' => $is_live,
    ];
}

function feedback_total_count(?PDO $pdo) {
    if (!$pdo) {
        return count(feedback_default_cards());
    }

    try {
        ensure_visitor_feedback_table($pdo);
        $total = (int) $pdo->query('SELECT COUNT(*) FROM visitor_feedback WHERE status <> "Archived"')->fetchColumn();
        return $total > 0 ? $total : count(feedback_default_cards());
    } catch (PDOException $e) {
        error_log('Feedback count failed: ' . $e->getMessage());
        return count(feedback_default_cards());
    }
}

function feedback_fetch_cards(?PDO $pdo, int $limit = 3, int $offset = 0) {
    $limit = max(1, min(6, $limit));
    $offset = max(0, $offset);

    if (!$pdo) {
        return array_slice(feedback_default_cards(), 0, $limit);
    }

    try {
        ensure_visitor_feedback_table($pdo);
        $stmt = $pdo->query('SELECT * FROM visitor_feedback WHERE status <> "Archived" ORDER BY created_at DESC, id DESC LIMIT ' . $limit . ' OFFSET ' . $offset);
        $rows = $stmt->fetchAll();
        if (!$rows) {
            return $offset === 0 ? array_slice(feedback_default_cards(), 0, $limit) : [];
        }
        $cards = [];
        foreach ($rows as $position => $row) {
            $cards[] = feedback_card_from_row($row, true, $offset + $position);
        }
        if ($offset === 0) {
            foreach (feedback_default_cards() as $default_card) {
                if (count($cards) >= $limit) {
                    break;
                }
                $default_card['avatar_url'] = feedback_reviewer_avatar_url(count($cards));
                $cards[] = $default_card;
            }
        }
        return $cards;
    } catch (PDOException $e) {
        error_log('Feedback card fetch failed: ' . $e->getMessage());
        return array_slice(feedback_default_cards(), 0, $limit);
    }
}

function feedback_stats(?PDO $pdo) {
    if (!$pdo) {
        return [
            'average' => '4.9',
            'total' => 3,
            'summary' => 'Based on early visitor and client reviews',
        ];
    }

    try {
        ensure_visitor_feedback_table($pdo);
        $row = $pdo->query('SELECT COUNT(*) total, AVG(rating) average_rating FROM visitor_feedback WHERE status <> "Archived"')->fetch();
        $total = (int) ($row['total'] ?? 0);
        if ($total === 0) {
            return [
                'average' => '4.9',
                'total' => 3,
                'summary' => 'Based on early visitor and client reviews',
            ];
        }

        $average = number_format((float) $row['average_rating'], 1);
        return [
            'average' => $average,
            'total' => $total,
            'summary' => 'Based on ' . $total . ' visitor ' . ($total === 1 ? 'rating' : 'ratings'),
        ];
    } catch (PDOException $e) {
        error_log('Feedback stats fetch failed: ' . $e->getMessage());
        return [
            'average' => '4.9',
            'total' => 3,
            'summary' => 'Based on early visitor and client reviews',
        ];
    }
}

function feedback_star_entities(int $rating) {
    $rating = max(1, min(5, $rating));
    return trim(str_repeat('&#9733; ', $rating) . str_repeat('&#9734; ', 5 - $rating));
}

function feedback_card_payload(array $card) {
    return [
        'id' => (int) ($card['id'] ?? 0),
        'rating' => (int) ($card['rating'] ?? 5),
        'message' => (string) ($card['message'] ?? ''),
        'display_name' => (string) ($card['display_name'] ?? 'Visitor'),
        'role_title' => (string) ($card['role_title'] ?? 'Visitor'),
        'tag' => (string) ($card['tag'] ?? 'Visitor review'),
        'avatar_url' => (string) ($card['avatar_url'] ?? feedback_reviewer_avatar_url()),
        'created_at' => $card['created_at'] ?? null,
    ];
}
