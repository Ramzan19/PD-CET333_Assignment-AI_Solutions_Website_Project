<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/cms.php';

// Shared catalog so the public Solutions page and the admin moderation
// screen agree on the canonical solution key + title for each solution.
// DB-first via the CMS, with the hardcoded defaults below as fallback + seed.
function ai_solutions_catalog() {
    return cms_catalog('solution', 'ai_solutions_catalog_defaults');
}

function ai_solutions_catalog_defaults() {
    return [
        [
            'key' => 'retail-support-assistant',
            'number' => '01',
            'title' => 'Retail support assistant',
            'industry' => 'retail',
            'category' => 'assistant',
            'summary' => 'Guide shoppers, answer common service questions, qualify leads, and hand complex requests to staff.',
            'features' => ['Product guidance', 'Lead capture', 'Human handover'],
            'href' => 'chatbot.php',
            'action' => 'Try assistant',
        ],
        [
            'key' => 'healthcare-intake-automation',
            'number' => '02',
            'title' => 'Healthcare intake automation',
            'industry' => 'healthcare',
            'category' => 'automation',
            'summary' => 'Organise non-clinical requests, route follow-ups, and reduce repeated manual admin steps.',
            'features' => ['Secure intake', 'Team routing', 'Status tracking'],
            'href' => 'contact.php',
            'action' => 'Request info',
        ],
        [
            'key' => 'professional-services-dashboard',
            'number' => '03',
            'title' => 'Professional services dashboard',
            'industry' => 'services',
            'category' => 'analytics',
            'summary' => 'Track inquiries, demo demand, conversion quality, and delivery signals in one admin view.',
            'features' => ['Inquiry KPIs', 'Demand trends', 'Exportable reports'],
            'href' => 'schedule-demo.php',
            'action' => 'Book demo',
        ],
        [
            'key' => 'education-event-assistant',
            'number' => '04',
            'title' => 'Education event assistant',
            'industry' => 'education',
            'category' => 'assistant',
            'summary' => 'Promote sessions, answer registration questions, and capture RSVP interest for events.',
            'features' => ['Event guidance', 'RSVP capture', 'Calendar links'],
            'href' => 'events.php',
            'action' => 'View events',
        ],
        [
            'key' => 'operations-workflow-automation',
            'number' => '05',
            'title' => 'Operations workflow automation',
            'industry' => 'services',
            'category' => 'automation',
            'summary' => 'Move repeated approvals, notifications, and task-routing steps out of spreadsheets and inboxes.',
            'features' => ['Process mapping', 'Notifications', 'Audit trail'],
            'href' => 'contact.php',
            'action' => 'Start project',
        ],
        [
            'key' => 'ai-prototype-sprint',
            'number' => '06',
            'title' => 'AI prototype sprint',
            'industry' => 'retail',
            'category' => 'prototype',
            'summary' => 'Build a focused proof-of-concept to validate an AI workflow before a larger rollout.',
            'features' => ['MVP scope', 'Clickable flows', 'Iteration plan'],
            'href' => 'schedule-demo.php',
            'action' => 'Plan sprint',
        ],
    ];
}

function ai_solutions_catalog_map() {
    $map = [];
    foreach (ai_solutions_catalog() as $solution) {
        $map[$solution['key']] = $solution['title'];
    }
    return $map;
}

// Structured case studies (objectives / challenges / solution / measurable result).
// DB-first via the CMS, with the hardcoded defaults below as fallback + seed.
function ai_solutions_case_studies() {
    return cms_catalog('case_study', 'ai_solutions_case_studies_defaults');
}

function ai_solutions_case_studies_defaults() {
    return [
        [
            'title' => 'Cutting retail support backlog with an AI assistant',
            'industry' => 'Retail',
            'objective' => 'Reduce repetitive "where is my order" and product questions overwhelming a small support team.',
            'challenge' => 'Two agents handled 400+ weekly chats, so complex cases waited hours behind routine questions.',
            'solution' => 'Deployed a retail support assistant that answered common questions, qualified leads, and handed only complex cases to staff with full context.',
            'result' => '62% of chats resolved without an agent; average first response down from 4 hours to under 2 minutes.',
        ],
        [
            'title' => 'Streamlining healthcare intake admin',
            'industry' => 'Healthcare',
            'objective' => 'Remove manual data re-entry from a non-clinical patient intake and referral workflow.',
            'challenge' => 'Staff re-keyed the same request details across three systems, causing delays and errors.',
            'solution' => 'Automated intake captured structured requests once and routed each follow-up to the right team with status tracking.',
            'result' => 'Manual admin time reduced by ~11 hours per week and intake errors fell by 40%.',
        ],
        [
            'title' => 'A single analytics view for a services firm',
            'industry' => 'Professional services',
            'objective' => 'Give leadership one place to see inquiry demand, demo interest, and conversion quality.',
            'challenge' => 'Data lived in spreadsheets and inboxes, so monthly reporting took a full day to assemble.',
            'solution' => 'A professional services dashboard consolidated inquiries, demos, and visitor analytics with exportable reports.',
            'result' => 'Monthly reporting time cut from ~8 hours to 20 minutes, with weekly demand trends now visible at a glance.',
        ],
    ];
}

function ensure_solution_reviews_table(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS solution_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            solution_key VARCHAR(80) NOT NULL,
            solution_title VARCHAR(150) NOT NULL,
            reviewer_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            company_name VARCHAR(150) NULL,
            rating TINYINT UNSIGNED NOT NULL,
            comment TEXT NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'New',
            admin_note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            INDEX idx_solution_key (solution_key),
            INDEX idx_solution_status (status)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
}

// Approved-review averages keyed by solution_key.
function solution_review_averages(PDO $pdo) {
    ensure_solution_reviews_table($pdo);
    $rows = $pdo->query("
        SELECT solution_key, COUNT(*) total, AVG(rating) avg_rating
        FROM solution_reviews
        WHERE status = 'Approved'
        GROUP BY solution_key
    ")->fetchAll();

    $map = [];
    foreach ($rows as $row) {
        $map[$row['solution_key']] = [
            'count' => (int) $row['total'],
            'average' => round((float) $row['avg_rating'], 1),
        ];
    }
    return $map;
}

function solution_approved_reviews(PDO $pdo, $solution_key, $limit = 5) {
    ensure_solution_reviews_table($pdo);
    $limit = max(1, min(20, (int) $limit));
    $stmt = $pdo->prepare("SELECT * FROM solution_reviews WHERE status = 'Approved' AND solution_key = ? ORDER BY created_at DESC LIMIT " . $limit);
    $stmt->execute([$solution_key]);
    return $stmt->fetchAll();
}

function solution_review_stars($rating) {
    $rating = max(0, min(5, (int) round($rating)));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
