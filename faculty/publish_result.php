<?php
// ============================================================
// FILE: faculty/publish_result.php
// PURPOSE: Faculty controls WHEN students can see their results
// 3 OPTIONS:
//   1. DRAFT     → Students cannot see (default when marks are being entered)
//   2. SCHEDULED → Set a release date (e.g. 14th Aug) → auto-visible on that date
//   3. PUBLISHED → Students can see immediately right now
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['faculty_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// ---------- HANDLE STATUS CHANGE ----------
if (isset($_POST['update_status'])) {
    $exam_id = $_POST['exam_id'];
    $new_status = $_POST['status'];
    $release_date = $_POST['release_date'];

    // If status is "scheduled", a release date is required
    if ($new_status == 'scheduled' && $release_date == '') {
        $message = '<div class="alert alert-error">Please set a release date for scheduled results!</div>';
    } else {
        // If not scheduled, clear the release date
        if ($new_status != 'scheduled') {
            $release_date = null;
        }

        // Update the exam's status and release date
        if ($release_date) {
            $sql = "UPDATE exams SET status = '$new_status', release_date = '$release_date' WHERE exam_id = '$exam_id'";
        } else {
            $sql = "UPDATE exams SET status = '$new_status', release_date = NULL WHERE exam_id = '$exam_id'";
        }

        if (mysqli_query($conn, $sql)) {
            $message = '<div class="alert alert-success">Exam status updated to: ' . strtoupper($new_status) . '</div>';
        }
    }
}

// Fetch all exams
$exams = mysqli_query($conn, "SELECT * FROM exams ORDER BY exam_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publish Results - Faculty</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">

    </div>

    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="enter_result.php">Enter Results</a>
        <a href="publish_result.php" class="active">Publish Results</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Publish / Schedule Results</h2>
        <?php echo $message; ?>

        <!-- Info box explaining the 3 states -->
        <div class="alert alert-info">
            <strong>How result visibility works:</strong><br>
            <strong>Draft</strong> = Hidden from students (while entering/checking marks)<br>
            <strong>Scheduled</strong> = Set a date — students see results only on/after that date<br>
            <strong>Published</strong> = Visible to students right now
        </div>

        <!-- TABLE: All exams with status controls -->
        <table class="data-table">
            <tr>
                <th>Exam</th>
                <th>Semester</th>
                <th>Year</th>
                <th>Current Status</th>
                <th>Release Date</th>
                <th>Change Status</th>
            </tr>
            <?php while ($exams && $row = mysqli_fetch_assoc($exams)): ?>
            <tr>
                <td><?php echo $row['exam_name']; ?></td>
                <td>Sem <?php echo $row['semester']; ?></td>
                <td><?php echo $row['academic_year']; ?></td>
                <td>
                    <?php
                    // Show colored badge for current status
                    if ($row['status'] == 'published') {
                        echo '<span class="badge badge-published">Published</span>';
                    } elseif ($row['status'] == 'scheduled') {
                        echo '<span class="badge badge-scheduled">Scheduled</span>';
                    } else {
                        echo '<span class="badge badge-draft">Draft</span>';
                    }
                    ?>
                </td>
                <td>
                    <?php echo $row['release_date'] ? date('d-M-Y', strtotime($row['release_date'])) : '-'; ?>
                </td>
                <td>
                    <!-- Form to change status -->
                    <form method="POST" style="display:flex; gap:5px; align-items:center; flex-wrap:wrap;">
                        <input type="hidden" name="exam_id" value="<?php echo $row['exam_id']; ?>">

                        <!-- Status dropdown -->
                        <select name="status" style="padding:5px; font-size:12px;"
                                onchange="toggleDate(this, '<?php echo $row['exam_id']; ?>')">
                            <option value="draft" <?php echo ($row['status']=='draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="scheduled" <?php echo ($row['status']=='scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="published" <?php echo ($row['status']=='published') ? 'selected' : ''; ?>>Published</option>
                        </select>

                        <!-- Date picker (only shown when "scheduled" is selected) -->
                        <input type="date" name="release_date"
                               id="date_<?php echo $row['exam_id']; ?>"
                               value="<?php echo $row['release_date']; ?>"
                               style="padding:5px; font-size:12px; <?php echo ($row['status']!='scheduled') ? 'display:none;' : ''; ?>">

                        <button type="submit" name="update_status" class="btn btn-primary" style="padding:5px 10px; font-size:12px;">
                            Save
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>

    <script>
    // Show/hide the date picker based on selected status
    function toggleDate(selectElement, examId) {
        var dateInput = document.getElementById('date_' + examId);
        if (selectElement.value === 'scheduled') {
            dateInput.style.display = 'inline-block';  // Show date picker
        } else {
            dateInput.style.display = 'none';           // Hide date picker
        }
    }
    </script>
</body>
</html>
