<?php
// ============================================================
// FILE: student/login.php
// PURPOSE: Student login page
// Login requires ONLY: Enrollment Number + CAPTCHA (NO password)
// ============================================================

require_once('../db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enrollment = $_POST['enrollment_no'];
    $captcha_input = $_POST['captcha'];

    // Step 1: Verify CAPTCHA
    if (!isset($_SESSION['captcha']) || strtolower($captcha_input) != strtolower($_SESSION['captcha'])) {
        $error = "Wrong CAPTCHA! Please try again.";
    } else {
        // Step 2: Check if enrollment number exists (no password needed)
        $sql = "SELECT * FROM students WHERE enrollment_no = '$enrollment'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $student = mysqli_fetch_assoc($result);
            $_SESSION['student_logged_in'] = true;
            $_SESSION['enrollment_no'] = $student['enrollment_no'];
            $_SESSION['student_name'] = $student['full_name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid enrollment number!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Indus University</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">
    </div>

    <div class="form-box">
        <h2>Student Login</h2>

        <?php if ($error != ''): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Enrollment Number</label>
                <input type="text" name="enrollment_no" required placeholder="e.g. 22CS001">
            </div>

            <!-- CAPTCHA Section -->
            <div class="form-group">
                <label>CAPTCHA Validation</label>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:5px;">
                    <img src="../captcha.php" id="captcha-img" style="border:1px solid #ddd; border-radius:4px;">
                    <a href="#" onclick="document.getElementById('captcha-img').src='../captcha.php?'+Math.random(); return false;"
                       style="color:#3498db; font-size:20px; text-decoration:none;" title="Refresh CAPTCHA">&#8635;</a>
                </div>
                <input type="text" name="captcha" required placeholder="Type the characters shown above">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <div style="text-align:center; margin-top:15px;">
            <a href="../index.php" style="color:#3498db; font-size:13px;">Back to Home</a>
        </div>
    </div>

</body>
</html>
