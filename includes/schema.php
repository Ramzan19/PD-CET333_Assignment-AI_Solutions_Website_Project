<?php
function db_identifier($identifier) {
    if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $identifier)) {
        throw new InvalidArgumentException('Invalid database identifier.');
    }
    return '`' . $identifier . '`';
}

function db_column_exists(PDO $pdo, $table, $column) {
    $stmt = $pdo->query('SHOW COLUMNS FROM ' . db_identifier($table) . ' LIKE ' . $pdo->quote($column));
    return (bool) $stmt->fetch();
}

function db_add_column_if_missing(PDO $pdo, $table, $column, $definition, $after = '') {
    if (db_column_exists($pdo, $table, $column)) {
        return;
    }

    $after_sql = $after !== '' && db_column_exists($pdo, $table, $after)
        ? ' AFTER ' . db_identifier($after)
        : '';
    $pdo->exec('ALTER TABLE ' . db_identifier($table) . ' ADD COLUMN ' . db_identifier($column) . ' ' . $definition . $after_sql);
}

function ensure_customer_inquiry_schema(PDO $pdo) {
    db_add_column_if_missing($pdo, 'customer_inquiries', 'solution_interest', "VARCHAR(150) NOT NULL DEFAULT 'General AI Solution'", 'country');
    // Priority lets the admin team triage inquiries by urgency (wish-list: inquiry
    // tagging and prioritisation), with High/Normal/Low filterable and sortable.
    db_add_column_if_missing($pdo, 'customer_inquiries', 'priority', "VARCHAR(20) NOT NULL DEFAULT 'Normal'", 'status');
}

function ensure_chatbot_schema(PDO $pdo) {
    db_add_column_if_missing($pdo, 'chatbot_conversations', 'phone', "VARCHAR(50) NOT NULL DEFAULT ''", 'email');
    db_add_column_if_missing($pdo, 'chatbot_conversations', 'country', "VARCHAR(100) NOT NULL DEFAULT ''", 'company_name');
}

function ensure_event_registrations_table(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS event_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            company_name VARCHAR(150) NOT NULL,
            country VARCHAR(100) NOT NULL,
            event_name VARCHAR(200) NOT NULL,
            interest_area VARCHAR(150) NOT NULL,
            notes TEXT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'Registered',
            admin_note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");

    db_add_column_if_missing($pdo, 'event_registrations', 'admin_note', 'TEXT NULL', 'status');
    db_add_column_if_missing($pdo, 'event_registrations', 'updated_at', 'DATETIME NULL', 'created_at');
}

function ensure_smtp_settings_table(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS smtp_settings (
            id TINYINT UNSIGNED PRIMARY KEY,
            enabled TINYINT(1) NOT NULL DEFAULT 0,
            host VARCHAR(180) NOT NULL DEFAULT '',
            port INT UNSIGNED NOT NULL DEFAULT 587,
            encryption VARCHAR(20) NOT NULL DEFAULT 'tls',
            username VARCHAR(180) NOT NULL DEFAULT '',
            password_value TEXT NULL,
            from_email VARCHAR(180) NOT NULL DEFAULT '',
            from_name VARCHAR(180) NOT NULL DEFAULT 'AI-Solutions Admin',
            updated_at DATETIME NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");

    $exists = (int) $pdo->query('SELECT COUNT(*) FROM smtp_settings WHERE id = 1')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare('INSERT INTO smtp_settings(id, enabled, host, port, encryption, username, password_value, from_email, from_name, updated_at) VALUES(1,0,"",587,"tls","",NULL,"","AI-Solutions Admin",NOW())');
        $stmt->execute();
    }
}

function ensure_assignment_schema(PDO $pdo) {
    ensure_customer_inquiry_schema($pdo);
    ensure_chatbot_schema($pdo);
    ensure_event_registrations_table($pdo);
    ensure_smtp_settings_table($pdo);
}

function assignment_country_counts(PDO $pdo, $limit = 5) {
    ensure_assignment_schema($pdo);
    $limit = max(1, min(10, (int) $limit));
    $stmt = $pdo->query("
        SELECT demand_country label, COUNT(*) total FROM (
            SELECT country demand_country FROM customer_inquiries
            UNION ALL SELECT country FROM demo_bookings
            UNION ALL SELECT country FROM event_registrations
            UNION ALL SELECT country FROM chatbot_conversations WHERE country <> ''
        ) demand
        WHERE demand_country <> ''
        GROUP BY demand_country
        ORDER BY total DESC, demand_country ASC
        LIMIT " . $limit);
    return $stmt->fetchAll();
}

function assignment_interest_counts(PDO $pdo, $limit = 6) {
    ensure_assignment_schema($pdo);
    $limit = max(1, min(10, (int) $limit));
    $stmt = $pdo->query("
        SELECT interest_label label, COUNT(*) total FROM (
            SELECT COALESCE(NULLIF(solution_interest, ''), 'General Inquiry') interest_label FROM customer_inquiries
            UNION ALL SELECT COALESCE(NULLIF(demo_type, ''), 'Scheduled Demo') FROM demo_bookings
            UNION ALL SELECT COALESCE(NULLIF(interest_area, ''), 'Promotional Event') FROM event_registrations
            UNION ALL SELECT COALESCE(NULLIF(topic, ''), 'Chatbot Handover') FROM chatbot_conversations
        ) demand
        GROUP BY interest_label
        ORDER BY total DESC, interest_label ASC
        LIMIT " . $limit);
    return $stmt->fetchAll();
}

function assignment_keyword_count(PDO $pdo, array $keywords) {
    ensure_assignment_schema($pdo);
    $conditions = [];
    $params = [];
    foreach ($keywords as $keyword) {
        $conditions[] = 'LOWER(interest_label) LIKE ?';
        $params[] = '%' . strtolower($keyword) . '%';
    }
    if (!$conditions) {
        return 0;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM (
            SELECT COALESCE(NULLIF(solution_interest, ''), job_title, 'General Inquiry') interest_label FROM customer_inquiries
            UNION ALL SELECT COALESCE(NULLIF(demo_type, ''), 'Scheduled Demo') FROM demo_bookings
            UNION ALL SELECT COALESCE(NULLIF(interest_area, ''), 'Promotional Event') FROM event_registrations
            UNION ALL SELECT COALESCE(NULLIF(topic, ''), 'Chatbot Handover') FROM chatbot_conversations
        ) demand
        WHERE " . implode(' OR ', $conditions));
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}
