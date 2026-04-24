-- GymTrack Sample Seed Data (English)
-- 35 branches, branded naming, real person names, and high-volume records.
-- Safe to run multiple times.

USE gym_db;

SET SQL_SAFE_UPDATES = 0;
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

-- 35 branches (Location + Brand)
INSERT INTO branches (branch_name, address, city, phone, status) VALUES
('Kadikoy FitSphere', 'Moda Caddesi No:14', 'Istanbul', '+90 216 520 00 01', 'active'),
('Besiktas FitSphere', 'Barbaros Bulvari No:88', 'Istanbul', '+90 212 520 00 02', 'active'),
('Sisli FitSphere', 'Halaskargazi Caddesi No:121', 'Istanbul', '+90 212 520 00 03', 'active'),
('Uskudar FitSphere', 'Hakimiyeti Milliye No:31', 'Istanbul', '+90 216 520 00 04', 'active'),
('Bakirkoy FitSphere', 'Ebuzziya Caddesi No:45', 'Istanbul', '+90 212 520 00 05', 'active'),
('Fatih FitSphere', 'Fevzipasa Caddesi No:74', 'Istanbul', '+90 212 520 00 06', 'active'),
('Kartal FitSphere', 'Ankara Caddesi No:206', 'Istanbul', '+90 216 520 00 07', 'active'),
('Pendik FitSphere', 'Sahil Yolu No:12', 'Istanbul', '+90 216 520 00 08', 'active'),
('Maltepe FitSphere', 'Bagdat Caddesi No:312', 'Istanbul', '+90 216 520 00 09', 'active'),
('Atasehir FitSphere', 'Kayisdagi Caddesi No:59', 'Istanbul', '+90 216 520 00 10', 'active'),
('Sariyer FitSphere', 'Buyukdere Caddesi No:178', 'Istanbul', '+90 212 520 00 11', 'active'),
('Eyupsultan FitSphere', 'Alibeykoy Yolu No:67', 'Istanbul', '+90 212 520 00 12', 'active'),
('Beylikduzu FitSphere', 'Cumhuriyet Mah. No:90', 'Istanbul', '+90 212 520 00 13', 'active'),
('Basaksehir FitSphere', '5. Etap No:103', 'Istanbul', '+90 212 520 00 14', 'active'),
('Kagithane FitSphere', 'Cendere Caddesi No:40', 'Istanbul', '+90 212 520 00 15', 'active'),
('Umraniye FitSphere', 'Alemdag Caddesi No:151', 'Istanbul', '+90 216 520 00 16', 'active'),
('Tuzla FitSphere', 'Postane Mah. No:5', 'Istanbul', '+90 216 520 00 17', 'active'),
('Avcilar FitSphere', 'Universite Mah. No:27', 'Istanbul', '+90 212 520 00 18', 'active'),
('Gaziosmanpasa FitSphere', 'Merkez Mah. No:61', 'Istanbul', '+90 212 520 00 19', 'active'),
('Zeytinburnu FitSphere', 'Seyitnizam Caddesi No:35', 'Istanbul', '+90 212 520 00 20', 'active'),
('Cankaya FitSphere', 'Tunali Hilmi No:77', 'Ankara', '+90 312 520 00 21', 'active'),
('Kecioren FitSphere', 'Fatih Caddesi No:62', 'Ankara', '+90 312 520 00 22', 'active'),
('Yenimahalle FitSphere', 'Ivedik Caddesi No:29', 'Ankara', '+90 312 520 00 23', 'active'),
('Etimesgut FitSphere', 'Istasyon Caddesi No:40', 'Ankara', '+90 312 520 00 24', 'active'),
('Konak FitSphere', 'Gazi Bulvari No:56', 'Izmir', '+90 232 520 00 25', 'active'),
('Karsiyaka FitSphere', 'Girne Bulvari No:18', 'Izmir', '+90 232 520 00 26', 'active'),
('Bornova FitSphere', 'Kazim Dirik Mah. No:33', 'Izmir', '+90 232 520 00 27', 'active'),
('Muratpasa FitSphere', 'Isiklar Caddesi No:29', 'Antalya', '+90 242 520 00 28', 'active'),
('Konyaalti FitSphere', 'Akdeniz Bulvari No:42', 'Antalya', '+90 242 520 00 29', 'active'),
('Kepez FitSphere', 'Varsak Yolu No:11', 'Antalya', '+90 242 520 00 30', 'active'),
('Nilufer FitSphere', 'FSM Bulvari No:104', 'Bursa', '+90 224 520 00 31', 'active'),
('Osmangazi FitSphere', 'Altiparmak Caddesi No:68', 'Bursa', '+90 224 520 00 32', 'active'),
('Yildirim FitSphere', 'Ankara Yolu No:205', 'Bursa', '+90 224 520 00 33', 'active'),
('Seyhan FitSphere', 'Ataturk Caddesi No:83', 'Adana', '+90 322 520 00 34', 'active'),
('Izmit FitSphere', 'Yuruyus Yolu No:91', 'Kocaeli', '+90 262 520 00 35', 'active');

-- Base users
INSERT INTO users (name, surname, email, phone, password_hash, status) VALUES
('Ozan', 'Icer', 'ozan.admin@fitsphere.local', '+90 555 100 00 01', '$2y$12$X/jWhVLFiMBiIJe5QkXSveLxJ8z2V4P1sUJ/HeMojbmW9cT5cXrmy', 'active'),
('Selin', 'Kaya', 'selin.manager@fitsphere.local', '+90 555 100 00 02', '$2y$12$WeMdr9WmnAD1tRDrJDg2tuBbrGBAfEhuA3oCMFVvPwA3LeksUgasW', 'active'),
('Sevval', '', 'sevval.admin@fitsphere.local', '+90 555 100 00 03', '$2y$12$tBrr6DsWwE.5HgLStARNXO6/l7vDBtTSNYjBGOMSowrCxLdbGZCWy', 'active'),
('Beren', '', 'beren.admin@fitsphere.local', '+90 555 100 00 04', '$2y$12$jgk7x78/1UP87I.7KAJmf.5EWfBOwh8uRIPKwKyepaQcCjXFlrwbG', 'active'),
('Nisa', '', 'nisa.admin@fitsphere.local', '+90 555 100 00 05', '$2y$12$wzSBFyBmkI2O1sztpg.XY.xh56mpaBQTiJ5VweOMr9h3SZgds.ojG', 'active'),
('Nehir', '', 'nehir.admin@fitsphere.local', '+90 555 100 00 06', '$2y$12$5Xxl9BnJhDvJRjlORPZyXuTTPH1aQXkoSql6avkntlQ51g7GcraUu', 'active');

-- Roles
INSERT INTO roles (role_name, description) VALUES
('admin', 'System administrator'),
('manager', 'Branch operations manager'),
('member', 'Gym member'),
('trainer', 'Fitness trainer'),
('developer', 'Software developer / technical team');

DROP TEMPORARY TABLE IF EXISTS tmp_seq;
CREATE TEMPORARY TABLE tmp_seq (n INT PRIMARY KEY);
INSERT INTO tmp_seq (n)
SELECT (th.d * 1000) + (h.d * 100) + (t.d * 10) + o.d + 1 AS n
FROM
    (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) o
    CROSS JOIN
    (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t
    CROSS JOIN
    (SELECT 0 d UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) h
    CROSS JOIN
    (SELECT 0 d UNION ALL SELECT 1) th
WHERE (th.d * 1000) + (h.d * 100) + (t.d * 10) + o.d + 1 <= 1500;

DROP TEMPORARY TABLE IF EXISTS tmp_first_names;
CREATE TEMPORARY TABLE tmp_first_names (id INT PRIMARY KEY, name VARCHAR(100) NOT NULL);
INSERT INTO tmp_first_names (id, name) VALUES
(1,'Ahmet'),(2,'Mehmet'),(3,'Mustafa'),(4,'Ali'),(5,'Murat'),(6,'Can'),(7,'Emre'),(8,'Burak'),
(9,'Kerem'),(10,'Eren'),(11,'Omer'),(12,'Onur'),(13,'Deniz'),(14,'Hakan'),(15,'Serkan'),(16,'Tolga'),
(17,'Yusuf'),(18,'Furkan'),(19,'Kaan'),(20,'Baris'),(21,'Selin'),(22,'Elif'),(23,'Zeynep'),(24,'Ayse'),
(25,'Merve'),(26,'Derya'),(27,'Asli'),(28,'Ece'),(29,'Seda'),(30,'Gizem'),(31,'Irem'),(32,'Melis'),
(33,'Buse'),(34,'Esra'),(35,'Nazli'),(36,'Aylin'),(37,'Pelin'),(38,'Ceren'),(39,'Sinem'),(40,'Yagmur');

DROP TEMPORARY TABLE IF EXISTS tmp_last_names;
CREATE TEMPORARY TABLE tmp_last_names (id INT PRIMARY KEY, surname VARCHAR(100) NOT NULL);
INSERT INTO tmp_last_names (id, surname) VALUES
(1,'Yilmaz'),(2,'Kaya'),(3,'Demir'),(4,'Sahin'),(5,'Celik'),(6,'Aydin'),(7,'Arslan'),(8,'Dogan'),
(9,'Kilic'),(10,'Aslan'),(11,'Cetin'),(12,'Ozdemir'),(13,'Koc'),(14,'Kurt'),(15,'Ozkan'),(16,'Tas'),
(17,'Acar'),(18,'Polat'),(19,'Erdem'),(20,'Yildiz'),(21,'Bozkurt'),(22,'Guler'),(23,'Yavuz'),(24,'Aksoy'),
(25,'Karaca'),(26,'Korkmaz'),(27,'Ekinci'),(28,'Bulut'),(29,'Toprak'),(30,'Akin'),(31,'Ates'),(32,'Kaplan'),
(33,'Erol'),(34,'Bayrak'),(35,'Karaman'),(36,'Oz'),(37,'Sari'),(38,'Akca'),(39,'Ucar'),(40,'Keskin');

-- Trainer users with real names
INSERT INTO users (name, surname, email, phone, password_hash, status)
SELECT
    fn.name,
    ln.surname,
    CONCAT('trainer', LPAD(s.n, 4, '0'), '@fitsphere.local'),
    CONCAT('+90 530 ', LPAD(7000000 + s.n, 7, '0')),
    '$2y$10$demoTrainerHashxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    CASE WHEN s.n % 13 = 0 THEN 'inactive' ELSE 'active' END
FROM tmp_seq s
JOIN tmp_first_names fn ON fn.id = ((s.n - 1) % 40) + 1
JOIN tmp_last_names ln ON ln.id = (((s.n - 1) DIV 7) % 40) + 1
WHERE s.n <= 140;

-- Member users with real names
INSERT INTO users (name, surname, email, phone, password_hash, status)
SELECT
    fn.name,
    ln.surname,
    CONCAT('member', LPAD(s.n, 5, '0'), '@fitsphere.local'),
    CONCAT('+90 532 ', LPAD(6000000 + s.n, 7, '0')),
    '$2y$10$demoMemberHashxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    CASE WHEN s.n % 23 = 0 THEN 'inactive' ELSE 'active' END
FROM tmp_seq s
JOIN tmp_first_names fn ON fn.id = (((s.n + 11) - 1) % 40) + 1
JOIN tmp_last_names ln ON ln.id = ((((s.n + 5) - 1) DIV 5) % 40) + 1
WHERE s.n <= 1300;

-- Role mappings
INSERT INTO user_roles (user_id, role_id) VALUES (1, 1), (2, 2);

INSERT INTO user_roles (user_id, role_id) VALUES (3, 1), (4, 1), (5, 1), (6, 1);

INSERT INTO user_roles (user_id, role_id)
SELECT user_id, 4
FROM users
WHERE email LIKE 'trainer%@fitsphere.local';

INSERT INTO user_roles (user_id, role_id)
SELECT user_id, 3
FROM users
WHERE email LIKE 'member%@fitsphere.local';

-- Trainers
INSERT INTO trainers (user_id, branch_id, specialization, availability_status)
SELECT
    u.user_id,
    ((u.user_id - 1) % 35) + 1,
    CASE u.user_id % 6
        WHEN 0 THEN 'Strength & Conditioning'
        WHEN 1 THEN 'Pilates & Mobility'
        WHEN 2 THEN 'HIIT & Cardio'
        WHEN 3 THEN 'Functional Training'
        WHEN 4 THEN 'Weight Loss Coaching'
        ELSE 'Athletic Performance'
    END,
    CASE
        WHEN u.user_id % 17 = 0 THEN 'on_leave'
        WHEN u.user_id % 13 = 0 THEN 'inactive'
        ELSE 'active'
    END
FROM users u
WHERE u.email LIKE 'trainer%@fitsphere.local';

-- Members
INSERT INTO members (user_id, branch_id, birth_date, gender, emergency_contact, join_date, status)
SELECT
    u.user_id,
    ((u.user_id - 1) % 35) + 1,
    DATE_SUB('2006-12-31', INTERVAL (u.user_id % 7300) DAY),
    CASE u.user_id % 3 WHEN 0 THEN 'male' WHEN 1 THEN 'female' ELSE 'other' END,
    CONCAT('Emergency Contact - +90 533 ', LPAD(4000000 + (u.user_id % 10000), 7, '0')),
    DATE_SUB(NOW(), INTERVAL (u.user_id % 500) DAY),
    CASE
        WHEN u.user_id % 29 = 0 THEN 'suspended'
        WHEN u.user_id % 11 = 0 THEN 'inactive'
        ELSE 'active'
    END
FROM users u
WHERE u.email LIKE 'member%@fitsphere.local';

-- Membership packages
INSERT INTO packages (package_name, duration_days, price, is_active) VALUES
('Monthly Basic', 30, 1500.00, 1),
('Quarterly Plus', 90, 4200.00, 1),
('Semi-Annual Peak', 180, 7900.00, 1),
('Annual Pro', 365, 14500.00, 1);

-- Subscriptions
INSERT INTO subscriptions (member_id, package_id, start_date, end_date, status)
SELECT
    m.member_id,
    (m.member_id % 4) + 1,
    DATE_SUB(CURDATE(), INTERVAL (m.member_id % 60) DAY),
    CASE
        WHEN m.member_id % 10 = 0 THEN DATE_ADD(CURDATE(), INTERVAL ((m.member_id % 7) + 1) DAY)
        WHEN m.member_id % 12 = 0 THEN DATE_SUB(CURDATE(), INTERVAL ((m.member_id % 30) + 1) DAY)
        ELSE DATE_ADD(CURDATE(), INTERVAL (20 + (m.member_id % 220)) DAY)
    END,
    CASE WHEN m.member_id % 12 = 0 THEN 'expired' ELSE 'active' END
FROM members m
WHERE m.status IN ('active', 'inactive');

-- Payments
INSERT INTO payments (subscription_id, amount, payment_method, status, paid_at)
SELECT
    s.subscription_id,
    p.price,
    CASE s.subscription_id % 4
        WHEN 0 THEN 'cash'
        WHEN 1 THEN 'card'
        WHEN 2 THEN 'transfer'
        ELSE 'online'
    END,
    'paid',
    DATE_SUB(NOW(), INTERVAL (s.subscription_id % 90) DAY)
FROM subscriptions s
JOIN packages p ON p.package_id = s.package_id;

-- Skills
INSERT INTO skills (skill_name, category) VALUES
('Weight Training', 'Strength'),
('Pilates', 'Mobility'),
('HIIT', 'Cardio'),
('Nutrition Coaching', 'Wellness'),
('Mobility Recovery', 'Recovery'),
('Functional Conditioning', 'Performance'),
('Bodybuilding', 'Strength'),
('Posture Improvement', 'Wellness');

-- Trainer skills (2 per trainer)
INSERT IGNORE INTO trainer_skills (trainer_id, skill_id, level)
SELECT
    t.trainer_id,
    ((t.trainer_id % 8) + 1),
    CASE t.trainer_id % 4
        WHEN 0 THEN 'expert'
        WHEN 1 THEN 'advanced'
        WHEN 2 THEN 'intermediate'
        ELSE 'advanced'
    END
FROM trainers t;

INSERT IGNORE INTO trainer_skills (trainer_id, skill_id, level)
SELECT
    t.trainer_id,
    (((t.trainer_id + 3) % 8) + 1),
    CASE t.trainer_id % 3
        WHEN 0 THEN 'advanced'
        WHEN 1 THEN 'intermediate'
        ELSE 'expert'
    END
FROM trainers t;

-- Classes
INSERT INTO classes (trainer_id, branch_id, class_name, capacity, duration_min, level)
SELECT
    t.trainer_id,
    t.branch_id,
    CASE t.trainer_id % 7
        WHEN 0 THEN 'Total Body Strength'
        WHEN 1 THEN 'Metabolic Burn'
        WHEN 2 THEN 'Mobility Flow'
        WHEN 3 THEN 'Power Circuit'
        WHEN 4 THEN 'Core Performance'
        WHEN 5 THEN 'Pilates Reformer Basics'
        ELSE 'Athletic Conditioning'
    END,
    18 + (t.trainer_id % 16),
    45 + ((t.trainer_id % 4) * 15),
    CASE t.trainer_id % 3
        WHEN 0 THEN 'beginner'
        WHEN 1 THEN 'intermediate'
        ELSE 'advanced'
    END
FROM trainers t
WHERE t.availability_status = 'active';

-- Class schedules
INSERT INTO class_schedules (class_id, day_of_week, start_time, end_time, is_active)
SELECT
    c.class_id,
    CASE c.class_id % 7
        WHEN 0 THEN 'monday'
        WHEN 1 THEN 'tuesday'
        WHEN 2 THEN 'wednesday'
        WHEN 3 THEN 'thursday'
        WHEN 4 THEN 'friday'
        WHEN 5 THEN 'saturday'
        ELSE 'sunday'
    END,
    CASE c.class_id % 3
        WHEN 0 THEN '07:30:00'
        WHEN 1 THEN '18:00:00'
        ELSE '20:00:00'
    END,
    CASE c.class_id % 3
        WHEN 0 THEN '08:30:00'
        WHEN 1 THEN '19:00:00'
        ELSE '21:00:00'
    END,
    1
FROM classes c;

INSERT INTO class_schedules (class_id, day_of_week, start_time, end_time, is_active)
SELECT
    c.class_id,
    CASE c.class_id % 2 WHEN 0 THEN 'saturday' ELSE 'sunday' END,
    '10:00:00',
    '11:00:00',
    1
FROM classes c
WHERE c.class_id % 2 = 0;

DROP TEMPORARY TABLE IF EXISTS tmp_schedule_map;
CREATE TEMPORARY TABLE tmp_schedule_map AS
SELECT
    (@rn := @rn + 1) AS rn,
    cs.schedule_id
FROM class_schedules cs
JOIN (SELECT @rn := 0) vars
ORDER BY cs.schedule_id;

SET @schedule_total = (SELECT COUNT(*) FROM tmp_schedule_map);

-- Reservations
INSERT INTO class_reservations (schedule_id, member_id, reserved_at, status)
SELECT
    sm.schedule_id,
    m.member_id,
    DATE_SUB(NOW(), INTERVAL (m.member_id % 25) DAY),
    CASE
        WHEN m.member_id % 9 = 0 THEN 'attended'
        WHEN m.member_id % 13 = 0 THEN 'cancelled'
        ELSE 'reserved'
    END
FROM members m
JOIN tmp_schedule_map sm ON sm.rn = ((m.member_id % @schedule_total) + 1)
WHERE m.status = 'active'
LIMIT 1600;

-- Equipment setup
INSERT INTO equipment_categories (category_name, description) VALUES
('Cardio Machines', 'Treadmills and bikes'),
('Strength Machines', 'Resistance and plate-loaded machines'),
('Free Weights', 'Dumbbells and barbells'),
('Functional Zone', 'Rigs, battle ropes, and sleds'),
('Recovery Zone', 'Massage and stretching tools'),
('Studio Gear', 'Mats, balls, and reformer accessories');

INSERT INTO equipment (branch_id, category_id, equipment_name, brand, purchase_date, status)
SELECT
    b.branch_id,
    ((b.branch_id + s.slot) % 6) + 1,
    CASE s.slot
        WHEN 1 THEN CONCAT('Treadmill Series ', b.branch_id)
        WHEN 2 THEN CONCAT('Leg Press Unit ', b.branch_id)
        WHEN 3 THEN CONCAT('Functional Rack ', b.branch_id)
        WHEN 4 THEN CONCAT('Rower Station ', b.branch_id)
        ELSE CONCAT('Dumbbell Set ', b.branch_id)
    END,
    CASE s.slot
        WHEN 1 THEN 'RunTech'
        WHEN 2 THEN 'IronForm'
        WHEN 3 THEN 'CoreMotion'
        WHEN 4 THEN 'CardioCore'
        ELSE 'PowerLift'
    END,
    DATE_SUB(CURDATE(), INTERVAL ((b.branch_id * 50) + (s.slot * 23)) DAY),
    CASE
        WHEN s.slot = 3 AND b.branch_id % 7 = 0 THEN 'maintenance'
        WHEN s.slot = 5 AND b.branch_id % 11 = 0 THEN 'out_of_order'
        ELSE 'active'
    END
FROM branches b
JOIN (
    SELECT 1 AS slot
    UNION ALL SELECT 2
    UNION ALL SELECT 3
    UNION ALL SELECT 4
    UNION ALL SELECT 5
) s;

INSERT INTO maintenance_records (equipment_id, maintenance_date, description, cost)
SELECT
    e.equipment_id,
    DATE_SUB(CURDATE(), INTERVAL (e.equipment_id % 14) DAY),
    'Scheduled maintenance and performance check',
    500 + (e.equipment_id % 9) * 130
FROM equipment e
WHERE e.status = 'maintenance';

-- Attendance logs
INSERT INTO attendance_logs (member_id, branch_id, check_in, check_out)
SELECT
    m.member_id,
    m.branch_id,
    DATE_SUB(NOW(), INTERVAL (m.member_id % 21) DAY),
    DATE_ADD(DATE_SUB(NOW(), INTERVAL (m.member_id % 21) DAY), INTERVAL (55 + (m.member_id % 95)) MINUTE)
FROM members m
WHERE m.status = 'active'
LIMIT 1800;

-- Notifications
INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
SELECT
    u.user_id,
    CASE u.user_id % 4
        WHEN 0 THEN 'Subscription Reminder'
        WHEN 1 THEN 'Class Capacity Alert'
        WHEN 2 THEN 'Maintenance Notice'
        ELSE 'Welcome Back'
    END,
    CASE u.user_id % 4
        WHEN 0 THEN 'Your subscription is approaching expiry within 7 days.'
        WHEN 1 THEN 'One of your classes is almost full this week.'
        WHEN 2 THEN 'A maintenance task has been planned in your branch.'
        ELSE 'Great to see your activity increasing this month.'
    END,
    CASE u.user_id % 4
        WHEN 0 THEN 'warning'
        WHEN 1 THEN 'info'
        WHEN 2 THEN 'danger'
        ELSE 'success'
    END,
    CASE WHEN u.user_id % 3 = 0 THEN 1 ELSE 0 END,
    DATE_SUB(NOW(), INTERVAL (u.user_id % 12) DAY)
FROM users u
WHERE u.user_id <= 500;

-- Feedback
INSERT INTO feedback (member_id, trainer_id, rating, comment, created_at)
SELECT
    m.member_id,
    ((m.member_id % (SELECT COUNT(*) FROM trainers)) + 1),
    (m.member_id % 5) + 1,
    CASE m.member_id % 4
        WHEN 0 THEN 'Great trainer support and clear communication.'
        WHEN 1 THEN 'Program quality is strong and workouts are engaging.'
        WHEN 2 THEN 'Facility is clean and class scheduling is convenient.'
        ELSE 'Overall positive experience with noticeable progress.'
    END,
    DATE_SUB(NOW(), INTERVAL (m.member_id % 30) DAY)
FROM members m
WHERE m.status = 'active'
LIMIT 700;

DROP TEMPORARY TABLE IF EXISTS tmp_seq;
DROP TEMPORARY TABLE IF EXISTS tmp_first_names;
DROP TEMPORARY TABLE IF EXISTS tmp_last_names;
DROP TEMPORARY TABLE IF EXISTS tmp_schedule_map;

SET FOREIGN_KEY_CHECKS = 1;
SET SQL_SAFE_UPDATES = 1;
