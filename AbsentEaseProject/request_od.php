<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "absenteasev02complex";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_rollno = $_SESSION['student_rollno'] ?? '';
$class_teacher_username = '';
$teacher_timetable = '';

if ($student_rollno) {
    $stmt = $conn->prepare("SELECT class_teacher_username FROM verifiable_student_details WHERE student_rollno = ?");
    $stmt->bind_param("s", $student_rollno);
    $stmt->execute();
    $stmt->bind_result($class_teacher_username);
    $stmt->fetch();
    $stmt->close();

    if ($class_teacher_username) {
        $stmt = $conn->prepare("SELECT timetable FROM class_teacher_details WHERE teacher_username = ?");
        $stmt->bind_param("s", $class_teacher_username);
        $stmt->execute();
        $stmt->bind_result($teacher_timetable);
        $stmt->fetch();
        $stmt->close();
    }
}

// Save OD request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_hours = $_POST['day_and_hours'] ?? '';
    $comment = trim($_POST['comment']) ?: null;
    $status = 'w';
    $od_dates = [];

    if (!empty($selected_hours)) {
        $weekDates = getWeekDates();
        preg_match_all('/(\w+)\[([^\]]+)\]/', $selected_hours, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $day = strtolower($match[1]);
            if (isset($weekDates[$day])) {
                $od_dates[] = $weekDates[$day];
            }
        }
    }

    $od_dates_str = implode(',', $od_dates);

    $faculty_verifier = $_POST['faculty_verifier'] ?? null;
    // Get today's date in Y-m-d format
    $today = date("Y-m-d");

    // Check if this student already submitted an OD request today
    $check_stmt = $conn->prepare("SELECT 1 FROM od_requests WHERE student_rollno = ? AND DATE(submission_date) = ?");
    $check_stmt->bind_param("ss", $student_rollno, $today);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script>alert('An OD request was already sent by you today. Please wait until tomorrow.');</script>";
        $check_stmt->close();
    } else {
        $check_stmt->close();

        // Proceed with insertion
        $faculty_verifier = $_POST['faculty_verifier'] ?? null;
        $link = trim($_POST['link']) ?: null;
        $stmt = $conn->prepare("INSERT INTO od_requests (student_rollno, day_and_hours, od_dates, faculty_verifier, status, comment, class_teacher_username, link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $student_rollno, $selected_hours, $od_dates_str, $faculty_verifier, $status, $comment, $class_teacher_username, $link);
        $stmt->execute();
        $stmt->close();

echo "<script>alert('OD request submitted successfully');</script>";


        echo "<script>alert('OD request submitted successfully');</script>";
    }

}

function getWeekDates() {
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $weekDates = [];

    $today = new DateTime();
    $todayDay = strtolower($today->format('l'));

    // Set reference to Monday
    $monday = clone $today;
    $dayOfWeek = array_search($todayDay, $days);
    if ($dayOfWeek === false) {
        // If today is Sunday, move to next Monday
        $monday->modify('next monday');
    } else {
        $monday->modify("-$dayOfWeek days");
    }

    foreach ($days as $index => $day) {
        $date = clone $monday;
        $date->modify("+$index days");
        $weekDates[$day] = $date->format('d-m-Y');
    }

    return $weekDates;
}

$weekDates = getWeekDates();

$faculty_usernames = [];
$faculty_result = $conn->query("SELECT username FROM teacher_credentials");

if ($faculty_result && $faculty_result->num_rows > 0) {
    while ($row = $faculty_result->fetch_assoc()) {
        $faculty_usernames[] = $row['username'];
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>OD Request</title>
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
            border: none;
            border-radius: 12px;
            padding: 30px; 
            max-width: 1000px; 
            background: white; 
            margin: 20px auto;
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

        h2 {
            color: #3a6ea5;
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
        }

        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin: 25px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        th { 
            border: 1px solid #cbd5e0; 
            padding: 12px 8px; 
            text-align: center; 
            background-color: #3a6ea5;
            color: white;
            font-weight: 600;
        }

        td { 
            border: 1px solid #e2e8f0; 
            padding: 10px 8px; 
            text-align: center; 
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tr:hover td:not(.lunch):not(.disabled) {
            background-color: #edf2f7;
        }

        .lunch { 
            background-color: #e53e3e !important; 
            color: white; 
            pointer-events: none;
            font-weight: bold;
        }

        .disabled { 
            background-color: #edf2f7 !important; 
            color: #a0aec0;
            pointer-events: none;
        }

        .selected { 
            background-color: #38a169 !important; 
            color: white;
            font-weight: bold;
        }

        textarea { 
            width: 100%; 
            height: 100px; 
            margin: 10px 0 20px; 
            padding: 12px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
        }

        textarea:focus {
            outline: none;
            border-color: #3a6ea5;
            box-shadow: 0 0 0 3px rgba(58, 110, 165, 0.2);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4a5568;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            background-color: white;
            font-family: inherit;
            margin-bottom: 20px;
            transition: border-color 0.2s ease;
        }

        select:focus {
            outline: none;
            border-color: #3a6ea5;
            box-shadow: 0 0 0 3px rgba(58, 110, 165, 0.2);
        }

        button[type="submit"] {
            background-color:rgb(13, 196, 0);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: block;
            margin: 0 auto;
        }

        button[type="submit"]:hover {
            background-color:rgb(2, 117, 19);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>

<div class="container">
    <span class="close-btn" onclick="window.parent.document.getElementById('dashboard-frame').remove()">✕</span>
    <form method="POST" onsubmit="return prepareData();">
        <h2>OD Request Form</h2>

        <table id="odTable">
            <tr>
                <th>Date</th>
                <th>Day</th>
                <?php for ($i = 1; $i <= 9; $i++): ?>
                    <th><?= $i ?></th>
                <?php endfor; ?>
            </tr>
            <?php
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $lunch = str_split('LUNCH*');

            // Parse teacher's timetable
            $enabledSlots = [];
            if ($teacher_timetable) {
                preg_match_all('/(\w+)\[([^\]]+)\]/', $teacher_timetable, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $dayKey = strtolower($match[1]);
                    $slots = array_map('intval', explode('-', $match[2]));
                    $enabledSlots[$dayKey] = $slots;
                }
            }

            foreach ($days as $index => $day): ?>
                <tr data-day="<?= strtolower($day) ?>">
                    <td><?= $weekDates[$day] ?></td>
                    <td><?= ucfirst($day) ?></td>
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                        <?php if ($i == 5): ?>
                            <td class="lunch"><?= $lunch[$index] ?? '*' ?></td>
                        <?php elseif (!in_array($i, $enabledSlots[$day] ?? [])): ?>
                            <td class="disabled"><?= $i ?></td>
                        <?php else: ?>
                            <td onclick="toggleCell(this)"><?= $i ?></td>
                        <?php endif; ?>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </table>


        <input type="hidden" name="day_and_hours" id="dayAndHours">
        <label for="comment"><b>Reason For OD:</b></label>
        <textarea name="comment" maxlength="500" placeholder="Write a Reason in order to receive the OD (max 500 characters)"></textarea>
        <br><br>
        <label for="faculty_verifier"><b>Select Faculty Verifier:</b></label>
        <select name="faculty_verifier">
            <option value="NONE">NULL (No Verifier Selected)</option>
            <?php foreach ($faculty_usernames as $username): ?>
                <option value="<?= htmlspecialchars($username) ?>"><?= htmlspecialchars($username) ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label for="link"><b>Enter Google Drive Link Containing Proof of OD Absence:</b></label>
        <textarea name="link" placeholder="Paste Google Drive URL here..." maxlength="500"></textarea>
        <br><br>

        <button type="submit">Submit OD Request</button>
    </form>
</div>

<script>
    function toggleCell(cell) {
        cell.classList.toggle('selected');
    }

    function prepareData() {
        let table = document.getElementById('odTable');
        let rows = table.querySelectorAll('tr[data-day]');
        let output = [];
        let hasSelected = false;

        rows.forEach(row => {
            let day = row.getAttribute('data-day');
            let selected = [];
            let cells = row.querySelectorAll('td');

            cells.forEach(cell => {
                if (cell.classList.contains('selected')) {
                    selected.push(cell.innerText.trim());
                }
            });

            if (selected.length > 0) {
                hasSelected = true;
                output.push(`${day}[${selected.join('-')}]`);
            }
        });

        const comment = document.querySelector('textarea[name="comment"]').value.trim();

        if (!hasSelected) {
            alert("Please select at least one hour from the table.");
            return false;
        }

        if (!comment) {
            alert("Please enter a reason/comment for the OD request.");
            return false;
        }

        document.getElementById('dayAndHours').value = output.join(',');

        const verifier = document.querySelector('select[name="faculty_verifier"]').value;
        
        return true; // Allow form submission
    }
</script>


</body>
</html>
