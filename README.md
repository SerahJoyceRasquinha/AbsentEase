AbsentEase is a web-based platform for streamlining OD (On-Duty) request management between students, teachers, and academic managers. The system facilitates seamless submission, verification, and tracking of OD requests while allowing class teachers to maintain and access timetables and attendance data efficiently.

🔧 Technologies Used
PHP

MySQL

HTML/CSS/JavaScript

XAMPP (for local server setup)

🚀 Getting Started
1. Install XAMPP
XAMPP is required to run the application locally. You can download it using the link below:

👉 Download XAMPP

2. Clone the Repository
bash
Copy
Edit
git clone https://github.com/your-username/absentease.git
cd absentease
3. Setup the Database
Import the following SQL queries into phpMyAdmin (typically found at http://localhost/phpmyadmin) to create the necessary tables.

🗄️ SQL Table Setup
Click the dropdown arrow to expand the SQL code for each table.

<details> <summary><strong>📁 class_teacher_details</strong></summary>
sql
Copy
Edit
CREATE TABLE class_teacher_details (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    teacher_username VARCHAR(100) NOT NULL,
    year_course VARCHAR(10) NOT NULL,
    semester VARCHAR(10) NOT NULL,
    timetable TEXT NOT NULL,
    submission_date TIMESTAMP NOT NULL DEFAULT current_timestamp()
);
</details> <details> <summary><strong>📁 od_requests</strong></summary>
sql
Copy
Edit
CREATE TABLE od_requests (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_rollno VARCHAR(20),
    day_and_hours TEXT,
    status CHAR(1) DEFAULT 'w',
    comment VARCHAR(500),
    submission_date DATE DEFAULT CURDATE(),
    od_dates TEXT,
    faculty_verifier VARCHAR(255),
    faculty_verifier_status VARCHAR(20) DEFAULT 'pending',
    class_teacher_username VARCHAR(255),
    link TEXT
);
</details> <details> <summary><strong>📁 student_credentials</strong></summary>
sql
Copy
Edit
CREATE TABLE student_credentials (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    rollno VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    INDEX (rollno),
    INDEX (email)
);
</details> <details> <summary><strong>📁 teacher_credentials</strong></summary>
sql
Copy
Edit
CREATE TABLE teacher_credentials (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    INDEX (email)
);
</details> <details> <summary><strong>📁 verifiable_student_details</strong></summary>
sql
Copy
Edit
CREATE TABLE verifiable_student_details (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_rollno VARCHAR(20) NOT NULL,
    student_course VARCHAR(20) NOT NULL,
    student_email VARCHAR(100) NOT NULL,
    course_year_start INT(11) NOT NULL,
    course_year_end INT(11) NOT NULL,
    semester_duration TEXT NOT NULL,
    student_current_semester INT(11) NOT NULL,
    class_teacher_username VARCHAR(255),
    INDEX (student_rollno)
);
</details> <details> <summary><strong>📁 verifiable_teacher_details</strong></summary>
sql
Copy
Edit
CREATE TABLE verifiable_teacher_details (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    INDEX (username)
);
</details> <details> <summary><strong>📁 manager_credentials (One-Time Setup)</strong></summary>
sql
Copy
Edit
CREATE TABLE manager_credentials (
    email VARCHAR(255) NOT NULL,
    password VARBINARY(255) NOT NULL
);
</details>
