<?php
// ============================================================
// FILE: student/result.php
// PURPOSE: Show student's complete grade history — semester-wise
//
// RESULT TABLE STRUCTURE (per subject):
//   THEORY:     Mid-Sem (Max/Min/Obtained) | End-Sem (Max/Min/Obtained)
//   PRACTICAL:  Mid-Sem (Max/Min/Obtained) | End-Sem (Max/Min/Obtained)
//   TOTAL:      Total Obtained / Total Max
//   GRADE:      O to F
//   GRADE POINTS: 0 to 10
//
// FAIL = red highlight if any section < 33% min marks
// BACKLOG results appear in separate table under same semester
// Bottom: Backlog Count + SGPA + CGPA
// ============================================================

require_once('../db.php');

if (!isset($_SESSION['student_logged_in'])) {
    header("Location: login.php");
    exit();
}

$enrollment = $_SESSION['enrollment_no'];

$student_result = mysqli_query($conn,
    "SELECT s.*, b.branch_name FROM students s
     LEFT JOIN branches b ON s.branch_id = b.branch_id
     WHERE s.enrollment_no = '$enrollment'");
$student = $student_result ? mysqli_fetch_assoc($student_result) : null;
if (!$student) {
    $student = ['full_name' => 'Unknown', 'enrollment_no' => $enrollment, 'course' => '-', 'branch_name' => '-', 'batch_year' => '-'];
}

$today = date('Y-m-d');

$exams_sql = "SELECT DISTINCT e.*
              FROM exams e
              JOIN results r ON e.exam_id = r.exam_id
              WHERE r.enrollment_no = '$enrollment'
              ORDER BY e.semester ASC";
$exams = mysqli_query($conn, $exams_sql);

$total_sgpa = 0;
$sgpa_count = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade History - <?php echo $student['full_name']; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="header">
        <img src="../img/logo.png" alt="Indus University Logo">

    </div>

    <div class="navbar no-print">
        <a href="dashboard.php">Dashboard</a>
        <a href="result.php" class="active">View Results</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">

        <!-- Student Info -->
        <div class="result-header">
            <h2><?php echo $student['full_name']; ?></h2>
            <p><strong>Enrollment No:</strong> <?php echo $student['enrollment_no']; ?></p>
            <p><strong>Course:</strong> <?php echo $student['course']; ?> | <strong>Branch:</strong> <?php echo $student['branch_name']; ?></p>
            <p><strong>Batch:</strong> <?php echo $student['batch_year']; ?></p>
        </div>

        <?php
        $any_result_shown = false;

        while ($exam = mysqli_fetch_assoc($exams)):

            // ========== VISIBILITY CHECK ==========
            $can_show = false;
            $show_date_message = '';

            if ($exam['status'] == 'published') {
                $can_show = true;
            } elseif ($exam['status'] == 'scheduled') {
                if ($today >= $exam['release_date']) {
                    $can_show = true;
                } else {
                    $show_date_message = 'Result will be available on ' . date('d-M-Y', strtotime($exam['release_date']));
                }
            } else {
                continue;
            }

            if (!$can_show && $show_date_message != ''):
        ?>
                <div class="semester-card">
                    <div class="semester-title"><?php echo $exam['exam_name']; ?> (<?php echo $exam['academic_year']; ?>)</div>
                    <div style="padding:20px; text-align:center;">
                        <div class="alert alert-warning" style="margin:0;">&#128197; <?php echo $show_date_message; ?></div>
                    </div>
                </div>
        <?php
                continue;
            endif;

            if ($can_show):
                $any_result_shown = true;

                // Regular results
                $results = mysqli_query($conn,
                    "SELECT r.*, s.subject_name, s.subject_code, s.credits,
                     s.theory_mid_max, s.theory_end_max, s.practical_mid_max, s.practical_end_max
                     FROM results r JOIN subjects s ON r.subject_id = s.subject_id
                     WHERE r.enrollment_no = '$enrollment' AND r.exam_id = '" . $exam['exam_id'] . "' AND r.is_backlog = 0
                     ORDER BY s.subject_code");

                // Backlog results
                $backlogs = mysqli_query($conn,
                    "SELECT r.*, s.subject_name, s.subject_code, s.credits,
                     s.theory_mid_max, s.theory_end_max, s.practical_mid_max, s.practical_end_max
                     FROM results r JOIN subjects s ON r.subject_id = s.subject_id
                     WHERE r.enrollment_no = '$enrollment' AND r.exam_id = '" . $exam['exam_id'] . "' AND r.is_backlog = 1
                     ORDER BY s.subject_code");

                $gpa_r = mysqli_query($conn,
                    "SELECT sgpa, backlog_count FROM gpa_summary WHERE enrollment_no = '$enrollment' AND exam_id = '" . $exam['exam_id'] . "'");
                $gpa_row = ($gpa_r && mysqli_num_rows($gpa_r) > 0) ? mysqli_fetch_assoc($gpa_r) : null;
                $sgpa = $gpa_row ? $gpa_row['sgpa'] : 0;
                $backlog_count = $gpa_row ? $gpa_row['backlog_count'] : 0;

                $total_sgpa += $sgpa;
                $sgpa_count++;
        ?>

                <div class="semester-card">
                    <div class="semester-title">
                        <?php echo $exam['exam_name']; ?> (<?php echo $exam['academic_year']; ?>)
                    </div>

                    <!-- REGULAR Results -->
                    <?php if (mysqli_num_rows($results) > 0): ?>
                    <div style="overflow-x:auto;">
                    <table class="result-table">
                        <thead>
                        <tr>
                            <th rowspan="3">Subject</th>
                            <th colspan="6">Theory</th>
                            <th colspan="6">Practical</th>
                            <th rowspan="3">Total<br>Marks</th>
                            <th rowspan="3">Grade</th>
                            <th rowspan="3">Grade<br>Points</th>
                        </tr>
                        <tr class="sub-header">
                            <th colspan="3">Mid Semester</th>
                            <th colspan="3">End Semester</th>
                            <th colspan="3">Mid Semester</th>
                            <th colspan="3">End Semester</th>
                        </tr>
                        <tr class="marks-header">
                            <th>Max</th><th>Min</th><th>Obt</th>
                            <th>Max</th><th>Min</th><th>Obt</th>
                            <th>Max</th><th>Min</th><th>Obt</th>
                            <th>Max</th><th>Min</th><th>Obt</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($results)):
                            $is_fail = ($row['grade'] == 'F');
                            $fp = explode(',', $row['failed_parts']);
                            $tm_min = ceil($row['theory_mid_max'] * 0.33);
                            $te_min = ceil($row['theory_end_max'] * 0.33);
                            $pm_min = ceil($row['practical_mid_max'] * 0.33);
                            $pe_min = ceil($row['practical_end_max'] * 0.33);
                        ?>
                        <tr class="<?php echo $is_fail ? 'fail-row' : ''; ?>">
                            <td class="subject-name">
                                <?php echo $row['subject_name']; ?>
                                <br><small style="color:#888;"><?php echo $row['subject_code']; ?> | Cr: <?php echo $row['credits']; ?></small>
                            </td>
                            <td><?php echo $row['theory_mid_max']; ?></td>
                            <td><?php echo $tm_min; ?></td>
                            <td class="<?php echo in_array('theory_mid', $fp) ? 'fail-mark' : ''; ?>"><?php echo $row['theory_mid_marks']; ?></td>
                            <td><?php echo $row['theory_end_max']; ?></td>
                            <td><?php echo $te_min; ?></td>
                            <td class="<?php echo in_array('theory_end', $fp) ? 'fail-mark' : ''; ?>"><?php echo $row['theory_end_marks']; ?></td>
                            <td><?php echo $row['practical_mid_max']; ?></td>
                            <td><?php echo $pm_min; ?></td>
                            <td class="<?php echo in_array('practical_mid', $fp) ? 'fail-mark' : ''; ?>"><?php echo $row['practical_mid_marks']; ?></td>
                            <td><?php echo $row['practical_end_max']; ?></td>
                            <td><?php echo $pe_min; ?></td>
                            <td class="<?php echo in_array('practical_end', $fp) ? 'fail-mark' : ''; ?>"><?php echo $row['practical_end_marks']; ?></td>
                            <td><strong><?php echo $row['total_obtained']; ?>/<?php echo $row['total_max']; ?></strong></td>
                            <td class="<?php echo $is_fail ? 'grade-fail' : 'grade-pass'; ?>"><?php echo $row['grade']; ?></td>
                            <td class="<?php echo $is_fail ? 'grade-fail' : ''; ?>"><?php echo $row['grade_points']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php endif; ?>

                    <!-- BACKLOG Results -->
                    <?php if (mysqli_num_rows($backlogs) > 0): ?>
                    <div class="backlog-section">
                        <div style="padding:10px 20px; background:#fde8e8; font-weight:bold; color:#c0392b;">
                            <span class="backlog-tag">BACKLOG</span> Re-examination Results
                        </div>
                        <div style="overflow-x:auto;">
                        <table class="result-table">
                            <thead>
                            <tr>
                                <th rowspan="3">Subject</th>
                                <th colspan="6">Theory</th>
                                <th colspan="6">Practical</th>
                                <th rowspan="3">Total</th>
                                <th rowspan="3">Grade</th>
                                <th rowspan="3">Points</th>
                            </tr>
                            <tr class="sub-header">
                                <th colspan="3">Mid Semester</th><th colspan="3">End Semester</th>
                                <th colspan="3">Mid Semester</th><th colspan="3">End Semester</th>
                            </tr>
                            <tr class="marks-header">
                                <th>Max</th><th>Min</th><th>Obt</th>
                                <th>Max</th><th>Min</th><th>Obt</th>
                                <th>Max</th><th>Min</th><th>Obt</th>
                                <th>Max</th><th>Min</th><th>Obt</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php while ($brow = mysqli_fetch_assoc($backlogs)):
                                $bf = ($brow['grade'] == 'F');
                                $bfp = explode(',', $brow['failed_parts']);
                                $btm = ceil($brow['theory_mid_max'] * 0.33);
                                $bte = ceil($brow['theory_end_max'] * 0.33);
                                $bpm = ceil($brow['practical_mid_max'] * 0.33);
                                $bpe = ceil($brow['practical_end_max'] * 0.33);
                            ?>
                            <tr class="<?php echo $bf ? 'fail-row' : ''; ?>">
                                <td class="subject-name"><?php echo $brow['subject_name']; ?><br><small><?php echo $brow['subject_code']; ?></small></td>
                                <td><?php echo $brow['theory_mid_max']; ?></td><td><?php echo $btm; ?></td>
                                <td class="<?php echo in_array('theory_mid', $bfp) ? 'fail-mark' : ''; ?>"><?php echo $brow['theory_mid_marks']; ?></td>
                                <td><?php echo $brow['theory_end_max']; ?></td><td><?php echo $bte; ?></td>
                                <td class="<?php echo in_array('theory_end', $bfp) ? 'fail-mark' : ''; ?>"><?php echo $brow['theory_end_marks']; ?></td>
                                <td><?php echo $brow['practical_mid_max']; ?></td><td><?php echo $bpm; ?></td>
                                <td class="<?php echo in_array('practical_mid', $bfp) ? 'fail-mark' : ''; ?>"><?php echo $brow['practical_mid_marks']; ?></td>
                                <td><?php echo $brow['practical_end_max']; ?></td><td><?php echo $bpe; ?></td>
                                <td class="<?php echo in_array('practical_end', $bfp) ? 'fail-mark' : ''; ?>"><?php echo $brow['practical_end_marks']; ?></td>
                                <td><strong><?php echo $brow['total_obtained']; ?>/<?php echo $brow['total_max']; ?></strong></td>
                                <td class="<?php echo $bf ? 'grade-fail' : 'grade-pass'; ?>"><?php echo $brow['grade']; ?></td>
                                <td class="<?php echo $bf ? 'grade-fail' : ''; ?>"><?php echo $brow['grade_points']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Semester Summary -->
                    <div class="semester-summary">
                        <div>SGPA: <strong><?php echo $sgpa; ?></strong> / 10.00</div>
                        <div class="<?php echo $backlog_count > 0 ? 'backlog-info' : ''; ?>">
                            Current Semester Backlog: <strong><?php echo $backlog_count; ?></strong>
                        </div>
                    </div>
                </div>

        <?php
            endif;
        endwhile;

        if ($sgpa_count > 0):
            $cgpa = round($total_sgpa / $sgpa_count, 2);
        ?>
            <div class="cgpa-box">
                <div class="cgpa-label">Cumulative Grade Point Average (CGPA)</div>
                <div class="cgpa-value"><?php echo $cgpa; ?></div>
                <div class="cgpa-label">Based on <?php echo $sgpa_count; ?> semester(s)</div>
            </div>
        <?php endif; ?>

        <?php if (!$any_result_shown && $sgpa_count == 0): ?>
            <div class="alert alert-info">No results available yet. Please check back later.</div>
        <?php endif; ?>

    </div>

    <div class="footer">&copy; 2026 Indus University</div>
</body>
</html>
