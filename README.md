# AbsentEase

On-Duty (OD) attendance management for a college (PHP + MySQL on XAMPP).

When a student misses lectures for a college event, they request OD for exactly those lecture hours and attach a proof link. Their **class teacher** opens the proof and **approves** it (the hours count as On-Duty) or **rejects** it with a reason.

| Role | Can do |
|---|---|
| **Admin** (manager) | add teachers and students (only they can sign up), edit or delete them, update semesters, see every class |
| **Teacher** | sign up, pick **their class** and mark its weekly lecture hours, **review the class's OD requests** (one page: check proof → approve / reject) |
| **Student** | sign up, **request OD** (event, dates, lecture hours, proof link), track every request, withdraw a pending one |

---

## ▶ How to run

1. Install XAMPP (it's found automatically in `C:\xampp`, `D:\xampp`, …).
2. Put this folder anywhere. It does **not** need to be inside `htdocs`.
3. Double-click **`START_ABSENTEASE.bat`**.

It starts Apache and MySQL, opens the XAMPP Control Panel and prepares the database. If you already have an older AbsentEase database, it is **upgraded in place** and your data is kept. Then it opens the site in Chrome.

To shut everything down, double-click **`STOP_ABSENTEASE.bat`**.

**Default admin login:** `admin@absentease.com` / `admin123`. It is created only when there is no admin yet. An existing admin keeps their password.

### Try the whole flow in 2 minutes

1. **Admin** → Teachers → add `anita` / `anita@college.com`. Then Students → add the range `26csmm01` → `26csmm10`.
2. **Teacher** tab → *Create your teacher account* with `anita` / `anita@college.com` → log in → **My Class** → choose `26csmm`, click the lecture hours, then save.
3. **Student** tab → *Create your student account* with `26csmm01` / `26csmm01@kristujayanti.com` → log in → **Request OD** → pick the dates, click the hours, add a proof link, then submit.
4. Log back in as **anita** → **OD Requests** → *View proof* → **Approve**. The "OD approved, attendance updated" dialog appears.

---

## 📁 Project structure

```
AbsentEase/
├── START_ABSENTEASE.bat / STOP_ABSENTEASE.bat   ← double-click
├── index.php                    ← login / sign-up page (all roles)
│
├── pages/                       ← the URLs. Each file: check login → call backend → show template
│   ├── logout.php
│   ├── student/  student_dashboard · request_od · my_od_requests
│   ├── teacher/  teacher_dashboard · od_requests (the ONE review page) · class_setup
│   └── manager/  manager_dashboard · students · teachers · classes
│
├── backend/                     ← all PHP logic + SQL (blocked from the browser)
│   ├── bootstrap.php            ← loaded by every page
│   ├── config/app_config.php    ← ⭐ all settings: DB login, email domain, OD date rules
│   ├── core/                    ← database helpers, login sessions, navigation, flash messages, view helpers
│   ├── repositories/            ← EVERY SQL query, one file per table group
│   └── services/                ← the rules
│       ├── od_request_service.php     submit / withdraw / approve / reject OD
│       ├── attendance_service.php     ⭐ placeholder - plug the attendance module in here
│       ├── teacher_admin_service.php  teachers + class setup
│       ├── student_admin_service.php  students
│       ├── auth_service.php           sign-up / login
│       ├── student_semester_service.php  roll no → course, year, semester
│       └── timetable_service.php      "monday[2-3]" format, date labels
│
├── frontend/                    ← what the browser sees. No SQL, no business logic.
│   ├── templates/               ← HTML (*.view.php only print variables)
│   │   ├── login.view.php
│   │   ├── partials/            ← top bar layout, OD request card, empty state, page head/foot
│   │   └── student/ teacher/ manager/
│   ├── css/  app.css (design system) · login.css · timetable.css
│   └── js/   app.js (toasts, dialogs, confirm, search) · login_page.js · od_request_form.js · class_setup.js
│
├── database/
│   ├── absentease_schema.sql    ← all tables, commented (for reading / phpMyAdmin)
│   └── setup_database.php       ← creates the database or upgrades an old one (run by START)
└── launcher/                    ← PowerShell used by the .bat files
```

## 🗄 Database

| Table | Holds | Cleans up automatically |
|---|---|---|
| `verifiable_teacher_details` | teachers added by the admin | deleting / renaming one also deletes / renames ↓ |
| `teacher_credentials` | teacher password hash | ← cascades from the teacher |
| `class_teacher_details` | which class a teacher owns + its timetable | ← cascades from the teacher |
| `verifiable_student_details` | students added by the admin | deleting / changing a roll no also deletes / changes ↓ |
| `student_credentials` | student password hash | ← cascades from the student |
| `od_requests` | event, details, proof link, status, reviewed by/at, remark | ← cascades from the student |
| `od_request_slots` | one row per date + hour of a request | ← cascades from the request |
| `manager_credentials` | admin email + password hash | — |

A student's **class** is the first 6 characters of the roll number (`26csmm01` → `26csmm`). Their class teacher is whoever owns that class, so it's worked out each time and never copied. When a class changes teacher, its pending requests go to the new class teacher automatically.

See **`CHANGELOG.md`** for what changed and why.
