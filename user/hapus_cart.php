<?php
require __DIR__ . '/../config/koneksi.php';
if (!is_post()) { go('user/keranjang.php'); }
verify_csrf();
$id=(int)($_POST['book_id']??0);
if ($id>0) { $cart=session_cart(); unset($cart[$id]); $_SESSION['cart']=$cart; notice('success','Buku dihapus dari keranjang.'); }
go('user/keranjang.php');
