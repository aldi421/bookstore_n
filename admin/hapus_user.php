<?php
require __DIR__.'/../config/koneksi.php';require_admin();
if(!is_post())go('admin/user.php');verify_csrf();
$id=(int)($_POST['id']??0);
if($id>0){
 $num=(int)statement($db,'SELECT COUNT(*) FROM pesanan WHERE id_user=:id',['id'=>$id])->fetchColumn();
 $role=statement($db,'SELECT role FROM users WHERE id_user=:id',['id'=>$id])->fetchColumn();
 if($num || $role!=='user')notice('error','Pengguna memiliki pesanan atau bukan pelanggan; tidak bisa dihapus.');
 else{try{statement($db,'DELETE FROM users WHERE id_user=:id',['id'=>$id]);notice('success','Pengguna dihapus.');}catch(PDOException $e){notice('error','Pengguna masih memiliki pesan atau data keranjang sehingga tidak dapat dihapus.');}}
}
go('admin/user.php');
