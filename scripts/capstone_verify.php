<?php

declare(strict_types=1);

/**
 * Capstone pre-demo verification — run before presenting to your professor.
 *
 *   php -S 127.0.0.1:8000 -t public public/router.php   # terminal 1
 *   php scripts/capstone_verify.php http://127.0.0.1:8000
 */

/**
 * @return array{0:int,1:string}
 */
function capstone_http(string $url): array
{
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true,
            'follow_location' => 0,
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $meta = $http_response_header ?? [];
    $code = 0;
    foreach ($meta as $line) {
        if (preg_match('#HTTP/\S+\s+(\d{3})#', $line, $m)) {
            $code = (int)$m[1];
        }
    }

    return [$code, $body === false ? '' : $body];
}

function capstone_has_php_leak(string $body): ?string
{
    $patterns = [
        'Deprecated:' => 'PHP Deprecated notice in HTML',
        'Fatal error:' => 'PHP Fatal error in HTML',
        'Parse error:' => 'PHP Parse error in HTML',
        'Warning:.* in /' => 'PHP Warning in HTML',
        'Uncaught ' => 'Uncaught exception in HTML',
    ];
    foreach ($patterns as $pat => $label) {
        if (preg_match('#' . $pat . '#i', $body)) {
            return $label;
        }
    }

    return null;
}

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');
$failed = false;
$passed = 0;

function check(bool $ok, string $label): void
{
    global $failed, $passed;
    if ($ok) {
        fwrite(STDOUT, "OK   {$label}\n");
        ++$passed;
    } else {
        fwrite(STDERR, "FAIL {$label}\n");
        $failed = true;
    }
}

check(
    preg_match('/^\s*(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s*$/', '10:00-11:15') === 1,
    'Time format regex accepts 10:00-11:15'
);
check(preg_match('/^[MTWRFSU]+$/', 'MWF') === 1, 'Meeting days regex accepts MWF');

try {
    require __DIR__ . '/../app/lib/view.php';
    require __DIR__ . '/../app/lib/bootstrap.php';
    bootstrap_app();
    require __DIR__ . '/../app/lib/db.php';
    $pdo = db();
    $pdo->query('SELECT 1')->fetchColumn();
    check(true, 'Database connection');

    $studentCnt = (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
    check($studentCnt > 0, "Students table has rows ({$studentCnt})");

    $termCnt = (int)$pdo->query('SELECT COUNT(*) FROM terms')->fetchColumn();
    check($termCnt > 0, "Terms configured ({$termCnt})");

    $sectionCnt = (int)$pdo->query('SELECT COUNT(*) FROM sections')->fetchColumn();
    check($sectionCnt > 0, "Sections exist ({$sectionCnt})");

    $courseCnt = (int)$pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
    check($courseCnt > 0, "Courses in catalog ({$courseCnt})");

    $demo = $pdo->prepare('SELECT 1 FROM students WHERE student_id = ? LIMIT 1');
    $demo->execute([123123]);
    if ((bool)$demo->fetchColumn()) {
        check(true, 'Demo student 123123 exists');
    } else {
        fwrite(STDERR, "WARN Demo student 123123 missing — run: php scripts/seed_demo_registration.php\n");
        check($studentCnt > 0, 'Students exist (use seed_demo_registration.php for demo ID 123123)');
    }

    $auth = $pdo->prepare('SELECT 1 FROM auth_users WHERE email IS NOT NULL AND TRIM(email) <> "" LIMIT 1');
    $auth->execute();
    check((bool)$auth->fetchColumn(), 'At least one staff login email configured');
} catch (Throwable $e) {
    check(false, 'Database: ' . $e->getMessage());
}

$publicPaths = ['/', '/health', '/login.php', '/login'];
foreach ($publicPaths as $path) {
    [$code, $body] = capstone_http($base . $path);
    $leak = capstone_has_php_leak($body);
    $ok = $code >= 200 && $code < 400 && $leak === null;
    check($ok, "{$path} (HTTP {$code}" . ($leak ? ", {$leak}" : '') . ')');
    if ($path === '/health') {
        check(str_contains($body, '"ok"'), '/health JSON ok field');
    }
}

[$adminCode] = capstone_http($base . '/admin.php?view=dashboard');
check($adminCode === 302 || $adminCode === 301, 'admin.php redirects when not logged in (HTTP ' . $adminCode . ')');

$adminViews = ['dashboard', 'courses', 'schedule', 'registration', 'holds', 'catalog', 'people'];
foreach ($adminViews as $view) {
    [$code] = capstone_http($base . '/admin.php?view=' . rawurlencode($view));
    check($code === 302 || $code === 301, "admin.php?view={$view} requires login (HTTP {$code})");
}

fwrite(STDOUT, "\n{$passed} checks passed.\n");
if ($failed) {
    fwrite(STDERR, "Some checks failed — fix before capstone demo.\n");
    exit(1);
}

fwrite(STDOUT, "Ready for capstone demo.\n");
exit(0);
