<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["sess_user"]) || !isset($_SESSION["last_activity"])){
    header("location:index.php");
    exit;
}

// Check for session timeout (30 minutes)
if (time() - $_SESSION["last_activity"] > 1800) {
    session_unset();
    session_destroy();
    header("location:index.php");
    exit;
}

$_SESSION["last_activity"] = time();

$userRole = $_SESSION['role'] === 'S' ? 'Student' : 'Teacher';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AbsentEase</title>
    <style>
        /* Add your dashboard styles here */
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION["sess_user"]); ?></h1>
    <p>You are logged in as: <?php echo htmlspecialchars($userRole); ?></p>
    <a href="logout.php">Logout</a>
</body>
</html>