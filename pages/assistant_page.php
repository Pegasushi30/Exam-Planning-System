<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if ($_SESSION['role'] !== 'assistant') {
    header("Location: ../other/dashboard.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch courses the assistant is NOT assigned to for exams
$sql = "
    SELECT courses.id, courses.name 
    FROM courses 
    WHERE courses.id NOT IN (
        SELECT exams.course_id 
        FROM exam_assignments 
        JOIN exams ON exam_assignments.exam_id = exams.id 
        WHERE exam_assignments.assistant_id = (SELECT id FROM users WHERE username='$username')
    ) AND (courses.department_id = (SELECT department_id FROM users WHERE username='$username') OR courses.department_id IS NULL)
";
$courses_result = $conn->query($sql);

if (!$courses_result) {
    die("Query failed: " . $conn->error);
}

// Fetch current course schedule for the assistant
$sql = "
    SELECT 
        courses.name AS course_name,
        course_schedule.day_of_week,
        course_schedule.start_time,
        course_schedule.end_time
    FROM 
        assistant_courses
    JOIN 
        course_schedule ON assistant_courses.course_id = course_schedule.course_id
    JOIN 
        courses ON assistant_courses.course_id = courses.id
    WHERE 
        assistant_courses.assistant_id = (SELECT id FROM users WHERE username='$username')
";
$current_courses_result = $conn->query($sql);

if (!$current_courses_result) {
    die("Query failed: " . $conn->error);
}

// Fetch weekly plan for the assistant
$sql = "
    SELECT 
        DISTINCT courses.name AS course_name,
        exams.name AS exam_name,
        exams.exam_date AS exam_date,
        exams.start_time AS exam_time,
        exams.end_time AS end_time
    FROM 
        exam_assignments 
    JOIN 
        exams ON exam_assignments.exam_id = exams.id
    JOIN 
        courses ON exams.course_id = courses.id
    WHERE 
        exam_assignments.assistant_id = (SELECT id FROM users WHERE username='$username')
    ORDER BY 
        exams.exam_date, exams.start_time
";
$plan_result = $conn->query($sql);

if (!$plan_result) {
    die("Query failed: " . $conn->error);
}

$weeklyPlans = [];
$weeklyPlansKey = [];

while ($row = $plan_result->fetch_assoc()) {
    $exam_date = new DateTime($row['exam_date']);
    $year = $exam_date->format("Y");
    $month = $exam_date->format("F");
    $week_of_month = ceil($exam_date->format("j") / 7);

    $week_key = "$month Week $week_of_month $year";

    $start_time = new DateTime($row['exam_time']);
    $end_time = new DateTime($row['end_time']);
    $interval = new DateInterval('PT1H');
    $timeslots = new DatePeriod($start_time, $interval, $end_time);

    $day = strtolower($exam_date->format('l'));


    if (!in_array($week_key, $weeklyPlansKey)) {
        $weeklyPlansKey[] = $week_key;
    }

    if (!isset($weeklyPlans[$week_key])) {
        $weeklyPlans[$week_key] = [];
    }

    foreach ($timeslots as $timeslot) {
        $timeslot_key = $timeslot->format('H:i') . ' - ' . $timeslot->add($interval)->format('H:i');
        if (!isset($weeklyPlans[$week_key][$timeslot_key])) {
            $weeklyPlans[$week_key][$timeslot_key] = [];
        }
        // Check for duplicates
        if (!in_array($row['exam_name'], $weeklyPlans[$week_key][$timeslot_key])) {
            $weeklyPlans[$week_key][$timeslot_key][$day] = $row['exam_name'];
        }
    }
}

// Fetch courses for the assistant's weekly plan
$weeklyCourses = [];
$sql = "
    SELECT 
        courses.name AS course_name,
        course_schedule.day_of_week,
        course_schedule.start_time,
        course_schedule.end_time
    FROM 
        assistant_courses
    JOIN 
        course_schedule ON assistant_courses.course_id = course_schedule.course_id
    JOIN 
        courses ON assistant_courses.course_id = courses.id
    WHERE 
        assistant_courses.assistant_id = (SELECT id FROM users WHERE username='$username')
";
$course_schedule_result = $conn->query($sql);

if ($course_schedule_result) {
    while ($row = $course_schedule_result->fetch_assoc()) {
        $day = strtolower($row['day_of_week']);
        $start_time = new DateTime($row['start_time']);
        $end_time = new DateTime($row['end_time']);
        $interval = new DateInterval('PT1H');
        $timeslots = new DatePeriod($start_time, $interval, $end_time);

        foreach ($timeslots as $timeslot) {
            $timeslot_key = $timeslot->format('H:i') . ' - ' . $timeslot->add($interval)->format('H:i');
            $weeklyCourses[$timeslot_key][$day] = $row['course_name'];
        }
    }
}

// Merge weeklyCourses into weeklyPlans for display
foreach ($weeklyCourses as $timeslot_key => $days) {
    foreach ($days as $day => $course_name) {
        foreach ($weeklyPlans as $week_key => &$weeklyPlan) {
            if (!isset($weeklyPlan[$timeslot_key])) {
                $weeklyPlan[$timeslot_key] = [];
            }
            $weeklyPlan[$timeslot_key][$day] = $course_name;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assistant Page</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<header>
    <h1>Assistant Page</h1>
    <a href="../authentication/logout.php">Logout</a>
</header>
<div class="container">
    <form method="post" action="../forms/add_course.php">
        <label for="add_course">Add Course to Schedule:</label>
        <select name="add_course" required>
            <?php while($course = $courses_result->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($course['id']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
            <?php } ?>
        </select>
        <input type="submit" value="Add Course">
    </form>

    <h3>Current Course Schedule</h3>
    <table>
        <tr>
            <th>Course Name</th>
            <th>Day of the Week</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>
        <?php while($course = $current_courses_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                <td><?php echo htmlspecialchars($course['day_of_week']); ?></td>
                <td><?php echo htmlspecialchars($course['start_time']); ?></td>
                <td><?php echo htmlspecialchars($course['end_time']); ?></td>
            </tr>
        <?php } ?>
    </table>

    <h3>Weekly Plan</h3>
    <?php foreach ($weeklyPlansKey as $week_key) { ?>
        <h4><?php echo $week_key; ?></h4>
        <table>
            <tr>
                <th>Timeslot</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
                <th>Sunday</th>
            </tr>
            <?php
            $timeslots = [
                '08:00 - 09:00',
                '09:00 - 10:00',
                '10:00 - 11:00',
                '11:00 - 12:00',
                '12:00 - 13:00',
                '13:00 - 14:00',
                '14:00 - 15:00',
                '15:00 - 16:00',
                '16:00 - 17:00',
                '17:00 - 18:00',
                '18:00 - 19:00',
                '19:00 - 20:00'
            ];

            foreach ($timeslots as $timeslot) {
                echo "<tr>";
                echo "<td>$timeslot</td>";
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                foreach ($days as $day) {
                    $course = isset($weeklyPlans[$week_key][$timeslot][$day]) ? $weeklyPlans[$week_key][$timeslot][$day] : '';
                    echo "<td>$course</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>
    <?php } ?>
    <button onclick="location.reload();">Refresh</button>
</div>
</body>
</html>


