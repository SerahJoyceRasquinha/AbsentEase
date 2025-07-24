# AbsentEase

AbsentEase is a web-based platform for streamlining On-Duty (OD) request management among students, teachers, and academic managers. It facilitates seamless submission, verification, and tracking of OD requests while enabling class teachers to maintain timetables and attendance data efficiently.

🔧 Technologies Used
1)PHP
2)MySQL
3)HTML/CSS/JavaScript
4)XAMPP (for local server setup)

🚀 Getting Started:

1️⃣ Install XAMPP
XAMPP is required to run the application locally. You can download it from the link below:
👉 Download XAMPP on : https://www.apachefriends.org/download.html

2️⃣ Clone the Repository
Open your terminal and run:

```
bash $ git clone https://github.com/your-username/absentease.git
cd absentease
```
3️⃣ Setup the Database
Open phpMyAdmin (http://localhost/phpmyadmin) and run the following SQL scripts to create the required tables.

🗄️ SQL Table Setup
Click on each dropdown to reveal the SQL code. You can copy the code directly and execute it in your MySQL environment.

1) class_teacher_details
```
CREATE TABLE class_teacher_details (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    teacher_username VARCHAR(100) NOT NULL,
    year_course VARCHAR(10) NOT NULL,
    semester VARCHAR(10) NOT NULL,
    timetable TEXT NOT NULL,
    submission_date TIMESTAMP NOT NULL DEFAULT current_timestamp()
);
```
2) od_requests

```
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
```

3) student_credentials

```
CREATE TABLE student_credentials (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    rollno VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    INDEX (rollno),
    INDEX (email)
);

```


4) teacher_credentials

```
CREATE TABLE teacher_credentials (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    INDEX (email)
);

```
5) verifiable_student_details

```
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

```


6) verifiable_teacher_details

```
CREATE TABLE verifiable_teacher_details (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    INDEX (username)
);
```

7) manager_credentials (One-Time Setup)

```
CREATE TABLE manager_credentials (
    email VARCHAR(255) NOT NULL,
    password VARBINARY(255) NOT NULL
);
```
