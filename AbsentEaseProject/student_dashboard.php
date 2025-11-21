<?php
session_start();

$studentUsername = $_SESSION['student_rollno'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <style>
        :root {
            --primary-color: #3a6ea5;
            --primary-light: #4a7fb5;
            --primary-dark: #2c5282;
            --secondary-color: #f8f9fa;
            --accent-color: #38b2ac;
            --text-color: #2d3748;
            --text-light: #718096;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }
        
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        
        .welcome {
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .welcome::before {
            content: "👤";
            margin-right: 8px;
            font-size: 1.2rem;
        }
        
        .title {
            font-size: 1.8rem;
            margin: 0 auto;
            text-align: center;
            flex-grow: 1;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .container {
            display: flex;
            height: calc(100vh - 74px);
            background-color: var(--secondary-color);
            text-align:center;
            
        }
        
        .sidebar {
            width: 240px;
            background: #70b5fa;
            padding: 1.5rem;
            box-sizing: border-box;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-align:center;
        }
        
        .sidebar button {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 6px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center; /* Changed from flex-start to center */
            text-align: center;
        }

        .sidebar button::before {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        
        .sidebar button:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .sidebar button:active {
            transform: translateY(0);
        }
        
        .sidebar form {
            margin-top: auto;
        }
        
        .sidebar form button {
            background-color: #e53e3e;
        }
        
        .sidebar form button:hover {
            background-color: #c53030;
        }
        
        .main-content {
            flex-grow: 1;
            position: relative;
            padding: 1.5rem;
            background-color: #f5f7fa;
            overflow: hidden;
        }
        
        iframe {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            width: calc(100% - 3rem);
            height: calc(100% - 3rem);
            border: none;
            background-color: white;
            z-index: 10;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        /* Animation for iframe */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        iframe {
            animation: fadeIn 0.3s ease forwards;
        }
    </style>
</head>
<body>
    <header>
        <div class="welcome">Welcome <?= htmlspecialchars($studentUsername) ?>!</div>
        <div class="title">Student Dashboard</div>
    </header>

    <div class="container">
        <div class="sidebar">
            <button onclick="openFrame('request_od.php')" >Request OD</button>
            <button onclick="openFrame('od_status.php')">View OD Status</button>
            <form action="student_logout.php" method="post">
                <button type="submit">Logout</button>
            </form>
        </div>
        <div class="main-content" id="main-content" onclick="closeFrame(event)">
            <!-- iframe will be added here -->
        </div>
    </div>

    <script>
        let iframe = null;

        function openFrame(url) {
            // Remove existing iframe if any
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