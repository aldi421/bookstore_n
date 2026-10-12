<?php
require __DIR__.'/../config/koneksi.php';require_admin();
$messages=$db->query('SELECT m.id_pesan AS id,m.id_user AS user_id,m.judul_pesan AS subject,m.isi_pesan AS message,m.balasan_admin AS reply,m.tanggal AS created_at,m.status AS status,u.nama AS customer_name,u.email AS sender_email FROM pesan m LEFT JOIN users u ON u.id_user=m.id_user ORDER BY m.id_pesan DESC LIMIT 300')->fetchAll();
$adminTitle='Pesan Pelanggan';require __DIR__.'/../template/sidebar.php';
?>
<div class="panel-heading"><div><h2>Kotak Masuk</h2><p class="muted">Pesan pengunjung dan pelanggan.</p></div></div>
<?php if(!$messages): ?><div class="admin-panel admin-empty">Belum ada pesan.</div><?php endif; ?>
<?php foreach($messages as $m): ?><div class="admin-panel" style="margin-bottom:1rem"><span class="eyebrow"><?= e(!$m['reply']?'BELUM DIBALAS':'SUDAH DIBALAS') ?> — <?= e($m['created_at']) ?></span><h3><?= e($m['subject']) ?></h3><p class="muted">Dari: <?= e($m['customer_name']) ?> · <?= e($m['sender_email']) ?></p><p><?= nl2br(e($m['message'])) ?></p><?php if($m['reply']!==null): ?><p><strong>Balasan:</strong> <?= nl2br(e($m['reply'])) ?></p><?php endif; ?><a class="btn btn-outline btn-sm" href="<?= path('admin/balas_pesan.php?id='.(int)$m['id']) ?>"><?= $m['reply']!==null?'Perbarui Balasan':'Balas Pesan' ?></a></div><?php endforeach; ?>
<?php require __DIR__.'/../template/admin_footer.php'; ?>
