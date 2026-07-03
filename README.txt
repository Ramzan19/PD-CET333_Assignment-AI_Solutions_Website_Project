AI-Solutions Website Project
Professional PHP/XAMPP prototype

INSTALLATION IN XAMPP
1. Copy the folder AI_Solutions_Website_Project into: C:/xampp/htdocs/
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin
4. Import database/setup.sql
5. Open http://localhost/AI_Solutions_Website_Project/index.php

ADMIN LOGIN FOR LOCAL TESTING
Username: admin
Password: Ronaldo-777

OPTIONAL AI ASSISTANT API
AI-Solutions Assistant works locally by default through chatbot-api.php and includes an optional server-side adapter for OpenAI-compatible chat APIs.
For Pollinations, set these environment values before starting Apache:
AI_ASSISTANT_API_URL=https://gen.pollinations.ai/v1/chat/completions
AI_ASSISTANT_API_KEY=your_pollinations_api_key
AI_ASSISTANT_MODEL=openai

SECURITY NOTES
- Change the default admin password before any public deployment.
- Keep database/setup.sql and admin credentials out of public sharing.
- The site now uses prepared statements, CSRF tokens on forms, safer sessions, security headers, POST-only deletes, basic login throttling, and Apache rules to block direct access to includes/database files.
- For production, create a dedicated MySQL user instead of using root with an empty password.

FEATURES
- Professional home page with custom AI hero visual
- Services / software solutions
- Events, insights gallery, and Join our Events registration
- Contact Us inquiry form
- Schedule demo form
- AI assistant interface
- Human support handover / lead capture
- Password-protected admin login
- Admin dashboard
- Customer inquiries, demo bookings, and chatbot leads
- Event registration records
- Articles/news page
- Solutions filtering by industry and capability
- Calendar downloads for events
- Cookie consent, privacy policy, and terms page
- View, edit, and delete records
- CSV/PDF exports for admin records
- Monthly admin performance reports and admin activity log
- Email export action with local outbox fallback for XAMPP when live SMTP is not configured
- Visitor, country, interest, feedback, demo, event, assistant, and prototype demand analytics
- MySQL database storage
- Responsive UI design

REAL EMAIL SETUP
1. Login to the admin area.
2. Open SMTP in the admin navigation.
3. Enable real SMTP sending.
4. Enter your provider details, for example:
   - Gmail: smtp.gmail.com, port 587, TLS / STARTTLS
   - Outlook: smtp.office365.com, port 587, TLS / STARTTLS
5. Use an app password if your provider requires two-step verification.
6. Click Save and Send Test.

ASSIGNMENT ALIGNMENT
- Built for the AI-Solutions web prototype scenario.
- Captures customer name, email, phone, company, country, solution interest, job details, demo requests, event registrations, and chatbot handovers.
- Customers do not create accounts or passwords.
- Admin data is password-protected and includes inquiry counts, demo counts, event counts, chatbot leads, feedback ratings, visitor tracking, country analysis, and interest analysis.
- Documentation templates are in the documentation folder to help prepare the portfolio report and demonstration script.

IMPORTANT
Customers do not create accounts or passwords. Only the admin area is password-protected.
