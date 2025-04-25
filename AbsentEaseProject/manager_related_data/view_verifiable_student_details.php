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

// Update Student Details
if (isset($_POST['update_student'])) {
    $old_student_rollno = $_POST['old_student_rollno'];
    $new_student_rollno = strtolower($_POST['new_student_rollno']);
    $new_email = $new_student_rollno . "@kristujayanti.com";
    $yy = substr($new_student_rollno, 0, 2);
                $course = substr($new_student_rollno, 2, 4);

                $year_start = 2000 + intval($yy);  // Calculate start year based on the roll number
                $year_end = $year_start + 3;       // Assume the course lasts 4 years

                // Generate semester durations
                $semesters = [];
                $months = [
                    ["june", "december"],
                    ["january", "may"]
                ];

                $current_year = $year_start;
                $sem_no = 1;

                for ($sem = 0; $sem < 6; $sem++) {
                    $semester = ($sem % 2 === 0)
                        ? "{$sem_no}:june{$current_year}-december{$current_year}"
                        : "{$sem_no}:january" . ($current_year + 1) . "-may" . ($current_year + 1);

                    $semesters[] = $semester;
                    if ($sem % 2 !== 0) $current_year++;
                    $sem_no++;
                }

                $semester_duration = implode(", ", $semesters);
                
                // Determine current semester based on real date
                $current_month = strtolower(date("F"));
                $current_year = intval(date("Y"));
                $student_current_semester = 0;

                foreach ($semesters as $index => $sem) {
                    if (preg_match("/(\w+)(\d+)-(\w+)(\d+)/", $sem, $parts)) {
                        $start_month = $parts[1];
                        $start_year = intval($parts[2]);
                        $end_month = $parts[3];
                        $end_year = intval($parts[4]);

                        $start_date = strtotime("{$start_month}{$start_year}");
                        $end_date = strtotime("{$end_month}{$end_year}");
                        $current_date = strtotime("{$current_month}{$current_year}");

                        if ($current_date >= $start_date && $current_date <= $end_date) {
                            $student_current_semester = $index + 1;
                            break;
                        }
                    }
                }

    // Check if the new rollno already exists
    $check_student_stmt = $conn->prepare("SELECT COUNT(*) FROM verifiable_student_details WHERE student_rollno = ?");
    $check_student_stmt->bind_param("s", $new_student_rollno);
    $check_student_stmt->execute();
    $check_student_stmt->bind_result($count);
    $check_student_stmt->fetch();
    $check_student_stmt->close();

    // If the rollno already exists, return an error message
    if ($count > 0) {
        echo "<script>alert('This roll number already exists for another student');</script>";
    } else {

        if ($student_current_semester == 0) {
            echo "<script>alert('Updated Student details are not valid if student is no longer in college');</script>";

        } else {
        // Proceed with updating the student details
        $stmt = $conn->prepare("UPDATE verifiable_student_details SET student_rollno = ?, student_course = ?, student_email = ?, course_year_start = ?, course_year_end = ?, semester_duration = ?, student_current_semester = ? WHERE student_rollno = ?");
        $stmt->bind_param("sssiisis", $new_student_rollno,$course, $new_email,$year_start,$year_end,$semester_duration,$student_current_semester, $old_student_rollno);
        $stmt->execute();
        $stmt->close();

        // Update the credentials table
        $sqlquery = $conn->prepare("UPDATE student_credentials SET rollno = ?, email = ? WHERE rollno = ?");
        $sqlquery->bind_param("sss", $new_student_rollno, $new_email, $old_student_rollno);
        $sqlquery->execute();
        $sqlquery->close();

        echo "<script>alert('Old Roll Number: $old_student_rollno details Updated with New Roll Number: $new_student_rollno details successfully!');</script>";
        }
    }
}



// Delete Student
if (isset($_POST['delete_student'])) {
    $rollno_to_delete = $_POST['student_rollno'];
    $stmt = $conn->prepare("DELETE FROM verifiable_student_details WHERE student_rollno = ?");
    $stmt->bind_param("s", $rollno_to_delete);
    $stmt->execute();
    $stmt->close();
    $sqlquery = $conn->prepare("DELETE FROM student_credentials WHERE rollno = ?");
    $sqlquery->bind_param("s", $rollno_to_delete);
    $deleting_rollno = $rollno_to_delete;
    $sqlquery->execute();
    echo "<script>alert('$deleting_rollno Details deleted successfully!');</script>";
    $sqlquery->close();
}

if (isset($_POST['refresh_students'])) {
    // Step 1: Select roll numbers of students with semester = 0
    $selectStmt = $conn->prepare("SELECT student_rollno FROM verifiable_student_details WHERE student_current_semester = 0");
    $selectStmt->execute();
    $result = $selectStmt->get_result();

    $rollnos_to_delete = [];
    while ($row = $result->fetch_assoc()) {
        $rollnos_to_delete[] = $row['student_rollno'];
    }
    $selectStmt->close();

    // Step 2: Delete from both tables
    if (!empty($rollnos_to_delete)) {
        // Prepare deletion from verifiable_student_details
        $placeholders = implode(',', array_fill(0, count($rollnos_to_delete), '?'));
        $types = str_repeat('s', count($rollnos_to_delete));

        $stmt1 = $conn->prepare("DELETE FROM verifiable_student_details WHERE student_rollno IN ($placeholders)");
        $stmt2 = $conn->prepare("DELETE FROM student_credentials WHERE rollno IN ($placeholders)");

        if ($stmt1 && $stmt2) {
            $stmt1->bind_param($types, ...$rollnos_to_delete);
            $stmt2->bind_param($types, ...$rollnos_to_delete);

            $stmt1->execute();
            $stmt2->execute();

            $stmt1->close();
            $stmt2->close();

            echo "<script>alert('Refresh complete: Removed students who are no longer in college');</script>";
        } else {
            echo "<script>alert('Error preparing deletion queries.');</script>";
        }
    } else {
        echo "<script>alert('Refresh Complete');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <style>
        /* Modal styles */
        .popup-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 550px;
            z-index: 1000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .popup-form h3 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 12px;
        }

        .popup-form form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 25px;
        }

        .popup-form label {
            font-weight: 500;
            margin-bottom: -10px;
            color: #555;
        }

        .popup-form input[type="text"],
        .popup-form input[type="email"] {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .popup-form input[type="text"]:focus,
        .popup-form input[type="email"]:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .popup-form input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 5px;
            transition: all 0.2s;
        }

        .popup-form input[type="submit"]:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #f5f5f5;
            border: none;
            font-size: 16px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            color: #555;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .close-btn:hover {
            background-color: #e74c3c;
            color: white;
        }

        /* Overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 999;
            backdrop-filter: blur(3px);
        }

        /* Table styles */
        .details-section {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 25px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 25px;
        }

        .details-section h3 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 22px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 12px;
        }

        #studentSearch {
            width: 100%;
            max-width: 400px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        #studentSearch:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        #studentTable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        #studentTable th {
            background-color: #3498db;
            color: white;
            padding: 14px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        #studentTable tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        #studentTable tr:hover {
            background-color: #f1f8ff;
        }

        #studentTable td {
            padding: 12px 10px;
            border-top: 1px solid #eee;
        }

        #studentTable input[type="text"],
        #studentTable input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        #studentTable input[disabled] {
            background-color: #f9f9f9;
            color: #777;
        }

        #studentTable input[type="submit"] {
            padding: 8px 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            width: 100%;
        }

        #studentTable input[type="submit"]:hover {
            background-color: #2980b9;
        }

        #studentTable input[name="delete_student"] {
            background-color: #e74c3c;
        }

        #studentTable input[name="delete_student"]:hover {
            background-color: #c0392b;
        }

        #studentTable form {
            margin: 0;
        }

        input[name="refresh_students"] {
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        input[name="refresh_students"]:hover {
            background-color: #27ae60;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .popup-form {
                width: 95%;
                padding: 20px;
            }
            
            .details-section {
                margin: 10px;
                padding: 15px;
                overflow-x: auto;
            }
            
            #studentTable {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<!-- Modal for Update/Delete -->
<div id="studentForm" class="popup-form">
    <button class="close-btn" id="closeModal">X</button>
    <h3>Update or Delete Student Details</h3>
    <form method="post">
        <label for="old_student_rollno">Old Roll Number:</label>
        <input type="text" name="old_student_rollno" required />
        <label for="new_student_rollno">New Roll Number:</label>
        <input type="text" name="new_student_rollno" required />
        <label for="new_email">New Email:</label>
        <input type="email" name="new_email" required />
        <input type="submit" name="update_student" value="Update Student" />
    </form>
    <form method="post">
        <label for="student_rollno">Roll Number to Delete:</label>
        <input type="text" name="student_rollno" required />
        <input type="submit" name="delete_student" value="Delete Student" />
    </form>
</div>

<div id="studentDetailsTable" class="details-section">
    <h3>Editable Student Details</h3>
    <input type="text" id="studentSearch" placeholder="Search by Roll No..." onkeyup="filterTable('studentSearch', 'studentTable', 0)" style="margin-bottom: 10px; padding: 8px; width: 50%;" />
    <table id="studentTable">
        <tr>
            <th>Roll No</th>
            <th>Email ID</th>
            <th>Course</th>
            <th>Course Start Year</th>
            <th>Course End Year</th>
            <th>Current Semester</th>
            <th>
                Action
            </th>
            <form method="post" style="display:inline;">
                    <input type="submit" name="refresh_students" value="Refresh" style="padding:5px 10px; font-size:12px; margin-left:10px;" />
                </form>

        </tr>
        <?php
        $students = $conn->query("SELECT * FROM verifiable_student_details");
        while ($row = $students->fetch_assoc()) {
            echo "<tr>
                <td>
                    <form method='post'>
                        <input type='hidden' name='old_student_rollno' value='{$row['student_rollno']}' />
                        <input type='text' name='new_student_rollno' value='{$row['student_rollno']}' required />
                </td>
                <td>
                        <input type='text' value='{$row['student_rollno']}@kristujayanti.com' disabled />
                </td>
                <td>
                        <input type='text' value='{$row['student_course']}' disabled />
                </td>
                <td>
                        <input type='text' value='{$row['course_year_start']}' disabled />
                </td>
                <td>
                        <input type='text' value='{$row['course_year_end']}' disabled />
                </td>
                <td>
                        <input type='text' value='{$row['student_current_semester']}' disabled />
                </td>
                <td>
                        <input type='submit' name='update_student' value='Update' />
                    </form>
        
                    <form method='post' onsubmit=\"return confirm('Are you sure you want to delete this student?');\" style='margin-top:5px;'>
                        <input type='hidden' name='student_rollno' value='{$row['student_rollno']}' />
                        <input type='submit' name='delete_student' value='Delete' style='background-color: #e74c3c;' />
                    </form>
                </td>
            </tr>";
        }        
        ?>
    </table>
</div>

<script>
    // Open modal
    function openModal() {
        document.getElementById('studentForm').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    // Close modal
    document.getElementById('closeModal').onclick = function() {
        document.getElementById('studentForm').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }

    // Filter table
    function filterTable(inputId, tableId, columnIndex) {
    const input = document.getElementById(inputId).value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName("tr");

    // Start from 1 to skip header
    for (let i = 1; i < rows.length; i++) {
        const cell = rows[i].getElementsByTagName("td")[columnIndex];
        if (cell) {
            const txt = cell.getElementsByTagName("input")[0]?.value.toLowerCase() || "";
            rows[i].style.display = txt.startsWith(input) ? "" : "none";
        }
    }
}

</script>

</body>
</html>