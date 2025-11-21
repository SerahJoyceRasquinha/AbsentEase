<?php
session_start();

// Destroy session variables
$_SESSION = array();
session_destroy();

// Redirect to login page or home
header("Location: index.php"); // Change to your actual login page
exit();
?>
