-- ============================================================
-- DATABASE: result_db
-- Result Management System — Indus University
--
-- TABLES:
--   1. admin_users     — Admin login accounts
--   2. faculty_users   — Faculty login accounts
--   3. branches        — University branches (B.Sc IT, B.Tech, etc.)
--   4. students        — Student records (NO password — login via enrollment + CAPTCHA)
--   5. subjects        — Subjects with theory/practical max marks
--   6. exams           — Exam sessions with visibility control
--   7. results         — Student marks (theory mid/end + practical mid/end) + backlog
--   8. gpa_summary     — SGPA per student per exam
-- ============================================================

-- ==================== DROP OLD TABLES (if re-importing) ====================
-- Drop in reverse order to avoid foreign key errors
DROP TABLE IF EXISTS gpa_summary;
DROP TABLE IF EXISTS results;
DROP TABLE IF EXISTS exams;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS branches;
DROP TABLE IF EXISTS faculty_users;
DROP TABLE IF EXISTS admin_users;


-- ==================== TABLE 1: admin_users ====================
-- Stores admin login credentials
CREATE TABLE IF NOT EXISTS admin_users (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,       -- stored as MD5 hash (for demo only)
    full_name VARCHAR(100) NOT NULL
);

-- Default admin account (password = "password")
INSERT INTO admin_users (username, password, full_name) VALUES
('admin', MD5('password'), 'System Administrator');


-- ==================== TABLE 2: faculty_users ====================
-- Stores faculty login credentials
CREATE TABLE IF NOT EXISTS faculty_users (
    faculty_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL
);

-- Default faculty account
INSERT INTO faculty_users (username, password, full_name, department) VALUES
('sharma', MD5('password'), 'Prof. Sharma', 'Computer Science');


-- ==================== TABLE 3: branches ====================
-- University branches like B.Sc IT, B.Tech CSE, etc.
-- Admin can add/edit/delete branches
CREATE TABLE IF NOT EXISTS branches (
    branch_id INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(100) NOT NULL,     -- e.g. "B.Sc IT"
    branch_code VARCHAR(20) NOT NULL       -- e.g. "BSCIT"
);

-- Sample branches
INSERT INTO branches (branch_name, branch_code) VALUES
('B.Sc IT', 'BSCIT'),
('B.Tech CSE', 'BTECHCSE'),
('B.Tech CE', 'BTECHCE'),
('BCA', 'BCA');


-- ==================== TABLE 4: students ====================
-- Student records — NO password field
-- Login uses only enrollment_no + CAPTCHA
CREATE TABLE IF NOT EXISTS students (
    enrollment_no VARCHAR(20) PRIMARY KEY,       -- e.g. "22CS001"
    full_name VARCHAR(100) NOT NULL,
    branch_id INT NOT NULL,                      -- links to branches table
    course VARCHAR(100) NOT NULL,                -- e.g. "B.Tech"
    batch_year VARCHAR(10) NOT NULL,             -- e.g. "2022-2026"
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id)
);

-- Sample students (branch_id=2 means B.Tech CSE)
INSERT INTO students (enrollment_no, full_name, branch_id, course, batch_year) VALUES
('22CS001', 'Rahul Patel', 2, 'B.Tech', '2022-2026'),
('22CS002', 'Priya Shah', 2, 'B.Tech', '2022-2026');


-- ==================== TABLE 5: subjects ====================
-- Each subject has max marks for 4 sections:
--   Theory Mid, Theory End, Practical Mid, Practical End
-- Min marks = 33% of max marks (auto-calculated in PHP, not stored here)
CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    semester INT NOT NULL,
    credits INT NOT NULL,
    theory_mid_max INT NOT NULL DEFAULT 30,       -- max marks for theory mid-sem
    theory_end_max INT NOT NULL DEFAULT 70,       -- max marks for theory end-sem
    practical_mid_max INT NOT NULL DEFAULT 25,    -- max marks for practical mid-sem
    practical_end_max INT NOT NULL DEFAULT 25     -- max marks for practical end-sem
);

-- Sample subjects for semester 1 and 2
INSERT INTO subjects (subject_name, subject_code, semester, credits, theory_mid_max, theory_end_max, practical_mid_max, practical_end_max) VALUES
('Mathematics-I', 'MA101', 1, 4, 30, 70, 25, 25),
('Physics', 'PH101', 1, 3, 30, 70, 25, 25),
('Programming in C', 'CS101', 1, 4, 30, 70, 25, 25),
('Mathematics-II', 'MA201', 2, 4, 30, 70, 25, 25),
('Data Structures', 'CS201', 2, 4, 30, 70, 25, 25);


-- ==================== TABLE 6: exams ====================
-- Exam sessions — faculty controls visibility via status
--   draft     = hidden from students completely
--   scheduled = visible only after release_date
--   published = visible immediately
CREATE TABLE IF NOT EXISTS exams (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL,              -- e.g. "Semester 1 Exam"
    semester INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,            -- e.g. "2023-24"
    status ENUM('draft','scheduled','published') DEFAULT 'draft',
    release_date DATE DEFAULT NULL
);

-- Sample exams
INSERT INTO exams (exam_name, semester, academic_year, status) VALUES
('Semester 1 Exam', 1, '2023-24', 'published'),
('Semester 2 Exam', 2, '2023-24', 'published');


-- ==================== TABLE 7: results ====================
-- Stores marks for each student + subject + exam
-- 4 mark columns: theory_mid, theory_end, practical_mid, practical_end
-- is_backlog = 1 means this is a backlog/KT attempt
-- failed_parts = which parts the student failed (e.g. "theory_mid,practical_end")
CREATE TABLE IF NOT EXISTS results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_no VARCHAR(20) NOT NULL,
    subject_id INT NOT NULL,
    exam_id INT NOT NULL,
    theory_mid_marks INT DEFAULT 0,               -- marks obtained in theory mid-sem
    theory_end_marks INT DEFAULT 0,               -- marks obtained in theory end-sem
    practical_mid_marks INT DEFAULT 0,            -- marks obtained in practical mid-sem
    practical_end_marks INT DEFAULT 0,            -- marks obtained in practical end-sem
    total_obtained INT DEFAULT 0,                 -- sum of all 4 marks
    total_max INT DEFAULT 0,                      -- sum of all 4 max marks
    grade VARCHAR(5) DEFAULT '',                  -- O, A+, A, B+, B, C, D, F
    grade_points DECIMAL(4,2) DEFAULT 0,          -- 10, 9, 8, 7, 6, 5, 4, 0
    is_backlog TINYINT DEFAULT 0,                 -- 0 = regular, 1 = backlog attempt
    failed_parts VARCHAR(200) DEFAULT '',         -- which parts the student failed
    FOREIGN KEY (enrollment_no) REFERENCES students(enrollment_no),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id)
);

-- Sample results for Semester 1 (student 22CS001 — all pass)
INSERT INTO results (enrollment_no, subject_id, exam_id, theory_mid_marks, theory_end_marks, practical_mid_marks, practical_end_marks, total_obtained, total_max, grade, grade_points, is_backlog, failed_parts) VALUES
('22CS001', 1, 1, 25, 60, 20, 22, 127, 150, 'A', 8, 0, ''),
('22CS001', 2, 1, 22, 55, 18, 20, 115, 150, 'B+', 7, 0, ''),
('22CS001', 3, 1, 28, 65, 23, 24, 140, 150, 'A+', 9, 0, '');

-- Sample results for Semester 1 (student 22CS002 — failed practical_mid in Physics)
INSERT INTO results (enrollment_no, subject_id, exam_id, theory_mid_marks, theory_end_marks, practical_mid_marks, practical_end_marks, total_obtained, total_max, grade, grade_points, is_backlog, failed_parts) VALUES
('22CS002', 1, 1, 20, 50, 15, 18, 103, 150, 'B', 6, 0, ''),
('22CS002', 2, 1, 18, 45, 5, 15, 83, 150, 'F', 0, 0, 'practical_mid'),
('22CS002', 3, 1, 24, 58, 20, 21, 123, 150, 'A', 8, 0, '');


-- ==================== TABLE 8: gpa_summary ====================
-- Stores SGPA per student per exam
CREATE TABLE IF NOT EXISTS gpa_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_no VARCHAR(20) NOT NULL,
    exam_id INT NOT NULL,
    sgpa DECIMAL(4,2) DEFAULT 0,
    backlog_count INT DEFAULT 0,                  -- number of backlogs in this semester
    FOREIGN KEY (enrollment_no) REFERENCES students(enrollment_no),
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id)
);

-- Sample GPA data
INSERT INTO gpa_summary (enrollment_no, exam_id, sgpa, backlog_count) VALUES
('22CS001', 1, 8.09, 0),
('22CS002', 1, 4.67, 1);
