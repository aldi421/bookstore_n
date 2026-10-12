<?php
require __DIR__.'/../config/koneksi.php'; require_login();
$errors=[];
$userId=(int)current_user()['id'];
if (is_post()) {
 verify_csrf(); $name=input('name',100); $current=(string)($_POST['current_password']??'');$new=(string)($_POST['new_password']??'');
 if (strlen($name)<2) $errors[]='Nama minimal dua karakter.';
 if ($new!=='' && strlen($new)<10) $errors[]='Password baru minimal 10 karakter.';
 if ($new!=='' && !legacy_password_matches($current,(string)statement($db,'SELECT password FROM users WHERE id_user=:id',['id'=>$userId])->fetchColumn())) $errors[]='Password lama tidak sesuai.';
 if (!$errors){
  statement($db,'UPDATE users SET nama=:name WHERE id_user=:id',['name'=>$name,'id'=>$userId]);
  if($new!=='') statement($db,'UPDATE users SET password=:pw WHERE id_user=:id',['pw'=>password_hash($new,PASSWORD_DEFAULT),'id'=>$userId]);
  $_SESSION['user']['name']=$name; notice('success','Profil berhasil diperbarui.'); go(current_user()['role']==='admin'?'admin/profil.php':'user/profil.php');
 }
}
$pageTitle='Profil Saya';require __DIR__.'/../template/navbar.php';
?>
<div class="page-intro"><div class="container"><span class="eyebrow">AKUN VELORA</span><h1>Profil <em>Saya</em></h1><p>Perbarui nama atau password dengan aman.</p></div></div>
<section class="section"><div class="container"><div class="admin-panel" style="max-width:720px;margin:auto"><h2>Pengaturan Akun</h2><p class="muted">Email: <?= e(current_user()['email']) ?></p><?php foreach($errors as $er): ?><div class="alert alert-error" role="alert"><?= e($er) ?></div><?php endforeach; ?><form method="post" class="stack-form"><?= csrf_input() ?><label class="field"><span>Nama lengkap</span><input name="name" required minlength="2" maxlength="100" value="<?= e($_POST['name']??current_user()['name']) ?>"></label><label class="field"><span>Password saat ini (jika mengganti)</span><input type="password" name="current_password" autocomplete="current-password"></label><label class="field"><span>Password baru (opsional)</span><input type="password" name="new_password" autocomplete="new-password" minlength="10"></label><button type="submit" class="btn btn-primary">Simpan Perubahan</button></form></div></div></section>
<?php require __DIR__.'/../template/footer.php'; ?>
