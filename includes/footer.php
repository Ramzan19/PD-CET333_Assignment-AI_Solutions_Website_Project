</main>
<button class="chatbot-launcher" type="button" aria-controls="chatWidget" aria-expanded="false" aria-label="Toggle AI assistant">
    <img class="chatbot-launcher-bot" src="assets/images/chatbot-robot-3d.png" alt="">
</button>
<section class="chat-widget" id="chatWidget" data-assistant-surface aria-label="AI assistant">
    <div class="chat-header">
        <div class="chat-header-main">
            <button class="chat-avatar robot-avatar chat-avatar-toggle" type="button" onclick="toggleAssistant()" aria-label="Close AI assistant"><img src="assets/images/chatbot-robot-3d.png" alt=""></button>
            <div><strong><?= e(tr('AI-Solutions Assistant')) ?></strong><span><i class="status-dot"></i><?= e(tr('Ready to help')) ?></span></div>
        </div>
    </div>
    <div class="chat-body" id="chatBody" data-chat-body>
        <div class="bot-message"><span class="message-copy"><?= e(tr('Hi, I am AI-Solutions. Ask about services, pricing, demos, automation, or human handover.')) ?></span></div>
        <div class="quick-actions" data-assistant-options>
            <button type="button" data-prompt="Which AI solution fits my business?"><?= e(tr('Find Fit')) ?></button>
            <button type="button" data-prompt="What software solutions do you offer?"><?= e(tr('Services')) ?></button>
            <button type="button" data-prompt="How much does an AI solution cost?"><?= e(tr('Pricing')) ?></button>
            <button type="button" data-route="events.php"><?= e(tr('Events')) ?></button>
            <button type="button" data-route="schedule-demo.php"><?= e(tr('Schedule Demo')) ?></button>
            <button type="button" data-handover="Sales Representative"><?= e(tr('Human Handover')) ?></button>
        </div>
    </div>
    <form class="chat-input" onsubmit="sendChat(event)">
        <textarea id="chatInput" rows="1" placeholder="<?= e(tr('Type your message...')) ?>" autocomplete="off" aria-label="Assistant message"></textarea>
        <button type="submit" data-assistant-submit><?= e(tr('Send')) ?></button>
    </form>
</section>
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-top">
            <div class="footer-brand">
                <a class="brand" href="index.php">
                    <span class="brand-logo ai-brand-mark" aria-hidden="true">
                        <span class="mark-core">AI</span>
                        <span class="mark-node node-a"></span>
                        <span class="mark-node node-b"></span>
                        <span class="mark-node node-c"></span>
                    </span>
                    <span>AI-Solutions</span>
                </a>
                <p><?= e(tr('Secure AI assistants, workflow automation, analytics, and product prototypes built around measurable operational progress.')) ?></p>
                <div class="footer-actions">
                    <a href="contact.php"><?= e(tr('Start a project')) ?></a>
                    <a href="schedule-demo.php"><?= e(tr('Schedule demo')) ?></a>
                </div>
            </div>
            <div class="footer-col">
                <h4><?= e(tr('Contact')) ?></h4>
                <a href="mailto:aisolutions777@gmail.com" class="footer-contact-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    aisolutions777@gmail.com
                </a>
                <a href="tel:+9779800000000" class="footer-contact-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.15 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.06 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 17z"/></svg>
                    +977 98-0000-0000
                </a>
                <div class="footer-social">
                    <a href="https://facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="https://twitter.com/" target="_blank" rel="noopener noreferrer" aria-label="Twitter / X">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 4l16 16M20 4 4 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/><path d="M2.05 3h4.5l14 18h-4.5z"/></svg>
                    </a>
                    <a href="https://linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                    </a>
                    <a href="https://instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                </div>
            </div>
            <div class="footer-col">
                <h4><?= e(tr('Company')) ?></h4>
                <a href="services.php"><?= e(tr('Solutions')) ?></a>
                <a href="events.php"><?= e(tr('Events')) ?></a>
                <a href="articles.php"><?= e(tr('Articles')) ?></a>
                <a href="contact.php"><?= e(tr('Contact Us')) ?></a>
            </div>
            <div class="footer-col">
                <h4><?= e(tr('Solutions')) ?></h4>
                <a href="chatbot.php"><?= e(tr('Try Assistant')) ?></a>
                <a href="schedule-demo.php"><?= e(tr('Book Demo')) ?></a>
                <a href="privacy.php"><?= e(tr('Privacy Policy')) ?></a>
                <a href="terms.php"><?= e(tr('Terms of Service')) ?></a>
            </div>
            <div class="footer-map" aria-label="AI-Solutions location">
                <div class="footer-map-copy">
                    <span><?= e(tr('Location')) ?></span>
                    <strong id="footerLocationName">Sunderland, United Kingdom</strong>
                    <a id="footerMapLink" href="https://www.openstreetmap.org/search?query=Sunderland%2C%20United%20Kingdom" target="_blank" rel="noopener noreferrer"><?= e(tr('Open map')) ?></a>
                </div>
                <div class="footer-map-frame">
                    <iframe
                        id="footerMap"
                        title="Map showing the current location"
                        src="https://www.openstreetmap.org/export/embed.html?bbox=-1.4900%2C54.8500%2C-1.2800%2C54.9700&amp;layer=mapnik&amp;marker=54.9069%2C-1.3838"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> AI-Solutions.</p>
        </div>
    </div>
</footer>
<section class="cookie-banner" data-cookie-banner aria-label="Cookie consent" hidden>
    <div>
        <strong><?= e(tr('Cookie preferences')) ?></strong>
        <p><?= e(tr('AI-Solutions uses a small analytics cookie to understand page visits and improve conversion paths.')) ?></p>
    </div>
    <div class="cookie-actions">
        <button class="btn btn-primary" type="button" data-cookie-accept><?= e(tr('Accept')) ?></button>
        <button class="btn btn-secondary" type="button" data-cookie-decline><?= e(tr('Decline')) ?></button>
    </div>
</section>
<script>
(function () {
    var map = document.getElementById('footerMap');
    var nameEl = document.getElementById('footerLocationName');
    var linkEl = document.getElementById('footerMapLink');
    if (!map) { return; }

    function apply(lat, lon, label) {
        var d = 0.06;
        var bbox = (lon - d) + '%2C' + (lat - d) + '%2C' + (lon + d) + '%2C' + (lat + d);
        map.src = 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox + '&layer=mapnik&marker=' + lat + '%2C' + lon;
        if (linkEl) { linkEl.href = 'https://www.openstreetmap.org/?mlat=' + lat + '&mlon=' + lon + '#map=13/' + lat + '/' + lon; }
        if (nameEl && label) { nameEl.textContent = label; }
    }

    // Approximate location from the visitor's IP. Used when precise GPS is
    // unavailable or the permission prompt is declined, so the map still
    // reflects where the visitor actually is rather than the default city.
    function ipFallback() {
        fetch('https://ipapi.co/json/')
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) {
                if (!j || typeof j.latitude !== 'number' || typeof j.longitude !== 'number') { return; }
                var city = j.city || j.region || '';
                var label = (city ? city + ', ' : '') + (j.country_name || '');
                apply(j.latitude, j.longitude, label.trim() || 'Your current location');
            })
            .catch(function () { /* offline or blocked: keep default Sunderland map */ });
    }

    if (!('geolocation' in navigator)) { ipFallback(); return; }

    navigator.geolocation.getCurrentPosition(function (pos) {
        var lat = pos.coords.latitude, lon = pos.coords.longitude;
        apply(lat, lon, 'Your current location');
        fetch('https://nominatim.openstreetmap.org/reverse?format=json&zoom=10&addressdetails=1&lat=' + lat + '&lon=' + lon, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) {
                if (!j || !j.address) { return; }
                var a = j.address;
                var city = a.city || a.town || a.village || a.county || a.state_district || '';
                var label = (city ? city + ', ' : '') + (a.country || '');
                if (label.trim()) { if (nameEl) { nameEl.textContent = label; } }
            })
            .catch(function () {});
    }, function () { ipFallback(); },
    { enableHighAccuracy: true, timeout: 8000, maximumAge: 600000 });
})();
</script>
<script src="assets/js/site.js?v=<?= filemtime(__DIR__ . '/../assets/js/site.js') ?>"></script>
<script src="assets/js/feedback.js?v=<?= filemtime(__DIR__ . '/../assets/js/feedback.js') ?>"></script>
<script src="assets/js/chatbot.js?v=<?= filemtime(__DIR__ . '/../assets/js/chatbot.js') ?>"></script>
</body>
</html>
