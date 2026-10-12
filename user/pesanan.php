<?php
require __DIR__ . '/../config/koneksi.php';
require_login();
$pageTitle = 'Pesanan Saya';
$orders = statement($db, 'SELECT p.id_pesanan AS id,p.id_user AS user_id,p.tanggal AS created_at,p.total AS total_amount,p.status AS status,p.metode_pembayaran AS payment_method,p.status_pembayaran AS payment_status,u.nama AS recipient_name FROM pesanan p LEFT JOIN users u ON u.id_user=p.id_user WHERE p.id_user=:user_id ORDER BY p.tanggal DESC,p.id_pesanan DESC', ['user_id' => (int)current_user()['id']])->fetchAll();
$orderItems = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $p = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT d.id_detail AS id,d.id_pesanan AS order_id,d.id_buku AS book_id,b.judul_buku AS book_title,d.jumlah AS quantity,d.harga AS unit_price, CASE WHEN d.harga IS NOT NULL THEN d.harga*d.jumlah ELSE NULL END AS subtotal FROM detail_pesanan d LEFT JOIN buku b ON b.id_buku=d.id_buku WHERE d.id_pesanan IN ($p) ORDER BY d.id_detail");
    $stmt->execute($ids);
    foreach ($stmt as $line) $orderItems[$line['order_id']][] = $line;
}
$labels = ['Menunggu Pembayaran'=>'Menunggu Pembayaran', 'Diproses'=>'Diproses','Dikirim'=>'Dikirim','Selesai'=>'Selesai','Dibatalkan'=>'Dibatalkan'];
require __DIR__ . '/../template/navbar.php';
?>
<div class="page-intro"><div class="container"><span class="eyebrow">YOUR ORDER HISTORY</span><h1>Pesanan <em>Saya</em></h1><p>Hai, <?= e(current_user()['name']) ?>. Berikut riwayat pesananmu.</p></div></div>
<section class="section"><div class="container"><div class="account-toolbar"><h2>Daftar Pesanan</h2><a href="<?= path('katalog.php') ?>" class="btn btn-outline">Jelajahi Buku</a></div><?php if (!$orders): ?><div class="empty-state"><h2>Belum ada pesanan</h2><p>Setelah checkout, pesananmu akan tampil di sini.</p><a class="btn btn-primary" href="<?= path('katalog.php') ?>">Temukan Buku</a></div><?php else: ?><div class="orders-list"><?php foreach ($orders as $order): ?><article class="order-card"><div class="order-card-top"><div><span class="eyebrow">PESANAN #<?= (int)$order['id'] ?></span><a class="subtle-link" href="<?= path('user/detail_pesanan.php?id=' . (int)$order['id']) ?>">Lihat Detail ↗</a><h3><?= date('d M Y', strtotime($order['created_at'])) ?></h3></div><span class="status status-<?= e(strtolower(str_replace(" ", "-", $order['status']))) ?>"><?= ($labels[$order['status']] ?? e($order['status'])) ?></span></div><div class="order-lines"><?php foreach ($orderItems[$order['id']] ?? [] as $item): ?><div><span><?= e($item['book_title']) ?> × <?= (int)$item['quantity'] ?></span><strong><?= ($item['subtotal']===null ? '—' : rupiah((int)$item['subtotal'])) ?></strong></div><?php endforeach; ?></div><div class="order-card-bottom"><span>Pemesan: <?= e($order['recipient_name']) ?></span><strong><?= rupiah((int)$order['total_amount']) ?></strong></div></article><?php endforeach; ?></div><?php endif; ?></div></section>
<?php require __DIR__ . '/../template/footer.php'; ?>
