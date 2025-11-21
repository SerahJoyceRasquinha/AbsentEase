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

// Handle student submission for a specific student
if (isset($_POST['submit_student'])) {
    $rollno = strtolower($_POST['student_rollno']);
    
    if (preg_match("/^(\d{2})([a-zA-Z]{4})(\d{2})$/", $rollno, $matches)) {
        $yy = $matches[1];
        $course = strtolower($matches[2]);

        $email = $rollno . "@kristujayanti.com";
        $year_start = 2000 + intval($yy);
        $year_end = $year_start + 3;

        // Generate semester durations
        $semesters = [];
        $months = [
            [ "june", "december" ],
            [ "january", "may" ]
        ];

        $current_year = $year_start;
        $sem_no = 1;

        for ($i = 0; $i < 6; $i++) {
            $sem = ($i % 2 === 0)
                ? "{$sem_no}:june{$current_year}-december{$current_year}"
                : "{$sem_no}:january" . ($current_year + 1) . "-may" . ($current_year + 1);
            
            $semesters[] = $sem;
            if ($i % 2 !== 0) $current_year++;
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

        if ($student_current_semester == 0) {
            echo "<script>alert('Student is no longer in college');</script>";
        } else {
            // Check if rollno already exists in the database
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM verifiable_student_details WHERE student_rollno = ?");
            $stmt_check->bind_param("s", $rollno);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count > 0) {
                echo "<script>alert('This Roll Number already exists in the database.');</script>";
            } else {
                // Insert the new student details if rollno does not exist
                $stmt = $conn->prepare("INSERT INTO verifiable_student_details 
                    (student_rollno, student_course, student_email, course_year_start, course_year_end, semester_duration, student_current_semester)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("sssissi", $rollno, $course, $email, $year_start, $year_end, $semester_duration, $student_current_semester);
                $stmt->execute();
                echo "<script>alert('Student: $rollno Details Added');</script>";
                $stmt->close();
            }
        }
        
    } else {
        echo "<script>alert('Invalid Roll Number Format. Please use NNLLLLNN.');</script>";
    }
}


if (isset($_POST['submit_range'])) {
    $start_rollno = strtolower($_POST['start_rollno']);
    $end_rollno = strtolower($_POST['end_rollno']);
    
    // Check if the first 6 characters are the same
    $start_prefix = substr($start_rollno, 0, 6);
    $end_prefix = substr($end_rollno, 0, 6);

    if ($start_prefix !== $end_prefix) {
        echo "<script>alert('Error: The Range is not possible for these Roll Numbers');</script>";
    } else {
        $start_num = intval(substr($start_rollno, 6, 2));  // Extract last two digits for start roll no.
        $end_num = intval(substr($end_rollno, 6, 2));      // Extract last two digits for end roll no.

        if ($end_num < $start_num) {
            echo "<script>alert('Error: The end roll number must be greater than the start roll number.');</script>";
        } else {
            $prefix = substr($start_rollno, 0, 6);  // Get the prefix (first 6 characters)
            
            for ($i = $start_num; $i <= $end_num; $i++) {
                $rollno = $prefix . str_pad($i, 2, '0', STR_PAD_LEFT);  // Generate roll number, e.g., 23csmm01

                // Check if this roll number already exists in the database
                $check_query = $conn->prepare("SELECT COUNT(*) FROM verifiable_student_details WHERE student_rollno = ?");
                $check_query->bind_param("s", $rollno);
                $check_query->execute();
                $check_query->bind_result($count);
                $check_query->fetch();
                $check_query->close();

                if ($count > 0) {
                    // If the roll number already exists, show an alert and skip the current iteration
                    echo "<script>alert('Roll Number $rollno already exists in the database. Skipping...');</script>";
                    continue;
                }

                // Calculate year and course from rollno
                $email = $rollno . "@kristujayanti.com";
                $yy = substr($rollno, 0, 2);
                $course = substr($rollno, 2, 4);

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

                // If the student is no longer in college
                if ($student_current_semester == 0) {
                    echo "<script>alert('Student $rollno is no longer in college');</script>";
                    continue;
                } else {
                    // Insert the student into the database if not a duplicate
                    $stmt = $conn->prepare("INSERT INTO verifiable_student_details 
                        (student_rollno, student_course, student_email, course_year_start, course_year_end, semester_duration, student_current_semester)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");

                    $stmt->bind_param("sssissi", $rollno, $course, $email, $year_start, $year_end, $semester_duration, $student_current_semester);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            echo "<script>alert('Process complete.');</script>";
        }
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <style>
        /* Modal styles */
        .popup-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 80%;
            max-width: 500px;
            z-index: 1000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .popup-form h3 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
            font-size: 18px;
        }

        .popup-form form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .popup-form input[type="text"] {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            outline: none;
        }

        .popup-form input[type="text"]:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .popup-form input[type="text"]::placeholder {
            color: #aaaaaa;
        }

        .popup-form input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s, transform 0.2s;
            margin-top: 5px;
        }

        .popup-form input[type="submit"]:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .popup-form input[type="submit"]:active {
            transform: translateY(0);
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
            transition: background-color 0.3s, color 0.3s;
            padding: 0;
            line-height: 1;
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
            transition: opacity 0.3s;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .popup-form {
                padding: 20px;
                width: 90%;
            }
            
            .popup-form h3 {
                font-size: 16px;
            }
            
            .popup-form input[type="text"],
            .popup-form input[type="submit"] {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<!-- Student Input Form (Modal) -->
<div id="studentForm" class="popup-form">
    <button class="close-btn" id="closeModal">X</button>
    <h3>Enter Specific Student Details</h3>
    <form method="post">
        <input type="text" name="student_rollno" placeholder="Roll No (NNLLLLNN)" pattern="\d{2}[A-Za-z]{4}\d{2}" required />
        <input type="submit" name="submit_student" value="Submit" />
    </form>
    <br><br>
    <h3>Enter Range of Student Details by Roll Number</h3>
    <form method="post">
        <input type="text" name="start_rollno" placeholder="Start Roll No (NNLLLLNN)" pattern="\d{2}[A-Za-z]{4}\d{2}" required />
        <input type="text" name="end_rollno" placeholder="End Roll No (NNLLLLNN)" pattern="\d{2}[A-Za-z]{4}\d{2}" required />
        <input type="submit" name="submit_range" value="Submit Range" />
    </form>
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

    // Open the modal when the page loads (for demo purposes)
    openModal();
</script>

</body>
</html>
