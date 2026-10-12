<?php
require __DIR__.'/../config/koneksi.php';require_admin();
if(!is_post()) go('admin/buku.php');verify_csrf();
$id=(int)($_POST['id']??0);
if($id>0){$num=(int)statement($db,'SELECT COUNT(*) FROM detail_pesanan WHERE id_buku=:id',['id'=>$id])->fetchColumn();if($num)notice('error','Buku memiliki riwayat transaksi dan tidak dapat dihapus.');else{try{statement($db,'DELETE FROM buku WHERE id_buku=:id',['id'=>$id]);notice('success','Buku dihapus.');}catch(PDOException $e){notice('error','Buku masih direferensikan data lain.');}}}
go('admin/buku.php');
