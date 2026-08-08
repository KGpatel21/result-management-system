<?php
// ============================================================
// FILE: admin/faculty.php
// PURPOSE: Admin can Add / View / Delete faculty accounts
// Faculty will use these accounts to log in to the Faculty Portal
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// ACTION: Add new faculty
if (isset($_POST['add_faculty'])) {
    $name = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $department = $_POST['department'];

    $sql = "INSERT INTO faculty_users (full_name, username, password, department)
            VALUES ('$name', '$username', MD5('$password'), '$department')";

    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">Faculty account created!</div>';
    } else {
        $message = '<div class="alert alert-error">Error: Username may already exist.</div>';
    }
}

// ACTION: Delete faculty
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM faculty_users WHERE faculty_id = '$id'");
    $message = '<div class="alert alert-success">Faculty deleted!</div>';
}

// Fetch all faculty
$faculty = mysqli_query($conn, "SELECT * FROM faculty_users ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">

    </div>

    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="students.php">Students</a>
        <a href="subjects.php">Subjects</a>
        <a href="exams.php">Exams</a>
        <a href="branches.php">Branches</a>
        <a href="faculty.php" class="active">Faculty</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Manage Faculty</h2>
        <?php echo $message; ?>

        <!-- ADD FACULTY FORM -->
        <div class="form-box" style="max-width:600px;">
            <h2>Add New Faculty</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="e.g. Prof. Sharma">
                </div>
                <div class="form-group">
                    <label>Username (for login)</label>
                    <input type="text" name="username" required placeholder="e.g. sharma">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Set faculty's login password">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" placeholder="e.g. Computer Science">
                </div>
                <button type="submit" name="add_faculty" class="btn btn-success btn-block">Add Faculty</button>
            </form>
        </div>

        <!-- TABLE: All Faculty -->
        <h3 style="margin:20px 0 10px;">All Faculty Members</h3>
        <table class="data-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Department</th>
                <th>Action</th>
            </tr>
            <?php while ($faculty && $row = mysqli_fetch_assoc($faculty)): ?>
            <tr>
                <td><?php echo $row['faculty_id']; ?></td>
                <td><?php echo $row['full_name']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['department']; ?></td>
                <td>
                    <a href="faculty.php?delete=<?php echo $row['faculty_id']; ?>" class="btn btn-danger" style="padding:5px 10px; font-size:12px;" onclick="return confirm('Delete this faculty?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
