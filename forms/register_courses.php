<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courses = $_POST['courses'];

    
    $user_id = 1;


    $sql = "DELETE FROM assistant_courses WHERE assistant_id = '$user_id'";
    $conn->query($sql);

    foreach ($courses as $course_id) {
        $sql = "INSERT INTO assistant_courses (assistant_id, course_id) VALUES ('$user_id', '$course_id')";
        $conn->query($sql);
    }

    header('Location: ../pages/assistant_page.php');
}

$conn->close();
?>

