<?php
// ============================================================
// FILE: admin/students.php
// PURPOSE: Admin can Add / View / Edit / Delete students
// Students now have branch_id (dropdown) instead of text branch
// No password field — students login with enrollment + CAPTCHA only
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// ---------- ADD STUDENT ----------
if (isset($_POST['add_student'])) {
    $enrollment = $_POST['enrollment_no'];
    $name = $_POST['full_name'];
    $course = $_POST['course'];
    $branch_id = $_POST['branch_id'];
    $batch = $_POST['batch_year'];

    $sql = "INSERT INTO students (enrollment_no, full_name, branch_id, course, batch_year)
            VALUES ('$enrollment', '$name', '$branch_id', '$course', '$batch')";

    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">Student added successfully!</div>';
    } else {
        $message = '<div class="alert alert-error">Error: Enrollment number may already exist.</div>';
    }
}

// ---------- DELETE STUDENT ----------
if (isset($_GET['delete'])) {
    $enrollment = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM results WHERE enrollment_no = '$enrollment'");
    mysqli_query($conn, "DELETE FROM gpa_summary WHERE enrollment_no = '$enrollment'");
    if (mysqli_query($conn, "DELETE FROM students WHERE enrollment_no = '$enrollment'")) {
        $message = '<div class="alert alert-success">Student deleted!</div>';
    }
}

// ---------- EDIT STUDENT ----------
if (isset($_POST['edit_student'])) {
    $enrollment = $_POST['enrollment_no'];
    $name = $_POST['full_name'];
    $course = $_POST['course'];
    $branch_id = $_POST['branch_id'];
    $batch = $_POST['batch_year'];

    $sql = "UPDATE students SET full_name='$name', course='$course', branch_id='$branch_id', batch_year='$batch'
            WHERE enrollment_no = '$enrollment'";

    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">Student updated!</div>';
    }
}

// Fetch all students with branch name (using JOIN)
$students = mysqli_query($conn, "SELECT s.*, b.branch_name FROM students s
                                  LEFT JOIN branches b ON s.branch_id = b.branch_id
                                  ORDER BY s.enrollment_no");

// Fetch branches for dropdown
$branches = mysqli_query($conn, "SELECT * FROM branches ORDER BY branch_name");

// Edit mode check
$edit_mode = false;
$edit_student = null;
if (isset($_GET['edit'])) {
    $edit_result = mysqli_query($conn, "SELECT * FROM students WHERE enrollment_no = '" . $_GET['edit'] . "'");
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_mode = true;
        $edit_student = mysqli_fetch_assoc($edit_result);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Indus University</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">
    </div>

    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="students.php" class="active">Students</a>
        <a href="subjects.php">Subjects</a>
        <a href="exams.php">Exams</a>
        <a href="branches.php">Branches</a>
        <a href="faculty.php">Faculty</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Manage Students</h2>
        <?php echo $message; ?>

        <!-- ADD / EDIT FORM -->
        <div class="form-box" style="max-width:700px;">
            <h2><?php echo $edit_mode ? 'Edit Student' : 'Add New Student'; ?></h2>

            <form method="POST" action="students.php">
                <div class="form-group">
                    <label>Enrollment Number</label>
                    <input type="text" name="enrollment_no"
                           value="<?php echo $edit_mode ? $edit_student['enrollment_no'] : ''; ?>"
                           <?php echo $edit_mode ? 'readonly' : ''; ?>
                           required placeholder="e.g. 22CS001">
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name"
                           value="<?php echo $edit_mode ? $edit_student['full_name'] : ''; ?>"
                           required placeholder="e.g. Rahul Kumar">
                </div>

                <div class="form-group">
                    <label>Course</label>
                    <input type="text" name="course"
                           value="<?php echo $edit_mode ? $edit_student['course'] : ''; ?>"
                           required placeholder="e.g. B.Tech">
                </div>

                <!-- Branch dropdown from branches table -->
                <div class="form-group">
                    <label>Branch</label>
                    <select name="branch_id" required>
                        <option value="">-- Select Branch --</option>
                        <?php
                        // Reset branches pointer for dropdown
                        if ($branches) mysqli_data_seek($branches, 0);
                        while ($branches && $b = mysqli_fetch_assoc($branches)):
                        ?>
                            <option value="<?php echo $b['branch_id']; ?>"
                                <?php echo ($edit_mode && $edit_student['branch_id'] == $b['branch_id']) ? 'selected' : ''; ?>>
                                <?php echo $b['branch_name']; ?> (<?php echo $b['branch_code']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Batch Year</label>
                    <input type="text" name="batch_year"
                           value="<?php echo $edit_mode ? $edit_student['batch_year'] : ''; ?>"
                           required placeholder="e.g. 2022-2026">
                </div>

                <?php if ($edit_mode): ?>
                    <button type="submit" name="edit_student" class="btn btn-warning btn-block">Update Student</button>
                <?php else: ?>
                    <button type="submit" name="add_student" class="btn btn-success btn-block">Add Student</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABLE: All Students -->
        <h3 style="margin:20px 0 10px;">All Students</h3>
        <table class="data-table">
            <tr>
                <th>Enrollment No</th>
                <th>Name</th>
                <th>Course</th>
                <th>Branch</th>
                <th>Batch</th>
                <th>Actions</th>
            </tr>
            <?php while ($students && $row = mysqli_fetch_assoc($students)): ?>
            <tr>
                <td><?php echo $row['enrollment_no']; ?></td>
                <td><?php echo $row['full_name']; ?></td>
                <td><?php echo $row['course']; ?></td>
                <td><?php echo $row['branch_name']; ?></td>
                <td><?php echo $row['batch_year']; ?></td>
                <td>
                    <a href="students.php?edit=<?php echo $row['enrollment_no']; ?>" class="btn btn-warning" style="padding:5px 10px; font-size:12px;">Edit</a>
                    <a href="students.php?delete=<?php echo $row['enrollment_no']; ?>" class="btn btn-danger" style="padding:5px 10px; font-size:12px;" onclick="return confirm('Delete this student?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
