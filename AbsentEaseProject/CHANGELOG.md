# Changelog

## Version 2 — single class-teacher review, data clean-up, new UI

### 1. OD flow: one reviewer instead of two

**Before:** a request went to two people.
- An "event faculty verifier" picked by the student used the **Verify students OD** page.
- The class teacher used the **View OD Requests** page.

**Now:** there is one page, **OD Requests**, for the **class teacher** of the student's class.
1. The class teacher opens the proof and clicks **Approve** or **Reject**. Rejecting requires a reason, which the student sees.
2. Approving shows the **"OD approved — attendance updated"** dialog.
3. The real attendance update will plug into `backend/services/attendance_service.php` → `mark_attendance_for_approved_od()`. That function is called on every approval.

### 2. Logic problems found and fixed

| # | Problem in the old version | What happens now |
|---|---|---|
| 1 | Students could only pick days of the **current week**, including days already past. On Sunday it jumped to next week. Events in later weeks couldn't be requested. | Students pick a **From / To date** (up to 15 days back for after-the-event requests, up to 30 days ahead, max 7 days per request). Only that weekday's lecture hours can be picked. |
| 2 | Dates and hours were stored as two text fields (`monday[3-4]` + `02-11-2026`) that had to be matched up by position. | Each date + hour is one row in **`od_request_slots`**. |
| 3 | A student could request the **same hour twice** (only "one request per day" was checked). | An hour that's already in a pending or approved request is refused, and the grid shows it as taken. The "one request per day" limit is gone, so two different events on different days are fine. |
| 4 | Clicking **OK** on an approved request **deleted it**, which destroyed the record that the attendance needs. | Requests are kept as history. Only a **pending** request can be removed (**Withdraw**). |
| 5 | The class teacher was **copied** into each student row and into each request. Deleting or changing a teacher left students and requests pointing at nobody: those requests could never be approved, and the class could never be claimed again. | The class teacher is worked out from the class each time. Pending requests always go to the **current** class teacher of the class. |
| 6 | Teacher B could take over teacher A's class whenever a new unassigned student was added to it, because a class was "free" if any one student was free. | **One class teacher per class**, enforced by the database. Only classes with no teacher, plus your own class, are offered. |
| 7 | The class semester was stored when the teacher saved the class and then went stale. | Worked out from the class year each time. |
| 8 | Email was stored twice (whitelist + login table). | Stored once, in the whitelist. |
| 9 | Deleting a student left their **login and OD requests** behind (orphan rows). | **Foreign keys with CASCADE.** Deleting a student deletes their login, requests and request dates. Changing a roll number moves all of them. Deleting a teacher deletes their login and class setup. |
| 10 | Manager password stored with **AES (reversible)** and a key written in the code. | **bcrypt**, the same as teachers and students. Existing passwords are converted with the same password. |
| 11 | Anyone could decide a request again after it was decided. | Only **pending** requests can be approved or rejected, and only by the class teacher of that class. |
| 12 | No proof was required. | A **proof link is required** and must be a real web link. `drive.google.com/...` without `https://` is accepted and fixed automatically. |
| 13 | Signing up as a teacher or student didn't check password length. | Minimum 6 characters. |
| 14 | A student whose class has no class teacher could still open the form. | The page explains why they can't request OD yet. The admin dashboard lists classes without a class teacher. |

### 3. Upgrading your existing database

`database/setup_database.php` runs automatically from `START_ABSENTEASE.bat`, using XAMPP's own `php.exe`. It upgrades the old tables in place:
- drops the copied columns
- converts old requests into the new slot format; your approved request stays approved, with `reviewed_by` set
- removes orphan rows
- adds the cascade rules
- converts the manager password

Running it again changes nothing.

### 4. New UI

- **No more sidebar + iframe.** The old dashboards were an empty area with a menu down the side. Now there's a **top navigation bar**, and each menu item is a full page.
- **Dashboards show real content:**
  - Student: pending / approved / rejected counts, approved OD hours, recent requests
  - Teacher: pending count, students, lecture hours per week, oldest pending requests
  - Admin: totals, plus classes that still need a class teacher
- **Admin menu shortened** from 5 items to 3. *Add* and *edit* are on the same page for students and for teachers, and *Classes* is an overview.
- **New login page** with Student / Teacher / Admin tabs. After an error it reopens the same tab.
- **Corner notifications instead of `alert()` pop-ups.** Dialogs are used for Approve / Reject / Edit / "Are you sure?".
- **Request cards** show the event, date + hour chips, proof button, status badge, and who decided, when, and why.
- **Works on phones:** the menu collapses and grids scroll inside their card.

### 5. File changes

| Removed | Replaced by |
|---|---|
| `pages/teacher/faculty_verify_od_requests.php` + `class_teacher_approve_od_requests.php` | `pages/teacher/od_requests.php` |
| `pages/teacher/class_teacher_setup.php` | `pages/teacher/class_setup.php` (now shows your saved class and timetable) |
| `pages/student/od_status.php` | `pages/student/my_od_requests.php` |
| `pages/manager/add_students.php` + `manage_students.php` | `pages/manager/students.php` |
| `pages/manager/add_teacher.php` + `manage_teachers.php` | `pages/manager/teachers.php` |
| `pages/manager/class_teacher_list.php` | `pages/manager/classes.php` |
| 10 per-page CSS files, 9 JS files | `app.css` + `login.css` + `timetable.css`, and `app.js` + 3 page scripts |
| dashboard iframe layout | `partials/layout_start` / `layout_end` (top bar) |

---

## Version 1 — restructure

- Split the original 17 mixed PHP files into `pages/` (URLs), `backend/` (logic + SQL) and `frontend/` (templates, CSS, JS).
- Removed duplicated code: 13 database connections, 4 copies of the semester calculation, and 4 copies of the timetable parser.
- Added the one-click `START_ABSENTEASE.bat` / `STOP_ABSENTEASE.bat`.
- Fixed:
  - manager pages had no login check
  - renaming a teacher crashed
  - "Refresh" deleted every student in December and May
