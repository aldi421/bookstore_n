<?php
require __DIR__ . '/../config/koneksi.php'; require_admin();
$adminTitle = 'Daftar Pengguna';
$users = $db->query("SELECT u.id_user AS id,u.nama AS name,u.email,COUNT(o.id_pesanan) AS orders_total FROM users u LEFT JOIN pesanan o ON o.id_user=u.id_user WHERE u.role='user' GROUP BY u.id_user,u.nama,u.email ORDER BY u.id_user DESC LIMIT 500")->fetchAll();
require __DIR__ . '/../template/sidebar.php';
?>
<div class="panel-heading admin-list-heading"><div><h2>Akun Pelanggan</h2><p class="muted">Daftar pelanggan yang telah mendaftar ke Velora Books.</p></div><a class="btn btn-primary" href="<?= path('admin/tambah_user.php') ?>">+ Tambah Pengguna</a></div><div class="admin-panel"><div class="table-scroll"><table><thead><tr><th>Nama</th><th>Email</th><th>Terdaftar</th><th>Jumlah Pesanan</th><th>Aksi</th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><?= e($user['name']) ?></td><td><?= e($user['email']) ?></td><td><?= '—' ?></td><td><?= (int)$user['orders_total'] ?></td><td><?php if ((int)$user['orders_total']===0): ?><form method="post" action="<?= path('admin/hapus_user.php') ?>"><?= csrf_input() ?><input type="hidden" name="id" value="<?= (int)$user['id'] ?>"><button class="text-button" type="submit">Hapus</button></form><?php else: ?><span class="muted">Ada pesanan</span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php if (!$users): ?><div class="admin-empty">Belum ada pelanggan terdaftar.</div><?php endif; ?></div>
<?php require __DIR__ . '/../template/admin_footer.php'; ?>
