# Result Management System — Indus University

A simple Result Management System built with **HTML, CSS, JavaScript, PHP, and MySQL**.

## Features

- **3 User Roles:** Admin, Faculty, Student
- **Admin Panel:** Manage students, subjects, branches, exams, and faculty accounts
- **Faculty Panel:** Enter marks (Theory Mid/End + Practical Mid/End), auto-calculate grades & SGPA, backlog entry
- **Student Portal:** Login with enrollment number + CAPTCHA (no password), view detailed semester results
- **Result Visibility:** Draft / Scheduled (release date) / Published
- **Detailed Result Table:** Theory & Practical with Mid/End semester, Max/Min/Obtained columns
- **Fail Detection:** Any section < 33% min marks = FAIL (red highlight)
- **KT/Backlog System:** Retake results appear under original semester with BACKLOG tag
- **Grade Scale:** O(10), A+(9), A(8), B+(7), B(6), C(5), D(4), F(0)
- **CAPTCHA** on student login

## Setup (XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/)
2. Copy `result_management/` into `C:\xampp\htdocs\`
3. Start Apache and MySQL
4. phpMyAdmin → Create database `result_db` → Import `database.sql`
5. Open `http://localhost/result_management/`

## Default Logins

| Role    | Username / ID | Password |
|---------|---------------|----------|
| Admin   | admin         | password |
| Faculty | sharma        | password |
| Student | 22CS001       | (CAPTCHA only) |
| Student | 22CS002       | (CAPTCHA only) |
