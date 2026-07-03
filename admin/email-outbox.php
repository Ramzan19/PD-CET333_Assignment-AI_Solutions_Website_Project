<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$current_page = 'outbox';
$page_title = 'Email Outbox';
$outbox_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'email-outbox';
$messages = [];

if (is_dir($outbox_dir)) {
    foreach (glob($outbox_dir . DIRECTORY_SEPARATOR . '*.json') ?: [] as $meta_file) {
        $meta = json_decode((string) file_get_contents($meta_file), true);
        if (!is_array($meta)) {
            continue;
        }

        $base = basename($meta_file, '.json');
        $attachments = glob($outbox_dir . DIRECTORY_SEPARATOR . $base . '-*') ?: [];
        $messages[] = [
            'base' => $base,
            'queued_at' => $meta['queued_at'] ?? date('Y-m-d H:i:s', filemtime($meta_file)),
            'to' => $meta['to'] ?? '',
            'subject' => $meta['subject'] ?? '',
            'attachment' => basename($attachments[0] ?? ''),
        ];
    }
}

usort($messages, fn($a, $b) => strcmp($b['queued_at'], $a['queued_at']));
include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Local mail fallback</span>
        <h1>Email Outbox</h1>
        <p>When live email is not configured in XAMPP, emailed reports are saved here for review and download.</p>
    </div>
</div>

<div class="table-panel">
    <div class="table-header"><h2>Queued Email Packages</h2><span class="badge"><?= e(count($messages)) ?> saved</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Queued At</th><th>To</th><th>Subject</th><th>Attachment</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= e($message['queued_at']) ?></td>
                    <td><?= e($message['to']) ?></td>
                    <td><?= e($message['subject']) ?></td>
                    <td><?= e($message['attachment'] ?: 'No separate attachment') ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="action-link" href="download-outbox.php?file=<?= e(urlencode($message['base'] . '.eml')) ?>">EML</a>
                            <?php if ($message['attachment']): ?>
                                <a class="action-link action-export" href="download-outbox.php?file=<?= e(urlencode($message['attachment'])) ?>">Attachment</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$messages): ?><tr><td colspan="5">No local email packages have been created yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
