<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if ($_SESSION['role'] !== 'dean') {
    header("Location: dashboard.php");
    exit();
}

$username = $_SESSION['username'];


$departments_sql = "
    SELECT 
        departments.id AS department_id,
        departments.name AS department_name
    FROM 
        departments 
    WHERE 
        departments.faculty_id = (SELECT faculty_id FROM users WHERE username='$username')
    UNION
    SELECT 
        NULL AS department_id,
        'Faculty Courses' AS department_name
";
$departments_result = $conn->query($departments_sql);

if (!$departments_result) {
    die("Query failed: " . $conn->error);
}


$filter = "";
if (isset($_POST['department_id']) && $_POST['department_id'] != '') {
    if ($_POST['department_id'] == 'faculty') {
        $filter = "WHERE courses.department_id IS NULL";
    } else {
        $department_id = intval($_POST['department_id']);
        $filter = "WHERE (departments.id = $department_id OR courses.department_id IS NULL)";
    }
} else {
    $filter = "WHERE departments.faculty_id = (SELECT faculty_id FROM users WHERE username='$username')";
}

$sql = "
    SELECT 
        departments.name AS department_name,
        courses.name AS course_name, 
        exams.name AS exam_name, 
        exams.exam_date AS exam_date, 
        exams.start_time AS start_time, 
        exams.end_time AS end_time
    FROM 
        exams 
    JOIN 
        courses ON exams.course_id = courses.id
    LEFT JOIN 
        departments ON courses.department_id = departments.id
    $filter
    ORDER BY 
        exams.exam_date, exams.start_time
";
$exams_result = $conn->query($sql);

if (!$exams_result) {
    die("Query failed: " . $conn->error);
}

$sql = "
    SELECT 
        assistants.name AS assistant_name, 
        assistants.score AS score
    FROM 
        assistants
    WHERE 
        faculty_id = (SELECT faculty_id FROM users WHERE username='$username')
";
$assistants_result = $conn->query($sql);

if (!$assistants_result) {
    die("Query failed: " . $conn->error);
}


$total_score = 0;
while($assistant = $assistants_result->fetch_assoc()) {
    $total_score += $assistant['score'];
}


$assistants_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dean Page</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<header>
    <h1>Dean Page</h1>
    <a href="../authentication/logout.php">Logout</a>
</header>
<div class="container">
    <h3>Filter Exams by Department</h3>
    <form method="post" action="../pages/dean_page.php">
        <label for="department_id">Select Department:</label>
        <select name="department_id" id="department_id" onchange="this.form.submit()">
            <option value="">All Departments</option>
            <?php while($department = $departments_result->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($department['department_id'] ?: 'faculty'); ?>" <?php if (isset($_POST['department_id']) && $_POST['department_id'] == ($department['department_id'] ?: 'faculty')) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($department['department_name']); ?>
                </option>
            <?php } ?>
        </select>
    </form>
    
    <h3>Exam Schedule</h3>
    <table>
        <tr>
            <th>Department</th>
            <th>Course</th>
            <th>Exam</th>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>
        <?php while($exam = $exams_result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($exam['department_name'] ?: 'Faculty Course'); ?></td>
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
        <?php while($assistant = $assistants_result->fetch_assoc()) { 
            $percentage = $total_score > 0 ? ($assistant['score'] / $total_score) * 100 : 0;
        ?>
        <tr>
            <td><?php echo htmlspecialchars($assistant['assistant_name']); ?></td>
            <td><?php echo htmlspecialchars($assistant['score']); ?></td>
            <td><?php echo number_format($percentage, 2) . '%'; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
</html>