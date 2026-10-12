<?php
require __DIR__.'/../config/koneksi.php';require_admin();
$id=max(0,page_int('id'));
$message=statement($db,'SELECT m.id_pesan AS id,m.id_user AS user_id,m.judul_pesan AS subject,m.isi_pesan AS message,m.balasan_admin AS reply,m.tanggal AS created_at,m.status AS status,u.nama AS sender_name,u.email AS sender_email FROM pesan m LEFT JOIN users u ON u.id_user=m.id_user WHERE m.id_pesan=:id',['id'=>$id])->fetch();
if(!$message){http_response_code(404);exit('Pesan tidak ditemukan.');}
$error='';
if(is_post()){
 verify_csrf();$reply=input('reply',5000);
 if(strlen($reply)<2) $error='Isi balasan minimal dua karakter.';
 else{statement($db,"UPDATE pesan SET balasan_admin=:reply,status='Dibalas' WHERE id_pesan=:id",['reply'=>$reply,'id'=>$id]);notice('success','Balasan disimpan. Balasan dapat dilihat pelanggan yang login.');go('admin/pesan.php');}
}
$adminTitle='Balas Pesan';require __DIR__.'/../template/sidebar.php';
?>
<div class="admin-panel"><h2><?= e($message['subject']) ?></h2><p>Pesan dari <?= e($message['sender_name']) ?> (<?= e($message['sender_email']) ?>)</p><p><?= nl2br(e($message['message'])) ?></p><?php if($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><?= csrf_input() ?><label class="field"><span>Balasan Admin</span><textarea name="reply" rows="6" maxlength="5000" required><?= e($_POST['reply']??($message['reply']??'')) ?></textarea></label><div class="form-end"><a class="btn btn-outline" href="<?= path('admin/pesan.php') ?>">Batal</a><button class="btn btn-primary" type="submit">Simpan Balasan</button></div></form></div>
<?php require __DIR__.'/../template/admin_footer.php'; ?>
