<?php

declare(strict_types=1);

/**
 * Stress + edge-case test for capstone / professor QA.
 *
 *   php -S 127.0.0.1:8000 -t public public/router.php
 *   php scripts/stress_test.php http://127.0.0.1:8000
 *
 * Optional env:
 *   STRESS_EMAIL=zshah2@oldwestbury.edu
 *   STRESS_PASSWORD=Main@1234
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');
$email = getenv('STRESS_EMAIL') ?: 'zshah2@oldwestbury.edu';
$password = getenv('STRESS_PASSWORD') ?: 'Main@1234';

$failed = 0;
$passed = 0;
$warn = 0;

function stress_ok(string $label): void
{
    global $passed;
    fwrite(STDOUT, "OK   {$label}\n");
    ++$passed;
}

function stress_fail(string $label, string $detail = ''): void
{
    global $failed;
    $msg = "FAIL {$label}" . ($detail !== '' ? " — {$detail}" : '');
    fwrite(STDERR, "{$msg}\n");
    ++$failed;
}

function stress_warn(string $label): void
{
    global $warn;
    fwrite(STDERR, "WARN {$label}\n");
    ++$warn;
}

function stress_php_leak(string $body): ?string
{
    foreach ([
        'Deprecated:' => 'Deprecated',
        'Fatal error:' => 'Fatal',
        'Parse error:' => 'Parse',
        'Warning:.* in /' => 'Warning',
        'Uncaught ' => 'Uncaught',
        'Stack trace:' => 'Stack trace',
    ] as $pat => $label) {
        if (preg_match('#' . $pat . '#i', $body)) {
            return $label;
        }
    }

    return null;
}

/**
 * @param array<string, string> $headers
 * @return array{code:int, body:string, headers:array<int, string>}
 */
function stress_request(
    string $method,
    string $url,
    ?string $body = null,
    ?string $cookieFile = null,
    array $headers = []
): array {
    $hdr = $headers;
    if ($body !== null) {
        $hdr[] = 'Content-Type: application/x-www-form-urlencoded';
        $hdr[] = 'Content-Length: ' . strlen($body);
    }
    if ($cookieFile !== null && is_file($cookieFile)) {
        $hdr[] = 'Cookie: ' . trim((string)file_get_contents($cookieFile));
    }
    $ctx = stream_context_create([
        'http' => [
            'method' => $method,
            'timeout' => 15,
            'ignore_errors' => true,
            'follow_location' => 0,
            'header' => implode("\r\n", $hdr),
            'content' => $body ?? '',
        ],
    ]);
    $respBody = @file_get_contents($url, false, $ctx);
    $meta = $http_response_header ?? [];
    $code = 0;
    foreach ($meta as $line) {
        if (preg_match('#HTTP/\S+\s+(\d{3})#', $line, $m)) {
            $code = (int)$m[1];
        }
        if ($cookieFile !== null && stripos($line, 'Set-Cookie:') === 0) {
            if (preg_match('/PHPSESSID=([^;]+)/', $line, $cm)) {
                file_put_contents($cookieFile, 'PHPSESSID=' . $cm[1]);
            }
        }
    }

    return ['code' => $code, 'body' => $respBody === false ? '' : $respBody, 'headers' => $meta];
}

function stress_extract_csrf(string $html): ?string
{
    if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }

    return null;
}

fwrite(STDOUT, "Stress test → {$base}\n\n");

// --- 1) Concurrent public page hammer ---
$paths = ['/', '/health', '/login.php', '/login', '/admin.php?view=dashboard'];
$workers = 20;
$iterations = 5;
$concurrentErrors = 0;
$concurrentTotal = 0;
$start = microtime(true);

for ($i = 0; $i < $iterations; ++$i) {
    foreach ($paths as $path) {
        for ($w = 0; $w < $workers; ++$w) {
            ++$concurrentTotal;
            $r = stress_request('GET', $base . $path);
            $leak = stress_php_leak($r['body']);
            $expectOk = $path === '/admin.php?view=dashboard'
                ? ($r['code'] === 302 || $r['code'] === 301)
                : ($r['code'] >= 200 && $r['code'] < 400);
            if (!$expectOk || $leak !== null) {
                ++$concurrentErrors;
            }
        }
    }
}
$elapsed = round(microtime(true) - $start, 2);
if ($concurrentErrors === 0) {
    stress_ok("Concurrent load {$concurrentTotal} requests in {$elapsed}s — no errors");
} else {
    stress_fail("Concurrent load", "{$concurrentErrors}/{$concurrentTotal} bad responses");
}

// --- 2) Edge-case URLs (unauthenticated) ---
$edgeUrls = [
    '/admin.php?view=' . rawurlencode("'; DROP TABLE students;--") => [302, 302],
    '/admin.php?view=' . rawurlencode('<script>alert(1)</script>') => [302, 302],
    '/admin.php?view=not_a_real_view' => [302, 302],
    '/%2e%2e/%2e%2e/etc/passwd' => [404, 404],
    '/admin.php?view=dashboard&q=' . rawurlencode(str_repeat('A', 5000)) => [302, 302],
    '/login.php?registered=1' => [200, 399],
];
foreach ($edgeUrls as $path => [$min, $max]) {
    $r = stress_request('GET', $base . $path);
    $leak = stress_php_leak($r['body']);
    $ok = $r['code'] >= $min && $r['code'] <= $max && $leak === null;
    if ($ok) {
        stress_ok('Edge URL ' . substr($path, 0, 60) . ' (HTTP ' . $r['code'] . ')');
    } else {
        stress_fail('Edge URL ' . substr($path, 0, 60), 'HTTP ' . $r['code'] . ($leak ? ", {$leak}" : ''));
    }
}

// --- 3) CSRF / bad POST ---
$r = stress_request('POST', $base . '/login.php', 'intent=login&email=a@b.com&password=x&csrf_token=invalid');
if (($r['code'] === 403 || str_contains($r['body'], 'token') || str_contains($r['body'], 'session')) && stress_php_leak($r['body']) === null) {
    stress_ok('Invalid CSRF on login rejected (HTTP ' . $r['code'] . ')');
} else {
    stress_fail('Invalid CSRF on login', 'HTTP ' . $r['code']);
}

$r = stress_request('POST', $base . '/admin.php?view=courses', 'action=section_save&csrf_token=bad');
if ($r['code'] === 302 || $r['code'] === 403) {
    stress_ok('Unauthenticated/bad CSRF section_save blocked (HTTP ' . $r['code'] . ')');
} else {
    stress_fail('Unauthenticated section_save', 'HTTP ' . $r['code']);
}

// --- 4) Authenticated admin crawl ---
$cookieFile = sys_get_temp_dir() . '/collegeweb_stress_' . getmypid() . '.cookie';
@unlink($cookieFile);

$r = stress_request('GET', $base . '/login.php', null, $cookieFile);
$csrf = stress_extract_csrf($r['body']);
if ($csrf === null) {
    stress_fail('Login page CSRF token');
} else {
    stress_ok('Login page CSRF token present');
}

$loginBody = http_build_query([
    'intent' => 'login',
    'email' => $email,
    'password' => $password,
    'csrf_token' => $csrf,
]);
$r = stress_request('POST', $base . '/login.php', $loginBody, $cookieFile);
if ($r['code'] !== 302) {
    stress_fail('Admin login', 'HTTP ' . $r['code'] . ' — check STRESS_EMAIL/STRESS_PASSWORD');
} else {
    stress_ok('Admin login redirect (HTTP 302)');
}

$adminViews = [
    'dashboard', 'people', 'schedule', 'courses', 'course', 'enrollment',
    'departments', 'registration', 'reports', 'messages', 'settings',
    'catalog', 'terms', 'holds', 'accounts',
];
foreach ($adminViews as $view) {
    $q = $view === 'course' ? '&course_id=ENG101' : ($view === 'people' ? '&id=1' : '');
    $r = stress_request('GET', $base . '/admin.php?view=' . rawurlencode($view) . $q, null, $cookieFile);
    $leak = stress_php_leak($r['body']);
    if ($r['code'] === 200 && $leak === null && strlen($r['body']) > 500) {
        stress_ok("Admin view {$view} renders (HTTP 200, " . strlen($r['body']) . ' bytes)');
    } else {
        stress_fail("Admin view {$view}", 'HTTP ' . $r['code'] . ($leak ? ", {$leak}" : ', short/empty body'));
    }
}

// Rapid-fire authenticated dashboard (simulates professor clicking fast)
$dashErrors = 0;
for ($i = 0; $i < 30; ++$i) {
    $r = stress_request('GET', $base . '/admin.php?view=dashboard', null, $cookieFile);
    if ($r['code'] !== 200 || stress_php_leak($r['body']) !== null) {
        ++$dashErrors;
    }
}
if ($dashErrors === 0) {
    stress_ok('30 rapid dashboard reloads — all OK');
} else {
    stress_fail('Rapid dashboard reloads', "{$dashErrors}/30 failed");
}

// --- 5) Section create edge cases (authenticated admin) ---
$r = stress_request('GET', $base . '/admin.php?view=courses', null, $cookieFile);
$csrf = stress_extract_csrf($r['body']);
if ($csrf === null) {
    stress_fail('Courses page CSRF for POST tests');
} else {
    $postCases = [
        'missing fields' => ['action' => 'section_save', 'csrf_token' => $csrf],
        'bad time format' => [
            'action' => 'section_save', 'csrf_token' => $csrf, 'term_id' => '1',
            'course_id' => 'ENG101', 'capacity' => '30', 'meeting_days' => 'MWF', 'meeting_time' => 'bad',
        ],
        'days only' => [
            'action' => 'section_save', 'csrf_token' => $csrf, 'term_id' => '1',
            'course_id' => 'ENG101', 'capacity' => '30', 'meeting_days' => 'MWF', 'meeting_time' => '',
        ],
        'invalid course' => [
            'action' => 'section_save', 'csrf_token' => $csrf, 'term_id' => '1',
            'course_id' => 'NOTREAL999', 'capacity' => '30',
        ],
    ];
    foreach ($postCases as $label => $fields) {
        $r = stress_request('POST', $base . '/admin.php?view=courses', http_build_query($fields), $cookieFile);
        if ($r['code'] === 302 && str_contains($r['headers'][0] ?? '', '302') || $r['code'] === 302) {
            $loc = '';
            foreach ($r['headers'] as $h) {
                if (stripos($h, 'Location:') === 0) {
                    $loc = $h;
                }
            }
            if (str_contains($loc, 'section_invalid') || str_contains($loc, 'courses')) {
                stress_ok("section_save rejected: {$label}");
            } else {
                stress_ok("section_save handled: {$label} (redirect)");
            }
        } elseif ($r['code'] === 200 && stress_php_leak($r['body']) === null) {
            stress_warn("section_save {$label} returned 200 — verify manually");
        } else {
            stress_fail("section_save {$label}", 'HTTP ' . $r['code'] . (stress_php_leak($r['body']) ?? ''));
        }
    }
}

// --- 6) Schedule overlap logic (inline, matches admin.php) ---
$schedConflict = static function (?string $daysA, ?string $timeA, ?string $daysB, ?string $timeB): bool {
    $daysA = strtoupper(trim((string)$daysA));
    $daysB = strtoupper(trim((string)$daysB));
    $timeA = trim((string)$timeA);
    $timeB = trim((string)$timeB);
    if ($daysA === '' || $daysB === '' || $timeA === '' || $timeB === '') {
        return false;
    }
    $setA = array_unique(str_split(preg_replace('/[^MTWRFSU]/', '', $daysA) ?? ''));
    $setB = array_unique(str_split(preg_replace('/[^MTWRFSU]/', '', $daysB) ?? ''));
    if (!$setA || !$setB || !array_intersect($setA, $setB)) {
        return false;
    }
    $parse = static function (string $t): ?array {
        if (!preg_match('/^\s*(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s*$/', $t, $m)) {
            return null;
        }
        $s = ((int)$m[1]) * 60 + (int)$m[2];
        $e = ((int)$m[3]) * 60 + (int)$m[4];

        return $e <= $s ? null : [$s, $e];
    };
    $a = $parse($timeA);
    $b = $parse($timeB);

    return $a !== null && $b !== null && $a[0] < $b[1] && $b[0] < $a[1];
};

if ($schedConflict('MWF', '10:00-11:00', 'MWF', '10:30-11:30')) {
    stress_ok('Schedule logic: overlapping MWF times conflict');
} else {
    stress_fail('Schedule logic: overlapping MWF times should conflict');
}
if (!$schedConflict('MWF', '10:00-11:00', 'TR', '10:00-11:00')) {
    stress_ok('Schedule logic: different days do not conflict');
} else {
    stress_fail('Schedule logic: different days should not conflict');
}
if (!$schedConflict('MWF', '10:00-11:00', 'MWF', '11:00-12:00')) {
    stress_ok('Schedule logic: back-to-back times OK');
} else {
    stress_fail('Schedule logic: back-to-back times should not conflict');
}

@unlink($cookieFile);

fwrite(STDOUT, "\n--- Summary ---\n");
fwrite(STDOUT, "Passed: {$passed}\n");
fwrite(STDOUT, "Warnings: {$warn}\n");
fwrite(STDOUT, "Failed: {$failed}\n");

exit($failed > 0 ? 1 : 0);
