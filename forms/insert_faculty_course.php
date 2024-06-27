<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if ($_SESSION['role'] !== 'head_of_secretary') {
    header("Location: ../other/dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_course_name = $_POST['new_course_name'];
    $faculty_id_query = $conn->prepare("SELECT faculty_id FROM users WHERE username = ?");
    $faculty_id_query->bind_param("s", $_SESSION['username']);
    $faculty_id_query->execute();
    $faculty_id_query->bind_result($faculty_id);
    $faculty_id_query->fetch();
    $faculty_id_query->close();

    $sql = "INSERT INTO courses (name, faculty_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_course_name, $faculty_id);

    if ($stmt->execute()) {
        echo "Course added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: ../pages/head_of_secretary_page.php");
    exit();
}
?>

