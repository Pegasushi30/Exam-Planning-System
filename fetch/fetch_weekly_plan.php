<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if ($_SESSION['role'] !== 'assistant') {
    header("Location: ../pages/dashboard.php");
    exit();
}

$username = $_SESSION['username'];
$selected_courses = isset($_SESSION['selected_courses']) ? $_SESSION['selected_courses'] : [];

// Create course filter based on the selected courses
$course_filter = '';
if (!empty($selected_courses)) {
    $course_ids = implode(',', array_map('intval', $selected_courses));
    $course_filter = "AND courses.id IN ($course_ids)";
}

// Fetch weekly plan for the assistant based on selected courses or department courses
$sql = "
    SELECT 
        courses.name AS course_name,
        exams.name AS exam_name,
        exams.date AS exam_date,
        exams.time AS exam_time
    FROM 
        exam_assignments 
    JOIN 
        exams ON exam_assignments.exam_id = exams.id
    JOIN 
        courses ON exams.course_id = courses.id
    WHERE 
        exam_assignments.assistant_id = (SELECT id FROM users WHERE username='$username')
        $course_filter
    ORDER BY 
        exams.date, exams.time
";
$plan_result = $conn->query($sql);

if (!$plan_result) {
    die("Query failed: " . $conn->error);
}

$weeklyPlans = [];
while ($row = $plan_result->fetch_assoc()) {
    $exam_date = new DateTime($row['exam_date']);
    $week = $exam_date->format("W");
    $year = $exam_date->format("Y");
    $week_key = "$year-W$week";

    $timeslot = date('H:i', strtotime($row['exam_time'])) . ' - ' . date('H:i', strtotime($row['exam_time']) + 3600);
    $day = strtolower($exam_date->format('l'));

    if (!isset($weeklyPlans[$week_key])) {
        $weeklyPlans[$week_key] = [];
    }

    if (!isset($weeklyPlans[$week_key][$timeslot])) {
        $weeklyPlans[$week_key][$timeslot] = [];
    }

    $weeklyPlans[$week_key][$timeslot][$day] = $row['course_name'] . ' ' . $row['exam_name'];
}

echo json_encode($weeklyPlans);

$conn->close();
?>

