<?php
session_start();
include '../db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password === $row['password']) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            switch ($row['role']) {
                case 'assistant':
                    header("Location: ../pages/assistant_page.php");
                    break;
                case 'secretary':
                    header("Location: ../pages/secretary_page.php");
                    break;
                case 'head_of_department':
                    header("Location: ../pages/head_of_department_page.php");
                    break;
                case 'head_of_secretary':
                    header("Location: ../pages/head_of_secretary_page.php");
                    break;
                case 'dean':
                    header("Location: ../pages/dean_page.php");
                    break;
                default:
                    header("Location: ../other/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="../styles.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
        }

        header {
            width: 100%;
            text-align: center;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            position: fixed;
            top: 0;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 80px auto 0; /* Adjust margin to push content below fixed header */
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .login-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .login-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
            cursor: pointer;
            margin-top: 20px;
        }

        .login-container input[type="submit"]:hover {
            background-color: #555;
        }

        .login-container a {
            display: block;
            margin-top: 10px;
            color: #333;
            text-decoration: none;
        }

        .login-container a:hover {
            text-decoration: underline;
        }

        .login-container p.error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<header>
    <h1>Exam Planning System</h1>
</header>
<div class="login-container">
    <h2>Login</h2>
    <form method="post" action="">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
</div>
</body>
</html>





