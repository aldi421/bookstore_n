<?php
require __DIR__.'/../config/koneksi.php'; require_admin();
$errors=[];
if(is_post()){
 verify_csrf();$name=input('name',100);$email=strtolower(input('email',100));$pass=(string)($_POST['password']??'');
 if(strlen($name)<2)$errors[]='Nama minimal dua karakter.';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Email tidak valid.';
 if(strlen($pass)<10)$errors[]='Password minimal sepuluh karakter.';
 if(!$errors){
  try{statement($db,"INSERT INTO users(nama,email,password,role) VALUES(:name,:email,:pw,'user')",['name'=>$name,'email'=>$email,'pw'=>password_hash($pass,PASSWORD_DEFAULT)]);notice('success','Pengguna baru ditambahkan.');go('admin/user.php');}
  catch(PDOException $e){$errors[]='Email sudah terdaftar atau pengguna tidak dapat disimpan.';}
 }
}
$adminTitle='Tambah Pengguna';require __DIR__.'/../template/sidebar.php';
?>
<div class="admin-panel"><h2>Buat Akun Pelanggan</h2><?php foreach($errors as $er): ?><div role="alert" class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?><form method="post" class="stack-form"><?= csrf_input() ?><label class="field"><span>Nama Lengkap</span><input name="name" minlength="2" maxlength="100" required value="<?= e($_POST['name']??'') ?>"></label><label class="field"><span>Email</span><input name="email" type="email" required maxlength="100" value="<?= e($_POST['email']??'') ?>"></label><label class="field"><span>Password Awal</span><input name="password" type="password" minlength="10" required></label><button class="btn btn-primary" type="submit">Simpan Akun</button></form></div>
<?php require __DIR__.'/../template/admin_footer.php'; ?>
