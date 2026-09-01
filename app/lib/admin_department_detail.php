<?php

declare(strict_types=1);

/**
 * Single-department admin record: contact info, chair, faculty, students, catalog courses.
 *
 * @param array<string, scalar|array|null> $get
 *
 * @return array{
 *   dept_id_param: string,
 *   department: array<string, mixed>|null,
 *   faculty: list<array<string, mixed>>,
 *   students: list<array<string, mixed>>,
 *   student_count: int,
 *   courses: list<array<string, mixed>>
 * }
 */
function admin_department_detail_state(PDO $pdo, array $get): array
{
    $rawDeptId = trim((string)($get['dept_id'] ?? ''));
    if ($rawDeptId === '' && isset($get['id'])) {
        $rawDeptId = trim((string)$get['id']);
    }
    $deptIdParam = strtoupper($rawDeptId);

    $department = null;
    $faculty = [];
    $students = [];
    $studentCount = 0;
    $courses = [];

    if ($deptIdParam !== '') {
        try {
            $st = $pdo->prepare('
              SELECT
                d.dept_id,
                d.dept_name,
                d.room_number,
                d.building_number,
                d.email,
                d.phone_number,
                d.dept_assistant,
                d.chair_id,
                cu.first_name AS chair_first,
                cu.last_name AS chair_last,
                cu.email AS chair_email
              FROM departments d
              LEFT JOIN users cu ON cu.user_id = d.chair_id
              WHERE d.dept_id = ?
              LIMIT 1
            ');
            $st->execute([$deptIdParam]);
            $department = $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable) {
            $department = null;
        }
    }

    if ($department !== null) {
        $did = (string)$department['dept_id'];

        try {
            $fst = $pdo->prepare('
              SELECT
                fd.faculty_id,
                fd.percent_time,
                fd.date_of_appointment,
                u.first_name,
                u.last_name,
                u.email,
                f.`rank`,
                f.faculty_type
              FROM faculty_departments fd
              INNER JOIN users u ON u.user_id = fd.faculty_id
              LEFT JOIN faculty f ON f.faculty_id = fd.faculty_id
              WHERE fd.dept_id = ?
              ORDER BY u.last_name, u.first_name, fd.faculty_id
            ');
            $fst->execute([$did]);
            $faculty = $fst->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            $faculty = [];
        }

        try {
            $cntSt = $pdo->prepare('SELECT COUNT(*) FROM student_departments WHERE dept_id = ?');
            $cntSt->execute([$did]);
            $studentCount = (int)$cntSt->fetchColumn();
        } catch (Throwable) {
            $studentCount = 0;
        }

        try {
            $sst = $pdo->prepare('
              SELECT
                sd.student_id,
                sd.declaration_role,
                sd.date_of_declaration,
                u.first_name,
                u.last_name,
                u.email
              FROM student_departments sd
              INNER JOIN users u ON u.user_id = sd.student_id
              WHERE sd.dept_id = ?
              ORDER BY sd.declaration_role, u.last_name, u.first_name, sd.student_id
              LIMIT 50
            ');
            $sst->execute([$did]);
            $students = $sst->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            try {
                $sst = $pdo->prepare('
                  SELECT
                    sd.student_id,
                    "major" AS declaration_role,
                    sd.date_of_declaration,
                    u.first_name,
                    u.last_name,
                    u.email
                  FROM student_departments sd
                  INNER JOIN users u ON u.user_id = sd.student_id
                  WHERE sd.dept_id = ?
                  ORDER BY u.last_name, u.first_name, sd.student_id
                  LIMIT 50
                ');
                $sst->execute([$did]);
                $students = $sst->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable) {
                $students = [];
            }
        }

        try {
            $cst = $pdo->prepare('
              SELECT course_id, course_name, credits, IFNULL(is_active, 1) AS is_active
              FROM courses
              WHERE dept_id = ?
              ORDER BY course_id
            ');
            $cst->execute([$did]);
            $courses = $cst->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            try {
                $cst = $pdo->prepare('
                  SELECT course_id, course_name, credits, 1 AS is_active
                  FROM courses
                  WHERE dept_id = ?
                  ORDER BY course_id
                ');
                $cst->execute([$did]);
                $courses = $cst->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable) {
                $courses = [];
            }
        }
    }

    return [
        'dept_id_param' => $deptIdParam,
        'department' => $department,
        'faculty' => $faculty,
        'students' => $students,
        'student_count' => $studentCount,
        'courses' => $courses,
    ];
}
