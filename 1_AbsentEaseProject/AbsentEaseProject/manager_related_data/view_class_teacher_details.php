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
    <title>Class Teacher Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            margin: 0;
            padding: 40px 20px;
            color: #333;
            line-height: 1.6;

        }

        h3 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        h3:after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background: #3498db;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        table {
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 0;
            width: 80%;
            max-width: 1000px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 16px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
            border: none;
        }

        td {
            border-bottom: 1px solid #eaeaea;
            border-left: none;
            border-right: none;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f8ff;
            transition: background-color 0.3s ease;
        }

        @media (max-width: 768px) {
            table {
                width: 95%;
            }
            
            th, td {
                padding: 12px 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <h3>Class Teacher Details</h3>

    <table>
        <tr>
            <th>Class Teacher Name</th>
            <th>Year</th>
            <th>Course</th>
            <th>Semester</th>
        </tr>
        <?php
        $result = $conn->query("SELECT teacher_username, year_course, semester FROM class_teacher_details");
        while ($row = $result->fetch_assoc()) {
            $teacher = $row['teacher_username'];
            $year_course = $row['year_course'];
            $semester = $row['semester'];

            $year = 2000 + intval(substr($year_course, 0, 2));
            $course = substr($year_course, 2, 4);

            echo "<tr>
                    <td>$teacher</td>
                    <td>$year</td>
                    <td>$course</td>
                    <td>$semester</td>
                </tr>";
        }
        ?>
    </table>

</body>
</html>
