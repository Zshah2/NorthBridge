<?php

declare(strict_types=1);

/**
 * Remove placeholder demo faculty/department data for a polished professor demo.
 *
 * What it does:
 *   1. Deletes placeholder department D01 (and its faculty_departments rows).
 *   2. Removes synthetic faculty named "ProfN Faculty" (e.g. Prof1 Faculty … Prof240 Faculty).
 *   3. Deduplicates remaining faculty by normalized first+last name (keeps lowest faculty_id).
 *      - Sections taught by removed duplicates are reassigned to the kept faculty row.
 *      - faculty_departments on removed rows are merged onto the kept row when missing.
 *   4. Ensures every remaining department has a valid chair from its departmental faculty.
 *
 * Safe FK order: detach sections/chairs → faculty_departments → faculty → users → departments.
 *
 * Usage:
 *   php scripts/cleanup_demo_faculty.php            # apply changes
 *   php scripts/cleanup_demo_faculty.php --dry-run  # preview only
 */

require __DIR__ . '/../app/lib/view.php';
require __DIR__ . '/../app/lib/db.php';

$dryRun = in_array('--dry-run', $argv, true);

/** Placeholder department created outside CSV import. */
const PLACEHOLDER_DEPT_ID = 'D01';

/** Synthetic faculty name pattern: Prof1 Faculty, Prof42 Faculty, etc. */
function is_synthetic_faculty_name(string $first, string $last): bool
{
    return $last === 'Faculty' && (bool)preg_match('/^Prof\d+$/', $first);
}

function norm_name(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/\s+/', ' ', $s) ?? $s;
    $s = preg_replace('/[^a-z0-9 ]/i', '', $s) ?? $s;

    return trim($s);
}

/**
 * @return int[]
 */
function fetch_synthetic_faculty_ids(PDO $pdo): array
{
    $rows = $pdo->query('
      SELECT f.faculty_id, u.first_name, u.last_name
      FROM faculty f
      INNER JOIN users u ON u.user_id = f.faculty_id
    ')->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $ids = [];
    foreach ($rows as $row) {
        $fid = (int)($row['faculty_id'] ?? 0);
        if ($fid < 1) {
            continue;
        }
        if (is_synthetic_faculty_name((string)($row['first_name'] ?? ''), (string)($row['last_name'] ?? ''))) {
            $ids[] = $fid;
        }
    }

    sort($ids);

    return $ids;
}

/**
 * @return array<int, int> map removed_faculty_id => kept_faculty_id
 */
function find_duplicate_faculty_remaps(PDO $pdo): array
{
    $rows = $pdo->query('
      SELECT f.faculty_id, u.first_name, u.last_name
      FROM faculty f
      INNER JOIN users u ON u.user_id = f.faculty_id
      ORDER BY f.faculty_id ASC
    ')->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $byName = [];
    $remap = [];
    foreach ($rows as $row) {
        $fid = (int)($row['faculty_id'] ?? 0);
        if ($fid < 1) {
            continue;
        }
        if (is_synthetic_faculty_name((string)($row['first_name'] ?? ''), (string)($row['last_name'] ?? ''))) {
            continue;
        }

        $first = norm_name((string)($row['first_name'] ?? ''));
        $last = norm_name((string)($row['last_name'] ?? ''));
        if ($first === '' || $last === '') {
            continue;
        }

        $key = $first . '|' . $last;
        if (!isset($byName[$key])) {
            $byName[$key] = $fid;
            continue;
        }

        $keep = (int)$byName[$key];
        if ($fid !== $keep) {
            $remap[$fid] = $keep;
        }
    }

    return $remap;
}

function count_snapshot(PDO $pdo): array
{
    $departments = (int)$pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn();
    $faculty = (int)$pdo->query('SELECT COUNT(*) FROM faculty')->fetchColumn();
    $synthetic = count(fetch_synthetic_faculty_ids($pdo));
    $d01 = (int)$pdo->query(
        'SELECT COUNT(*) FROM departments WHERE dept_id = ' . $pdo->quote(PLACEHOLDER_DEPT_ID)
    )->fetchColumn();
    $chairs = (int)$pdo->query(
        'SELECT COUNT(*) FROM departments d
         INNER JOIN users u ON u.user_id = d.chair_id
         INNER JOIN faculty f ON f.faculty_id = d.chair_id
         WHERE d.dept_id <> ' . $pdo->quote(PLACEHOLDER_DEPT_ID)
    )->fetchColumn();
    $realDepts = (int)$pdo->query(
        'SELECT COUNT(*) FROM departments WHERE dept_id <> ' . $pdo->quote(PLACEHOLDER_DEPT_ID)
    )->fetchColumn();
    $dupGroups = (int)$pdo->query('
      SELECT COUNT(*) FROM (
        SELECT 1
        FROM faculty f
        INNER JOIN users u ON u.user_id = f.faculty_id
        WHERE NOT (u.last_name = \'Faculty\' AND u.first_name REGEXP \'^Prof[0-9]+$\')
        GROUP BY LOWER(TRIM(u.first_name)), LOWER(TRIM(u.last_name))
        HAVING COUNT(*) > 1
      ) t
    ')->fetchColumn();

    return [
        'departments' => $departments,
        'real_departments' => $realDepts,
        'faculty' => $faculty,
        'synthetic_faculty' => $synthetic,
        'placeholder_dept_d01' => $d01,
        'valid_chairs' => $chairs,
        'duplicate_name_groups' => $dupGroups,
    ];
}

function print_snapshot(string $label, array $snap): void
{
    fwrite(STDOUT, "{$label}\n");
    fwrite(STDOUT, "  departments: {$snap['departments']} (real: {$snap['real_departments']})\n");
    fwrite(STDOUT, "  faculty: {$snap['faculty']}\n");
    fwrite(STDOUT, "  synthetic ProfN Faculty: {$snap['synthetic_faculty']}\n");
    fwrite(STDOUT, "  placeholder D01: {$snap['placeholder_dept_d01']}\n");
    fwrite(STDOUT, "  valid chairs (real depts): {$snap['valid_chairs']}\n");
    fwrite(STDOUT, "  duplicate name groups: {$snap['duplicate_name_groups']}\n");
}

/**
 * @param int[] $ids
 */
function remove_faculty_ids(PDO $pdo, array $ids, bool $dryRun, string $reason): int
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $v): bool => $v > 0)));
    if ($ids === []) {
        return 0;
    }

    $place = implode(',', array_fill(0, count($ids), '?'));

    if ($dryRun) {
        fwrite(STDOUT, "[dry-run] would remove {$reason}: " . count($ids) . " faculty (" . implode(', ', array_slice($ids, 0, 5))
            . (count($ids) > 5 ? ', …' : '') . ")\n");

        return count($ids);
    }

    $pdo->prepare('UPDATE sections SET faculty_id = NULL WHERE faculty_id IN (' . $place . ')')->execute($ids);
    $pdo->prepare('UPDATE departments SET chair_id = NULL WHERE chair_id IN (' . $place . ')')->execute($ids);
    $pdo->prepare('DELETE FROM faculty_departments WHERE faculty_id IN (' . $place . ')')->execute($ids);
    $pdo->prepare('DELETE FROM faculty WHERE faculty_id IN (' . $place . ')')->execute($ids);
    $pdo->prepare('DELETE FROM users WHERE user_id IN (' . $place . ')')->execute($ids);

    fwrite(STDOUT, "Removed {$reason}: " . count($ids) . " faculty\n");

    return count($ids);
}

/**
 * @param array<int, int> $remap removed => kept
 */
function apply_duplicate_remaps(PDO $pdo, array $remap, bool $dryRun): int
{
    if ($remap === []) {
        return 0;
    }

    $reassignSections = $pdo->prepare('UPDATE sections SET faculty_id = ? WHERE faculty_id = ?');
    $nullChair = $pdo->prepare('UPDATE departments SET chair_id = ? WHERE chair_id = ?');
    $mergeDept = $pdo->prepare('
      INSERT IGNORE INTO faculty_departments (faculty_id, dept_id, percent_time, date_of_appointment)
      SELECT ?, dept_id, percent_time, date_of_appointment
      FROM faculty_departments
      WHERE faculty_id = ?
    ');
    $deleteDept = $pdo->prepare('DELETE FROM faculty_departments WHERE faculty_id = ?');
    $deleteFaculty = $pdo->prepare('DELETE FROM faculty WHERE faculty_id = ?');
    $deleteUser = $pdo->prepare('DELETE FROM users WHERE user_id = ?');

    $removed = 0;
    foreach ($remap as $fromId => $toId) {
        $fromId = (int)$fromId;
        $toId = (int)$toId;
        if ($fromId < 1 || $toId < 1 || $fromId === $toId) {
            continue;
        }

        if ($dryRun) {
            fwrite(STDOUT, "[dry-run] would dedupe faculty {$fromId} → {$toId}\n");
            $removed++;
            continue;
        }

        $reassignSections->execute([$toId, $fromId]);
        $nullChair->execute([$toId, $fromId]);
        $mergeDept->execute([$toId, $fromId]);
        $deleteDept->execute([$fromId]);
        $deleteFaculty->execute([$fromId]);
        $deleteUser->execute([$fromId]);
        $removed++;
    }

    if (!$dryRun && $removed > 0) {
        fwrite(STDOUT, "Deduplicated duplicate faculty rows: {$removed}\n");
    }

    return $removed;
}

function remove_placeholder_department(PDO $pdo, bool $dryRun): bool
{
    $exists = (int)$pdo->query(
        'SELECT COUNT(*) FROM departments WHERE dept_id = ' . $pdo->quote(PLACEHOLDER_DEPT_ID)
    )->fetchColumn();
    if ($exists === 0) {
        fwrite(STDOUT, "Placeholder department " . PLACEHOLDER_DEPT_ID . " not found; skipping.\n");

        return false;
    }

    $fdCount = (int)$pdo->query(
        'SELECT COUNT(*) FROM faculty_departments WHERE dept_id = ' . $pdo->quote(PLACEHOLDER_DEPT_ID)
    )->fetchColumn();

    if ($dryRun) {
        fwrite(STDOUT, "[dry-run] would delete department " . PLACEHOLDER_DEPT_ID . " ({$fdCount} faculty_departments rows)\n");

        return true;
    }

    $pdo->prepare(
        'UPDATE departments SET chair_id = NULL WHERE dept_id = ?'
    )->execute([PLACEHOLDER_DEPT_ID]);
    $pdo->prepare(
        'DELETE FROM faculty_departments WHERE dept_id = ?'
    )->execute([PLACEHOLDER_DEPT_ID]);
    $pdo->prepare(
        'DELETE FROM departments WHERE dept_id = ?'
    )->execute([PLACEHOLDER_DEPT_ID]);

    fwrite(STDOUT, "Deleted placeholder department " . PLACEHOLDER_DEPT_ID . " ({$fdCount} faculty_departments rows)\n");

    return true;
}

function reseed_department_chairs(PDO $pdo, bool $dryRun): int
{
    if ($dryRun) {
        $needs = (int)$pdo->query('
          SELECT COUNT(*) FROM departments d
          LEFT JOIN faculty f ON f.faculty_id = d.chair_id
          WHERE f.faculty_id IS NULL
        ')->fetchColumn();
        fwrite(STDOUT, "[dry-run] would reseed chairs for {$needs} department(s)\n");

        return $needs;
    }

    $updated = $pdo->exec('
      UPDATE departments d
      LEFT JOIN faculty f ON f.faculty_id = d.chair_id
      INNER JOIN (
        SELECT fd.dept_id, MIN(fd.faculty_id) AS faculty_id
        FROM faculty_departments fd
        INNER JOIN faculty ff ON ff.faculty_id = fd.faculty_id
        GROUP BY fd.dept_id
      ) pick ON pick.dept_id = d.dept_id
      SET d.chair_id = pick.faculty_id
      WHERE f.faculty_id IS NULL
    ');

    $count = $updated === false ? 0 : (int)$updated;
    if ($count > 0) {
        fwrite(STDOUT, "Reseeded department chairs: {$count}\n");
    }

    return $count;
}

$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$before = count_snapshot($pdo);
print_snapshot('Before:', $before);
fwrite(STDOUT, ($dryRun ? "Mode: dry-run\n\n" : "Mode: apply\n\n"));

$syntheticIds = fetch_synthetic_faculty_ids($pdo);
$dupRemap = find_duplicate_faculty_remaps($pdo);

fwrite(STDOUT, "Plan:\n");
fwrite(STDOUT, '  - Remove synthetic faculty: ' . count($syntheticIds) . "\n");
fwrite(STDOUT, '  - Remove placeholder dept ' . PLACEHOLDER_DEPT_ID . ': '
    . ($before['placeholder_dept_d01'] > 0 ? 'yes' : 'no') . "\n");
fwrite(STDOUT, '  - Deduplicate faculty by name: ' . count($dupRemap) . " rows\n\n");

if (!$dryRun) {
    $pdo->beginTransaction();
}

try {
    remove_placeholder_department($pdo, $dryRun);
    remove_faculty_ids($pdo, $syntheticIds, $dryRun, 'synthetic ProfN Faculty');
    apply_duplicate_remaps($pdo, $dupRemap, $dryRun);
    reseed_department_chairs($pdo, $dryRun);

    if (!$dryRun) {
        $pdo->commit();
    }
} catch (Throwable $e) {
    if (!$dryRun && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'FAILED: ' . $e->getMessage() . "\n");
    exit(1);
}

$after = count_snapshot($pdo);
fwrite(STDOUT, "\n");
print_snapshot('After:', $after);

if (!$dryRun && ($after['synthetic_faculty'] > 0 || $after['placeholder_dept_d01'] > 0)) {
    fwrite(STDERR, "WARNING: placeholder data may remain; review output above.\n");
    exit(1);
}

if (!$dryRun && $after['real_departments'] > 0 && $after['valid_chairs'] < $after['real_departments']) {
    fwrite(STDERR, "WARNING: not all real departments have valid chairs.\n");
    exit(1);
}

fwrite(STDOUT, ($dryRun ? "Dry-run complete.\n" : "Cleanup complete.\n"));
exit(0);
