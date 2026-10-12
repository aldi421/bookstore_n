<?php
require __DIR__.'/../config/koneksi.php';require_admin();
$summary=$db->query("SELECT status,COUNT(*) AS total,COALESCE(SUM(total),0) AS amount FROM pesanan GROUP BY status")->fetchAll();
$adminTitle='Rekap Nilai Pesanan';require __DIR__.'/../template/sidebar.php';
?>
<div class="admin-panel"><h2>Rekap Pesanan Berdasarkan Status</h2><p class="muted">Nilai pesanan bukan pendapatan terverifikasi. Pembayaran belum terintegrasi, sehingga angka di bawah tidak boleh diperlakukan sebagai pemasukan nyata.</p><div class="table-scroll"><table><thead><tr><th>Status</th><th>Jumlah Pesanan</th><th>Nilai Pesanan</th></tr></thead><tbody><?php foreach($summary as $s): ?><tr><td><?= e(ucfirst($s['status'])) ?></td><td><?= (int)$s['total'] ?></td><td><?= rupiah((int)$s['amount']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__.'/../template/admin_footer.php'; ?>
