<?php
session_start();

$student_rollno = $_SESSION['student_rollno'] ?? '';

if (!$student_rollno) {
    die("Access denied. Please log in.");
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "absenteasev02complex";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle OK button (delete approved or rejected record)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_date'], $_POST['status']) && in_array($_POST['status'], ['a', 'r'])) {
    $submission_date = $_POST['submission_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("DELETE FROM od_requests WHERE student_rollno = ? AND submission_date = ? AND status = ?");
    $stmt->bind_param("sss", $student_rollno, $submission_date, $status);
    $stmt->execute();
    $stmt->close();

    header("Location: od_status.php");
    exit;
}

// Fetch faculty verification data
function get_faculty_verification_data($conn, $student_rollno) {
    $stmt = $conn->prepare("SELECT faculty_verifier, submission_date, faculty_verifier_status, od_dates FROM od_requests WHERE student_rollno = ? AND faculty_verifier_status IN ('pending', 'verified', 'rejected')");
    $stmt->bind_param("s", $student_rollno);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

// Fetch OD approval data by class teacher
function get_od_data($conn, $student_rollno, $status) {
    $stmt = $conn->prepare("SELECT class_teacher_username, submission_date, od_dates FROM od_requests WHERE student_rollno = ? AND status = ?");
    $stmt->bind_param("ss", $student_rollno, $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

$faculty_verification_data = get_faculty_verification_data($conn, $student_rollno);
$approved = get_od_data($conn, $student_rollno, 'a');
$waiting = get_od_data($conn, $student_rollno, 'w');
$rejected = get_od_data($conn, $student_rollno, 'r');
?>

<!DOCTYPE html>
<html>
<head>
    <title>OD Status</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 0; 
            margin: 0;
            background-color: #f5f7fa;
            color: #2d3748;
            line-height: 1.6;
        }

        .container { 
            position: relative; 
            max-width: 1000px; 
            margin: 20px auto; 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .close-btn { 
            position: absolute; 
            top: 15px; 
            right: 20px; 
            font-size: 22px; 
            cursor: pointer; 
            color: #e53e3e;
            transition: all 0.2s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close-btn:hover {
            background-color: #fee2e2;
            transform: scale(1.1);
        }

        h1 {
            color:rgb(11, 51, 97);
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }

        h2 { 
            border-bottom: 1px solid #ddd; 
            padding-bottom: 10px;
            color: #3a6ea5;
            font-weight: 600;
            margin-top: 30px;
        }

        h3 {
            font-size: 18px;
            margin-top: 25px;
            margin-bottom: 15px;
            font-weight: 600;
            border-left: 4px solid;
            padding-left: 12px;
        }

        /* Color-coded section headers */
        h3:nth-of-type(1) {
            border-left-color: #38a169;
            color: #276749;
        }

        h3:nth-of-type(2) {
            border-left-color: #ed8936;
            color: #c05621;
        }

        h3:nth-of-type(3) {
            border-left-color: #e53e3e;
            color: #c53030;
        }

        .section { 
            margin-bottom: 40px; 
        }

        .entry { 
            margin-bottom: 12px; 
            padding: 14px 18px; 
            border-radius: 8px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-left: 4px solid;
        }

        .entry:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Color-code entries based on section */
        .section:nth-child(2) .entry {
            background: #f7fafc;
            border-left-color: #4299e1;
        }

        .section:nth-child(3) h3:nth-of-type(1) + p + .entry,
        .section:nth-child(3) h3:nth-of-type(1) + .entry {
            background: #f0fff4;
            border-left-color: #38a169;
            
        }

        .section:nth-child(3) h3:nth-of-type(2) + p + .entry,
        .section:nth-child(3) h3:nth-of-type(2) + .entry {
            background: #fffaf0;
            border-left-color: #ed8936;
        }

        .section:nth-child(3) h3:nth-of-type(3) + p + .entry,
        .section:nth-child(3) h3:nth-of-type(3) + .entry {
            background: #fff5f5;
            border-left-color: #e53e3e;
        }

        .entry-text { 
            max-width: 85%; 
            line-height: 1.6;
        }

        .entry-text b {
            color: #2d3748;
            background-color: rgba(0, 0, 0, 0.04);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }

        .ok-btn { 
            padding: 8px 16px; 
            background-color: #3a6ea5; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .ok-btn:hover {
            background-color: #2c5282;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .ok-btn:active {
            transform: translateY(0);
        }

        p {
            color: #718096;
            padding: 10px 16px;
            background-color: #f8fafc;
            border-radius: 6px;
            margin-top: 8px;
            margin-bottom: 16px;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="container">
    <span class="close-btn" onclick="window.parent.document.getElementById('dashboard-frame')?.remove()">✕</span>
    <h1>OD Request Status</h1>

    <!-- Faculty Verification Section -->
<div class="section">
    <h2>Respective Faculty For OD Event Conductance and Verification</h2>
    <?php if (empty($faculty_verification_data)): ?>
        <p>No faculty verification requests.</p>
    <?php else: ?>
        <?php foreach ($faculty_verification_data as $row): ?>
            <div class="entry">
                <div class="entry-text">
                    <?php if ($row['faculty_verifier_status'] === 'verified'): ?>
                        <?= htmlspecialchars($row['faculty_verifier']) ?> has verified your OD request which was submitted on <b><?= htmlspecialchars($row['submission_date']) ?></b> for the dates: <b><?= htmlspecialchars($row['od_dates']) ?></b>.
                    <?php elseif ($row['faculty_verifier_status'] === 'pending' && $row['faculty_verifier'] != 'NONE'): ?>
                        <?= htmlspecialchars($row['faculty_verifier']) ?> has not yet verified your OD request which was submitted on <b><?= htmlspecialchars($row['submission_date']) ?></b> for the dates: <b><?= htmlspecialchars($row['od_dates']) ?></b>.
                     <?php elseif ($row['faculty_verifier_status'] === 'pending' && $row['faculty_verifier'] == 'NONE'): ?>
                        <b> ---- </b> 
                    <?php elseif ($row['faculty_verifier_status'] === 'rejected'): ?>
                        <?= htmlspecialchars($row['faculty_verifier']) ?> has rejected the verification for your OD submitted on <b><?= htmlspecialchars($row['submission_date']) ?></b> for the dates: <b><?= htmlspecialchars($row['od_dates']) ?></b>.
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Class Teacher Approval Section -->
<div class="section">
    <h2>Class Teacher Approval</h2>

    <!-- Approved -->
    <h3>Approved</h3>
    <?php if (empty($approved)): ?>
        <p>No approved requests yet.</p>
    <?php else: ?>
        <?php foreach ($approved as $row): ?>
            <div class="entry">
                <div class="entry-text">
                    <?= htmlspecialchars($row['class_teacher_username']) ?> has approved your OD which was submitted on <b><?= htmlspecialchars($row['submission_date']) ?></b> for the dates: <b><?= htmlspecialchars($row['od_dates']) ?></b>
                </div>
                <form method="POST">
                    <input type="hidden" name="submission_date" value="<?= $row['submission_date'] ?>">
                    <input type="hidden" name="status" value="a">
                    <button type="submit" class="ok-btn">OK</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Waiting -->
    <h3>Waiting</h3>
    <?php if (empty($waiting)): ?>
        <p>No pending requests.</p>
    <?php else: ?>
        <?php foreach ($waiting as $row): ?>
            <div class="entry">
                <div class="entry-text">
                    <?= htmlspecialchars($row['class_teacher_username']) ?> has not yet approved your OD which was submitted on <b><?= htmlspecialchars($row['submission_date']) ?></b> for the dates: <b><?= htmlspecialchars($row['od_dates']) ?></b>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Rejected -->
    <h3>Rejected</h3>
    <?php if (empty($rejected)): ?>
        <p>No rejected requests.</p>
    <?php else: ?>
        <?php foreach ($rejected as $row): ?>
            <div class="entry">
                <div class="entry-text">
                    <?= htmlspecialchars($row['class_teacher_username']) ?> has rejected your OD which was submitted on <b><?= htmlspecialchars($row['submission_date']) ?></b> for the dates: <b><?= htmlspecialchars($row['od_dates']) ?></b>. Please contact your class teacher.
                </div>
                <form method="POST">
                    <input type="hidden" name="submission_date" value="<?= $row['submission_date'] ?>">
                    <input type="hidden" name="status" value="r">
                    <button type="submit" class="ok-btn">OK</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


</div>
</body>
</html>
