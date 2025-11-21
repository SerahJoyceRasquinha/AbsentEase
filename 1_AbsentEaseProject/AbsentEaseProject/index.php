<?php
session_start();  // Start the session to track logged-in users

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $conn = new mysqli('localhost', 'root', '', 'absenteasev02complex');

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

// --- TEACHER SIGNUP ---
if (isset($_POST['teacher_signup'])) {
  $username = $_POST['signup_username'];
  $email = $_POST['signup_email'];
  $password = $_POST['signup_password'];
  $confirmPassword = $_POST['signup_confirm_password'];

  if ($password !== $confirmPassword) {
      echo "<script>alert('Passwords do not match!');</script>";
  } else {
      // Step 1: Verify the teacher exists in verifiable_teacher_details
      $verifyStmt = $conn->prepare("SELECT * FROM verifiable_teacher_details WHERE username = ? AND email = ?");
      $verifyStmt->bind_param("ss", $username, $email);
      $verifyStmt->execute();
      $verifyStmt->store_result();

      if ($verifyStmt->num_rows !== 1) {
          // No matching teacher found in verifiable_teacher_details
          echo "<script>alert('Teacher verification failed. Please consult the admin');</script>";
      } else {
          // Step 2: Check if account already exists in teacher_credentials
          $checkStmt = $conn->prepare("SELECT * FROM teacher_credentials WHERE username = ? OR email = ?");
          $checkStmt->bind_param("ss", $username, $email);
          $checkStmt->execute();
          $checkStmt->store_result();

          if ($checkStmt->num_rows === 1) {
              echo "<script>alert('This Account exists. Continue with Login or Sign Up with different credentials.');</script>";
          } else {
              // Step 3: Proceed with registration
              $passwordHash = password_hash($password, PASSWORD_BCRYPT);

              $encStmt = $conn->prepare("INSERT INTO teacher_credentials (username, email, password) VALUES (?, ?, ?)");
              $encStmt->bind_param("sss", $username, $email, $passwordHash);

              if ($encStmt->execute()) {
                  echo "<script>alert('Teacher registered successfully!'); window.location.href = 'index.php';</script>";
              } else {
                  echo "<script>alert('Registration failed.');</script>";
              }
              $encStmt->close();
          }
          $checkStmt->close();
      }
      $verifyStmt->close();
  }
}

  // --- TEACHER LOGIN ---
  if (isset($_POST['teacher_login'])) {
    $username = $_POST['login_username'];
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT password FROM teacher_credentials WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($dbPassword);
    $stmt->fetch();

    if ($dbPassword && password_verify($password, $dbPassword)) {
      // Set session for logged-in user
      $_SESSION['teacher_username'] = $username;
      header("Location: teacher_dashboard.php");
      exit;
    } else {
      echo "<script>alert('Invalid credentials!');</script>";
    }
    $stmt->close();
  }

  // --- MANAGER LOGIN ---
  if (isset($_POST['manager_login'])) {
    $email = $_POST['manager_email'];
    $password = $_POST['manager_password'];

    $stmt = $conn->prepare("SELECT password FROM manager_credentials WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
      $stmt->bind_result($dbPassword);
      $stmt->fetch();

    // Decrypt password using AES (if used instead of hash)
      $decryptStmt = $conn->prepare("SELECT AES_DECRYPT(password, 'my_secret_key') FROM manager_credentials WHERE email = ?");
      $decryptStmt->bind_param("s", $email);
      $decryptStmt->execute();
      $decryptStmt->bind_result($decryptedPassword);
      $decryptStmt->fetch();

      if ($decryptedPassword === $password) {
        header("Location: manager_dashboard.php");
        exit;
        } else {
          echo "<script>alert('Invalid password');</script>";
        }
        $decryptStmt->close();
        } else {
          echo "<script>alert('Manager not found');</script>";
        }
    $stmt->close();
  }

  // --- STUDENT LOGIN ---
  // --- STUDENT SIGNUP ---
  if (isset($_POST['student_signup'])) {
    $rollno = $_POST['student_rollno'];
    $email = $_POST['student_email'];
    $password = $_POST['student_password'];
    $confirmPassword = $_POST['student_confirm_password'];

    // Check if password matches confirm password
    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Step 1: Verify rollno and email against verifiable_student_details table
        $verifyStmt = $conn->prepare("SELECT * FROM verifiable_student_details WHERE student_rollno = ? AND student_email = ?");
        $verifyStmt->bind_param("ss", $rollno, $email);
        $verifyStmt->execute();
        $verifyStmt->store_result();

        if ($verifyStmt->num_rows === 1) {
            // Step 2: Check if rollno or email already exists in student_credentials
            $existsStmt = $conn->prepare("SELECT * FROM student_credentials WHERE rollno = ? OR email = ?");
            $existsStmt->bind_param("ss", $rollno, $email);
            $existsStmt->execute();
            $existsStmt->store_result();

            if ($existsStmt->num_rows > 0) {
                echo "<script>alert('$rollno already exists. Login or Sign up with different credentials.');</script>";
            } else {
                // Step 3: Proceed with registration
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                $encStmt = $conn->prepare("INSERT INTO student_credentials (rollno, email, password) VALUES (?, ?, ?)");
                $encStmt->bind_param("sss", $rollno, $email, $passwordHash);

                if ($encStmt->execute()) {
                    echo "<script>alert('Student registered successfully!'); window.location.href = 'index.php';</script>";
                } else {
                    echo "<script>alert('Registration failed.');</script>";
                }
                $encStmt->close();
            }
            $existsStmt->close();
        } else {
            echo "<script>alert('Student verification failed. Please consult the admin');</script>";
        }
        $verifyStmt->close();
    }
}


// --- STUDENT LOGIN ---
if (isset($_POST['student_login'])) {
  $rollno = $_POST['student_login_rollno'];
  $password = $_POST['student_login_password'];

  // Verify rollno and password from student_credentials table
  $stmt = $conn->prepare("SELECT password FROM student_credentials WHERE rollno = ?");
  $stmt->bind_param("s", $rollno);
  $stmt->execute();
  $stmt->bind_result($dbPassword);
  $stmt->fetch();

  if ($dbPassword && password_verify($password, $dbPassword)) {
    // Set session for logged-in student
    $_SESSION['student_rollno'] = $rollno;
    header("Location: student_dashboard.php");
    exit;
  } else {
    echo "<script>alert('Invalid credentials!');</script>";
  }
  $stmt->close();
}


  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Homepage - Absentease</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      padding: 30px;
      width: 80%;
      max-width: 600px;
      text-align: center; /* Center all content in container */
    }

    h1 {
      color:rgb(31, 44, 57);
      margin-bottom: 30px;
      font-weight: 600;
      font-size: 40px;
      text-align: center; /* Ensure heading is centered */
    }

    .container button {
      padding: 12px 24px;
      margin: 12px;
      font-size: 16px;
      cursor: pointer;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 5px;
      transition: all 0.3s ease;
      font-weight: 500;
      width: 120px;
    }

    /* Center the buttons container */
    .container div {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
    }

    .container button:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 2;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background: rgba(0, 0, 0, 0.6);
      animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background: #fff;
      margin: 8% auto;
      padding: 35px;
      width: 350px;
      border-radius: 8px;
      position: relative;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
      animation: slideDown 0.4s;
      text-align: center; /* Center content in modals */
    }

    @keyframes slideDown {
      from { transform: translateY(-50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .modal-content input {
      width: 100%;
      padding: 12px;
      margin: 12px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
      box-sizing: border-box;
      font-size: 15px;
      transition: border 0.3s ease;
    }

    .modal-content input:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
    }

    .modal-content button {
      width: 100%;
      padding: 12px 0;
      margin-top: 15px;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s ease;
    }

    .modal-content button:hover {
      background-color: #2980b9;
    }

    .close {
      position: absolute;
      right: 20px;
      top: 15px;
      font-size: 24px;
      font-weight: bold;
      color: #aaa;
      cursor: pointer;
      transition: color 0.3s ease;
    }

    .close:hover {
      color: #2c3e50;
    }

    .form-section {
      display: none;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .form-section.active {
      display: block;
      opacity: 1;
    }

    h2, h3 {
      color: #2c3e50;
      margin-bottom: 20px;
      text-align: center;
      font-weight: 600;
    }

    .modal-content div:first-of-type {
      display: flex;
      justify-content: center; /* Center the option buttons */
      margin-bottom: 20px;
    }

    .modal-content div:first-of-type button {
      flex: 1;
      margin: 0 5px;
      background-color: #f8f9fa;
      color: #2c3e50;
      border: 1px solid #ddd;
    }

    .modal-content div:first-of-type button:hover {
      background-color: #e9ecef;
    }
  </style>
</head>
<body>

  <div class="container">
    <h1><b>ABSENTEASE</b></h1>
    <h3>An On-Duty (OD) Attendance Management System </h3>
    <button onclick="document.getElementById('managerModal').style.display='block'">Manager</button>
    <button onclick="document.getElementById('teacherModal').style.display='block'">Teacher</button>
    <button onclick="document.getElementById('studentModal').style.display='block'">Student</button>
  </div>

  <!-- Manager Login Modal -->
  <div id="managerModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('managerModal').style.display='none'">&times;</span>
      <h2>Manager Login</h2>
      <form method="POST">
        <input type="email" name="manager_email" placeholder="Email" required>
        <input type="password" name="manager_password" placeholder="Password" required>
        <button type="submit" name="manager_login">Login</button>
      </form>
    </div>
  </div>

  <!-- Teacher Modal -->
  <div id="teacherModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('teacherModal').style.display='none'">&times;</span>
      
      <!-- Options -->
      <div>
        <button onclick="showForm('signup')">Sign Up</button>
        <button onclick="showForm('login')">Login</button>
      </div>

      <!-- Teacher Sign Up Form -->
      <div id="signupForm" class="form-section">
        <h3>Teacher Sign Up</h3>
        <form method="POST">
          <input type="text" name="signup_username" placeholder="Username" required>
          <input type="email" name="signup_email" placeholder="Email" required>
          <input type="password" name="signup_password" placeholder="Password" required>
          <input type="password" name="signup_confirm_password" placeholder="Confirm Password" required>
          <button type="submit" name="teacher_signup">Sign Up</button>
        </form>
      </div>

      <!-- Teacher Login Form -->
      <div id="loginForm" class="form-section">
        <h3>Teacher Login</h3>
        <form method="POST">
          <input type="text" name="login_username" placeholder="Username" required>
          <input type="password" name="login_password" placeholder="Password" required>
          <button type="submit" name="teacher_login">Login</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Student Modal -->
<div id="studentModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('studentModal').style.display='none'">&times;</span>

    <!-- Options -->
    <div>
      <button onclick="showStudentForm('signup')">Sign Up</button>
      <button onclick="showStudentForm('login')">Login</button>
    </div>

    <!-- Student Sign Up Form -->
    <div id="studentSignupForm" class="form-section">
      <h3>Student Sign Up</h3>
      <form method="POST">
        <input type="text" name="student_rollno" placeholder="Roll Number" required>
        <input type="email" name="student_email" placeholder="College Email" required>
        <input type="password" name="student_password" placeholder="Password" required>
        <input type="password" name="student_confirm_password" placeholder="Confirm Password" required>
        <button type="submit" name="student_signup">Sign Up</button>
      </form>
    </div>

    <!-- Student Login Form -->
    <div id="studentLoginForm" class="form-section">
      <h3>Student Login</h3>
      <form method="POST">
        <input type="text" name="student_login_rollno" placeholder="Roll Number" required>
        <input type="password" name="student_login_password" placeholder="Password" required>
        <button type="submit" name="student_login">Login</button>
      </form>
    </div>
  </div>
</div>


  <script>
    function showForm(type) {
      document.getElementById('signupForm').classList.remove('active');
      document.getElementById('loginForm').classList.remove('active');
      if (type === 'signup') {
        document.getElementById('signupForm').classList.add('active');
      } else {
        document.getElementById('loginForm').classList.add('active');
      }
    }

    function showStudentForm(type) {
    document.getElementById('studentSignupForm').classList.remove('active');
    document.getElementById('studentLoginForm').classList.remove('active');
    if (type === 'signup') {
      document.getElementById('studentSignupForm').classList.add('active');
    } else {
      document.getElementById('studentLoginForm').classList.add('active');
    }
  }

  </script>

</body>
</html>
