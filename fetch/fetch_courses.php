<?php
include '../db.php';

$username = $_SESSION['username'];


$sql = "
    SELECT * FROM courses 
    WHERE department_id = (SELECT department_id FROM users WHERE username='$username') 
    OR (department_id IS NULL AND faculty_id = (SELECT faculty_id FROM users WHERE username='$username'))
";
$courses_result = $conn->query($sql);

if (!$courses_result) {
    die("Query failed: " . $conn->error);
}

$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode($courses);
?>


