<?php

declare(strict_types=1);

/**
 * Admin departments directory: contact, chair, and roster/catalog counts.
 *
 * @return array{departments: list<array<string, mixed>>}
 */
function admin_departments_list_state(PDO $pdo): array
{
    $departments = [];

    try {
        $departments = $pdo->query('
          SELECT
            d.dept_id,
            d.dept_name,
            d.room_number,
            d.building_number,
            d.email,
            d.phone_number,
            d.chair_id,
            cu.first_name AS chair_first,
            cu.last_name AS chair_last,
            (SELECT COUNT(*) FROM faculty_departments fd WHERE fd.dept_id = d.dept_id) AS faculty_count,
            (SELECT COUNT(*) FROM student_departments sd WHERE sd.dept_id = d.dept_id) AS student_count,
            (SELECT COUNT(*) FROM courses c WHERE c.dept_id = d.dept_id) AS course_count
          FROM departments d
          LEFT JOIN users cu ON cu.user_id = d.chair_id
          ORDER BY d.dept_name, d.dept_id
        ')->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable) {
        try {
            $departments = $pdo->query('
              SELECT
                d.dept_id,
                d.dept_name,
                d.room_number,
                d.building_number,
                d.email,
                d.phone_number,
                d.chair_id,
                cu.first_name AS chair_first,
                cu.last_name AS chair_last,
                0 AS faculty_count,
                0 AS student_count,
                0 AS course_count
              FROM departments d
              LEFT JOIN users cu ON cu.user_id = d.chair_id
              ORDER BY d.dept_name, d.dept_id
            ')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            try {
                $departments = $pdo->query('
                  SELECT dept_id, dept_name
                  FROM departments
                  ORDER BY dept_name, dept_id
                ')->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable) {
                $departments = [];
            }
        }
    }

    return ['departments' => $departments];
}
