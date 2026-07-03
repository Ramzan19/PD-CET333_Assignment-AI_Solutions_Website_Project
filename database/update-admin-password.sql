-- Run this in phpMyAdmin (SQL tab) on the ai_solutions_db database to change the
-- EXISTING admin password to "Ronaldo_777" without re-importing / wiping data.
-- (setup.sql already uses this hash for fresh installs.)

USE ai_solutions_db;

UPDATE admin_users
SET password_hash = '$2y$10$GI0EnOt3EKmtBlb6BsXLpusuQVrZZ4UE3FIYXuhgajEPToZAZsoES'
WHERE username = 'admin';

-- Login after running this:
--   Username: admin
--   Password: Ronaldo_777
