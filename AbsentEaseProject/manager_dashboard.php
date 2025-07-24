<?php
session_start();

$host = 'localhost';
$db = 'absenteasev02complex';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manager Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2c3e50, #1a2530);
            color: white;
            padding: 15px 30px;
            font-size: 24px;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center; /* Center the header text horizontally */
            height: 70px;
            text-align: center;
            width: 100%; /* Ensure the header spans the full width */
        }

        /* Layout */
        .container {
            display: flex;
            height: calc(100vh - 70px);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: #70b5fa;
            padding: 25px 15px;
            border-right: 1px solid #e6e9ed;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .sidebar button {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 12px;
            background-color: rgb(15, 43, 61);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            text-align: center;
            transition: all 0.2s ease;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.2);
        }

        .sidebar button:hover {
            background-color: rgb(28, 85, 123);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(41, 128, 185, 0.3);
        }

        .sidebar button:active {
            transform: translateY(0);
        }

        .sub-buttons {
            display: none;
            margin-left: 15px;
            margin-bottom: 15px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .sub-buttons button {
            background-color: rgb(0, 181, 3);
            font-size: 14px;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-left: 3px solid rgb(9, 64, 0);
        }

        .sub-buttons button:hover {
            background-color: rgb(7, 74, 0);
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            padding: 25px;
            overflow: auto;
            background-color: #f5f7fa;
            transition: all 0.3s ease;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        /* Logout button styling */
        form button[type="submit"] {
            background-color: rgb(227, 36, 15);
            margin-top: 30px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-align:center;
        }

        form button[type="submit"]:hover {
            background-color: #c0392b;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #e6e9ed;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    Manager Dashboard
</div>

<!-- Layout -->
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <button onclick="toggleSub('studentSub')">Student Details</button>
        <div id="studentSub" class="sub-buttons">
            <button onclick="openFrame('manager_related_data/submit_student_details.php')">Submit Student Details</button>
            <button onclick="openFrame('manager_related_data/view_verifiable_student_details.php')">View Verifiable Student Details</button>
        </div>

        <button onclick="toggleSub('teacherSub')">Teacher Details</button>
        <div id="teacherSub" class="sub-buttons">
            <button onclick="openFrame('manager_related_data/submit_teacher_details.php')">Submit Teacher Details</button>
            <button onclick="openFrame('manager_related_data/view_verifiable_teacher_details.php')">View Verifiable Teacher Details</button>
            <button onclick="openFrame('manager_related_data/view_class_teacher_details.php')">Class Teacher Details</button>
        </div>

        <form action="manager_logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="main-content">
        <iframe id="dashboard-frame" src="" style="display: none;"></iframe>
    </div>
</div>

<script>
    function toggleSub(id) {
        const section = document.getElementById(id);
        section.style.display = section.style.display === "block" ? "none" : "block";
    }

    function openFrame(url) {
        const iframe = document.getElementById('dashboard-frame');
        iframe.style.display = 'block';
        iframe.src = url;
    }
</script>

</body>
</html>