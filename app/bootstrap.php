<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

const ROOT_DIR = __DIR__ . '/..';
$configFile = ROOT_DIR . '/config.php';
$config = require (is_file($configFile) ? $configFile : ROOT_DIR . '/config.example.php');
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/database.php';

try {
    $db = velora_db($config['db']);
    $check = $db->query("SHOW TABLES LIKE 'kategori'");
    if (!$check->fetchColumn()) throw new RuntimeException('Tabel kategori belum tersedia di database bookstore_n.');
} catch (Throwable $e) {
    error_log('Velora database connection failed: ' . $e->getMessage());
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Velora Books — Konfigurasi database</title><style>body{background:#F8F6F1;color:#1E2A44;font:16px/1.7 system-ui;display:grid;place-items:center;min-height:90vh;padding:2rem}main{max-width:600px;background:white;padding:3rem;border-radius:18px;box-shadow:0 16px 40px #1e2a4414}h1{font-family:Georgia,serif}code{background:#f0ede6;padding:.2rem .4rem;border-radius:4px}</style><main><h1>Velora Books belum terhubung ke database</h1><p>Website sudah terpasang, tetapi koneksi MySQL belum tersedia. Pastikan database <code>bookstore_n</code> memiliki tabel Indonesia <code>buku</code>, <code>kategori</code>, <code>users</code>, <code>pesanan</code>, <code>detail_pesanan</code>, <code>pesan</code> dan <code>keranjang</code>. Jangan mengimpor skema Inggris. Lihat README untuk petunjuk Laragon.</p><p>Detail kesalahan dicatat di server, bukan ditampilkan kepada pengunjung.</p></main></html>';
    exit;
}
