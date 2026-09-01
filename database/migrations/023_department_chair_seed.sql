-- Assign one chair per department from departmental faculty (lowest faculty_id per dept).
UPDATE departments d
INNER JOIN (
  SELECT fd.dept_id, MIN(fd.faculty_id) AS faculty_id
  FROM faculty_departments fd
  INNER JOIN faculty f ON f.faculty_id = fd.faculty_id
  GROUP BY fd.dept_id
) pick ON pick.dept_id = d.dept_id
SET d.chair_id = pick.faculty_id
WHERE d.chair_id IS NULL;
