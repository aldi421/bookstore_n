<?php
require __DIR__ . '/config/koneksi.php';
if (!is_post()) { http_response_code(405); exit('Method tidak diizinkan.'); }
verify_csrf();
$_SESSION = [];
session_regenerate_id(true);
notice('success', 'Kamu sudah keluar dari akun.');
go('index.php');
