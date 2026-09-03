# NorthBridge Class Notes

## Course and Team Context

- Course: CS5910-HY2, Fall 2026
- Keep all team members up to date on project progress.
- Each member should prepare for and understand their assigned work, meet milestones, communicate problems promptly, attend meetings, and respond to team communications.
- Address project communication to all team members.
- Test each new module as it is added and use unit testing.
- Remain professional with teammates and in project documentation and presentations.

### Team Information

| Team member | Email | Cell | Role |
| --- | --- | --- | --- |
| To be defined | To be defined | To be defined | To be defined |
| To be defined | To be defined | To be defined | To be defined |
| To be defined | To be defined | To be defined | To be defined |

## Technology

- Front end: HTML5, CSS, and JavaScript
- Back end: PHP and SQL
- Database: MySQL
- Hosting: AWS

## Requirements Terminology

- Explicitly required: behavior the client requires.
- Explicitly forbidden: behavior the client prohibits.
- Client-silent behavior: behavior not specified by the client.
- `R(SRS)`: required behavior in the software requirements specification.
- `F(SRS)`: forbidden behavior in the software requirements specification.
- `R(M)`: liveness criteria in the system model.
- `F(M)`: safety criteria in the system model.
- If `R(M)` is a subset of `R(SRS)`, the model is incomplete.
- If `R(SRS)` is a subset of `R(M)`, the model has extraneous behavior.
- The model is incomplete or incorrect when required SRS behavior is missing or contradicted by forbidden model behavior.
- The model is unsafe when it permits behavior that the SRS forbids.

## Master Schedule

The master schedule should show:

- CRN
- Course name
- Section number
- Faculty name
- Day and period
- Building and room
- Number of available seats
- Year and semester

## User Roles

- Visitor: cannot use the registration system
- Student
- Faculty
- Admin
- Statistics department member

## User and Login Data

- User ID: six-digit, randomly generated identifier
- User fields: user ID, first name, middle name, last name, gender, date of birth, street, city, state, ZIP code, and user type
- Login fields: user ID, email, password, login attempts, and locked status
- Student classifications include undergraduate/graduate and full-time/part-time.
- User IDs and email addresses cannot be changed by students or faculty.

## Administration

- Administrators are full-time only.
- Administration assigns priority and security levels controlling database read/write access.
- The system creates users for students, faculty, administrators, and statistics department members.
- Administration manages users, departments, department courses, majors, minors, major requirements, minor requirements, prerequisites, terms, and course-sections.

## Semester and Year Data

- Generate or update users and their corresponding student classification records.
- Generate or update departments and department courses.
- Generate or update department majors and minor offerings.
- Generate or update major and minor course requirements.
- Generate or update course prerequisites.
- Generate or update semester and year records.

## Course-Section and Time-Slot Rules

- A course-section includes a CRN, course, section number, credits, faculty, time slot, building, room, and available seats.
- A time slot is chosen from days of the week and a period during each day.
- Course-sections may have 5 to 10 available seats.
- No course-section may conflict with another course-section in the same applicable resource or schedule.

## Missing or Unclear Source Items

- The source notes contain obscured text in the undergraduate/graduate registration restriction.
- The source notes end with an incomplete item after “Generate/update course prerequisites.”
- Team names, contact details, team leader, and LUSID testing credentials still need to be supplied separately. Credentials should not be committed to the repository.

## Student Requirements

1. Support undergraduate and graduate students.
2. Support full-time and part-time status. Full-time students have a 16-credit limit; part-time students have an 8-credit limit.
3. View and search the master schedule by department, course name, faculty name, day/period, building/room number, and available seats.
4. View a personal schedule for the current semester, Fall 2026, and the next semester, Spring 2027.
5. View financial, academic, health, and disciplinary holds.
6. View semester grades, an unofficial transcript, degree audit, advisors, and student information.
7. Declare up to two majors, or one major and one minor; change majors and minors.
8. View departments, department courses, prerequisites, majors, minors, major requirements, and minor requirements.
9. Update address and password.
10. Add and drop course-sections within the allowed registration time window.

## Student Restrictions

- A student with an account hold cannot register.
- Required prerequisites must be completed before registration.
- Undergraduate students cannot register for graduate courses, and graduate students cannot register for undergraduate-only courses.
- A student cannot register for a course-section with no available seats.
- Full-time students cannot exceed 16 credits; part-time students cannot exceed 8 credits.
- Students cannot change their user ID or email address.
- Students cannot register for sections with overlapping time slots.
- Students cannot retake a course they have already passed.
- Add/drop actions are blocked outside the registration time window.
- Students cannot change grades or update attendance.
- Students cannot view other users' private information.

## Faculty Requirements

1. Support full-time and part-time faculty.
2. Allow faculty to belong to more than one department, subject to the limits below.
3. View and search the master schedule by department, course name, faculty name, time slot, building/room number, and available seats.
4. View current and next semester schedules.
5. View course-section rosters, including student IDs and email addresses.
6. View up to 15 advisees, unofficial transcripts, degree audits, departments, department courses, prerequisites, majors, minors, and their requirements.
7. Mark course-section attendance during the scheduled day/period.
8. Assign grades within the allowed grading time window.
9. Update address and password.

## Faculty Constraints

- Full-time faculty may teach no more than two course-sections.
- Part-time faculty must teach exactly one course-section.
- Full-time faculty cannot teach two sections with overlapping time slots.
- Full-time faculty may belong to no more than three departments.
- Part-time faculty may belong to only one department.
- Full-time faculty may have no more than 15 advisees.
- Part-time faculty may have no advisees.
- Faculty cannot teach course-sections outside their departments.
- Attendance cannot be marked outside the course-section's scheduled day/period.
- Attendance cannot be changed after it has been recorded.
- Grades can be assigned or changed only within 72 hours after the examination. After 72 hours, grades cannot be changed.
- Faculty cannot change their user ID or email address.
- Faculty may access student information, but cannot access unrelated non-student user information.
- Faculty may view student records but cannot change student information.
- Faculty cannot add or drop students from course-sections.

## Prerequisite Course Table

| Course | Prerequisite | Minimum grade |
| --- | --- | --- |
| CS5910 | CS4501 | C or better |
| CS5910 | CS4550 | C or better |
| CS5910 | CS4720 | C or better |
| CS4720 | CS4550 | C or better |

## Faculty-Department Table

| Faculty name | Department name | Percentage of time | Date of appointment |
| --- | --- | --- | --- |
| To be defined | To be defined | To be defined | To be defined |