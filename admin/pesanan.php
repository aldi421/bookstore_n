<?php
require __DIR__ . '/../config/koneksi.php'; require_admin();
$statusNames = ['Menunggu Pembayaran'=>'Menunggu Pembayaran','Diproses'=>'Diproses','Dikirim'=>'Dikirim','Selesai'=>'Selesai','Dibatalkan'=>'Dibatalkan'];
$transitions = ['Menunggu Pembayaran'=>['Diproses','Dibatalkan'],'Diproses'=>['Dikirim','Selesai','Dibatalkan'],'Dikirim'=>['Selesai'],'Selesai'=>[],'Dibatalkan'=>[]];
if (is_post()) {
    verify_csrf();
    $id = max(0,(int)($_POST['order_id'] ?? 0));
    $next = (string)($_POST['status'] ?? '');
    try {
        $db->beginTransaction();
        $order = statement($db,'SELECT id_pesanan AS id,status FROM pesanan WHERE id_pesanan=:id FOR UPDATE',['id'=>$id])->fetch();
        if (!$order || !in_array($next,$transitions[$order['status']] ?? [],true)) throw new RuntimeException('Perubahan status tidak diizinkan.');
        if ($next === 'Dibatalkan') {
            $lines = statement($db,'SELECT id_buku AS book_id,jumlah AS quantity FROM detail_pesanan WHERE id_pesanan=:id',['id'=>$id])->fetchAll();
            foreach ($lines as $line) statement($db,'UPDATE buku SET stok=stok+:qty WHERE id_buku=:id',['qty'=>(int)$line['quantity'],'id'=>(int)$line['book_id']]);
        }
        statement($db,'UPDATE pesanan SET status=:status WHERE id_pesanan=:id',['status'=>$next,'id'=>$id]);
        $db->commit(); notice('success','Status pesanan #' . $id . ' berhasil diperbarui.');
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        notice('error', $e instanceof RuntimeException ? $e->getMessage() : 'Gagal memperbarui pesanan.');
        if (!($e instanceof RuntimeException)) error_log('Admin order: ' . $e->getMessage());
    }
    go('admin/pesanan.php');
}
$adminTitle = 'Kelola Pesanan';
$orders = $db->query('SELECT p.id_pesanan AS id,p.id_user AS user_id,p.tanggal AS created_at,p.total AS total_amount,p.status AS status,p.metode_pembayaran AS payment_method,p.status_pembayaran AS payment_status,u.nama AS user_name,u.nama AS recipient_name,u.alamat AS address_current,u.email FROM pesanan p JOIN users u ON u.id_user=p.id_user ORDER BY p.id_pesanan DESC LIMIT 300')->fetchAll();
$orderItems = [];
if ($orders) {
    $ids = array_column($orders,'id');
    $placeholders = implode(',',array_fill(0,count($ids),'?'));
    $stmt = $db->prepare("SELECT d.id_detail AS id,d.id_pesanan AS order_id,d.id_buku AS book_id,b.judul_buku AS book_title,d.jumlah AS quantity,d.harga AS unit_price, CASE WHEN d.harga IS NOT NULL THEN d.harga*d.jumlah ELSE NULL END AS subtotal FROM detail_pesanan d LEFT JOIN buku b ON b.id_buku=d.id_buku WHERE d.id_pesanan IN ($placeholders) ORDER BY d.id_detail");
    $stmt->execute($ids);
    foreach ($stmt as $item) $orderItems[$item['order_id']][] = $item;
}
require __DIR__ . '/../template/sidebar.php';
?>
<div class="panel-heading admin-list-heading"><div><h2>Manajemen Pesanan</h2><p class="muted">Pembatalan pesanan sebelum pengiriman otomatis mengembalikan stok.</p></div></div>
<?php if (!$orders): ?><div class="admin-panel admin-empty">Belum ada pesanan pelanggan.</div><?php else: ?><div class="admin-order-list"><?php foreach ($orders as $order): ?><article class="admin-panel admin-order-card"><div class="admin-order-heading"><div><span class="eyebrow">PESANAN #<?= (int)$order['id'] ?></span><a class="subtle-link" href="<?= path('admin/detail_pesanan.php?id='.(int)$order['id']) ?>">Lihat detail ↗</a><h3><?= e($order['user_name']) ?> <span class="muted admin-order-email">(<?= e($order['email']) ?>)</span></h3><p class="muted"><?= date('d/m/Y', strtotime($order['created_at'])) ?></p></div><span class="status status-<?= e(strtolower(str_replace(" ", "-", $order['status']))) ?>"><?= $statusNames[$order['status']] ?? e($order['status']) ?></span></div><div class="admin-order-details"><div><strong>Tujuan Pengiriman</strong><p><?= nl2br(e($order['recipient_name'] . ' — ' . ($order['address_current'] ?? 'Alamat belum ada'))) ?> <small class="muted">(alamat akun saat ini)</small></p><p><em>Pembayaran:</em> <?= e($order['payment_method'] ?? '—') ?> — <?= e($order['payment_status'] ?? '—') ?></p></div><div><strong>Isi Pesanan</strong><?php foreach ($orderItems[$order['id']] ?? [] as $line): ?><p><?= e($line['book_title']) ?> × <?= (int)$line['quantity'] ?> — <?= ($line['subtotal']===null ? 'Harga lama tidak tercatat' : rupiah((int)$line['subtotal'])) ?></p><?php endforeach; ?></div></div><div class="admin-order-footer"><strong>Total: <?= rupiah((int)$order['total_amount']) ?></strong><?php if ($transitions[$order['status']] ?? []): ?><form method="post" class="admin-status-form"><?= csrf_input() ?><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><label for="status-<?= (int)$order['id'] ?>">Ubah status</label><select id="status-<?= (int)$order['id'] ?>" name="status" required><option value="">Pilih...</option><?php foreach ($transitions[$order['status']] as $allowed): ?><option value="<?= e($allowed) ?>"><?= $statusNames[$allowed] ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary" type="submit">Perbarui</button></form><?php endif; ?></div></article><?php endforeach; ?></div><?php if (count($orders) === 300): ?><p class="muted admin-limit">Menampilkan 300 pesanan terbaru.</p><?php endif; ?><?php endif; ?>
<?php require __DIR__ . '/../template/admin_footer.php'; ?>
