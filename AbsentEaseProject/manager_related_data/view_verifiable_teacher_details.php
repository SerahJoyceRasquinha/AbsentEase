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

// Update Teacher Details
if (isset($_POST['update_teacher'])) {
    $old_username = $_POST['old_username'];
    $new_username = $_POST['username'];
    $old_email = $_POST['old_email'];
    $new_email = $_POST['email'];

    // Check if the new username or email already exists
    $check_teacher_stmt = $conn->prepare("SELECT COUNT(*) FROM verifiable_teacher_details WHERE username = ? OR email = ?");
    $check_teacher_stmt->bind_param("ss", $new_username, $new_email);
    $check_teacher_stmt->execute();
    $check_teacher_stmt->bind_result($count);
    $check_teacher_stmt->fetch();
    $check_teacher_stmt->close();

    // If the username or email already exists, return an error message
    if ($count > 0) {
        echo "<script>alert('This Username or email already exists for another teacher');</script>";
    } else {
        // Proceed with updating the teacher details
        $stmt = $conn->prepare("UPDATE verifiable_teacher_details SET username = ?, email = ? WHERE username = ?");
        $stmt->bind_param("sss", $new_username, $new_email, $old_username);
        $stmt->execute();
        $stmt->close();

        // Update the credentials table
        $sqlquery = $conn->prepare("UPDATE teacher_credentials SET username = ?, email = ? WHERE username = ?");
        $sqlquery->bind_param("sss", $new_username, $new_email, $old_username);
        $sqlquery->execute();
        $sqlquery->close();

        $sqlquery2 = $conn->prepare("UPDATE class_teacher_details SET username = ? WHERE username = ?");
        $sqlquery2->bind_param("sss", $new_username, $old_username);
        $sqlquery2->execute();
        $sqlquery2->close();

        echo "<script>alert('Old Username: $old_username and Old Email ID: $old_email is Updated with New Username: $new_username and New Email ID: $new_email successfully!');</script>";
    }
}

// Delete Teacher
if (isset($_POST['delete_teacher'])) {
    $username_to_delete = $_POST['username'];
    $stmt = $conn->prepare("DELETE FROM verifiable_teacher_details WHERE username = ?");
    $stmt->bind_param("s", $username_to_delete);
    $stmt->execute();
    $stmt->close();
    $sqlquery = $conn->prepare("DELETE FROM teacher_credentials WHERE username = ?");
    $sqlquery->bind_param("s", $username_to_delete);
    $deleting_username = $username_to_delete;
    $sqlquery->execute();
    echo "<script>alert('$deleting_username Details deleted successfully!');</script>";
    $sqlquery->close();
    $stmt = $conn->prepare("DELETE FROM class_teacher_details WHERE teacher_username = ?");
    $stmt->bind_param("s", $deleting_username);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Teacher Details</title>
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
            padding-bottom: 10px;
        }

        h3:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #3498db;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .details-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        table {
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            padding: 16px;
            text-align: left;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="email"]:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        input[type="submit"] {
            padding: 10px 16px;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 13px;
        }

        input[name="update_teacher"] {
            background-color: #2ecc71;
            color: white;
            width: 100%;
        }

        input[name="update_teacher"]:hover {
            background-color: #27ae60;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        input[name="delete_teacher"] {
            background-color: #e74c3c;
            color: white;
            width: 100%;
            margin-top: 8px;
        }

        input[name="delete_teacher"]:hover {
            background-color: #c0392b;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        #teacherSearch {
            padding: 14px;
            width: 50%;
            margin: 0 auto 20px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 15px;
            display: block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        #teacherSearch:focus {
            border-color: #3498db;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
            outline: none;
        }

        @media (max-width: 768px) {
            .details-section {
                padding: 20px 15px;
            }
            
            table {
                width: 100%;
            }
            
            #teacherSearch {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Teacher Details Table -->
<div id="teacherDetailsTable" class="details-section">
    <h3>Editable Teacher Details</h3>
    <input type="text" id="teacherSearch" placeholder="Search by Username..." onkeyup="filterTable('teacherSearch', 'teacherTable', 0)" style="margin-bottom: 10px; padding: 8px; width: 50%;" />
    <table id="teacherTable">
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php
        $teachers = $conn->query("SELECT * FROM verifiable_teacher_details");
        while ($row = $teachers->fetch_assoc()) {
            echo "<tr>
                <td>
                    <form method='post'>
                        <input type='hidden' name='old_username' value='{$row['username']}' />
                        <input type='text' name='username' value='{$row['username']}' required />
                </td>
                <td>
                        <input type='hidden' name='old_email' value='{$row['email']}' />
                        <input type='email' name='email' value='{$row['email']}' required />
                </td>
                <td>
                        <input type='submit' name='update_teacher' value='Update' />
                    </form>
        
                    <form method='post' onsubmit=\"return confirm('Are you sure you want to delete this teacher?');\" style='margin-top: 5px;'>
                        <input type='hidden' name='username' value='{$row['username']}' />
                        <input type='submit' name='delete_teacher' value='Delete' style='background-color: #e74c3c;' />
                    </form>
                </td>
            </tr>";
        }
        
        ?>
    </table>
</div>

<script>
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
