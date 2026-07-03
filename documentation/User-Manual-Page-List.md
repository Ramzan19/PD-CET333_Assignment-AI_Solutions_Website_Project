# AI-Solutions — User Manual: Page & Content List

Prepared for the **User Manual** section (inside Solution Design Documentation), matching the OLD_SAMPLE format: each page is documented with a **page name → short description → screenshot(s)**. Customer (user) side first, then Admin side. Tall pages are captured as **multiple landscape images** so each fits a Word page.

---

## Part A — Customer / Public Website

**1. Home Page** — `index.php`
Welcome/landing page: hero with the AI-Solutions value proposition, "Secure AI delivery" strapline, 24/7 · 3x · 100% proof stats, "What we build" service cards (Assistants, Automation, Analytics, Prototypes), delivery-approach steps, latest News/Articles preview, the **customer ratings wall** (average score + paginated testimonials), call-to-action banners, the **GPS location map** in the footer, and the floating **AI assistant** launcher.
*Screenshots: 3–4 slices (hero → services → ratings → footer/map).*

**2. Solutions / Services Page** — `services.php`
Catalogue of AI software solutions with industry/capability **filters**, solution cards (description + industries served), and **per-solution customer reviews & star ratings** (visitors can submit a review). 
*Screenshots: 2–3 slices.*

**3. Events & Insights Page** — `events.php`
Upcoming events list, **photo gallery** of promotional events, and the **"Join our Events" registration form** (name, email, phone, company, country, event, interest area). Includes downloadable calendar (.ics).
*Screenshots: 2–3 slices.*

**4. Articles & News Page** — `articles.php`
Grid of company articles/blog posts with category labels and pagination.
*Screenshots: 1–2 slices.*

**5. Article Detail Page** — `article.php`
Full single-article reading view (title, body, date/category).
*Screenshot: 1.*

**6. Contact Us / Customer Inquiry Page** — `contact.php`
The main inquiry form collecting **name, email, phone, company name, country, solution interest, job title, job details**, plus a math **security check** and a **consent** checkbox; client-side + server-side validation. Also includes the **visitor feedback / rating** form.
*Screenshots: 2 slices (inquiry form + feedback form).*

**7. Schedule Demo Page** — `schedule-demo.php`
Demo booking form: name, email, phone, company, country, preferred date, preferred time, interested solution, notes, consent.
*Screenshot: 1–2.*

**8. AI Virtual Assistant (Chatbot)** — `chatbot.php` + floating widget
Live AI assistant (now API-powered) with quick-action buttons (Find Fit, Services, Pricing, Events, Schedule Demo, Human Handover), typing indicator, and a draggable launcher. Show an open conversation.
*Screenshots: 1–2 (page + widget open with a sample Q&A).*

**9. Chatbot Handover Page** — `chatbot-handover.php`
Human-support lead-capture form (name, email, phone, company, country, topic, conversation summary) used when the assistant transfers to a person.
*Screenshot: 1.*

**10. Success / Confirmation Page** — `success.php`
Confirmation message shown after a form is submitted successfully.
*Screenshot: 1.*

**11. Privacy Policy & Terms of Service** — `privacy.php`, `terms.php`
GDPR/compliance pages; plus the **cookie consent banner**.
*Screenshots: 1 each (optional 1 for the cookie banner).*

*(Recommended extra: one **mobile / responsive** screenshot of the Home page as evidence for the "responsive design" requirement.)*

---

## Part B — Admin / Staff Panel (password-protected)

**12. Admin Login** — `admin/login.php`
Secure login (username + password) with brute-force lockout. Login: `admin` / `Ronaldo_777`.
*Screenshot: 1.*

**13. Admin Dashboard** — `admin/dashboard.php`
Command center: stat cards (page views, visitors, customer demand, average feedback, event registrations, assistant/prototype interest) and **Chart.js analytics** — Website Engagement (bar), 7-Day Visitor Trend (line), Top Pages, Device Mix (doughnut), Demand by Country, Interest Mix — plus Recent Submissions.
*Screenshots: 2–3 slices (cards → charts → table).*

**14. Customer Inquiries** — `admin/inquiries.php`
List of inquiries with **search/filter**, status badges, and per-record **View / Edit / Delete**, plus **CSV / PDF export** and **Email CSV to customer**.
*Screenshots: 1–2.*

**15. Demo Bookings** — `admin/demo-bookings.php` · **16. Event Registrations** — `admin/event-registrations.php` · **17. Chatbot Leads** — `admin/chatbot-leads.php`
Record tables for each channel with view/edit/delete/export.
*Screenshots: 1 each.*

**18. Visitor Feedback** — `admin/visitor-feedback.php`
Ratings & messages; mark as **featured testimonial**.
*Screenshot: 1.*

**19. Solution Reviews** — `admin/solution-reviews.php`
Moderate the per-solution customer reviews (approve/feature/remove).
*Screenshot: 1.*

**20. Visitor Tracker** — `admin/visitors.php`
Page visits with device, browser, and timestamps (analytics evidence).
*Screenshot: 1.*

**21. Monthly Reports** — `admin/monthly-report.php`
Period-based analytics summary.
*Screenshot: 1.*

**22. Content Manager** — `admin/content.php` (+ `content-edit.php`, `content-delete.php`)
Create/edit/delete website content (articles, events, items) — full CRUD.
*Screenshots: 1–2 (list + edit form).*

**23. Manage Records** — `admin/manage-records.php`
Combined overview of all record types with CRUD links.
*Screenshot: 1.*

**24. Send Email to Customer** — `admin/send-message.php`
Compose and send an email to a customer via SMTP.
*Screenshot: 1.*

**25. Email Outbox** — `admin/email-outbox.php`
Queued/sent emails with download.
*Screenshot: 1.*

**26. SMTP Settings** — `admin/smtp-settings.php`
Mail-server configuration (host, port, encryption, credentials, from address).
*Screenshot: 1.*

**27. Security (2FA / Account)** — `admin/security.php`
Change password, enable **two-factor authentication**, manage account security.
*Screenshot: 1.*

**28. Activity Log** — `admin/activity-log.php`
Audit trail of admin actions (login, view, update, delete, export).
*Screenshot: 1.*

**29. Logout** — `admin/logout.php` (action; mention in text, no screenshot needed).

---

## Part C — Assignment requirement coverage

| Assignment requirement | Demonstrated on |
|---|---|
| Home / overview / mission / USP | Home (1) |
| Solutions with descriptions & industries | Solutions (2) |
| Past solutions / portfolio highlights | Home (1), Solutions (2) |
| Customer testimonials with ratings | Ratings wall on Home (1); Solution Reviews (2/19); Feedback (6/18) |
| Articles / blog | Articles (4), Article detail (5) |
| Photo gallery of events | Events (3) |
| Contact Us form (7 fields) | Contact (6) |
| Store submission + confirmation | Contact (6) → Success (10) |
| Admin login (password protected) | Admin Login (12) |
| View inquiry numbers & details | Dashboard (13), Inquiries (14) |
| Search / filter inquiries | Inquiries (14) |
| Data export (CSV/Excel/PDF) | Inquiries (14), Manage Records (23) |
| Analytics of customer demand | Dashboard (13), Visitor Tracker (20), Monthly Reports (21) |
| Wish list: AI chatbot | Chatbot (8), Handover (9), Chatbot Leads (17) |
| Wish list: appointment/demo booking | Schedule Demo (7), Demo Bookings (15) |
| Wish list: automated email notifications | SMTP (26), Send Email (24), Email Outbox (25) |
| Wish list: testimonial submission | Feedback (6), Solution Reviews (2) |
| Security / GDPR / compliance | Privacy & Terms (11), Security/2FA (27), Activity Log (28) |

---

## Capture shot-list (ordered file names)

Customer: `01-Home` (×3–4), `02-Solutions` (×2–3), `03-Events-Gallery` (×2–3), `04-Articles`, `05-Article-Detail`, `06-Contact` (×2), `07-Schedule-Demo`, `08-Chatbot`, `09-Chatbot-Handover`, `10-Success`, `11-Privacy`, `11b-Terms`, `11c-Mobile-Home`.

Admin: `12-Admin-Login`, `13-Dashboard` (×2–3), `14-Inquiries`, `15-Demo-Bookings`, `16-Event-Registrations`, `17-Chatbot-Leads`, `18-Feedback`, `19-Solution-Reviews`, `20-Visitor-Tracker`, `21-Monthly-Reports`, `22-Content-Manager`, `23-Manage-Records`, `24-Send-Email`, `25-Email-Outbox`, `26-SMTP-Settings`, `27-Security-2FA`, `28-Activity-Log`.
