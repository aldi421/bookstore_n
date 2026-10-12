<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
if (!isset($argv[1])) { fwrite(STDERR, "Usage: VELORA_ADMIN_PASSWORD='strong-password' php scripts/create_admin.php admin@example.com\n"); exit(2); }
$email = trim(strtolower($argv[1]));
$password = getenv('VELORA_ADMIN_PASSWORD');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password) || strlen($password) < 10) {
    fwrite(STDERR, "Provide valid email and password at least 10 characters via VELORA_ADMIN_PASSWORD.\n"); exit(2);
}
$config = require __DIR__ . '/../config.php';
require __DIR__ . '/../app/database.php';
$db = velora_db($config['db']);
$stmt = $db->prepare("INSERT INTO users(nama,email,password,role) VALUES('Administrator',?,?, 'admin')");
try { $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]); echo "Admin account created.\n"; }
catch (PDOException $e) { fwrite(STDERR, "Account already exists or database unavailable.\n"); exit(1); }
