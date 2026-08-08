<?php
// ============================================================
// FILE: faculty/dashboard.php
// PURPOSE: Faculty home page — Enter Results + Publish Results
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['faculty_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Indus University</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">

    </div>

    <div class="navbar">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="enter_result.php">Enter Results</a>
        <a href="publish_result.php">Publish Results</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Welcome, <?php echo $_SESSION['faculty_name']; ?>!</h2>

        <div class="card-grid">
            <a href="enter_result.php" class="card">
                <div class="card-icon">&#9997;</div>
                <div class="card-title">Enter Results</div>
                <div class="card-desc">Enter theory + practical marks for each subject</div>
            </a>
            <a href="publish_result.php" class="card">
                <div class="card-icon">&#128197;</div>
                <div class="card-title">Publish Results</div>
                <div class="card-desc">Set release date or publish results instantly</div>
            </a>
        </div>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
