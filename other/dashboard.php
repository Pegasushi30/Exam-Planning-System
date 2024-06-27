<?php
session_start();
include '../db.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../authentication/login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

switch ($role) {
    case 'assistant':
        header("Location: ../pages/assistant_page.php");
        exit();
    case 'secretary':
        header("Location: ../pages/secretary_page.php");
        exit();
    case 'head_of_department':
        header("Location: ../pages/head_of_department_page.php");
        exit();
    case 'head_of_secretary':
        header("Location: ../pages/head_of_secretary_page.php");
        exit();
    case 'dean':
        header("Location: ../pages/dean_page.php");
        exit();
    default:
        header("Location: ../authentication/login.php");
        exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<header>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
    <a href="../authentication/logout.php">Logout</a>
</header>
</body>
</html>


