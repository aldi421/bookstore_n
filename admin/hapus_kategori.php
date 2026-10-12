<?php
require __DIR__.'/../config/koneksi.php'; require_admin();
if(!is_post())go('admin/kategori.php');verify_csrf();
$id=(int)($_POST['id']??0);
if($id>0){
 $num=(int)statement($db,'SELECT COUNT(*) FROM buku WHERE id_kategori=:id',['id'=>$id])->fetchColumn();
 if($num>0)notice('error','Kategori masih digunakan oleh buku; tidak dapat dihapus.');
 else {statement($db,'DELETE FROM kategori WHERE id_kategori=:id',['id'=>$id]);notice('success','Kategori berhasil dihapus.');}
}
go('admin/kategori.php');
