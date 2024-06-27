<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['error_message'])) {
    header("Location: ../pages/dashboard.php");
    exit();
}

$error_message = $_SESSION['error_message'];
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<header>
    <h1>Error</h1>
    <a href="../authentication/logout.php">Logout</a>
</header>
<div class="container">
    <p><?php echo htmlspecialchars($error_message); ?></p>
    <a href="../pages/secretary_page.php">Go Back</a>
</div>
</body>
</html>
