CREATE DATABASE IF NOT EXISTS ai_solutions_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ai_solutions_db;

DROP TABLE IF EXISTS chatbot_conversations;
DROP TABLE IF EXISTS event_registrations;
DROP TABLE IF EXISTS demo_bookings;
DROP TABLE IF EXISTS customer_inquiries;
DROP TABLE IF EXISTS visitor_feedback;
DROP TABLE IF EXISTS solution_reviews;
DROP TABLE IF EXISTS website_visits;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS admin_activity;
DROP TABLE IF EXISTS smtp_settings;
DROP TABLE IF EXISTS content_items;

CREATE TABLE admin_users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(100) NOT NULL UNIQUE,
 email VARCHAR(180) NULL,
 role VARCHAR(40) NOT NULL DEFAULT 'super_admin',
 mfa_enabled TINYINT(1) NOT NULL DEFAULT 0,
 mfa_secret VARCHAR(64) NULL,
 password_hash VARCHAR(255) NOT NULL,
 last_login DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_activity (
 id INT AUTO_INCREMENT PRIMARY KEY,
 admin_id INT NULL,
 username VARCHAR(100) NOT NULL DEFAULT '',
 role VARCHAR(40) NOT NULL DEFAULT '',
 action VARCHAR(80) NOT NULL,
 detail VARCHAR(255) NULL,
 ip_hash CHAR(64) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_admin_activity_created (created_at),
 INDEX idx_admin_activity_action (action)
);

CREATE TABLE customer_inquiries (
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150) NOT NULL,
 email VARCHAR(150) NOT NULL,
 phone VARCHAR(50) NOT NULL,
 company_name VARCHAR(150) NOT NULL,
 country VARCHAR(100) NOT NULL,
 solution_interest VARCHAR(150) NOT NULL DEFAULT 'General AI Solution',
 job_title VARCHAR(150) NOT NULL,
 job_details TEXT NOT NULL,
 status VARCHAR(50) NOT NULL DEFAULT 'New',
 priority VARCHAR(20) NOT NULL DEFAULT 'Normal',
 admin_note TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL
);

CREATE TABLE demo_bookings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150) NOT NULL,
 email VARCHAR(150) NOT NULL,
 phone VARCHAR(50) NOT NULL,
 company_name VARCHAR(150) NOT NULL,
 country VARCHAR(100) NOT NULL,
 preferred_date DATE NOT NULL,
 preferred_time TIME NOT NULL,
 demo_type VARCHAR(150) NOT NULL,
 notes TEXT NULL,
 status VARCHAR(50) NOT NULL DEFAULT 'Booked',
 admin_note TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL
);

CREATE TABLE chatbot_conversations (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_name VARCHAR(150) NOT NULL,
 email VARCHAR(150) NOT NULL,
 phone VARCHAR(50) NOT NULL,
 company_name VARCHAR(150) NULL,
 country VARCHAR(100) NOT NULL,
 topic VARCHAR(150) NOT NULL,
 chat_summary TEXT NOT NULL,
 handover_required TINYINT(1) NOT NULL DEFAULT 1,
 status VARCHAR(50) NOT NULL DEFAULT 'New',
 admin_note TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL
);

CREATE TABLE event_registrations (
 id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(150) NOT NULL,
 email VARCHAR(150) NOT NULL,
 phone VARCHAR(50) NOT NULL,
 company_name VARCHAR(150) NOT NULL,
 country VARCHAR(100) NOT NULL,
 event_name VARCHAR(200) NOT NULL,
 interest_area VARCHAR(150) NOT NULL,
 notes TEXT NULL,
 status VARCHAR(50) NOT NULL DEFAULT 'Registered',
 admin_note TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL
);

CREATE TABLE visitor_feedback (
 id INT AUTO_INCREMENT PRIMARY KEY,
 visitor_name VARCHAR(150) NOT NULL,
 email VARCHAR(150) NOT NULL,
 company_name VARCHAR(150) NULL,
 role_title VARCHAR(150) NULL,
 rating TINYINT UNSIGNED NOT NULL,
 message TEXT NOT NULL,
 status VARCHAR(50) NOT NULL DEFAULT 'New',
 admin_note TEXT NULL,
 is_featured TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL
);

CREATE TABLE website_visits (
 id INT AUTO_INCREMENT PRIMARY KEY,
 visitor_key VARCHAR(80) NOT NULL,
 session_key VARCHAR(128) NULL,
 page_path VARCHAR(255) NOT NULL,
 page_title VARCHAR(200) NULL,
 page_name VARCHAR(80) NULL,
 referrer VARCHAR(500) NULL,
 ip_hash CHAR(64) NULL,
 user_agent TEXT NULL,
 device_type VARCHAR(30) NOT NULL DEFAULT 'Desktop',
 browser_name VARCHAR(50) NOT NULL DEFAULT 'Unknown',
 visit_date DATE NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_visit_date (visit_date),
 INDEX idx_visitor_key (visitor_key),
 INDEX idx_page_path (page_path)
);

CREATE TABLE solution_reviews (
 id INT AUTO_INCREMENT PRIMARY KEY,
 solution_key VARCHAR(80) NOT NULL,
 solution_title VARCHAR(150) NOT NULL,
 reviewer_name VARCHAR(150) NOT NULL,
 email VARCHAR(150) NOT NULL,
 company_name VARCHAR(150) NULL,
 rating TINYINT UNSIGNED NOT NULL,
 comment TEXT NOT NULL,
 status VARCHAR(50) NOT NULL DEFAULT 'New',
 admin_note TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL,
 INDEX idx_solution_key (solution_key),
 INDEX idx_solution_status (status)
);

CREATE TABLE content_items (
 id INT AUTO_INCREMENT PRIMARY KEY,
 type VARCHAR(50) NOT NULL,
 title VARCHAR(200) NOT NULL,
 summary TEXT NOT NULL,
 image_path VARCHAR(255) NULL,
 event_date DATE NULL,
 is_published TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE smtp_settings (
 id TINYINT UNSIGNED PRIMARY KEY,
 enabled TINYINT(1) NOT NULL DEFAULT 0,
 host VARCHAR(180) NOT NULL DEFAULT '',
 port INT UNSIGNED NOT NULL DEFAULT 587,
 encryption VARCHAR(20) NOT NULL DEFAULT 'tls',
 username VARCHAR(180) NOT NULL DEFAULT '',
 password_value TEXT NULL,
 from_email VARCHAR(180) NOT NULL DEFAULT '',
 from_name VARCHAR(180) NOT NULL DEFAULT 'AI-Solutions Admin',
 updated_at DATETIME NULL
);

INSERT INTO smtp_settings(id, enabled, host, port, encryption, username, password_value, from_email, from_name, updated_at)
VALUES (1, 0, '', 587, 'tls', '', NULL, '', 'AI-Solutions Admin', NOW());

-- Local admin login. CHANGE BEFORE ANY PUBLIC DEPLOYMENT.
-- Username: admin
-- Password: Ronaldo_777
INSERT INTO admin_users (username, email, role, mfa_enabled, password_hash)
VALUES ('admin', 'admin@example.com', 'super_admin', 0, '$2y$10$GI0EnOt3EKmtBlb6BsXLpusuQVrZZ4UE3FIYXuhgajEPToZAZsoES');

INSERT INTO customer_inquiries (full_name,email,phone,company_name,country,solution_interest,job_title,job_details,status,priority)
VALUES
('John Doe','john@example.com','+977980000001','TechCorp','Nepal','Virtual Assistant','Operations Manager','Interested in AI-powered customer support automation.','New','Normal'),
('Sarah Johnson','sarah@example.com','+441234567890','DataWorks','UK','Data Analytics','Product Lead','Looking for analytics dashboard and workflow automation.','In Progress','High');

INSERT INTO demo_bookings (full_name,email,phone,company_name,country,preferred_date,preferred_time,demo_type,notes,status)
VALUES ('Aarav Kaur','aarav@example.com','+977980000002','Bright Retail','Nepal',DATE_ADD(CURDATE(), INTERVAL 7 DAY),'11:00:00','Virtual Assistant','Interested in e-commerce support chatbot.','Booked');

INSERT INTO event_registrations (full_name,email,phone,company_name,country,event_name,interest_area,notes,status)
VALUES ('Emily Brown','emily@example.com','+441234567891','NorthCloud Services','UK','Virtual Assistant Live Demo','Virtual Assistant','Wants to see the handover workflow during the event.','Registered');

INSERT INTO chatbot_conversations (user_name,email,phone,company_name,country,topic,chat_summary,handover_required,status)
VALUES ('Michael Lee','michael@example.com','+441234567892','Lee Solutions','UK','Sales Representative','Customer asked chatbot about pricing and requested human sales follow-up.',1,'New');

INSERT INTO visitor_feedback (visitor_name,email,company_name,role_title,rating,message,status,is_featured)
VALUES
('Priya Shrestha','priya@example.com','Sunderland Tech Ltd','Operations Manager',5,'The homepage makes the AI-Solutions offer clear, modern, and easy for our team to understand.','New',1),
('Daniel Carter','daniel@example.com','NorthCloud Services','IT Support Lead',5,'The assistant, demo request, and contact path feel simple enough for customers to act without confusion.','New',1);

INSERT INTO solution_reviews (solution_key,solution_title,reviewer_name,email,company_name,rating,comment,status)
VALUES
('retail-support-assistant','Retail support assistant','Aarav Kaur','aarav@example.com','Bright Retail',5,'Cut our routine support questions dramatically and handed complex cases to staff with full context.','Approved'),
('professional-services-dashboard','Professional services dashboard','Sarah Johnson','sarah@example.com','DataWorks',4,'A single clear view of inquiries and demo demand. Monthly reporting is far faster now.','Approved');
