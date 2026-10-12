<?php
// Form status tetap diproses oleh admin/pesanan.php (CSRF dan transaksi database).
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Location: pesanan.php',true,302); exit; }
require __DIR__ . '/pesanan.php';
