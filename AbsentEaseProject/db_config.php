<?php
$host = "localhost";
$dbUsername = "your_db_username";
$dbPassword = "your_db_password";
$dbname = "LoginPage";

try {
    $conn = new mysqli($host, $dbUsername, $dbPassword, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to prevent injection attacks
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log($e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>