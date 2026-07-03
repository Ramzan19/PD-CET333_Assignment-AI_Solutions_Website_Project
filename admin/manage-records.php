<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/feedback.php';
require_once __DIR__ . '/../includes/schema.php';
require_admin();
ensure_assignment_schema($pdo);
ensure_visitor_feedback_table($pdo);

$current_page = 'records';
$page_title = 'Manage Records';
$records = $pdo->query("SELECT id,full_name name,'Inquiry' category,created_at,status FROM customer_inquiries UNION ALL SELECT id,full_name name,'Demo Booking' category,created_at,status FROM demo_bookings UNION ALL SELECT id,full_name name,'Event Registration' category,created_at,status FROM event_registrations UNION ALL SELECT id,user_name name,'Chatbot Lead' category,created_at,status FROM chatbot_conversations UNION ALL SELECT id,visitor_name name,'Feedback Rating' category,created_at,status FROM visitor_feedback ORDER BY created_at DESC LIMIT 15")->fetchAll();
include 'admin-header.php';
?>
<div class="admin-hero">
    <div>
        <span class="section-kicker">Records</span>
        <h1>Manage Records</h1>
        <p>Combined overview of inquiries, demo bookings, event registrations, chatbot leads, and feedback.</p>
    </div>
</div>
<div class="grid-3 admin-link-grid">
    <a class="card" href="inquiries.php"><span class="card-number">01</span><h3>Customer Inquiries</h3><p>Manage requirement messages.</p></a>
    <a class="card" href="demo-bookings.php"><span class="card-number">02</span><h3>Demo Bookings</h3><p>Track scheduled demos.</p></a>
    <a class="card" href="event-registrations.php"><span class="card-number">03</span><h3>Event Registrations</h3><p>Measure Join our Events interest.</p></a>
    <a class="card" href="chatbot-leads.php"><span class="card-number">04</span><h3>Chatbot Leads</h3><p>Follow up handover leads.</p></a>
    <a class="card" href="visitor-feedback.php"><span class="card-number">05</span><h3>Visitor Feedback</h3><p>Review ratings, messages, and featured testimonials.</p></a>
    <a class="card" href="visitors.php"><span class="card-number">06</span><h3>Visitor Tracker</h3><p>Monitor public page visits, devices, browsers, and timestamps.</p></a>
</div>
<div class="table-panel">
    <div class="table-header"><h2>Recent Combined Records</h2><span class="badge">CRUD overview</span></div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr><td><?= e($r['id']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['category']) ?></td><td><?= e($r['created_at']) ?></td><td><span class="status <?= e(strtolower(str_replace(' ', '-', $r['status'] ?? 'new'))) ?>"><?= e($r['status']) ?></span></td></tr>
            <?php endforeach; ?>
            <?php if (!$records): ?><tr><td colspan="5">No records yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'admin-footer.php'; ?>
