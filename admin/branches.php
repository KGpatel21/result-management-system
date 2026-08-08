<?php
// ============================================================
// FILE: admin/branches.php
// PURPOSE: Manage university branches (B.Sc IT, B.Tech CSE, etc.)
// ACTIONS: Add new branch, Delete existing branch
// ============================================================

require_once('../db.php');

// Security check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// ---------- ADD BRANCH ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_branch'])) {
    $branch_name = $_POST['branch_name'];
    $branch_code = strtoupper($_POST['branch_code']);  // Convert to uppercase

    $sql = "INSERT INTO branches (branch_name, branch_code) VALUES ('$branch_name', '$branch_code')";
    if (mysqli_query($conn, $sql)) {
        $message = "Branch added successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// ---------- DELETE BRANCH ----------
if (isset($_GET['delete'])) {
    $branch_id = $_GET['delete'];
    // Check if any students are using this branch
    $check_result = mysqli_query($conn, "SELECT COUNT(*) as c FROM students WHERE branch_id = '$branch_id'");
    $check = $check_result ? mysqli_fetch_assoc($check_result) : ['c' => 0];
    if ($check['c'] > 0) {
        $message = "Cannot delete! Students are assigned to this branch.";
    } else {
        mysqli_query($conn, "DELETE FROM branches WHERE branch_id = '$branch_id'");
        $message = "Branch deleted!";
    }
}

// Fetch all branches
$branches = mysqli_query($conn, "SELECT * FROM branches ORDER BY branch_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Branches - Indus University</title>
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
        <a href="branches.php" class="active">Branches</a>
        <a href="faculty.php">Faculty</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Manage Branches</h2>

        <?php if ($message != ''): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Add Branch Form -->
        <div class="form-inline">
            <h3 style="margin-bottom:15px;">Add New Branch</h3>
            <form method="POST">
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
                    <div class="form-group" style="flex:2; margin-bottom:0;">
                        <label>Branch Name</label>
                        <input type="text" name="branch_name" required placeholder="e.g. B.Sc IT">
                    </div>
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <label>Branch Code</label>
                        <input type="text" name="branch_code" required placeholder="e.g. BSCIT">
                    </div>
                    <button type="submit" name="add_branch" class="btn btn-success" style="height:42px;">Add Branch</button>
                </div>
            </form>
        </div>

        <!-- Branches Table -->
        <table class="data-table">
            <tr>
                <th>ID</th>
                <th>Branch Name</th>
                <th>Branch Code</th>
                <th>Action</th>
            </tr>
            <?php while ($branches && $row = mysqli_fetch_assoc($branches)): ?>
            <tr>
                <td><?php echo $row['branch_id']; ?></td>
                <td><?php echo $row['branch_name']; ?></td>
                <td><?php echo $row['branch_code']; ?></td>
                <td>
                    <a href="branches.php?delete=<?php echo $row['branch_id']; ?>"
                       class="btn btn-danger" style="padding:5px 10px; font-size:12px;"
                       onclick="return confirm('Delete this branch?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
