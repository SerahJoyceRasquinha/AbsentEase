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

// Handle teacher submission
if (isset($_POST['submit_teacher'])) {
    $username = $_POST['teacher_username'];
    $email = $_POST['teacher_email'];

    $stmt = $conn->prepare("INSERT INTO verifiable_teacher_details (username, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Teacher details submitted successfully!');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Teacher Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            text-align: center;
            padding: 40px 20px;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        h3 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
            text-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        form {
            background-color: white;
            padding: 35px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        form:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        form input[type="text"],
        form input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            margin: 12px 0;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            color: #333;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        form input[type="text"]:focus,
        form input[type="email"]:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        form input[type="text"]::placeholder,
        form input[type="email"]::placeholder {
            color: #aaa;
        }

        form input[type="submit"] {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            border: none;
            color: white;
            padding: 14px 20px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 20px;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(46, 204, 113, 0.2);
        }

        form input[type="submit"]:hover {
            background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
            box-shadow: 0 6px 10px rgba(46, 204, 113, 0.3);
            transform: translateY(-2px);
        }

        form input[type="submit"]:active {
            transform: translateY(1px);
            box-shadow: 0 2px 5px rgba(46, 204, 113, 0.2);
        }

        @media (max-width: 480px) {
            form {
                padding: 25px 20px;
            }
            
            h3 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    <h3>Enter Teacher Details</h3>
    <form method="post">
        <input type="text" name="teacher_username" placeholder="Username" required />
        <input type="email" name="teacher_email" placeholder="Email" required />
        <input type="submit" name="submit_teacher" value="Submit" />
    </form>

</body>
</html>
