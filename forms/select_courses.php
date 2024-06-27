<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_courses = $_POST['courses'] ?? [];
    $_SESSION['selected_courses'] = $selected_courses;


    header('Location: ../pages/assistant_page.php');
    exit();
}
?>





