<?php
require __DIR__.'/../config/koneksi.php';
require_login(); $errors=[]; $me=current_user();
if (is_post()) {
 verify_csrf(); $subject=input('subject',100);$message=input('message',5000);
 if(strlen($subject)<3 || strlen($message)<10) $errors[]='Isi subjek dan pesan minimal 3 dan 10 karakter.';
 if(!$errors) {
  statement($db,'INSERT INTO pesan(id_user,judul_pesan,isi_pesan,tanggal,status) VALUES(:id,:subject,:message,CURDATE(),:status)',[
   'id'=>$me?(int)$me['id']:null,'subject'=>$subject,'message'=>$message,'status'=>'Belum Dibalas']);
  notice('success','Pesan diterima. Tim admin dapat membaca pesan ini.');go('user/contact.php');
 }
}
$pageTitle='Kontak'; require __DIR__.'/../template/navbar.php';
?>
<div class="page-intro"><div class="container"><span class="eyebrow">WE ARE HERE TO HELP</span><h1>Hubungi <em>Kami.</em></h1><p>Punya pertanyaan tentang koleksi atau pesanan? Tulis pesan di bawah ini.</p></div></div>
<section class="section"><div class="container"><div class="admin-panel" style="max-width:780px;margin:auto"><h2>Kirim Pesan ke Velora</h2><?php foreach($errors as $er): ?><div class="alert alert-error" role="alert"><?= e($er) ?></div><?php endforeach; ?><form class="stack-form" method="post"><?= csrf_input() ?><p class="muted">Pesan ini akan dikirim menggunakan akun <?= e($me['name']) ?> (<?= e($me['email']) ?>).</p><label class="field"><span>Subjek</span><input name="subject" minlength="3" required maxlength="100" value="<?= e($_POST['subject']??'') ?>"></label><label class="field"><span>Pesan</span><textarea name="message" rows="7" minlength="10" maxlength="5000" required><?= e($_POST['message']??'') ?></textarea></label><button class="btn btn-primary" type="submit">Kirim Pesan ↗</button></form><?php if($me): ?><a class="back-link" href="<?= path('user/pesan_saya.php') ?>">Lihat pesan saya →</a><?php endif; ?></div></div></section>
<?php require __DIR__.'/../template/footer.php'; ?>
