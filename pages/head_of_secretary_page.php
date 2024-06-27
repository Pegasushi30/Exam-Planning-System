<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if ($_SESSION['role'] !== 'head_of_secretary') {
    header("Location: ../other/dashboard.php");
    exit();
}

$username = $_SESSION['username'];

$sql = "SELECT * FROM courses WHERE faculty_id=(SELECT faculty_id FROM users WHERE username='$username') AND department_id IS NULL";
$courses_result = $conn->query($sql);

if (!$courses_result) {
    die("Query failed: " . $conn->error);
}


$sql = "SELECT * FROM assistants WHERE faculty_id=(SELECT faculty_id FROM users WHERE username='$username') ORDER BY score ASC";
$assistants_result = $conn->query($sql);

if (!$assistants_result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Head of Secretary Page</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<header>
    <h1>Head of Secretary Page</h1>
    <a href="../authentication/logout.php">Logout</a>
</header>
<div class="container">
    <form method="post" action="../forms/insert_exam.php">
        <label for="course">Select Course:</label>
        <select name="course" required>
            <?php while($course = $courses_result->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($course['id']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
            <?php } ?>
        </select><br>
        Exam Name: <input type="text" name="exam_name" required><br>
        Exam Date: <input type="date" name="exam_date" required><br>
        Start Time: <input type="time" name="start_time" required><br>
        End Time: <input type="time" name="end_time" required><br>
        Number of Assistants: <input type="number" name="num_assistants" required><br>
        Number of Classes: <input type="number" name="num_classes" required><br>
        <input type="submit" value="Insert Exam">
    </form>

    <h3>Add New Faculty Course</h3>
    <form method="post" action="../forms/insert_faculty_course.php">
        <label for="new_course_name">Course Name:</label>
        <input type="text" name="new_course_name" required><br>
        <label for="day_of_week">Day of the Week:</label>
        <select name="day_of_week" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
        </select><br>
        Start Time: <input type="time" name="start_time" required><br>
        End Time: <input type="time" name="end_time" required><br>
        <input type="submit" value="Add Course">
    </form>

    <h3>Assistant Scores</h3>
    <table>
        <tr>
            <th>Assistant Name</th>
            <th>Score</th>
        </tr>
        <?php while($assistant = $assistants_result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($assistant['name']); ?></td>
            <td><?php echo htmlspecialchars($assistant['score']); ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>


