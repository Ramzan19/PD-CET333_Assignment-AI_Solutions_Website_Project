# CET333 — AI-Solutions Project Analysis, Upgrade List & Rating

**Prepared for:** Ramjan Rawal
**Scenario chosen (per your client meeting notes):** Computer Systems Engineering
**Reviewed against:** CET333 Assignment Brief V2, Grading Criteria, and your signed Requirements Specification
**Date:** 20 June 2026

---

## 1. The single most important thing to understand

The website is only **35 of the 100 marks** ("Prototype Product, Documentation and Demonstration"). The other **65 marks are the Portfolio Report (PDF) and 3 signed client meetings**:

| Rubric section | Marks |
|---|---|
| Requirements, Sign-off & Client Engagement | 10 |
| Project Planning | 10 |
| Methodology | 15 |
| **Prototype Product, Documentation & Demonstration** | **35** |
| Testing, Evaluation & Deployment | 20 |
| Reflective Practice | 10 |

**Your website is genuinely strong — but a perfect website alone caps the module at ~35%.** "Outstanding and above" overall is only possible if the report and client-meeting evidence are also Outstanding. So this analysis covers both: the product (what you asked about) *and* the documentation you still need.

---

## 2. Does the website meet the assignment? — YES, and it exceeds it

Your scenario (Computer Systems Engineering) requires: software-solution details, highlights of past solutions, customer feedback with ratings, articles, event photo galleries + upcoming events, a Contact Us form (name, email, phone, company, country, job title, job details — no accounts), and a password-protected admin area showing inquiry numbers.

| Required by CSE scenario | In your build | Status |
|---|---|---|
| Details of software solutions offered | `services.php` (filters, solution cards) | ✅ Met |
| Highlights of past solutions | Services + articles + event gallery | ✅ Met |
| Customer feedback **with ratings** | Feedback wall, star ratings, average, pagination (`includes/feedback.php`, `contact.php`) | ✅ Met |
| Articles promoting the company | `articles.php` | ✅ Met |
| Photo galleries of events + upcoming events | `events.php`, `calendar.php` | ✅ Met |
| Contact Us form with the 7 exact fields | `contact.php` — name, email, phone, company, country, job title, job details (+ solution interest) | ✅ Met exactly |
| No accounts / passwords for customers | Public forms only; admin is the only protected area | ✅ Met |
| Password-protected admin area | `admin/login.php`, `includes/auth.php` | ✅ Met |
| Admin sees customer-inquiry numbers + analysis | `admin/dashboard.php` totals + charts | ✅ Met |

**Beyond the brief (these raise the product toward Exceptional):** AI chatbot with human handover, scheduled-demo booking, event registration, visitor/device/browser analytics, 7-day trend, demand-by-country and interest-mix charts, monthly report, CSV/PDF export, activity log, SMTP settings, role-based access (RBAC), MFA flag.

**Engineering quality is high:** PDO prepared statements, CSRF tokens, honeypot + math CAPTCHA, consent checkboxes, server + HTML5 validation, security headers, `password_verify` (bcrypt), brute-force lockout (5 attempts → 5-min block), session regeneration, IP hashing. This is well above typical student standard.

---

## 3. Upgrade list for the WEBSITE (to move from Outstanding → Exceptional)

### A. Correctness / consistency fixes (quick wins)
1. **Demo form consent isn't `required` in HTML.** `schedule-demo.php` validates consent server-side, but the checkbox lacks the `required` attribute (the contact form has it). Add it for consistency.
2. **`edit-record.php` only edits status + admin note.** The brief says admin should "update or delete records." Consider allowing edits to the actual record fields (name/email/etc.) so CRUD is unambiguous in your demo.
3. **Default admin credentials are committed** in `database/setup.sql` (`admin / Ronaldo-777`). Fine for a local prototype, but change them and state this clearly in the deployment section.
4. **Phone field has no format hint/validation** — add a pattern or helper text (minor polish).
5. **Heavy decorative SVG/animation** on the homepage — confirm a `prefers-reduced-motion` fallback exists for accessibility marks.

### B. Requirements alignment (important for marks — see §5)
6. **Your delivered product is far bigger than your signed Requirements Specification.** Your signed spec only lists inquiry + demo booking + admin CRUD + totals, but you built chatbot, events, feedback, analytics, exports, etc. Graders test the product *against the agreed requirements*, so update the signed spec (or add an approved "scope expansion") to list every feature you actually built. Otherwise great work goes uncredited and looks unplanned.

### C. Analytics polish (optional, lifts "Exceptional")
7. **Charts are CSS bars.** They work, but adding a real charting library (e.g. Chart.js) for a pie chart of interest mix and a bar chart of demand-by-country makes the analytics visibly professional and matches the assignment's language about bar/pie charts. Single-file, CDN-free options are fine.

---

## 4. The 65% you still need — Portfolio Report (PDF) checklist

The website cannot score these; they are written/produced evidence. Your own `documentation/assignment-compliance-checklist.md` already flags most of these — good awareness.

- [ ] **Front cover** — module code (CET333), project title, your name, student ID.
- [ ] **Contents page with page numbers** (mandatory).
- [ ] **1. Requirements Specification** — signed by client. ✅ You have this, but reconcile it with the full product (see B6).
- [ ] **2. Planning Documentation** — Gantt/schedule with tasks, effort, timescales, **and evidence of revisions** during the project. *Not yet visible.*
- [ ] **3. Client Contact Record Sheets — 3 signed meetings.** You currently show **2** (Initial Meeting + Progress Report). **Outstanding requires 3+.** Add a third (final review/sign-off) with bulleted action points.
- [ ] **4. Methodology** — justify your approach (e.g. iterative/Agile prototyping) and tools, in your own words, referenced to your plan. *Must be written.*
- [ ] **5. Solution Design Documentation** — wireframes, navigation flow, flowchart, DFD/UML, ER diagram, MySQL schema. *Your meeting notes promise these — put them in the PDF.*
- [ ] **6. Testing & Evaluation** — a test table mapping each functional + non-functional requirement to a test case, expected vs actual result, and screenshots. *Critical for the 20-mark section.*
- [ ] **7. Technical Deployment** — XAMPP setup steps, DB import, config, plus a production-deployment note (HTTPS, backups). A short screencast is recommended.
- [ ] **8. Critical Reflection** — honest reflection on what went well/badly, lessons learned, and future development.
- [ ] **Harvard references** for any external sources.
- [ ] **Task 2: Demonstration video** — a planned, error-free walkthrough that explicitly shows each agreed requirement being met.

---

## 5. Honest rating against the rubric

**Website / prototype quality (the 35-mark section): Outstanding (≈30–33/35), close to Exceptional.** It fully meets the client's needs with very few minor errors and shows a systematic, professional approach. Fixing §3A–B and adding real charts would push it to Exceptional.

**Overall module projection right now:**

| Section | Current standing | Why |
|---|---|---|
| Requirements & Client Engagement (10) | Very Good → Outstanding *if* you add the 3rd meeting & reconcile scope | Detailed signed spec, but only 2 meetings shown and spec under-states the build |
| Project Planning (10) | Cannot judge — produce schedule + revisions | Not present in repo |
| Methodology (15) | Cannot judge — must be written | Not present |
| Prototype Product (35) | **Outstanding / near-Exceptional** | Exceeds scenario, high code quality |
| Testing, Evaluation & Deployment (20) | At risk — only scaffolding exists | Needs real test evidence + deployment spec |
| Reflective Practice (10) | Cannot judge — must be written | Not present |

**Bottom line:** The part you control by coding is already at the level you want. **Your grade will be decided by the report.** If you complete the 8 report sections to the same standard as the website — especially 3 signed meetings, a real test table, and design diagrams — "Outstanding and above" is realistically achievable. If the report stays as scaffolding, the module mark will land far below the website's quality.

---

## 6. Suggested next-step priority order

1. Add the **3rd signed client meeting** and **reconcile the signed Requirements Spec** with the full product (highest marks-per-effort).
2. Build the **Testing & Evaluation table** (20 marks, currently the biggest risk).
3. Produce **design diagrams** (wireframes, flowchart, DFD, ER diagram) for the report.
4. Write **Methodology** + **Critical Reflection**.
5. Apply website fixes §3A (consent `required`, full-record edit, change default credentials).
6. Optional: add **Chart.js** analytics and record the **demo video**.
