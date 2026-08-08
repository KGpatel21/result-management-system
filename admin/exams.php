<?php
// ============================================================
// FILE: admin/exams.php
// PURPOSE: Admin can Create / View / Delete exam sessions
// Each exam = one semester exam event (e.g. "Semester 1 - 2024")
// Status starts as "draft" — faculty will change it later
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// ACTION: Add new exam
if (isset($_POST['add_exam'])) {
    $name = $_POST['exam_name'];
    $semester = $_POST['semester'];
    $year = $_POST['academic_year'];

    // New exams always start as "draft" (hidden from students)
    $sql = "INSERT INTO exams (exam_name, semester, academic_year, status)
            VALUES ('$name', '$semester', '$year', 'draft')";

    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">Exam created successfully!</div>';
    } else {
        $message = '<div class="alert alert-error">Error creating exam.</div>';
    }
}

// ACTION: Delete exam
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete related results and GPA first
    mysqli_query($conn, "DELETE FROM results WHERE exam_id = '$id'");
    mysqli_query($conn, "DELETE FROM gpa_summary WHERE exam_id = '$id'");
    mysqli_query($conn, "DELETE FROM exams WHERE exam_id = '$id'");
    $message = '<div class="alert alert-success">Exam deleted!</div>';
}

// Fetch all exams
$exams = mysqli_query($conn, "SELECT * FROM exams ORDER BY exam_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams - Admin</title>
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
        <a href="exams.php" class="active">Exams</a>
        <a href="branches.php">Branches</a>
        <a href="faculty.php">Faculty</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Manage Exams</h2>
        <?php echo $message; ?>

        <!-- ADD EXAM FORM -->
        <div class="form-box" style="max-width:600px;">
            <h2>Create New Exam</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Exam Name</label>
                    <input type="text" name="exam_name" required placeholder="e.g. Semester 1 End Exam">
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester" required>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Academic Year</label>
                    <input type="text" name="academic_year" required placeholder="e.g. 2024-25">
                </div>
                <button type="submit" name="add_exam" class="btn btn-success btn-block">Create Exam</button>
            </form>
        </div>

        <!-- TABLE: All Exams -->
        <h3 style="margin:20px 0 10px;">All Exams</h3>
        <table class="data-table">
            <tr>
                <th>ID</th>
                <th>Exam Name</th>
                <th>Semester</th>
                <th>Academic Year</th>
                <th>Status</th>
                <th>Release Date</th>
                <th>Action</th>
            </tr>
            <?php while ($exams && $row = mysqli_fetch_assoc($exams)): ?>
            <tr>
                <td><?php echo $row['exam_id']; ?></td>
                <td><?php echo $row['exam_name']; ?></td>
                <td>Sem <?php echo $row['semester']; ?></td>
                <td><?php echo $row['academic_year']; ?></td>
                <td>
                    <!-- Show status as a colored badge -->
                    <?php
                    if ($row['status'] == 'published') {
                        echo '<span class="badge badge-published">Published</span>';
                    } elseif ($row['status'] == 'scheduled') {
                        echo '<span class="badge badge-scheduled">Scheduled</span>';
                    } else {
                        echo '<span class="badge badge-draft">Draft</span>';
                    }
                    ?>
                </td>
                <td><?php echo $row['release_date'] ? $row['release_date'] : '-'; ?></td>
                <td>
                    <a href="exams.php?delete=<?php echo $row['exam_id']; ?>" class="btn btn-danger" style="padding:5px 10px; font-size:12px;" onclick="return confirm('Delete this exam and all its results?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
