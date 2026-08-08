<?php
// ============================================================
// FILE: admin/dashboard.php
// PURPOSE: Admin home page — menu cards + stats
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Indus University</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">
    </div>

    <div class="navbar">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="students.php">Students</a>
        <a href="subjects.php">Subjects</a>
        <a href="exams.php">Exams</a>
        <a href="branches.php">Branches</a>
        <a href="faculty.php">Faculty</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Welcome, <?php echo $_SESSION['admin_username']; ?>!</h2>

        <div class="card-grid">
            <a href="students.php" class="card">
                <div class="card-icon">&#128100;</div>
                <div class="card-title">Manage Students</div>
                <div class="card-desc">Add, edit, or delete student records</div>
            </a>
            <a href="subjects.php" class="card">
                <div class="card-icon">&#128214;</div>
                <div class="card-title">Manage Subjects</div>
                <div class="card-desc">Add subjects with theory/practical marks</div>
            </a>
            <a href="exams.php" class="card">
                <div class="card-icon">&#128221;</div>
                <div class="card-title">Manage Exams</div>
                <div class="card-desc">Create exam sessions per semester</div>
            </a>
            <a href="branches.php" class="card">
                <div class="card-icon">&#127963;</div>
                <div class="card-title">Manage Branches</div>
                <div class="card-desc">Add B.Sc IT, B.Tech, BCA, etc.</div>
            </a>
            <a href="faculty.php" class="card">
                <div class="card-icon">&#128105;&#8205;&#127979;</div>
                <div class="card-title">Manage Faculty</div>
                <div class="card-desc">Create faculty login accounts</div>
            </a>
        </div>

        <?php
        // Helper: safe count query — returns 0 if table doesn't exist
        function safe_count($conn, $table) {
            $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM $table");
            if ($r) { return mysqli_fetch_assoc($r)['c']; }
            return 0;
        }
        $students_count = safe_count($conn, 'students');
        $subjects_count = safe_count($conn, 'subjects');
        $exams_count = safe_count($conn, 'exams');
        $faculty_count = safe_count($conn, 'faculty_users');
        $branches_count = safe_count($conn, 'branches');
        ?>
        <div class="card-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#3498db;"><?php echo $students_count; ?></div>
                <div class="stat-label">Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#27ae60;"><?php echo $subjects_count; ?></div>
                <div class="stat-label">Subjects</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#f39c12;"><?php echo $exams_count; ?></div>
                <div class="stat-label">Exams</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#9b59b6;"><?php echo $branches_count; ?></div>
                <div class="stat-label">Branches</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#e74c3c;"><?php echo $faculty_count; ?></div>
                <div class="stat-label">Faculty</div>
            </div>
        </div>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
