<?php
// Form edit hanya menerima ID positif; diproses oleh form bersama.
if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id']) || (int)$_GET['id'] < 1) { http_response_code(400); exit('ID buku tidak valid.'); }
require __DIR__ . '/_book_form.php';
