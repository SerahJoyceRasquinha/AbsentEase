<?php
session_start();

// Assuming teacher username is stored in session after login
$teacher_username = $_SESSION['teacher_username'] ?? '';

if (!$teacher_username) {
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

// Handle form actions (Verify or Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['student_rollno'], $_POST['submission_date'])) {
    $action = $_POST['action'] === 'verify' ? 'verified' : 'rejected';
    $student_rollno = $_POST['student_rollno'];
    $submission_date = $_POST['submission_date'];

    $stmt = $conn->prepare("UPDATE od_requests SET faculty_verifier_status = ? WHERE student_rollno = ? AND submission_date = ?");
    $stmt->bind_param("sss", $action, $student_rollno, $submission_date);
    $stmt->execute();
    $stmt->close();

    // Optional: Redirect to refresh the page and avoid form resubmission
    header("Location: verify_student_od.php");
    exit;
}

// Fetch pending OD requests for the logged-in teacher
$stmt = $conn->prepare("SELECT student_rollno, day_and_hours, od_dates, comment, submission_date FROM od_requests WHERE faculty_verifier = ? AND faculty_verifier_status = 'pending'");
$stmt->bind_param("s", $teacher_username);
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Student OD Requests</title>
    <style>
        body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 30px;
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
    margin: 0;
    line-height: 1.6;
    min-height: 100vh;
}

.container { 
    position: relative; 
    max-width: 1000px; 
    margin: auto; 
    background: #fff; 
    padding: 30px;
    border-radius: 12px; 
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
}

.close-btn { 
    position: absolute; 
    top: 15px; 
    right: 20px; 
    font-size: 20px; 
    cursor: pointer; 
    color: #e74c3c;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.close-btn:hover {
    background-color: #e74c3c;
    color: white;
    transform: scale(1.1);
}

h2 { 
    text-align: center;
    color: #2c3e50;
    font-size: 28px;
    margin-bottom: 30px;
    position: relative;
    padding-bottom: 15px;
}

h2:after {
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
    width: 100%; 
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 25px;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

th, td { 
    padding: 14px 12px; 
    text-align: center;
    border: none;
    border-bottom: 1px solid #eaeaea;
    border-right: 1px solid #eaeaea;
}

th { 
    background-color: #3498db; 
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
}

td:last-child, th:last-child {
    border-right: none;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover {
    background-color: #f8f9fb;
}

/* Nested table styling */
td table {
    border: none;
    margin: 0;
    box-shadow: none;
    background-color: #f8f9fb;
    border-radius: 6px;
    overflow: hidden;
}

td table th {
    background-color: #5dade2;
    color: white;
    font-size: 12px;
    padding: 8px;
}

td table td {
    font-size: 13px;
    padding: 8px;
    border-right: 1px solid #e0e0e0;
    border-bottom: 1px solid #e0e0e0;
}

td table tr:nth-child(odd) {
    background-color: #f1f1f1;
}

td table tr:hover {
    background-color: #e8f4fc;
}

form.inline-form { 
    display: inline; 
}

button { 
    padding: 8px 16px; 
    margin: 0 3px; 
    border: none; 
    border-radius: 6px; 
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
}

.verify-btn { 
    background-color: #2ecc71; 
    color: white; 
}

.verify-btn:hover {
    background-color: #27ae60;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
}

.reject-btn { 
    background-color: #e74c3c; 
    color: white; 
}

.reject-btn:hover {
    background-color: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
}

/* Empty state message */
p {
    font-size: 16px;
    color: #7f8c8d;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    text-align: center;
}

@media (max-width: 768px) {
    .container {
        padding: 20px 15px;
    }
    
    table {
        font-size: 14px;
    }
    
    th, td {
        padding: 10px 8px;
    }
    
    button {
        padding: 6px 12px;
        font-size: 12px;
    }
}
    </style>
</head>
<body>

<div class="container">
    <span class="close-btn" onclick="window.parent.document.getElementById('dashboard-frame')?.remove()">✕</span>
    <h2>Pending OD Requests for Verification</h2>

    <?php if (empty($requests)): ?>
        <p style="text-align: center;">No pending OD requests to verify.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Student Roll Number</th>
                <th>Day and Hours</th>
                <th>Reason</th>
                <th>Submission Date</th>
                <th>Action</th>
            </tr>
            <?php foreach ($requests as $row): ?>
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
                    <td>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="student_rollno" value="<?= $row['student_rollno'] ?>">
                            <input type="hidden" name="submission_date" value="<?= $row['submission_date'] ?>">
                            <button type="submit" name="action" value="verify" class="verify-btn">Verify</button>
                        </form>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="student_rollno" value="<?= $row['student_rollno'] ?>">
                            <input type="hidden" name="submission_date" value="<?= $row['submission_date'] ?>">
                            <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
