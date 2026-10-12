<?php
require __DIR__.'/../config/koneksi.php';require_login();
$messages=statement($db,'SELECT judul_pesan AS subject,isi_pesan AS message,balasan_admin AS reply,tanggal AS created_at,status FROM pesan WHERE id_user=:id ORDER BY id_pesan DESC LIMIT 100',['id'=>(int)current_user()['id']])->fetchAll();
$pageTitle='Pesan Saya'; require __DIR__.'/../template/navbar.php';
?>
<div class="page-intro"><div class="container"><span class="eyebrow">KONTAK VELORA</span><h1>Riwayat <em>Pesan.</em></h1><p>Pertanyaan yang kamu kirim beserta jawaban admin.</p></div></div>
<section class="section"><div class="container"><div class="panel-heading"><h2>Pesan Saya</h2><a class="btn btn-primary" href="<?= path('user/contact.php') ?>">+ Pesan Baru</a></div><?php if(!$messages): ?><div class="empty-state"><h3>Belum ada pesan</h3></div><?php endif; ?><?php foreach($messages as $m): ?><article class="admin-panel" style="margin-bottom:1rem"><span class="eyebrow"><?= e($m['created_at']) ?></span><h3><?= e($m['subject']) ?></h3><p><?= nl2br(e($m['message'])) ?></p><p><strong>Balasan admin:</strong> <?= $m['reply']!==null ? nl2br(e($m['reply'])) : 'Belum ada balasan' ?></p></article><?php endforeach; ?></div></section>
<?php require __DIR__.'/../template/footer.php'; ?>
