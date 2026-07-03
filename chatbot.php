<?php
$current_page = 'chatbot';
$page_title = 'AI Assistant';
include 'includes/header.php';
?>
<section class="page-hero chatbot-hero">
    <div class="chatbot-hero-copy">
        <span class="eyebrow"><?= e(tr('Virtual assistant')) ?></span>
        <h1><?= e(tr('Meet AI-Solutions, your 3D AI service concierge.')) ?></h1>
        <p><?= e(tr('AI-Solutions helps visitors understand AI solutions, compare next steps, book demos, and move complex requests to a human team member with useful context attached.')) ?></p>
        <div class="assistant-metrics" aria-label="Assistant highlights">
            <div><strong>24/7</strong><span><?= e(tr('visitor guidance')) ?></span></div>
            <div><strong>4</strong><span><?= e(tr('smart pathways')) ?></span></div>
            <div><strong>1-click</strong><span><?= e(tr('human handover')) ?></span></div>
        </div>
    </div>
    <div class="chatbot-hero-visual" aria-hidden="true">
        <span class="model-platform"></span>
        <img class="chatbot-model" src="assets/images/chatbot-robot-3d.png" alt="">
    </div>
</section>

<section class="section chatbot-page">
    <div class="chat-panel" data-assistant-surface>
        <div class="chat-panel-header">
            <div class="chat-header-main">
                <span class="chat-avatar robot-avatar" aria-hidden="true"><img src="assets/images/chatbot-robot-3d.png" alt=""></span>
                <div>
                    <strong><?= e(tr('AI-Solutions Assistant')) ?></strong>
                    <span><i class="status-dot"></i> <?= e(tr('Ready to help')) ?></span>
                </div>
            </div>
            <button class="panel-clear" type="button" onclick="resetAssistant(this)"><?= e(tr('Clear Chat')) ?></button>
        </div>
        <div class="chat-message-area" data-chat-body>
            <div class="bot-message"><span class="message-copy"><?= e(tr('Hi, I am AI-Solutions. Tell me your goal and I will suggest the best AI solution, next step, or human handover.')) ?></span></div>
            <div class="quick-actions" data-assistant-options>
                <button type="button" data-prompt="Which AI solution fits my business?"><?= e(tr('Find Fit')) ?></button>
                <button type="button" data-prompt="Help me map a workflow automation idea"><?= e(tr('Automation')) ?></button>
                <button type="button" data-prompt="How much does an AI solution cost?"><?= e(tr('Pricing')) ?></button>
                <button type="button" data-handover="Sales Representative"><?= e(tr('Human Handover')) ?></button>
            </div>
        </div>
        <form class="chat-input panel-chat-input" onsubmit="sendChat(event)">
            <textarea rows="1" placeholder="<?= e(tr('Ask about services, demos, automation, pricing...')) ?>" autocomplete="off" aria-label="Assistant message"></textarea>
            <button type="submit" data-assistant-submit><?= e(tr('Send')) ?></button>
        </form>
        <div class="chat-panel-actions">
            <a class="btn btn-primary" href="chatbot-handover.php" data-handover-link><?= e(tr('Request Human Handover')) ?></a>
            <a class="btn btn-secondary" href="services.php"><?= e(tr('View Services')) ?></a>
        </div>
    </div>
    <aside class="assistant-notes">
        <span class="section-kicker"><?= e(tr('Designed for handover')) ?></span>
        <h2><?= e(tr('Friendly automation with a clean human path.')) ?></h2>
        <p><?= e(tr('AI-Solutions keeps the conversation focused, suggests useful next steps, and prepares a summary when a visitor wants the team to follow up.')) ?></p>
        <div class="assistant-capabilities">
            <div><strong><?= e(tr('Service matching')) ?></strong><span><?= e(tr('Recommends assistants, automation, dashboards, or prototypes.')) ?></span></div>
            <div><strong><?= e(tr('Smart routing')) ?></strong><span><?= e(tr('Moves visitors toward demos, events, sales, or contact paths.')) ?></span></div>
            <div><strong><?= e(tr('Context capture')) ?></strong><span><?= e(tr('Summarizes the chat before handover.')) ?></span></div>
        </div>
    </aside>
</section>
<?php include 'includes/footer.php'; ?>
