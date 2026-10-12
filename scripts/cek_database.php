<?php
/** CLI only: verify original database columns without changing any data. */
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Hanya untuk terminal.');}
$configFile=__DIR__.'/../config.php';
$config=require is_file($configFile)?$configFile:__DIR__.'/../config.example.php';
require __DIR__.'/../app/database.php';
try{$db=velora_db($config['db']);}catch(Throwable $e){fwrite(STDERR,"Koneksi database gagal. Periksa config.php dan Laragon.\n");exit(1);}
$req=[
'buku'=>['id_buku','id_kategori','judul_buku','penulis','harga','stok','gambar','deskripsi','penerbit','tahun'],
'kategori'=>['id_kategori','nama_kategori'],
'users'=>['id_user','nama','email','password','alamat','role'],
'keranjang'=>['id_cart','id_user','id_buku','jumlah'],
'pesanan'=>['id_pesanan','id_user','tanggal','total','metode_pembayaran','status_pembayaran','status'],
'detail_pesanan'=>['id_detail','id_pesanan','id_buku','jumlah','harga'],
'pesan'=>['id_pesan','id_user','judul_pesan','isi_pesan','balasan_admin','tanggal','status']];
$errors=0;
foreach($req as $t=>$cols){
try{$res=$db->query('SHOW COLUMNS FROM `'.$t.'`')->fetchAll(PDO::FETCH_COLUMN);$missing=array_diff($cols,$res);$count=$db->query('SELECT COUNT(*) FROM `'.$t.'`')->fetchColumn();
if($missing){echo "[FAIL] $t kekurangan kolom: ".implode(', ',$missing)."\n";$errors++;}
else echo "[OK] $t ($count baris)\n";
}catch(Throwable $e){echo "[FAIL] Tabel $t tidak tersedia atau tidak dapat dibaca.\n";$errors++;}
}
echo $errors ? "ADA $errors MASALAH. Jangan lakukan impor yang menghapus data.\n" : "Database Indonesia cocok dengan VELORA.\n";
exit($errors?1:0);
