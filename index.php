<?php
// ============================================================
// FILE: index.php
// PURPOSE: Landing page — shows 3 portal links: Admin, Faculty, Student
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indus University - Result Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- HEADER: Indus University logo + title -->
    <div class="header">
        <img src="img/logo.png" alt="Indus University Logo">
    </div>

    <div class="container">

        <div class="landing-hero">
            <h1>Welcome to Result Management System</h1>
            <p>Select your portal to continue</p>
        </div>

        <div class="portal-grid">

            <a href="admin/login.php" class="portal-card">
                <div class="portal-icon">&#128736;</div>
                <h3>Admin Portal</h3>
                <p>Manage students, subjects, branches, faculty accounts, and exam settings</p>
            </a>

            <a href="faculty/login.php" class="portal-card">
                <div class="portal-icon">&#128105;&#8205;&#127979;</div>
                <h3>Faculty Portal</h3>
                <p>Enter student marks, set release dates, and publish results</p>
            </a>

            <a href="student/login.php" class="portal-card">
                <div class="portal-icon">&#127891;</div>
                <h3>Student Portal</h3>
                <p>View your semester-wise grade history and results</p>
            </a>

        </div>
    </div>

    <div class="footer">
        &copy; 2026 Indus University | Result Management System
    </div>

</body>
</html>
