<?php
session_start();
$host = "localhost";
$user = "root";
$password = ""; // Change if needed
$dbname = "absenteasev02complex";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$yearCourseList = [];
$semesterMapping = [];
$class_teacher = $_SESSION['teacher_username']; // Logged-in teacher's username

// Modify the query to show only students where the class_teacher_username is NULL or matches the logged-in teacher's username
$query = "SELECT student_rollno, student_current_semester 
          FROM verifiable_student_details 
          WHERE class_teacher_username IS NULL OR class_teacher_username = ?";  // Use placeholder for class_teacher_username

// Prepare and bind the query to prevent SQL injection
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $class_teacher); // Bind the logged-in teacher's username
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    $rollData = [];
    while ($row = $result->fetch_assoc()) {
        $prefix = substr($row['student_rollno'], 0, 6);
        $rollData[$prefix][] = $row['student_current_semester'];
    }

    foreach ($rollData as $prefix => $sems) {
        $yearCourseList[] = $prefix;
        $semesterMapping[$prefix] = array_values(array_unique($sems))[0]; // Get first distinct semester
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['teacher_username'];
    $yearCourse = $_POST['year_course'];
    $semester = $_POST['semester'];
    $timetable = $_POST['timetable'];

    // Step 1: Check if this teacher already exists in class_teacher_details
    $checkCTQuery = "SELECT COUNT(*) as count FROM class_teacher_details WHERE teacher_username = ?";
    $checkCTStmt = $conn->prepare($checkCTQuery);
    $checkCTStmt->bind_param("s", $username);
    $checkCTStmt->execute();
    $ctResult = $checkCTStmt->get_result()->fetch_assoc();
    $checkCTStmt->close();

    if ($ctResult['count'] > 0) {
        // Step 2: UPDATE existing row
        $updateCT = "
            UPDATE class_teacher_details 
            SET year_course = ?, semester = ?, timetable = ?, submission_date = CURRENT_TIMESTAMP
            WHERE teacher_username = ?
        ";
        $updateCTStmt = $conn->prepare($updateCT);
        $updateCTStmt->bind_param("ssss", $yearCourse, $semester, $timetable, $username);
        $updateCTStmt->execute();
        $updateCTStmt->close();
    } else {
        // Step 3: INSERT new row
        $insertCT = "
            INSERT INTO class_teacher_details (teacher_username, year_course, semester, timetable, submission_date)
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
        ";
        $insertStmt = $conn->prepare($insertCT);
        $insertStmt->bind_param("ssss", $username, $yearCourse, $semester, $timetable);
        $insertStmt->execute();
        $insertStmt->close();
    }

    // Step 4: Check and reset any existing student associations for this teacher
    $checkQuery = "SELECT COUNT(*) as count FROM verifiable_student_details WHERE class_teacher_username = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $result = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($result['count'] > 0) {
        $resetQuery = "UPDATE verifiable_student_details SET class_teacher_username = NULL WHERE class_teacher_username = ?";
        $resetStmt = $conn->prepare($resetQuery);
        $resetStmt->bind_param("s", $username);
        $resetStmt->execute();
        $resetStmt->close();
    }

    // Step 5: Assign current teacher to selected student group
    $updateQuery = "
        UPDATE verifiable_student_details 
        SET class_teacher_username = ? 
        WHERE LEFT(student_rollno, 6) = ?
    ";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ss", $username, $yearCourse);
    $updateStmt->execute();
    $updateStmt->close();

    echo "<script>alert('Details submitted successfully. Teacher assignment updated.');</script>";
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Class Teacher Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            position: relative;
            border: none;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 800px;
            background-color: #ffffff;
            margin: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 20px;
            cursor: pointer;
            color: #e74c3c;
            background: #fff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .close-btn:hover {
            background-color: #e74c3c;
            color: white;
            transform: scale(1.1);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
        }

        select, input[type="text"] {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 100%;
            font-size: 15px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        td, th {
            border: none;
            padding: 12px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        td {
            border-bottom: 1px solid #eaeaea;
            border-right: 1px solid #eaeaea;
            position: relative;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f8ff;
        }

        td:last-child {
            border-right: none;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .lunch {
            background-color: #ff7675 !important;
            color: white;
            pointer-events: none;
            font-weight: bold;
            position: relative;
        }

        .lunch::after {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 10px;
            opacity: 0.7;
        }

        .selected {
            background-color: #2ecc71;
            color: white;
            font-weight: bold;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.2);
            transform: scale(0.98);
        }

        td:hover:not(.lunch) {
            background-color: rgba(46, 204, 113, 0.2);
        }

        tr:first-child th:first-child {
            border-top-left-radius: 8px;
        }

        tr:first-child th:last-child {
            border-top-right-radius: 8px;
        }

        tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                width: 95%;
            }
            
            table {
                font-size: 14px;
            }
            
            td, th {
                padding: 10px 5px;
            }
        }

        .submit-btn{
            background-color: green;
        }
    </style>
</head>
<body>

<div class="form-container">
    <span class="close-btn" onclick="window.parent.document.getElementById('dashboard-frame').remove()">✕</span>
    <form method="POST">
        <label><h3><b>Year + Course of Student:</b></h3></label>
        <select name="year_course" id="yearCourseDropdown" required onchange="updateSemester()">
            <option value="">Select</option>
            <?php foreach (array_unique($yearCourseList) as $item): ?>
                <option value="<?= $item ?>"><?= $item ?></option>
            <?php endforeach; ?>
        </select><br>

        <label><h3><b>Semester: (This field is auto-entered)</b></h3></label>
        <input type="text" name="semester" id="semesterField" readonly required><br>

        <label><h3><b>Time Table:</b></h3></label>
        <table id="timetable">
            <tr>
                <th>Day</th>
                <?php for ($i = 1; $i <= 9; $i++): ?>
                    <th><?= $i ?></th>
                <?php endfor; ?>
            </tr>
            <?php
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $lunch = str_split('LUNCH');
            foreach ($days as $index => $day): ?>
                <tr data-day="<?= strtolower($day) ?>">
                    <td><?= $day ?></td>
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                        <?php if ($i == 5): ?>
                            <td class="lunch"><?= $lunch[$index] ?? '*' ?></td>
                        <?php else: ?>
                            <td onclick="toggleCell(this)"><?= $i ?></td>
                        <?php endif; ?>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <input type="hidden" name="timetable" id="timetableData">
        <br><button type="submit" class="submit-btn" onclick="prepareData()">Submit</button>
    </form>
</div>

<script>
    const semesterMap = <?= json_encode($semesterMapping) ?>;

    function updateSemester() {
        const selected = document.getElementById("yearCourseDropdown").value;
        document.getElementById("semesterField").value = semesterMap[selected] || '';
    }

    function toggleCell(cell) {
        cell.classList.toggle("selected");
    }

    function prepareData() {
        const rows = document.querySelectorAll("#timetable tr[data-day]");
        let data = [];

        rows.forEach(row => {
            const day = row.getAttribute("data-day");
            const selectedCols = Array.from(row.children)
                .slice(1) // skip day column
                .filter(td => td.classList.contains("selected"))
                .map(td => td.innerText);
            if (selectedCols.length > 0) {
                data.push(`${day}[${selectedCols.join('-')}]`);
            }
        });

        document.getElementById("timetableData").value = data.join(",");
    }
</script>

</body>
</html>
