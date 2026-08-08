<?php
// ============================================================
// FILE: admin/subjects.php
// PURPOSE: Admin can Add / View / Delete subjects
// Each subject now has max marks for Theory Mid/End + Practical Mid/End
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// ---------- ADD SUBJECT ----------
if (isset($_POST['add_subject'])) {
    $name = $_POST['subject_name'];
    $code = $_POST['subject_code'];
    $semester = $_POST['semester'];
    $credits = $_POST['credits'];
    $tm_max = $_POST['theory_mid_max'];
    $te_max = $_POST['theory_end_max'];
    $pm_max = $_POST['practical_mid_max'];
    $pe_max = $_POST['practical_end_max'];

    $sql = "INSERT INTO subjects (subject_name, subject_code, semester, credits, theory_mid_max, theory_end_max, practical_mid_max, practical_end_max)
            VALUES ('$name', '$code', '$semester', '$credits', '$tm_max', '$te_max', '$pm_max', '$pe_max')";

    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">Subject added successfully!</div>';
    } else {
        $message = '<div class="alert alert-error">Error adding subject.</div>';
    }
}

// ---------- DELETE SUBJECT ----------
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM results WHERE subject_id = '$id'");
    mysqli_query($conn, "DELETE FROM subjects WHERE subject_id = '$id'");
    $message = '<div class="alert alert-success">Subject deleted!</div>';
}

$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY semester, subject_code");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Indus University</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">
    </div>

    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="students.php">Students</a>
        <a href="subjects.php" class="active">Subjects</a>
        <a href="exams.php">Exams</a>
        <a href="branches.php">Branches</a>
        <a href="faculty.php">Faculty</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Manage Subjects</h2>
        <?php echo $message; ?>

        <!-- ADD SUBJECT FORM -->
        <div class="form-box" style="max-width:700px;">
            <h2>Add New Subject</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" required placeholder="e.g. Data Structures">
                </div>
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" name="subject_code" required placeholder="e.g. CS201">
                </div>
                <div class="form-group">
                    <label>Semester (1-8)</label>
                    <select name="semester" required>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Credits</label>
                    <input type="number" name="credits" required min="1" max="10" placeholder="e.g. 4">
                </div>

                <!-- Max marks for each section -->
                <h3 style="margin:15px 0 10px; font-size:15px; color:#555;">Max Marks per Section</h3>
                <p style="font-size:12px; color:#888; margin-bottom:10px;">Min marks = 33% of max (auto-calculated)</p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div class="form-group">
                        <label>Theory Mid-Sem</label>
                        <input type="number" name="theory_mid_max" required value="30" min="0">
                    </div>
                    <div class="form-group">
                        <label>Theory End-Sem</label>
                        <input type="number" name="theory_end_max" required value="70" min="0">
                    </div>
                    <div class="form-group">
                        <label>Practical Mid-Sem</label>
                        <input type="number" name="practical_mid_max" required value="25" min="0">
                    </div>
                    <div class="form-group">
                        <label>Practical End-Sem</label>
                        <input type="number" name="practical_end_max" required value="25" min="0">
                    </div>
                </div>

                <button type="submit" name="add_subject" class="btn btn-success btn-block">Add Subject</button>
            </form>
        </div>

        <!-- TABLE: All Subjects -->
        <h3 style="margin:20px 0 10px;">All Subjects</h3>
        <table class="data-table">
            <tr>
                <th>Code</th>
                <th>Subject Name</th>
                <th>Sem</th>
                <th>Credits</th>
                <th>Theory (Mid/End)</th>
                <th>Practical (Mid/End)</th>
                <th>Total Max</th>
                <th>Action</th>
            </tr>
            <?php while ($subjects && $row = mysqli_fetch_assoc($subjects)):
                $total_max = $row['theory_mid_max'] + $row['theory_end_max'] + $row['practical_mid_max'] + $row['practical_end_max'];
            ?>
            <tr>
                <td><?php echo $row['subject_code']; ?></td>
                <td><?php echo $row['subject_name']; ?></td>
                <td><?php echo $row['semester']; ?></td>
                <td><?php echo $row['credits']; ?></td>
                <td><?php echo $row['theory_mid_max']; ?> / <?php echo $row['theory_end_max']; ?></td>
                <td><?php echo $row['practical_mid_max']; ?> / <?php echo $row['practical_end_max']; ?></td>
                <td><strong><?php echo $total_max; ?></strong></td>
                <td>
                    <a href="subjects.php?delete=<?php echo $row['subject_id']; ?>" class="btn btn-danger" style="padding:5px 10px; font-size:12px;" onclick="return confirm('Delete this subject?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
