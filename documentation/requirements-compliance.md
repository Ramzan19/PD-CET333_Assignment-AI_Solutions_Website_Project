# AI-Solutions Requirements Compliance

Updated: 2026-06-24

## Implemented in the project

- Home page: overview, solution cards, visitor testimonials, and news/articles preview.
- Solutions page: industry and capability filters, solution cards, and customer feedback.
- Events: event details, RSVP form, and downloadable calendar files.
- Articles/blog: dedicated articles page for company news and industry insights.
- Contact form: required fields, server validation, live browser validation, CAPTCHA, consent checkbox, and acknowledgement email/outbox fallback.
- Admin area: inquiry dashboard, charts, filtering, CSV/PDF exports, monthly report page, visitor tracking, and activity log.
- Data handling: prepared statements, CSRF protection, secure sessions, validation, and database-backed submissions.
- Security: password-protected admin area, full authenticator-app two-factor authentication (TOTP, RFC 6238) with self-service enable/disable on the admin Security page, login rate-limiting, role column, role checks for sensitive admin actions, security headers, and storage/database directory blocking.
- SEO and analytics: structured data, metadata, consent-based analytics, visitor/device/browser tracking, and conversion records.
- Compliance support: privacy policy, terms page, and cookie consent banner.

## June 2026 enhancements (gap closure)

- Two-factor authentication: real authenticator-app TOTP (Google Authenticator / Authy / Microsoft Authenticator). New `includes/mfa.php` (verified against RFC 6238 test vectors), `admin/security.php` setup screen, and a two-phase `admin/login.php` (password then 6-digit code).
- Gallery lightbox: gallery images open an accessible overlay (keyboard, Esc, prev/next) — `assets/js/site.js` + CSS.
- Admin inquiries: whitelisted sortable column headers plus country / job-title / status filter dropdowns — `admin/inquiries.php`.
- Per-solution ratings + case studies: moderated customer reviews per solution (`solution_reviews` table, `solution-review-submit.php`, `admin/solution-reviews.php`), average stars on each solution card, and structured case studies (objective / challenge / solution / measurable result) on `services.php`.
- Blog search + per-article SEO: article search box, dedicated `article.php` pages with per-article meta description/keywords and Article schema, plus Open Graph tags site-wide.
- Dynamic country code: phone fields are pre-filled with a dial code based on the visitor's detected country (`assets/js/site.js`).
- Google Analytics 4: gated `gtag` hook in `includes/header.php`, activated by the `GA_MEASUREMENT_ID` config constant (off by default).
- Multi-language scaffold: `includes/i18n.php` with English + Nepali, a `t()` helper, session/cookie persistence, and a header language switcher.

## Partially implemented or deployment-dependent

- SSL/TLS encryption: code includes HTTPS security headers, but the live server must install and enforce an SSL certificate.
- CAPTCHA: local math CAPTCHA is implemented; production can replace it with a managed CAPTCHA provider.
- CDN: assets are cache-busted locally; a CDN must be configured at hosting/deployment level.
- Horizontal scaling, load balancing, 99.9% uptime, failover, and redundancy require production infrastructure.
- Regular backups, vulnerability scanning, and security audits require scheduled operational processes.
- CI/CD pipeline requires repository hosting and deployment automation outside this XAMPP prototype.
- GDPR retention policy is documented and supported by admin delete/export tools; final retention periods should be approved by the organisation.

## Recommended production tasks

- The seeded local admin password was rotated to a strong 18-character credential (username `admin`, password `OMmXa1h#qKyL%UaBAW`, stored only as a bcrypt hash in `database/setup.sql`). This is for local demo use only — replace it again and set a real admin email before any public deployment.
- Configure SMTP so MFA and acknowledgements are delivered by email.
- Enforce HTTPS redirects at the web server or hosting provider.
- Add scheduled database backups and restore testing.
- Add CI checks for PHP linting, accessibility scans, and deployment.
- Run vulnerability scans before public launch.
