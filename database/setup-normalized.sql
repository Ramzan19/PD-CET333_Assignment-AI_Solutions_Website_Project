-- =====================================================================
-- AI-Solutions — Normalised Relational Schema (matches the ER diagram)
-- ---------------------------------------------------------------------
-- This is the DESIGN-LEVEL schema that implements proper relationships
-- (1:N and M:N) around a central `customer` entity and an `event` entity.
--
-- NOTE: The current working prototype (database/setup.sql) stores the
-- customer name/email inline in each table. Adopting this normalised
-- schema requires the PHP form handlers to first look up / insert a
-- `customer` row (by email) and then store the related record with a
-- customer_id foreign key. Keep setup.sql for the running prototype and
-- use this file as the documented relational design / future migration.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS ai_solutions_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ai_solutions_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS admin_activity;
DROP TABLE IF EXISTS content_item;
DROP TABLE IF EXISTS website_visit;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS chatbot_conversation;
DROP TABLE IF EXISTS event_registration;
DROP TABLE IF EXISTS demo_booking;
DROP TABLE IF EXISTS inquiry;
DROP TABLE IF EXISTS event;
DROP TABLE IF EXISTS customer;
DROP TABLE IF EXISTS admin_user;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Core / parent entities
-- ---------------------------------------------------------------------
CREATE TABLE admin_user (
  admin_id       INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(100) NOT NULL,
  email          VARCHAR(180) NULL,
  role           VARCHAR(40)  NOT NULL DEFAULT 'super_admin',
  mfa_enabled    TINYINT(1)   NOT NULL DEFAULT 0,
  password_hash  VARCHAR(255) NOT NULL,
  last_login     DATETIME     NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customer (
  customer_id    INT AUTO_INCREMENT PRIMARY KEY,
  full_name      VARCHAR(150) NOT NULL,
  email          VARCHAR(150) NOT NULL,
  phone          VARCHAR(50)  NULL,
  company_name   VARCHAR(150) NULL,
  country        VARCHAR(100) NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_customer_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE event (
  event_id       INT AUTO_INCREMENT PRIMARY KEY,
  event_name     VARCHAR(200) NOT NULL,
  interest_area  VARCHAR(150) NULL,
  event_date     DATE         NULL,
  location       VARCHAR(150) NULL,
  description    TEXT         NULL,
  is_published   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Customer activity (each references customer 1:N; admin handles 0..N)
-- ---------------------------------------------------------------------
CREATE TABLE inquiry (
  inquiry_id        INT AUTO_INCREMENT PRIMARY KEY,
  customer_id       INT NOT NULL,
  handled_by        INT NULL,
  solution_interest VARCHAR(150) NOT NULL DEFAULT 'General AI Solution',
  job_title         VARCHAR(150) NOT NULL,
  job_details       TEXT NOT NULL,
  status            VARCHAR(50)  NOT NULL DEFAULT 'New',
  priority          VARCHAR(20)  NOT NULL DEFAULT 'Normal',
  admin_note        TEXT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NULL,
  CONSTRAINT fk_inquiry_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
  CONSTRAINT fk_inquiry_admin    FOREIGN KEY (handled_by)  REFERENCES admin_user(admin_id)  ON DELETE SET NULL,
  INDEX idx_inquiry_customer (customer_id),
  INDEX idx_inquiry_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE demo_booking (
  demo_id        INT AUTO_INCREMENT PRIMARY KEY,
  customer_id    INT NOT NULL,
  handled_by     INT NULL,
  preferred_date DATE NOT NULL,
  preferred_time TIME NOT NULL,
  demo_type      VARCHAR(150) NOT NULL,
  notes          TEXT NULL,
  status         VARCHAR(50) NOT NULL DEFAULT 'Booked',
  admin_note     TEXT NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NULL,
  CONSTRAINT fk_demo_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
  CONSTRAINT fk_demo_admin    FOREIGN KEY (handled_by)  REFERENCES admin_user(admin_id)  ON DELETE SET NULL,
  INDEX idx_demo_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Junction table: resolves the many-to-many between customer and event
CREATE TABLE event_registration (
  registration_id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id     INT NOT NULL,
  event_id        INT NOT NULL,
  notes           TEXT NULL,
  status          VARCHAR(50) NOT NULL DEFAULT 'Registered',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reg_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
  CONSTRAINT fk_reg_event    FOREIGN KEY (event_id)    REFERENCES event(event_id)       ON DELETE CASCADE,
  UNIQUE KEY uq_customer_event (customer_id, event_id),
  INDEX idx_reg_event (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE chatbot_conversation (
  conversation_id   INT AUTO_INCREMENT PRIMARY KEY,
  customer_id       INT NOT NULL,
  handled_by        INT NULL,
  topic             VARCHAR(150) NOT NULL,
  chat_summary      TEXT NOT NULL,
  handover_required TINYINT(1) NOT NULL DEFAULT 1,
  status            VARCHAR(50) NOT NULL DEFAULT 'New',
  admin_note        TEXT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_chat_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
  CONSTRAINT fk_chat_admin    FOREIGN KEY (handled_by)  REFERENCES admin_user(admin_id)  ON DELETE SET NULL,
  INDEX idx_chat_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE feedback (
  feedback_id  INT AUTO_INCREMENT PRIMARY KEY,
  customer_id  INT NOT NULL,
  rating       TINYINT UNSIGNED NOT NULL,
  message      TEXT NOT NULL,
  role_title   VARCHAR(150) NULL,
  is_featured  TINYINT(1) NOT NULL DEFAULT 0,
  status       VARCHAR(50) NOT NULL DEFAULT 'New',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_feedback_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE CASCADE,
  INDEX idx_feedback_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE website_visit (
  visit_id     INT AUTO_INCREMENT PRIMARY KEY,
  customer_id  INT NULL,                       -- nullable: most visits are anonymous
  visitor_key  VARCHAR(80) NOT NULL,
  session_key  VARCHAR(128) NULL,
  page_path    VARCHAR(255) NOT NULL,
  device_type  VARCHAR(30) NOT NULL DEFAULT 'Desktop',
  browser_name VARCHAR(50) NOT NULL DEFAULT 'Unknown',
  ip_hash      CHAR(64) NULL,
  visit_date   DATE NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_visit_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id) ON DELETE SET NULL,
  INDEX idx_visit_date (visit_date),
  INDEX idx_visit_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Content authored by an admin (1:N)
CREATE TABLE content_item (
  content_id   INT AUTO_INCREMENT PRIMARY KEY,
  author_id    INT NULL,
  type         VARCHAR(50) NOT NULL,
  title        VARCHAR(200) NOT NULL,
  summary      TEXT NOT NULL,
  image_path   VARCHAR(255) NULL,
  event_date   DATE NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_content_admin FOREIGN KEY (author_id) REFERENCES admin_user(admin_id) ON DELETE SET NULL,
  INDEX idx_content_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin audit log (admin_user 1:N admin_activity)
CREATE TABLE admin_activity (
  activity_id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id    INT NULL,
  action      VARCHAR(80) NOT NULL,
  detail      VARCHAR(255) NULL,
  ip_hash     CHAR(64) NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_admin FOREIGN KEY (admin_id) REFERENCES admin_user(admin_id) ON DELETE SET NULL,
  INDEX idx_activity_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Sample data demonstrating the relationships
-- ---------------------------------------------------------------------
INSERT INTO admin_user (username, email, role, mfa_enabled, password_hash)
VALUES ('admin', 'admin@example.com', 'super_admin', 0, '$2y$10$5xitGxrky05xg/6dhcPVG.ab0HpBthTQ1f0F0jyV3/ZWBIHgBVy5C');
-- (password: OMmXa1h#qKyL%UaBAW — change before deployment)

INSERT INTO customer (full_name, email, phone, company_name, country) VALUES
 ('John Doe','john@example.com','+977980000001','TechCorp','Nepal'),
 ('Sarah Johnson','sarah@example.com','+441234567890','DataWorks','UK');

INSERT INTO event (event_name, interest_area, event_date, location, description) VALUES
 ('Virtual Assistant Live Demo','Virtual Assistant', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Online','Live walkthrough of the AI assistant and handover workflow.');

INSERT INTO inquiry (customer_id, handled_by, solution_interest, job_title, job_details, status)
VALUES (1, 1, 'Virtual Assistant', 'Operations Manager', 'Interested in AI-powered customer support automation.', 'New');

INSERT INTO demo_booking (customer_id, handled_by, preferred_date, preferred_time, demo_type, notes, status)
VALUES (2, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '11:00:00', 'Data Analytics', 'Looking for analytics dashboard.', 'Booked');

INSERT INTO event_registration (customer_id, event_id, status) VALUES (1, 1, 'Registered'), (2, 1, 'Registered');

INSERT INTO chatbot_conversation (customer_id, handled_by, topic, chat_summary, handover_required, status)
VALUES (2, 1, 'Sales Representative', 'Customer asked about pricing and requested human sales follow-up.', 1, 'New');

INSERT INTO feedback (customer_id, rating, message, role_title, is_featured)
VALUES (1, 5, 'The homepage makes the AI-Solutions offer clear and easy to understand.', 'Operations Manager', 1);

INSERT INTO content_item (author_id, type, title, summary)
VALUES (1, 'article', 'Designing Practical AI Assistants', 'How AI-Solutions designs assistants around real business workflows.');
