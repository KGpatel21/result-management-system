<?php
// ============================================================
// FILE: faculty/login.php
// PURPOSE: Faculty login page
// Same logic as admin login, but checks faculty_users table
// ============================================================

require_once('../db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check faculty_users table for matching username + password
    $sql = "SELECT * FROM faculty_users WHERE username = '$username' AND password = MD5('$password')";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        // Login successful!
        $faculty = mysqli_fetch_assoc($result);

        // Save faculty info in session
        $_SESSION['faculty_logged_in'] = true;
        $_SESSION['faculty_id'] = $faculty['faculty_id'];
        $_SESSION['faculty_name'] = $faculty['full_name'];

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Login - Indus University</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">

    </div>

    <div class="form-box">
        <h2>Faculty Login</h2>

        <?php if ($error != ''): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter faculty username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <div style="text-align:center; margin-top:15px;">
            <a href="../index.php" style="color:#3498db; font-size:13px;">Back to Home</a>
        </div>
    </div>

</body>
</html>
