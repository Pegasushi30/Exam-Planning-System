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

// Fetch the user ID
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$user_id = $user['id'];

// Get the course ID from the form
$course_id = isset($_POST['add_course']) ? intval($_POST['add_course']) : 0;

if ($course_id === 0) {
    die("Invalid course selected.");
}

// Checking if the assistant is already assigned to this course for exams
$sql = "
    SELECT 1
    FROM exam_assignments
    JOIN exams ON exam_assignments.exam_id = exams.id
    WHERE exam_assignments.assistant_id = ? AND exams.course_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("You cannot add a course you are assigned to for exams.");
}

// Checking for conflicts with the current course schedule
$sql = "
    SELECT 
        cs.day_of_week, cs.start_time, cs.end_time 
    FROM course_schedule cs
    JOIN assistant_courses ac ON cs.course_id = ac.course_id
    WHERE ac.assistant_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_schedule = $stmt->get_result();

$current_courses = [];
while ($row = $current_schedule->fetch_assoc()) {
    $day_of_week = $row['day_of_week'];
    $start_time = $row['start_time'];
    $end_time = $row['end_time'];
    $current_courses[$day_of_week][] = ['start' => $start_time, 'end' => $end_time];
}

// Fetch the selected course schedule
$sql = "
    SELECT 
        cs.day_of_week, cs.start_time, cs.end_time
    FROM course_schedule cs
    WHERE cs.course_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course_schedule = $stmt->get_result();

while ($row = $course_schedule->fetch_assoc()) {
    $day_of_week = $row['day_of_week'];
    $start_time = $row['start_time'];
    $end_time = $row['end_time'];

    if (isset($current_courses[$day_of_week])) {
        foreach ($current_courses[$day_of_week] as $schedule) {
            if (($start_time >= $schedule['start'] && $start_time < $schedule['end']) ||
                ($end_time > $schedule['start'] && $end_time <= $schedule['end'])) {
                die("The selected course conflicts with your current schedule.");
            }
        }
    }
}

// Add the course to the assistant's schedule
$sql = "
    INSERT INTO assistant_courses (assistant_id, course_id)
    VALUES (?, ?)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $course_id);

if ($stmt->execute()) {
    header("Location: ../pages/assistant_page.php");
    exit();
} else {
    die("Failed to add course to schedule: " . $conn->error);
}
?>
