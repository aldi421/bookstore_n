<?php
require __DIR__ . '/../config/koneksi.php'; require_admin();
$adminTitle = 'Dashboard';
$stats = [
  'books' => (int)$db->query('SELECT COUNT(*) FROM buku')->fetchColumn(),
  'categories' => (int)$db->query('SELECT COUNT(*) FROM kategori')->fetchColumn(),
  'orders' => (int)$db->query('SELECT COUNT(*) FROM pesanan')->fetchColumn(),
  'users' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
];
$latest = $db->query('SELECT o.id_pesanan AS id,o.tanggal AS created_at,o.total AS total_amount,o.status,u.nama AS name FROM pesanan o JOIN users u ON u.id_user=o.id_user ORDER BY o.id_pesanan DESC LIMIT 8')->fetchAll();
$statusNames = ['Menunggu Pembayaran'=>'Menunggu Pembayaran','Diproses'=>'Diproses','Dikirim'=>'Dikirim','Selesai'=>'Selesai','Dibatalkan'=>'Dibatalkan'];
require __DIR__ . '/../template/sidebar.php';
?>
<div class="admin-welcome"><div><span class="eyebrow">OVERVIEW</span><h2>Welcome back to <em>Velora.</em></h2><p>Pantau koleksi dan aktivitas toko buku dari satu tempat.</p></div><a href="<?= path('admin/tambah_buku.php') ?>" class="btn btn-accent">+ Tambah Buku</a></div><div class="stat-grid"><a class="stat-card" href="<?= path('admin/buku.php') ?>"><span>Total Buku Aktif</span><strong><?= $stats['books'] ?></strong><small>Kelola koleksi ↗</small></a><a class="stat-card" href="<?= path('admin/kategori.php') ?>"><span>Kategori</span><strong><?= $stats['categories'] ?></strong><small>Kelola kategori ↗</small></a><a class="stat-card" href="<?= path('admin/pesanan.php') ?>"><span>Pesanan</span><strong><?= $stats['orders'] ?></strong><small>Kelola pesanan ↗</small></a><a class="stat-card" href="<?= path('admin/user.php') ?>"><span>Pelanggan</span><strong><?= $stats['users'] ?></strong><small>Lihat pelanggan ↗</small></a></div><div class="admin-panel"><div class="panel-heading"><div><span class="eyebrow">TRANSAKSI</span><h2>Pesanan Terbaru</h2></div><a href="<?= path('admin/pesanan.php') ?>">Lihat Semua →</a></div><?php if (!$latest): ?><div class="admin-empty">Belum ada pesanan masuk. Pesanan pelanggan akan tampil otomatis di sini.</div><?php else: ?><div class="table-scroll"><table><thead><tr><th>No. Pesanan</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th><th>Status</th></tr></thead><tbody><?php foreach ($latest as $o): ?><tr><td>#<?= (int)$o['id'] ?></td><td><?= e($o['name']) ?></td><td><?= date('d/m/Y', strtotime($o['created_at'])) ?></td><td><?= rupiah((int)$o['total_amount']) ?></td><td><span class="status status-<?= e(strtolower(str_replace(" ", "-", $o['status']))) ?>"><?= ($statusNames[$o['status']] ?? $o['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
<?php require __DIR__ . '/../template/admin_footer.php'; ?>
