<?php
session_start();

// Assuming class teacher's username is stored in session
$class_teacher_username = $_SESSION['teacher_username'] ?? '';

if (!$class_teacher_username) {
    die("Access denied. Please log in.");
}

// DB connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "absenteasev02complex";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Approve or Dismiss actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['student_rollno'], $_POST['submission_date'])) {
    $action = $_POST['action'] === 'approve' ? 'a' : 'r';
    $student_rollno = $_POST['student_rollno'];
    $submission_date = $_POST['submission_date'];

    $stmt = $conn->prepare("UPDATE od_requests SET status = ? WHERE student_rollno = ? AND submission_date = ? AND class_teacher_username = ?");
    $stmt->bind_param("ssss", $action, $student_rollno, $submission_date, $class_teacher_username);
    $stmt->execute();
    $stmt->close();

    // Refresh page to avoid resubmission
    header("Location: view_od_requests.php");
    exit;
}

// Fetch pending OD requests for this class teacher
$stmt = $conn->prepare("
    SELECT student_rollno, day_and_hours, comment, submission_date, od_dates, faculty_verifier, faculty_verifier_status 
    FROM od_requests 
    WHERE class_teacher_username = ? AND status = 'w'
");

$stmt->bind_param("s", $class_teacher_username);
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View OD Requests</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            padding: 0; 
            margin: 0;
            background-color: #f8f9fa; 
            color: #333;
        }

        .container { 
            position: relative; 
            max-width: 1200px; 
            margin: 30px auto;
            background: #fff; 
            padding: 25px 30px; 
            border-radius: 12px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); 
        }

        .close-btn { 
            position: absolute; 
            top: 15px; 
            right: 20px; 
            font-size: 22px; 
            cursor: pointer; 
            color: #dc3545;
            transition: transform 0.2s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close-btn:hover {
            background-color: #ffeeee;
            transform: scale(1.1);
        }

        h2 { 
            text-align: center; 
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 600;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f1f1;
        }

        table { 
            width: 100%; 
            border-collapse: collapse;
            margin-top: 20px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #ccd0d4;
        }

        th, td { 
            padding: 12px 15px; 
            text-align: center;
            border: 1px solid #ccd0d4;
        }

        th { 
            background-color: #3498db; 
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
            border: 1px solid #2980b9;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f8ff;
        }

        td table {
            margin: 0;
            width: 100%;
            box-shadow: none;
            border: 1px solid #ccd0d4;
        }

        td table th {
            background-color: #e9ecef;
            color: #495057;
            font-size: 0.85em;
            padding: 8px 10px;
            border: 1px solid #ced4da;
        }

        td table td {
            padding: 8px 10px;
            font-size: 0.9em;
            border: 1px solid #ced4da;
        }

        button { 
            padding: 8px 16px; 
            margin: 3px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .approve-btn { 
            background-color: #28a745; 
            color: white; 
        }

        .approve-btn:hover {
            background-color: #218838;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .dismiss-btn { 
            background-color: #dc3545; 
            color: white; 
        }

        .dismiss-btn:hover {
            background-color: #c82333;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        form.inline-form { 
            display: inline-block;
            margin: 2px;
        }

        p {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="container">
    <span class="close-btn" onclick="window.parent.document.getElementById('dashboard-frame')?.remove()">✕</span>
    <h2><b>Pending OD Requests for Class Teacher Approval</b></h2>

    <?php if (empty($requests)): ?>
        <p style="text-align: center;">No pending OD requests to approve.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Student Roll Number</th>
                <th>Day and Hours</th>
                <th>Reason</th>
                <th>Submission Date</th>
                <th>Faculty Verifier</th>
                <th>Faculty Verifier Status</th>
                <th>Action</th>
            </tr>
            <?php
            function generateSubTable($od_dates_str, $day_and_hours_str) {
                $od_dates = explode(',', $od_dates_str);
                $dayHours = [];

                // Parse day and hours like "monday[2-3-4],tuesday[4-8-9]"
                preg_match_all('/(\w+)\[([^\]]+)\]/', $day_and_hours_str, $matches, PREG_SET_ORDER);
                foreach ($matches as $i => $match) {
                    $day = ucfirst($match[1]); // e.g., "Monday"
                    $hours = str_replace('-', ',', $match[2]); // e.g., "2-3-4" => "2,3,4"
                    $date = $od_dates[$i] ?? '';
                    echo "<tr><td>$date</td><td>$day</td><td>$hours</td></tr>";
                }
            }

            foreach ($requests as $row):
                $status_raw = strtolower($row['faculty_verifier_status']);
                $status_display = strtoupper($status_raw);
                $status_color = match($status_raw) {
                    'pending' => 'orange',
                    'verified' => 'green',
                    'rejected' => 'red',
                    default => 'black'
                };
            ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_rollno']) ?></td>
                    <td>
                        <?php
                        // Split comma-separated dates
                        $od_dates = explode(',', $row['od_dates']); // Make sure 'od_dates' is selected in query
                        $day_hour_string = $row['day_and_hours'];
                        $day_hour_map = [];

                        // Parse day_and_hours like "monday[2-3-4],tuesday[3-4]"
                        preg_match_all('/(\w+)\[([^\]]+)\]/', $day_hour_string, $matches, PREG_SET_ORDER);
                        foreach ($matches as $match) {
                            $day = ucfirst(strtolower($match[1])); // Normalize capitalization
                            $hours = explode('-', $match[2]);
                            $day_hour_map[$day] = $hours;
                        }

                        // Match od_dates with days based on array index
                        echo '<table style="width:100%; border: 1px solid #ddd; margin-top: 5px;">';
                        echo '<tr><th>Date</th><th>Day</th><th>Hours</th></tr>';
                        $index = 0;
                        foreach ($day_hour_map as $day => $hours) {
                            $date = $od_dates[$index] ?? '';
                            echo "<tr>
                                    <td>" . htmlspecialchars($date) . "</td>
                                    <td>" . htmlspecialchars($day) . "</td>
                                    <td>" . htmlspecialchars(implode(',', $hours)) . "</td>
                                </tr>";
                            $index++;
                        }
                        echo '</table>';
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['comment']) ?></td>
                    <td><?= htmlspecialchars($row['submission_date']) ?></td>
                    <td><?= htmlspecialchars($row['faculty_verifier']) ?></td>
                    <td style="color: <?= $status_color ?>; font-weight: bold;">
                        <?= $status_display ?>
                    </td>
                    <td>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="student_rollno" value="<?= $row['student_rollno'] ?>">
                            <input type="hidden" name="submission_date" value="<?= $row['submission_date'] ?>">
                            <button type="submit" name="action" value="approve" class="approve-btn">Approve</button>
                        </form>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="student_rollno" value="<?= $row['student_rollno'] ?>">
                            <input type="hidden" name="submission_date" value="<?= $row['submission_date'] ?>">
                            <button type="submit" name="action" value="dismiss" class="dismiss-btn">Dismiss</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

    <?php endif; ?>
</div>

</body>
</html>
