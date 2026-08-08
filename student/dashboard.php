<?php
// ============================================================
// FILE: student/dashboard.php
// PURPOSE: Student home page — profile + link to results
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: login.php");
    exit();
}

$enrollment = $_SESSION['enrollment_no'];
$sql = "SELECT s.*, b.branch_name FROM students s
        LEFT JOIN branches b ON s.branch_id = b.branch_id
        WHERE s.enrollment_no = '$enrollment'";
$student_result = mysqli_query($conn, $sql);
$student = $student_result ? mysqli_fetch_assoc($student_result) : null;
if (!$student) {
    $student = ['full_name' => 'Unknown', 'enrollment_no' => $enrollment, 'course' => '-', 'branch_name' => '-', 'batch_year' => '-'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Indus University</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">

    </div>

    <div class="navbar">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="result.php">View Results</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Welcome, <?php echo $student['full_name']; ?>!</h2>

        <div class="result-header">
            <h2>Your Profile</h2>
            <p><strong>Enrollment No:</strong> <?php echo $student['enrollment_no']; ?></p>
            <p><strong>Course:</strong> <?php echo $student['course']; ?></p>
            <p><strong>Branch:</strong> <?php echo $student['branch_name']; ?></p>
            <p><strong>Batch Year:</strong> <?php echo $student['batch_year']; ?></p>
        </div>

        <div class="card-grid">
            <a href="result.php" class="card">
                <div class="card-icon">&#128202;</div>
                <div class="card-title">View Grade History</div>
                <div class="card-desc">See all your semester results, SGPA, and CGPA</div>
            </a>
        </div>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
