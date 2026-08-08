<?php
// ============================================================
// FILE: faculty/enter_result.php
// PURPOSE: Faculty enters marks for each student per exam
//
// Each subject has 4 mark sections:
//   Theory Mid-Sem, Theory End-Sem, Practical Mid-Sem, Practical End-Sem
//
// FAIL LOGIC:
//   Min marks = 33% of max marks for each section
//   If obtained < min in ANY section → whole subject = FAIL (grade F)
//
// GRADE SCALE:
//   O=10 (>=90%), A+=9 (>=80%), A=8 (>=70%), B+=7 (>=60%),
//   B=6 (>=50%), C=5 (>=40%), D=4 (>=33%), F=0 (<33% or any section failed)
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['faculty_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = '';

// ---------- GRADE CALCULATION FUNCTION ----------
function calculateGrade($percentage, $any_section_failed) {
    if ($any_section_failed) {
        return ['grade' => 'F', 'points' => 0];
    }
    if ($percentage >= 90) return ['grade' => 'O', 'points' => 10];
    if ($percentage >= 80) return ['grade' => 'A+', 'points' => 9];
    if ($percentage >= 70) return ['grade' => 'A', 'points' => 8];
    if ($percentage >= 60) return ['grade' => 'B+', 'points' => 7];
    if ($percentage >= 50) return ['grade' => 'B', 'points' => 6];
    if ($percentage >= 40) return ['grade' => 'C', 'points' => 5];
    if ($percentage >= 33) return ['grade' => 'D', 'points' => 4];
    return ['grade' => 'F', 'points' => 0];
}

// ---------- SAVE MARKS ----------
if (isset($_POST['save_marks'])) {
    $exam_id = $_POST['exam_id'];
    $enrollment = $_POST['enrollment_no'];
    $is_backlog = isset($_POST['is_backlog']) ? 1 : 0;

    // Delete old results for this student+exam (same backlog status)
    mysqli_query($conn, "DELETE FROM results WHERE enrollment_no='$enrollment' AND exam_id='$exam_id' AND is_backlog='$is_backlog'");

    $subject_ids = $_POST['subject_id'];
    $total_grade_points = 0;
    $total_credits = 0;
    $backlog_count = 0;

    for ($i = 0; $i < count($subject_ids); $i++) {
        $sub_id = $subject_ids[$i];
        $sub_r = mysqli_query($conn, "SELECT * FROM subjects WHERE subject_id='$sub_id'");
        $sub = $sub_r ? mysqli_fetch_assoc($sub_r) : null;
        if (!$sub) continue;  // skip if subject not found

        $tm = intval($_POST['theory_mid'][$i]);
        $te = intval($_POST['theory_end'][$i]);
        $pm = intval($_POST['practical_mid'][$i]);
        $pe = intval($_POST['practical_end'][$i]);

        $total_obtained = $tm + $te + $pm + $pe;
        $total_max = $sub['theory_mid_max'] + $sub['theory_end_max'] + $sub['practical_mid_max'] + $sub['practical_end_max'];

        // Check each section against 33% minimum
        $failed_parts = [];
        if ($sub['theory_mid_max'] > 0 && $tm < ceil($sub['theory_mid_max'] * 0.33)) $failed_parts[] = 'theory_mid';
        if ($sub['theory_end_max'] > 0 && $te < ceil($sub['theory_end_max'] * 0.33)) $failed_parts[] = 'theory_end';
        if ($sub['practical_mid_max'] > 0 && $pm < ceil($sub['practical_mid_max'] * 0.33)) $failed_parts[] = 'practical_mid';
        if ($sub['practical_end_max'] > 0 && $pe < ceil($sub['practical_end_max'] * 0.33)) $failed_parts[] = 'practical_end';

        $any_failed = count($failed_parts) > 0;
        $failed_str = implode(',', $failed_parts);

        $percentage = ($total_max > 0) ? ($total_obtained / $total_max) * 100 : 0;
        $gradeInfo = calculateGrade($percentage, $any_failed);

        $sql = "INSERT INTO results (enrollment_no, subject_id, exam_id, theory_mid_marks, theory_end_marks,
                practical_mid_marks, practical_end_marks, total_obtained, total_max, grade, grade_points,
                is_backlog, failed_parts)
                VALUES ('$enrollment', '$sub_id', '$exam_id', '$tm', '$te', '$pm', '$pe',
                '$total_obtained', '$total_max', '{$gradeInfo['grade']}', '{$gradeInfo['points']}',
                '$is_backlog', '$failed_str')";
        mysqli_query($conn, $sql);

        $total_grade_points += $gradeInfo['points'] * $sub['credits'];
        $total_credits += $sub['credits'];
        if ($gradeInfo['grade'] == 'F') $backlog_count++;
    }

    // Save SGPA
    $sgpa = ($total_credits > 0) ? round($total_grade_points / $total_credits, 2) : 0;
    mysqli_query($conn, "DELETE FROM gpa_summary WHERE enrollment_no='$enrollment' AND exam_id='$exam_id'");
    mysqli_query($conn, "INSERT INTO gpa_summary (enrollment_no, exam_id, sgpa, backlog_count) VALUES ('$enrollment', '$exam_id', '$sgpa', '$backlog_count')");

    $message = '<div class="alert alert-success">Results saved! SGPA: ' . $sgpa . ' | Backlogs: ' . $backlog_count . '</div>';
}

$exams = mysqli_query($conn, "SELECT * FROM exams ORDER BY exam_id DESC");
$students = mysqli_query($conn, "SELECT * FROM students ORDER BY enrollment_no");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Results - Indus University</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">

    </div>

    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="enter_result.php" class="active">Enter Results</a>
        <a href="publish_result.php">Publish Results</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2 class="page-title">Enter Student Results</h2>
        <?php echo $message; ?>

        <!-- STEP 1: Select Exam + Student -->
        <div class="form-inline">
            <h3 style="margin-bottom:15px;">Step 1: Select Exam & Student</h3>
            <form method="GET">
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <label>Exam</label>
                        <select name="exam_id" required>
                            <option value="">-- Select Exam --</option>
                            <?php if ($exams) mysqli_data_seek($exams, 0); while ($exams && $e = mysqli_fetch_assoc($exams)): ?>
                                <option value="<?php echo $e['exam_id']; ?>"
                                    <?php echo (isset($_GET['exam_id']) && $_GET['exam_id'] == $e['exam_id']) ? 'selected' : ''; ?>>
                                    <?php echo $e['exam_name']; ?> (<?php echo $e['academic_year']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1; margin-bottom:0;">
                        <label>Student</label>
                        <select name="enrollment_no" required>
                            <option value="">-- Select Student --</option>
                            <?php if ($students) mysqli_data_seek($students, 0); while ($students && $s = mysqli_fetch_assoc($students)): ?>
                                <option value="<?php echo $s['enrollment_no']; ?>"
                                    <?php echo (isset($_GET['enrollment_no']) && $_GET['enrollment_no'] == $s['enrollment_no']) ? 'selected' : ''; ?>>
                                    <?php echo $s['enrollment_no']; ?> - <?php echo $s['full_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="height:42px;">Load Subjects</button>
                </div>
            </form>
        </div>

        <!-- STEP 2: Enter Marks -->
        <?php if (isset($_GET['exam_id']) && isset($_GET['enrollment_no'])):
            $exam_id = $_GET['exam_id'];
            $enrollment = $_GET['enrollment_no'];
            $exam_r = mysqli_query($conn, "SELECT * FROM exams WHERE exam_id='$exam_id'");
            $exam = $exam_r ? mysqli_fetch_assoc($exam_r) : null;
            $subjects = $exam ? mysqli_query($conn, "SELECT * FROM subjects WHERE semester='" . $exam['semester'] . "' ORDER BY subject_code") : false;
            $student_r = mysqli_query($conn, "SELECT * FROM students WHERE enrollment_no='$enrollment'");
            $student = $student_r ? mysqli_fetch_assoc($student_r) : null;

            if ($student && mysqli_num_rows($subjects) > 0):
        ?>
        <div class="form-inline">
            <h3 style="margin-bottom:5px;">Step 2: Enter Marks for <?php echo $student['full_name']; ?></h3>
            <p style="color:#888; font-size:13px; margin-bottom:15px;">
                <?php echo $exam['exam_name']; ?> | Semester <?php echo $exam['semester']; ?>
                | Min marks = 33% of max (shown in red below each field)
            </p>

            <form method="POST">
                <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                <input type="hidden" name="enrollment_no" value="<?php echo $enrollment; ?>">

                <div style="margin-bottom:15px;">
                    <label style="font-weight:bold; cursor:pointer;">
                        <input type="checkbox" name="is_backlog" value="1"> Mark as Backlog (KT) attempt
                    </label>
                </div>

                <table class="data-table">
                    <tr>
                        <th rowspan="2">Subject</th>
                        <th colspan="2" style="text-align:center;">Theory</th>
                        <th colspan="2" style="text-align:center;">Practical</th>
                    </tr>
                    <tr>
                        <th>Mid-Sem</th>
                        <th>End-Sem</th>
                        <th>Mid-Sem</th>
                        <th>End-Sem</th>
                    </tr>

                    <?php $idx = 0; while ($sub = mysqli_fetch_assoc($subjects)):
                        $tm_min = ceil($sub['theory_mid_max'] * 0.33);
                        $te_min = ceil($sub['theory_end_max'] * 0.33);
                        $pm_min = ceil($sub['practical_mid_max'] * 0.33);
                        $pe_min = ceil($sub['practical_end_max'] * 0.33);

                        $exist_r = mysqli_query($conn,
                            "SELECT * FROM results WHERE enrollment_no='$enrollment' AND subject_id='{$sub['subject_id']}' AND exam_id='$exam_id' AND is_backlog=0");
                        $existing = ($exist_r && mysqli_num_rows($exist_r) > 0) ? mysqli_fetch_assoc($exist_r) : null;
                    ?>
                    <tr>
                        <td style="text-align:left;">
                            <strong><?php echo $sub['subject_name']; ?></strong>
                            <br><small style="color:#888;"><?php echo $sub['subject_code']; ?> | Cr: <?php echo $sub['credits']; ?></small>
                            <input type="hidden" name="subject_id[]" value="<?php echo $sub['subject_id']; ?>">
                        </td>
                        <td>
                            <input type="number" name="theory_mid[]" min="0" max="<?php echo $sub['theory_mid_max']; ?>"
                                   value="<?php echo $existing ? $existing['theory_mid_marks'] : ''; ?>"
                                   style="width:70px;" placeholder="/<?php echo $sub['theory_mid_max']; ?>">
                            <br><small style="color:#e74c3c;">min: <?php echo $tm_min; ?></small>
                        </td>
                        <td>
                            <input type="number" name="theory_end[]" min="0" max="<?php echo $sub['theory_end_max']; ?>"
                                   value="<?php echo $existing ? $existing['theory_end_marks'] : ''; ?>"
                                   style="width:70px;" placeholder="/<?php echo $sub['theory_end_max']; ?>">
                            <br><small style="color:#e74c3c;">min: <?php echo $te_min; ?></small>
                        </td>
                        <td>
                            <input type="number" name="practical_mid[]" min="0" max="<?php echo $sub['practical_mid_max']; ?>"
                                   value="<?php echo $existing ? $existing['practical_mid_marks'] : ''; ?>"
                                   style="width:70px;" placeholder="/<?php echo $sub['practical_mid_max']; ?>">
                            <br><small style="color:#e74c3c;">min: <?php echo $pm_min; ?></small>
                        </td>
                        <td>
                            <input type="number" name="practical_end[]" min="0" max="<?php echo $sub['practical_end_max']; ?>"
                                   value="<?php echo $existing ? $existing['practical_end_marks'] : ''; ?>"
                                   style="width:70px;" placeholder="/<?php echo $sub['practical_end_max']; ?>">
                            <br><small style="color:#e74c3c;">min: <?php echo $pe_min; ?></small>
                        </td>
                    </tr>
                    <?php $idx++; endwhile; ?>
                </table>

                <div style="text-align:center; margin-top:15px;">
                    <button type="submit" name="save_marks" class="btn btn-success">Save All Results</button>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">No subjects found for this semester or student not found.</div>
        <?php endif; endif; ?>
    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
