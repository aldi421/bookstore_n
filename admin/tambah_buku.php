<?php
// Form buku baru menggunakan implementasi Velora bersama.
if (isset($_GET['id'])) { http_response_code(400); exit('Gunakan menu Edit Buku untuk mengubah buku.'); }
require __DIR__ . '/_book_form.php';
