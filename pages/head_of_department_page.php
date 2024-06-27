<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if ($_SESSION['role'] !== 'head_of_department') {
    header("Location: ../other/dashboard.php");
    exit();
}

$username = $_SESSION['username'];

$sql = "
    SELECT 
        courses.name AS course_name, 
        exams.name AS exam_name, 
        exams.exam_date AS exam_date, 
        exams.start_time AS start_time, 
        exams.end_time AS end_time 
    FROM exams 
    JOIN courses ON exams.course_id = courses.id 
    WHERE courses.department_id = (SELECT department_id FROM users WHERE username='$username') 
    ORDER BY exams.exam_date, exams.start_time
";
$exams_result = $conn->query($sql);

if (!$exams_result) {
    die("Query failed: " . $conn->error);
}


$sql = "SELECT * FROM assistants WHERE department_id=(SELECT department_id FROM users WHERE username='$username') ORDER BY score ASC";
$assistants_result = $conn->query($sql);

if (!$assistants_result) {
    die("Query failed: " . $conn->error);
}


$total_score = 0;
$assistant_scores = [];
while ($row = $assistants_result->fetch_assoc()) {
    $assistant_scores[] = $row;
    $total_score += $row['score'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Head of Department Page</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<header>
    <h1>Head of Department Page</h1>
    <a href="../authentication/logout.php">Logout</a>
</header>
<div class="container">
    <h3>Exam Schedule</h3>
    <table>
        <tr>
            <th>Course</th>
            <th>Exam</th>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>
        <?php while($exam = $exams_result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($exam['course_name']); ?></td>
            <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
            <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
            <td><?php echo htmlspecialchars($exam['start_time']); ?></td>
            <td><?php echo htmlspecialchars($exam['end_time']); ?></td>
        </tr>
        <?php } ?>
    </table>

    <h3>Assistant Scores</h3>
    <table>
        <tr>
            <th>Assistant Name</th>
            <th>Score</th>
            <th>Percentage</th>
        </tr>
        <?php foreach ($assistant_scores as $assistant) { 
            $percentage = $total_score ? ($assistant['score'] / $total_score) * 100 : 0;
        ?>
        <tr>
            <td><?php echo htmlspecialchars($assistant['name']); ?></td>
            <td><?php echo htmlspecialchars($assistant['score']); ?></td>
            <td><?php echo number_format($percentage, 2) . '%'; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>



