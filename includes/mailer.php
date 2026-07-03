<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/schema.php';

function smtp_default_settings() {
    return [
        'enabled' => 0,
        'host' => '',
        'port' => 587,
        'encryption' => 'tls',
        'username' => '',
        'password_value' => '',
        'from_email' => '',
        'from_name' => 'AI-Solutions Admin',
    ];
}

function smtp_settings(PDO $pdo) {
    ensure_smtp_settings_table($pdo);
    $row = $pdo->query('SELECT * FROM smtp_settings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
    return array_merge(smtp_default_settings(), $row ?: []);
}

function smtp_save_settings(PDO $pdo, array $data, $password) {
    ensure_smtp_settings_table($pdo);
    $current = smtp_settings($pdo);
    $password_value = $password !== '' ? $password : ($current['password_value'] ?? '');

    $stmt = $pdo->prepare('UPDATE smtp_settings SET enabled = ?, host = ?, port = ?, encryption = ?, username = ?, password_value = ?, from_email = ?, from_name = ?, updated_at = NOW() WHERE id = 1');
    $stmt->execute([
        !empty($data['enabled']) ? 1 : 0,
        trim((string) ($data['host'] ?? '')),
        max(1, min(65535, (int) ($data['port'] ?? 587))),
        in_array(($data['encryption'] ?? 'tls'), ['tls', 'ssl', 'none'], true) ? $data['encryption'] : 'tls',
        trim((string) ($data['username'] ?? '')),
        $password_value,
        trim((string) ($data['from_email'] ?? '')),
        trim((string) ($data['from_name'] ?? 'AI-Solutions Admin')) ?: 'AI-Solutions Admin',
    ]);
}

function smtp_is_configured(array $settings) {
    return !empty($settings['enabled'])
        && trim((string) ($settings['host'] ?? '')) !== ''
        && (int) ($settings['port'] ?? 0) > 0
        && valid_email($settings['from_email'] ?? '');
}

function smtp_mask_secret($secret) {
    $secret = (string) $secret;
    if ($secret === '') {
        return 'Not saved';
    }
    return str_repeat('*', min(12, max(8, strlen($secret))));
}

function smtp_encode_header($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (preg_match('/[^\x20-\x7E]/', $value)) {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
    return str_replace(["\r", "\n"], '', $value);
}

function smtp_address_header($email, $name = '') {
    $email = trim((string) $email);
    $name = smtp_encode_header($name);
    return $name !== '' ? '"' . addcslashes($name, '"\\') . '" <' . $email . '>' : $email;
}

function smtp_build_message(array $settings, $to, $subject, $body, array $attachments = []) {
    $boundary = 'mail-' . bin2hex(random_bytes(12));
    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: ' . smtp_address_header($settings['from_email'], $settings['from_name']),
        'To: ' . $to,
        'Subject: ' . smtp_encode_header($subject),
        'MIME-Version: 1.0',
        'Content-Type: multipart/mixed; boundary="' . $boundary . '"',
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n";
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= str_replace(["\r\n", "\r"], "\n", (string) $body);
    $message .= "\r\n\r\n";

    foreach ($attachments as $attachment) {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) ($attachment['filename'] ?? 'report.dat'));
        $mime = (string) ($attachment['mime'] ?? 'application/octet-stream');
        $content = (string) ($attachment['content'] ?? '');
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: $mime; name=\"$filename\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
        $message .= chunk_split(base64_encode($content));
        $message .= "\r\n";
    }

    $message .= "--$boundary--\r\n";
    return $message;
}

function smtp_read_response($socket) {
    $response = '';
    while (!feof($socket)) {
        $line = fgets($socket, 515);
        if ($line === false) {
            break;
        }
        $response .= $line;
        if (preg_match('/^\d{3}\s/', $line)) {
            break;
        }
    }
    return $response;
}

function smtp_response_code($response) {
    return (int) substr((string) $response, 0, 3);
}

function smtp_command($socket, $command, array $expected_codes) {
    fwrite($socket, $command . "\r\n");
    $response = smtp_read_response($socket);
    $code = smtp_response_code($response);
    if (!in_array($code, $expected_codes, true)) {
        throw new RuntimeException(trim($response) ?: 'SMTP command failed.');
    }
    return $response;
}

function smtp_dot_stuff($message) {
    $message = str_replace(["\r\n", "\r"], "\n", (string) $message);
    $lines = explode("\n", $message);
    foreach ($lines as &$line) {
        if (isset($line[0]) && $line[0] === '.') {
            $line = '.' . $line;
        }
    }
    unset($line);
    return implode("\r\n", $lines);
}

function smtp_send_mail(PDO $pdo, $to, $subject, $body, array $attachments = []) {
    $settings = smtp_settings($pdo);
    if (!smtp_is_configured($settings)) {
        return ['sent' => false, 'reason' => 'SMTP settings are not complete.'];
    }

    $to = trim((string) $to);
    if (!valid_email($to)) {
        return ['sent' => false, 'reason' => 'Recipient email address is invalid.'];
    }

    $host = trim((string) $settings['host']);
    $port = (int) $settings['port'];
    $encryption = (string) $settings['encryption'];
    $target = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
        ],
    ]);

    try {
        $socket = @stream_socket_client($target, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            throw new RuntimeException($errstr ?: 'Could not connect to SMTP server.');
        }

        stream_set_timeout($socket, 20);
        $greeting = smtp_read_response($socket);
        if (smtp_response_code($greeting) !== 220) {
            throw new RuntimeException(trim($greeting) ?: 'SMTP server did not accept the connection.');
        }

        smtp_command($socket, 'EHLO localhost', [250]);

        if ($encryption === 'tls') {
            smtp_command($socket, 'STARTTLS', [220]);
            if (!function_exists('stream_socket_enable_crypto') || !@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Could not start SMTP TLS encryption.');
            }
            smtp_command($socket, 'EHLO localhost', [250]);
        }

        $username = trim((string) ($settings['username'] ?? ''));
        $password = (string) ($settings['password_value'] ?? '');
        if ($username !== '') {
            smtp_command($socket, 'AUTH LOGIN', [334]);
            smtp_command($socket, base64_encode($username), [334]);
            smtp_command($socket, base64_encode($password), [235]);
        }

        smtp_command($socket, 'MAIL FROM:<' . $settings['from_email'] . '>', [250]);
        smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        smtp_command($socket, 'DATA', [354]);
        fwrite($socket, smtp_dot_stuff(smtp_build_message($settings, $to, $subject, $body, $attachments)) . "\r\n.\r\n");
        $data_response = smtp_read_response($socket);
        if (!in_array(smtp_response_code($data_response), [250], true)) {
            throw new RuntimeException(trim($data_response) ?: 'SMTP server rejected the message.');
        }

        @smtp_command($socket, 'QUIT', [221, 250]);
        fclose($socket);
        return ['sent' => true, 'reason' => 'Sent through SMTP.'];
    } catch (Throwable $e) {
        if (isset($socket) && is_resource($socket)) {
            @fclose($socket);
        }
        error_log('SMTP send failed: ' . $e->getMessage());
        return ['sent' => false, 'reason' => $e->getMessage()];
    }
}

function mail_queue_local_message($to, $subject, $body, $reason = '') {
    $outbox_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'email-outbox';
    if (!is_dir($outbox_dir) && !@mkdir($outbox_dir, 0700, true)) {
        return false;
    }
    if (!is_writable($outbox_dir)) {
        return false;
    }

    $base = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
    $headers = [
        'To: ' . trim((string) $to),
        'Subject: ' . trim((string) $subject),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: AI-Solutions Admin <no-reply@ai-solutions.local>',
    ];
    $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace(["\r\n", "\r"], "\n", (string) $body);
    $summary = [
        'queued_at' => date('Y-m-d H:i:s'),
        'to' => trim((string) $to),
        'subject' => trim((string) $subject),
        'attachment' => '',
        'note' => $reason !== '' ? $reason : 'Created because live email was not available.',
    ];

    return file_put_contents($outbox_dir . DIRECTORY_SEPARATOR . $base . '.eml', $message) !== false
        && file_put_contents($outbox_dir . DIRECTORY_SEPARATOR . $base . '.json', json_encode($summary, JSON_PRETTY_PRINT)) !== false;
}

function mail_send_or_queue(PDO $pdo, $to, $subject, $body) {
    $result = smtp_send_mail($pdo, $to, $subject, $body);
    if ($result['sent']) {
        return $result;
    }

    $queued = mail_queue_local_message($to, $subject, $body, $result['reason'] ?? '');
    return [
        'sent' => false,
        'queued' => $queued,
        'reason' => $result['reason'] ?? 'SMTP unavailable.',
    ];
}
