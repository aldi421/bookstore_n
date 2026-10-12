<?php
declare(strict_types=1);

function velora_db(array $settings): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $settings['host'], (int)($settings['port'] ?? 3306),
        $settings['name'], $settings['charset'] ?? 'utf8mb4');
    return new PDO($dsn, $settings['user'], $settings['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
