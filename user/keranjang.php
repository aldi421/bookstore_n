<?php
require __DIR__ . '/../config/koneksi.php';
if (is_post()) {
    verify_csrf();
    $action = isset($_POST['remove_id']) ? 'remove' : (string)($_POST['action'] ?? '');
    $id = max(0, (int)($_POST['remove_id'] ?? $_POST['book_id'] ?? 0));
    $cart = session_cart();
    if ($action === 'add' && $id > 0) {
        $book = statement($db, 'SELECT id_buku AS id,judul_buku AS title,stok AS stock FROM buku WHERE id_buku=:id', ['id' => $id])->fetch();
        $qty = min(99, max(1, (int)($_POST['quantity'] ?? 1)));
        if (!$book || (int)$book['stock'] < 1) notice('error', 'Buku tidak tersedia.');
        else {
            $wanted = ($cart[$id] ?? 0) + $qty;
            $cart[$id] = min($wanted, (int)$book['stock']);
            notice('success', $wanted > (int)$book['stock'] ? 'Jumlah dibatasi sesuai stok tersedia.' : 'Buku ditambahkan ke keranjang.');
        }
    } elseif ($action === 'remove') {
        unset($cart[$id]); notice('success', 'Buku dihapus dari keranjang.');
    } elseif ($action === 'update') {
        foreach (($_POST['quantities'] ?? []) as $bookId => $qty) {
            $bookId = (int)$bookId;
            if ($bookId < 1 || !isset($cart[$bookId])) continue;
            $qty = (int)$qty;
            if ($qty < 1) { unset($cart[$bookId]); continue; }
            $stock = (int)statement($db, 'SELECT stok FROM buku WHERE id_buku=:id', ['id' => $bookId])->fetchColumn();
            if ($stock < 1) unset($cart[$bookId]); else $cart[$bookId] = min(99, $qty, $stock);
        }
        notice('success', 'Keranjang diperbarui sesuai stok.');
    }
    $_SESSION['cart'] = $cart;
    go('user/keranjang.php');
}
$pageTitle = 'Keranjang';
$items = cart_products($db);
$total = array_sum(array_column($items, 'subtotal'));
require __DIR__ . '/../template/navbar.php';
?>
<div class="page-intro"><div class="container"><span class="eyebrow">YOUR READING LIST</span><h1>Keranjang <em>Belanja</em></h1><p>Periksa bacaan pilihanmu sebelum memesan.</p></div></div>
<section class="section"><div class="container"><?php if (!$items): ?><div class="empty-state"><span class="empty-symbol">◇</span><h2>Keranjangmu masih kosong</h2><p>Ayo temukan bacaan yang menarik dan tambahkan ke keranjang.</p><a class="btn btn-primary" href="<?= path('katalog.php') ?>">Jelajahi Koleksi</a></div><?php else: ?><div class="cart-layout"><div class="cart-list"><form action="<?= path('user/keranjang.php') ?>" method="post"><?= csrf_input() ?><input type="hidden" name="action" value="update"><?php foreach ($items as $item): ?><div class="cart-item"><a href="<?= path('user/detail_buku.php?id=' . (int)$item['id']) ?>"><img src="<?= cover_url($item['cover_image']) ?>" alt="Sampul <?= e($item['title']) ?>"></a><div><h3><a href="<?= path('user/detail_buku.php?id=' . (int)$item['id']) ?>"><?= e($item['title']) ?></a></h3><p class="muted"><?= e($item['author']) ?></p><p><?= rupiah((int)$item['price']) ?></p><label class="quantity-label">Jumlah <input type="number" name="quantities[<?= (int)$item['id'] ?>]" min="0" max="<?= max(0,(int)$item['stock']) ?>" value="<?= $item['quantity'] ?>"></label><?php if (!(int)$item['is_active'] || (int)$item['stock'] < $item['quantity']): ?><p class="text-danger">Stok berubah. Perbarui keranjang sebelum checkout.</p><?php endif; ?></div><div class="cart-right"><strong><?= rupiah((int)$item['subtotal']) ?></strong><button type="submit" name="remove_id" value="<?= (int)$item['id'] ?>" class="text-danger">Hapus</button></div></div><?php endforeach; ?><input type="hidden" name="book_id" value="0"><button type="submit" class="btn btn-outline">Perbarui Keranjang</button></form></div><aside class="order-summary"><span class="eyebrow">RINGKASAN PESANAN</span><h2>Total Belanja</h2><div class="summary-line"><span><?= count($items) ?> jenis buku</span><strong><?= rupiah($total) ?></strong></div><div class="summary-total"><span>Total produk</span><strong><?= rupiah($total) ?></strong></div><p class="muted summary-note">Biaya pengiriman dan pembayaran belum diproses secara online.</p><a href="<?= path('user/checkout.php') ?>" class="btn btn-primary btn-block">Lanjut ke Checkout →</a><a href="<?= path('katalog.php') ?>" class="back-link">← Tambah buku lainnya</a></aside></div><?php endif; ?></div></section>
<?php require __DIR__ . '/../template/footer.php'; ?>
