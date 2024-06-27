<?php
include '../db.php';

session_start();
$username = $_SESSION['username'];

if ($_SESSION['role'] !== 'secretary') {
    header("Location: ../other/dashboard.php");
    exit();
}

$new_course_name = $_POST['new_course_name'];
$day_of_week = $_POST['day_of_week'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];

// Fetch the department id of the secretary
$sql = "SELECT department_id FROM users WHERE username='$username'";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$row = $result->fetch_assoc();
$department_id = $row['department_id'];

// Insert the new course
$sql = "INSERT INTO courses (name, department_id, faculty_id) VALUES (?, ?, (SELECT faculty_id FROM departments WHERE id=?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sii', $new_course_name, $department_id, $department_id);
$stmt->execute();
$course_id = $stmt->insert_id;

// Insert the course schedule
$sql = "INSERT INTO course_schedule (course_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('isss', $course_id, $day_of_week, $start_time, $end_time);
$stmt->execute();

header("Location: ../pages/secretary_page.php");
?>


