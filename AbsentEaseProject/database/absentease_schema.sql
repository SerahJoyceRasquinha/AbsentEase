-- =====================================================================
--  AbsentEase - database schema (version 2)
--  ---------------------------------------------------------------------
--  You normally never run this by hand: START_ABSENTEASE.bat runs
--  database/setup_database.php, which creates these tables on a fresh
--  install AND upgrades an older AbsentEase database to this version.
--
--  You can still import this file in phpMyAdmin for a FRESH install.
--
--  Design rules (why the tables look like this):
--   * Every fact is stored ONCE. Anything that can be worked out
--     (a student's class teacher, a class's semester) is calculated,
--     not copied, so copies can never disagree.
--   * FOREIGN KEYS with ON DELETE / ON UPDATE CASCADE make MySQL itself
--     clean up: delete a student -> their login and OD requests go too;
--     change a roll number -> it changes everywhere.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS absenteasev02complex
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE absenteasev02complex;

-- ---------------------------------------------------------------------
-- MANAGER (admin). Password = bcrypt hash.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS manager_credentials (
  email    VARCHAR(150) NOT NULL PRIMARY KEY,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TEACHERS
--   verifiable_teacher_details = added by the manager (whitelist)
--   teacher_credentials        = created when the teacher signs up
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS verifiable_teacher_details (
  username VARCHAR(100) NOT NULL PRIMARY KEY,
  email    VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_credentials (
  username VARCHAR(100) NOT NULL PRIMARY KEY,
  password VARCHAR(255) NOT NULL,
  CONSTRAINT fk_teacher_login_whitelist FOREIGN KEY (username)
    REFERENCES verifiable_teacher_details (username) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- STUDENTS
--   Roll number format: YY + 4-letter course + NN  (e.g. 26csmm01)
--   The first 6 characters (26csmm) are the student's CLASS.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS verifiable_student_details (
  student_rollno           VARCHAR(8)   NOT NULL PRIMARY KEY,
  student_course           VARCHAR(10)  NOT NULL,
  student_email            VARCHAR(150) NOT NULL UNIQUE,
  course_year_start        INT          NOT NULL,
  course_year_end          INT          NOT NULL,
  semester_duration        TEXT         NOT NULL,
  student_current_semester INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_credentials (
  rollno   VARCHAR(8)   NOT NULL PRIMARY KEY,
  password VARCHAR(255) NOT NULL,
  CONSTRAINT fk_student_login_whitelist FOREIGN KEY (rollno)
    REFERENCES verifiable_student_details (student_rollno) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- CLASS TEACHER: one teacher per class, one class per teacher.
--   timetable = the class's lecture hours, e.g. "monday[2-3-4],tuesday[4-6]"
--   Students can only ask for OD in these hours.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS class_teacher_details (
  teacher_username VARCHAR(100) NOT NULL PRIMARY KEY,
  year_course      VARCHAR(6)   NOT NULL UNIQUE,
  timetable        TEXT         NOT NULL,
  submission_date  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_class_teacher_whitelist FOREIGN KEY (teacher_username)
    REFERENCES verifiable_teacher_details (username) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- OD REQUESTS
--   status: pending -> approved / rejected   (decided by the class teacher)
--   The reviewing teacher is NOT stored while pending: the request goes to
--   whoever is the class teacher of the student's class at that moment.
--   reviewed_by keeps the name of the teacher who decided (history).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS od_requests (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  student_rollno  VARCHAR(8)   NOT NULL,
  event_name      VARCHAR(150) NOT NULL,
  reason          VARCHAR(500) NULL,
  proof_link      VARCHAR(500) NOT NULL,
  status          VARCHAR(10)  NOT NULL DEFAULT 'pending',
  submission_date TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_by     VARCHAR(100) NULL,
  reviewed_at     DATETIME     NULL,
  review_remark   VARCHAR(500) NULL,
  INDEX idx_od_status (status),
  CONSTRAINT fk_od_request_student FOREIGN KEY (student_rollno)
    REFERENCES verifiable_student_details (student_rollno) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- One row per (date, hour) asked for. Replaces the old "monday[3-4]" + "02-11-2026"
-- text pair, which had to be matched up by position.
CREATE TABLE IF NOT EXISTS od_request_slots (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  od_request_id INT     NOT NULL,
  od_date       DATE    NOT NULL,
  hour          TINYINT NOT NULL,
  UNIQUE KEY uq_slot_per_request (od_request_id, od_date, hour),
  INDEX idx_slot_date (od_date),
  CONSTRAINT fk_slot_request FOREIGN KEY (od_request_id)
    REFERENCES od_requests (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Default manager, only when there is none:  admin@absentease.com / admin123
-- ---------------------------------------------------------------------
INSERT INTO manager_credentials (email, password)
SELECT 'admin@absentease.com', '$2y$10$vLxBDuGEn8dqEcb6Y3.ta./Pbw2fWLBypUMPG9n/BtSXKSy0OaA2m'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM manager_credentials);
