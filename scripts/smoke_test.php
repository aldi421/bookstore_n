<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
// Lightweight smoke checks that do not require a running database.
$config = ['base_url' => ''];
$_SERVER['SCRIPT_NAME'] = '/bookstore_n/admin/index.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require __DIR__ . '/../app/functions.php';
$tests = [
    'HTML escaping prevents XSS' => e('<script>x</script>') === '&lt;script&gt;x&lt;/script&gt;',
    'Escape tolerates malformed input' => e(['bad' => 1]) === '',
    'Indonesian money formatting' => rupiah(125000) === 'Rp 125.000',
    'Nested folder URL routing' => path('katalog.php') === '/bookstore_n/katalog.php',
    'CSRF token generated' => strlen(csrf_token()) === 64,
    'Default cart is empty' => cart_count() === 0,
    'Catalog filters parameterized' => (function(){[$sql,$vals] = book_query_parts(['q' => "'% OR 1=1 --", 'category' => 2]); return str_contains($sql, ':q1') && str_contains($sql, ':category') && !str_contains($sql, 'OR 1=1');})(),
    'Fallback cover local' => cover_url(null) === '/bookstore_n/images/fallback-cover.svg',
    'Legacy cover filename with spaces' => cover_url('cover buku dilan.jpg') === '/bookstore_n/images/buku/cover%20buku%20dilan.jpg',
    'Unsafe cover path rejected' => cover_url('../../config.php') === '/bookstore_n/images/fallback-cover.svg',
    'Legacy plaintext password verification' => legacy_password_matches('test-old-password','test-old-password'),
    'Hash-based account password verification' => legacy_password_matches('test-new-password', password_hash('test-new-password',PASSWORD_DEFAULT)),
];
$errors = 0;
foreach ($tests as $test => $ok) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $test . PHP_EOL;
    if (!$ok) $errors++;
}
exit($errors ? 1 : 0);
