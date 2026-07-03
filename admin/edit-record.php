<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
require_once __DIR__ . '/../includes/schema.php';
require_once __DIR__ . '/../includes/event-data.php';
require_admin();
ensure_assignment_schema($pdo);
ensure_customer_inquiry_schema($pdo);

if (!function_exists('array_is_list')) {
    function array_is_list(array $arr) {
        return $arr === [] || array_keys($arr) === range(0, count($arr) - 1);
    }
}

$type = $_GET['type'] ?? '';
$id = (int) ($_GET['id'] ?? 0);
$tables = [
    'inquiry' => ['table' => 'customer_inquiries', 'back' => 'inquiries.php'],
    'demo' => ['table' => 'demo_bookings', 'back' => 'demo-bookings.php'],
    'event' => ['table' => 'event_registrations', 'back' => 'event-registrations.php'],
    'chatbot' => ['table' => 'chatbot_conversations', 'back' => 'chatbot-leads.php'],
    'feedback' => ['table' => 'visitor_feedback', 'back' => 'visitor-feedback.php'],
];

if (!isset($tables[$type]) || $id <= 0) {
    http_response_code(400);
    exit('Invalid record.');
}

if ($type === 'feedback') {
    ensure_visitor_feedback_table($pdo);
}

$table = $tables[$type]['table'];

// Option lists (kept consistent with the public forms).
$interest_options = function_exists('ai_solutions_interest_options') ? ai_solutions_interest_options() : [];
$demo_types = ['Virtual Assistant', 'Workflow Automation', 'Data Analytics', 'AI Product Prototyping', 'Full Consultation'];
$yes_no = ['1' => 'Yes', '0' => 'No'];
$rating_choices = ['5' => '5 - Excellent', '4' => '4 - Good', '3' => '3 - Average', '2' => '2 - Poor', '1' => '1 - Very poor'];

$status_options = [
    'event' => ['Registered', 'Confirmed', 'Attended', 'Cancelled'],
    'feedback' => ['New', 'Reviewed', 'Featured', 'Archived'],
];
$allowed_statuses = $status_options[$type] ?? ['New', 'In Progress', 'Booked', 'Resolved'];

// Inquiry records carry a triage priority so the admin team can sort/filter by urgency.
$has_priority = ($type === 'inquiry');
$priority_options = ['High', 'Normal', 'Low'];

// Editable data fields per record type.
// Each: [column, label, kind, options, required, maxlength]
$field_sets = [
    'inquiry' => [
        ['full_name', 'Full Name', 'text', null, true, 150],
        ['email', 'Email', 'email', null, true, 150],
        ['phone', 'Phone', 'tel', null, true, 50],
        ['company_name', 'Company', 'text', null, true, 150],
        ['country', 'Country', 'text', null, true, 100],
        ['solution_interest', 'Solution Interest', 'select', $interest_options, true, 150],
        ['job_title', 'Job Title', 'text', null, true, 150],
        ['job_details', 'Job Details', 'textarea', null, true, 3000],
    ],
    'demo' => [
        ['full_name', 'Full Name', 'text', null, true, 150],
        ['email', 'Email', 'email', null, true, 150],
        ['phone', 'Phone', 'tel', null, true, 50],
        ['company_name', 'Company', 'text', null, true, 150],
        ['country', 'Country', 'text', null, true, 100],
        ['preferred_date', 'Preferred Date', 'date', null, true, 10],
        ['preferred_time', 'Preferred Time', 'time', null, true, 8],
        ['demo_type', 'Interested Solution', 'select', $demo_types, true, 150],
        ['notes', 'Notes', 'textarea', null, false, 2000],
    ],
    'event' => [
        ['full_name', 'Full Name', 'text', null, true, 150],
        ['email', 'Email', 'email', null, true, 150],
        ['phone', 'Phone', 'tel', null, true, 50],
        ['company_name', 'Company', 'text', null, true, 150],
        ['country', 'Country', 'text', null, true, 100],
        ['event_name', 'Event', 'text', null, true, 200],
        ['interest_area', 'Interest Area', 'select', $interest_options, true, 150],
        ['notes', 'Notes', 'textarea', null, false, 2000],
    ],
    'chatbot' => [
        ['user_name', 'Name', 'text', null, true, 150],
        ['email', 'Email', 'email', null, true, 150],
        ['phone', 'Phone', 'tel', null, true, 50],
        ['company_name', 'Company', 'text', null, false, 150],
        ['country', 'Country', 'text', null, true, 100],
        ['topic', 'Topic', 'text', null, true, 150],
        ['chat_summary', 'Conversation Summary', 'textarea', null, true, 2000],
        ['handover_required', 'Handover Required', 'select', $yes_no, true, 1],
    ],
    'feedback' => [
        ['visitor_name', 'Name', 'text', null, true, 150],
        ['email', 'Email', 'email', null, true, 150],
        ['company_name', 'Company', 'text', null, false, 150],
        ['role_title', 'Role / Title', 'text', null, false, 150],
        ['rating', 'Rating', 'select', $rating_choices, true, 1],
        ['message', 'Message', 'textarea', null, true, 1200],
        ['is_featured', 'Featured Testimonial', 'select', $yes_no, true, 1],
    ],
];

$fields = $field_sets[$type];
$errors = [];

// Load existing record first (also used to pre-fill the form).
$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    http_response_code(404);
    exit('Record not found.');
}

// Working copy for the form (current DB values by default).
$form = [];
foreach ($fields as $f) {
    $form[$f[0]] = (string) ($record[$f[0]] ?? '');
}
$form['status'] = (string) ($record['status'] ?? $allowed_statuses[0]);
$form['admin_note'] = (string) ($record['admin_note'] ?? '');
$form['priority'] = (string) ($record['priority'] ?? 'Normal');

if (is_post()) {
    csrf_or_fail();

    foreach ($fields as $f) {
        [$col, $label, $kind, $options, $required, $maxlen] = $f;
        $form[$col] = post_value($col, $maxlen);

        if ($required && $form[$col] === '') {
            $errors[] = $label . ' is required.';
        }
        if ($form[$col] !== '' && strlen($form[$col]) > $maxlen) {
            $errors[] = $label . ' must be ' . $maxlen . ' characters or fewer.';
        }
        if ($kind === 'email' && $form[$col] !== '' && !valid_email($form[$col])) {
            $errors[] = $label . ' must be a valid email address.';
        }
        if ($kind === 'select' && is_array($options) && $form[$col] !== '') {
            $valid_values = array_is_list($options) ? $options : array_keys($options);
            if (!in_array($form[$col], array_map('strval', $valid_values), true)) {
                $errors[] = 'Please select a valid ' . strtolower($label) . '.';
            }
        }
        if ($kind === 'date' && $form[$col] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $form[$col])) {
            $errors[] = $label . ' must be a valid date.';
        }
    }

    $form['status'] = post_value('status', 50);
    $form['admin_note'] = post_value('admin_note', 2000);
    if (!in_array($form['status'], $allowed_statuses, true)) {
        $errors[] = 'Please select a valid status.';
    }
    if ($has_priority) {
        $form['priority'] = post_value('priority', 20);
        if (!in_array($form['priority'], $priority_options, true)) {
            $errors[] = 'Please select a valid priority.';
        }
    }

    if (!$errors) {
        $set_cols = [];
        $values = [];
        foreach ($fields as $f) {
            $set_cols[] = $f[0] . ' = ?';
            $values[] = $form[$f[0]] === '' && !$f[4] ? null : $form[$f[0]];
        }
        $set_cols[] = 'status = ?';
        $values[] = $form['status'];
        if ($has_priority) {
            $set_cols[] = 'priority = ?';
            $values[] = $form['priority'];
        }
        $set_cols[] = 'admin_note = ?';
        $values[] = $form['admin_note'] === '' ? null : $form['admin_note'];
        $set_cols[] = 'updated_at = NOW()';
        $values[] = $id;

        $sql = "UPDATE $table SET " . implode(', ', $set_cols) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        admin_log_activity($pdo, 'update', $type . ' #' . $id);
        header('Location: view-record.php?type=' . urlencode($type) . '&id=' . $id);
        exit;
    }
}

$current_page = 'records';
$page_title = 'Edit Record';
include 'admin-header.php';
?>
<div class="admin-hero">
    <div><h1>Edit Record</h1><p>Update the full details, status, and internal admin note for this <?= e($type) ?> record.</p></div>
    <a class="btn btn-secondary" href="view-record.php?type=<?= e($type) ?>&id=<?= e($id) ?>">Back</a>
</div>
<div class="form-shell">
    <?php if ($errors): ?><div class="alert alert-error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <div class="form-grid">
            <?php foreach ($fields as $f):
                [$col, $label, $kind, $options, $required, $maxlen] = $f;
                $value = $form[$col];
                $group_class = ($kind === 'textarea') ? 'form-group full' : 'form-group';
                $req_attr = $required ? 'required' : '';
                $req_star = $required ? ' *' : '';
            ?>
                <div class="<?= $group_class ?>">
                    <label for="f_<?= e($col) ?>"><?= e($label) . $req_star ?></label>
                    <?php if ($kind === 'textarea'): ?>
                        <textarea id="f_<?= e($col) ?>" name="<?= e($col) ?>" maxlength="<?= e($maxlen) ?>" <?= $req_attr ?>><?= e($value) ?></textarea>
                    <?php elseif ($kind === 'select'):
                        $is_list = array_is_list($options);
                        ?>
                        <select id="f_<?= e($col) ?>" name="<?= e($col) ?>" <?= $req_attr ?>>
                            <?php if (!$required): ?><option value="">- None -</option><?php endif; ?>
                            <?php
                            $current_in_options = false;
                            foreach ($options as $opt_key => $opt_label):
                                $opt_value = $is_list ? $opt_label : (string) $opt_key;
                                if ((string) $opt_value === (string) $value) { $current_in_options = true; }
                            ?>
                                <option value="<?= e($opt_value) ?>" <?= selected($value, $opt_value) ?>><?= e($opt_label) ?></option>
                            <?php endforeach; ?>
                            <?php if ($value !== '' && !$current_in_options): ?>
                                <option value="<?= e($value) ?>" selected><?= e($value) ?> (current)</option>
                            <?php endif; ?>
                        </select>
                    <?php else: ?>
                        <input id="f_<?= e($col) ?>" name="<?= e($col) ?>" type="<?= e($kind === 'tel' ? 'tel' : ($kind === 'email' ? 'email' : ($kind === 'date' ? 'date' : ($kind === 'time' ? 'time' : 'text')))) ?>" maxlength="<?= e($maxlen) ?>" value="<?= e($value) ?>" <?= $kind === 'tel' ? 'inputmode="tel" pattern="[0-9+()\-\s]{7,30}" title="Use 7-30 characters: digits, spaces, and + ( ) - only."' : '' ?> <?= $req_attr ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php foreach ($allowed_statuses as $s): ?><option value="<?= e($s) ?>" <?= selected($form['status'], $s) ?>><?= e($s) ?></option><?php endforeach; ?>
                </select>
            </div>
            <?php if ($has_priority): ?>
            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <?php foreach ($priority_options as $p): ?><option value="<?= e($p) ?>" <?= selected($form['priority'], $p) ?>><?= e($p) ?></option><?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group full"><label for="admin_note">Internal Admin Note</label><textarea id="admin_note" name="admin_note" maxlength="2000"><?= e($form['admin_note']) ?></textarea></div>
        </div>
        <div class="button-row"><button class="btn btn-primary" type="submit">Save Changes</button><a class="btn btn-secondary" href="view-record.php?type=<?= e($type) ?>&id=<?= e($id) ?>">Cancel</a></div>
    </form>
</div>
<?php include 'admin-footer.php'; ?>
