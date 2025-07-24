<?php
session_start();

$teacherUsername = $_SESSION['teacher_username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2c3e50;
            --text-light: #ffffff;
            --text-dark: #333333;
            --border-radius: 6px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            background-color: #f5f7fa;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: var(--text-light);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            font-size: 16px;
            font-weight: 500;
        }

        .header-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .container {
            display: flex;
            height: calc(100vh - 40px);
        }

        .sidebar {
            width: 250px;
            background-color: #34495e;
            padding: 25px;
            box-sizing: border-box;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar button {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            background-color: var(--primary-color);
            color: var(--text-light);
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
        }

        .sidebar button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .sidebar button:active {
            transform: translateY(0);
        }

        .sidebar form button {
            background-color: #e74c3c;
        }

        .sidebar form button:hover {
            background-color: #c0392b;
        }

        .main-content {
            flex-grow: 1;
            position: relative;
            padding: 20px;
            background-color: #f5f7fa;
            overflow: hidden;
        }

        iframe {
            position: absolute;
            top: 20px;
            left: 20px;
            width: calc(100% - 40px);
            height: calc(100% - 40px);
            border: none;
            border-radius: var(--border-radius);
            background-color: white;
            z-index: 10;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left">👤 Welcome <?php echo htmlspecialchars($teacherUsername); ?>!</div>
        <div class="header-center">Dashboard</div>
    </header>

    <div class="container">
        <div class="sidebar">
            <button onclick="openFrame('class_teacher_details.php')">Class Teacher details</button>
            <button onclick="openFrame('verify_student_od.php')">Verify students OD</button>
            <button onclick="openFrame('view_od_requests.php')">View OD Requests</button>
            <form action="teacher_logout.php" method="post" style="margin-top: 20px;">
                <button type="submit">Logout</button>
            </form>
        </div>
        <div class="main-content" id="main-content" onclick="closeFrame(event)">
            <!-- iframe will be inserted here -->
        </div>
    </div>

    <script>
        let iframe = null;

        function openFrame(url) {
            if (iframe) iframe.remove();

            iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.id = "dashboard-frame";
            document.getElementById('main-content').appendChild(iframe);
        }

        function closeFrame(event) {
            if (!iframe) return;

            const rect = iframe.getBoundingClientRect();
            const x = event.clientX;
            const y = event.clientY;

            if (
                x < rect.left || x > rect.right ||
                y < rect.top || y > rect.bottom
            ) {
                iframe.remove();
                iframe = null;
            }
        }
    </script>
</body>
</html>

    

