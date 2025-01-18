<?php
// Database connection
$host = 'localhost';
$dbname = 'LoginPage';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Query to fetch login data
$sql = "SELECT id, username, role, created_at FROM login";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data); // Convert to JSON
} else {
    echo json_encode(["message" => "No records found"]);
}

$conn->close();
?>
