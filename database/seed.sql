-- GymTrack Sample Seed Data (English)
-- This script fills the schema with realistic demo records.
-- Safe to run multiple times: it clears existing data in dependency order.

USE gym_db;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM feedback;
DELETE FROM notifications;
DELETE FROM attendance_logs;
DELETE FROM maintenance_records;
DELETE FROM equipment;
DELETE FROM equipment_categories;
DELETE FROM class_reservations;
DELETE FROM class_schedules;
DELETE FROM classes;
DELETE FROM trainer_skills;
DELETE FROM skills;
DELETE FROM trainers;
DELETE FROM payments;
DELETE FROM subscriptions;
DELETE FROM packages;
DELETE FROM members;
DELETE FROM user_roles;
DELETE FROM roles;
DELETE FROM users;
DELETE FROM branches;

ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE roles AUTO_INCREMENT = 1;
ALTER TABLE user_roles AUTO_INCREMENT = 1;
ALTER TABLE branches AUTO_INCREMENT = 1;
ALTER TABLE members AUTO_INCREMENT = 1;
ALTER TABLE packages AUTO_INCREMENT = 1;
ALTER TABLE subscriptions AUTO_INCREMENT = 1;
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE trainers AUTO_INCREMENT = 1;
ALTER TABLE skills AUTO_INCREMENT = 1;
ALTER TABLE trainer_skills AUTO_INCREMENT = 1;
ALTER TABLE classes AUTO_INCREMENT = 1;
ALTER TABLE class_schedules AUTO_INCREMENT = 1;
ALTER TABLE class_reservations AUTO_INCREMENT = 1;
ALTER TABLE equipment_categories AUTO_INCREMENT = 1;
ALTER TABLE equipment AUTO_INCREMENT = 1;
ALTER TABLE maintenance_records AUTO_INCREMENT = 1;
ALTER TABLE attendance_logs AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE feedback AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- Branches
INSERT INTO branches (branch_name, address, city, phone, status) VALUES
('Central Branch', '12 Ataturk Ave', 'Istanbul', '+90 212 100 00 01', 'active'),
('West Branch', '45 Marina Street', 'Istanbul', '+90 212 100 00 02', 'active');

-- Users (1 admin, 1 manager, 3 members, 2 trainers)
-- password_hash values are placeholders for demo usage only.
INSERT INTO users (name, surname, email, phone, password_hash, status) VALUES
('Ozan', 'Icer', 'ozan.admin@gymtrack.local', '+90 555 100 00 01', '$2y$10$demoAdminHashxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'active'),
('Selin', 'Kaya', 'selin.manager@gymtrack.local', '+90 555 100 00 02', '$2y$10$demoManagerHashxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'active'),
('Mert', 'Demir', 'mert.member@gymtrack.local', '+90 555 100 00 03', '$2y$10$demoMemberHash1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'active'),
('Elif', 'Yildiz', 'elif.member@gymtrack.local', '+90 555 100 00 04', '$2y$10$demoMemberHash2xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'active'),
('Deniz', 'Acar', 'deniz.member@gymtrack.local', '+90 555 100 00 05', '$2y$10$demoMemberHash3xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'active'),
('Can', 'Arslan', 'can.trainer@gymtrack.local', '+90 555 100 00 06', '$2y$10$demoTrainerHash1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'active'),
('Irem', 'Sahin', 'irem.trainer@gymtrack.local', '+90 555 100 00 07', '$2y$10$demoTrainerHash2xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'active');

-- Roles and role assignments
INSERT INTO roles (role_name, description) VALUES
('admin', 'System administrator'),
('manager', 'Branch operations manager'),
('member', 'Gym member'),
('trainer', 'Fitness trainer');

INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 3),
(5, 3),
(6, 4),
(7, 4);

-- Members
INSERT INTO members (user_id, branch_id, birth_date, gender, emergency_contact, join_date, status) VALUES
(3, 1, '1998-05-12', 'male', 'Fatma Demir - +90 555 200 00 01', '2026-01-10 09:30:00', 'active'),
(4, 1, '2001-11-02', 'female', 'Yusuf Yildiz - +90 555 200 00 02', '2026-02-01 10:15:00', 'active'),
(5, 2, '1995-07-26', 'other', 'Seda Acar - +90 555 200 00 03', '2026-03-05 16:40:00', 'active');

-- Membership packages
INSERT INTO packages (package_name, duration_days, price, is_active) VALUES
('Monthly Basic', 30, 1200.00, 1),
('Quarterly Plus', 90, 3200.00, 1),
('Annual Pro', 365, 10800.00, 1);

-- Subscriptions
INSERT INTO subscriptions (member_id, package_id, start_date, end_date, status) VALUES
(1, 1, '2026-04-01', '2026-04-30', 'active'),
(2, 2, '2026-03-15', '2026-06-13', 'active'),
(3, 3, '2026-01-01', '2026-12-31', 'active');

-- Payments
INSERT INTO payments (subscription_id, amount, payment_method, status, paid_at) VALUES
(1, 1200.00, 'card', 'paid', '2026-04-01 11:10:00'),
(2, 3200.00, 'transfer', 'paid', '2026-03-15 13:25:00'),
(3, 10800.00, 'online', 'paid', '2026-01-01 08:55:00');

-- Trainers
INSERT INTO trainers (user_id, branch_id, specialization, availability_status) VALUES
(6, 1, 'Strength & Conditioning', 'active'),
(7, 2, 'Pilates & Mobility', 'active');

-- Skills and trainer skills
INSERT INTO skills (skill_name, category) VALUES
('Weight Training', 'Strength'),
('Pilates', 'Mobility'),
('HIIT', 'Cardio'),
('Nutrition Coaching', 'Wellness');

INSERT INTO trainer_skills (trainer_id, skill_id, level) VALUES
(1, 1, 'expert'),
(1, 3, 'advanced'),
(2, 2, 'expert'),
(2, 4, 'advanced');

-- Classes
INSERT INTO classes (trainer_id, branch_id, class_name, capacity, duration_min, level) VALUES
(1, 1, 'Functional Strength', 18, 60, 'intermediate'),
(1, 1, 'HIIT Burn', 20, 45, 'advanced'),
(2, 2, 'Morning Pilates', 16, 50, 'beginner');

-- Weekly schedules
INSERT INTO class_schedules (class_id, day_of_week, start_time, end_time, is_active) VALUES
(1, 'monday', '18:00:00', '19:00:00', 1),
(2, 'wednesday', '19:15:00', '20:00:00', 1),
(3, 'tuesday', '09:00:00', '09:50:00', 1),
(3, 'thursday', '09:00:00', '09:50:00', 1);

-- Reservations
INSERT INTO class_reservations (schedule_id, member_id, reserved_at, status) VALUES
(1, 1, '2026-04-14 12:00:00', 'reserved'),
(3, 2, '2026-04-14 12:10:00', 'attended'),
(2, 3, '2026-04-14 12:25:00', 'reserved');

-- Equipment and maintenance
INSERT INTO equipment_categories (category_name, description) VALUES
('Cardio Machines', 'Treadmills and bikes'),
('Strength Machines', 'Resistance and plate-loaded machines'),
('Free Weights', 'Dumbbells and barbells');

INSERT INTO equipment (branch_id, category_id, equipment_name, brand, purchase_date, status) VALUES
(1, 1, 'Treadmill X5', 'RunTech', '2024-06-10', 'active'),
(1, 2, 'Leg Press Pro', 'IronForm', '2023-11-22', 'maintenance'),
(2, 3, 'Adjustable Dumbbell Set', 'PowerLift', '2025-01-05', 'active'),
(2, 1, 'Air Bike Max', 'CardioCore', '2024-09-14', 'active');

INSERT INTO maintenance_records (equipment_id, maintenance_date, description, cost) VALUES
(2, '2026-04-10', 'Hydraulic pressure system check and replacement', 750.00);

-- Attendance logs
INSERT INTO attendance_logs (member_id, branch_id, check_in, check_out) VALUES
(1, 1, '2026-04-14 17:40:00', '2026-04-14 19:05:00'),
(2, 1, '2026-04-14 08:50:00', '2026-04-14 10:10:00'),
(3, 2, '2026-04-14 18:20:00', NULL);

-- Notifications
INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES
(3, 'Subscription Reminder', 'Your Monthly Basic plan expires in 7 days.', 'warning', 0, '2026-04-23 09:00:00'),
(6, 'Class Capacity Alert', 'HIIT Burn reached 90% capacity for this week.', 'info', 0, '2026-04-14 14:10:00'),
(1, 'Maintenance Completed', 'Leg Press Pro maintenance has been logged.', 'success', 1, '2026-04-10 18:30:00');

-- Feedback
INSERT INTO feedback (member_id, trainer_id, rating, comment, created_at) VALUES
(1, 1, 5, 'Great coaching style and clear instructions.', '2026-04-08 20:10:00'),
(2, 2, 4, 'Very motivating pilates sessions.', '2026-04-12 10:05:00');
