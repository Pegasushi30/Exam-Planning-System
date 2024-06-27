<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['secretary', 'head_of_secretary'])) {
    header("Location: ../other/dashboard.php");
    exit();
}

include '../db.php';

// Get the message from the URL
$message = isset($_GET['message']) ? urldecode($_GET['message']) : "An error occurred.";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Conflict Warning</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<header>
    <h1>Conflict Warning</h1>
    <a href="../authentication/logout.php">Logout</a>
</header>
<div class="container">
    <p><?php echo htmlspecialchars($message); ?></p>
    <?php if ($_SESSION['role'] === 'secretary'): ?>
        <a href="../pages/secretary_page.php">Go Back to Secretary Page</a>
    <?php elseif ($_SESSION['role'] === 'head_of_secretary'): ?>
        <a href="../pages/head_of_secretary_page.php">Go Back to Head of Secretary Page</a>
    <?php endif; ?>
</div>
</body>
</html>