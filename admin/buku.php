<?php
require __DIR__ . '/../config/koneksi.php'; require_admin();
if (is_post()) {
    verify_csrf();
    $bookId = max(0, (int)($_POST['book_id'] ?? 0));
    $action = (string)($_POST['action'] ?? '');
    if ($bookId && $action === 'delete') {
        $used = (int)statement($db,'SELECT COUNT(*) FROM detail_pesanan WHERE id_buku=:id',['id'=>$bookId])->fetchColumn();
        if ($used) notice('error','Buku sudah tercatat dalam pesanan dan tidak dapat dihapus.');
        else {
            try { statement($db,'DELETE FROM buku WHERE id_buku=:id',['id'=>$bookId]); notice('success','Buku berhasil dihapus.'); }
            catch (PDOException $e) {notice('error','Buku terkait data lain dan tidak dapat dihapus.');}
        }
    }
    go('admin/buku.php');
}
$adminTitle = 'Kelola Buku';
$books = $db->query('SELECT b.id_buku AS id,b.id_kategori AS category_id,b.judul_buku AS title,b.penulis AS author,b.harga AS price,b.stok AS stock,b.gambar AS cover_image,b.deskripsi AS description,b.penerbit AS publisher,b.tahun AS publication_year,1 AS is_active,c.nama_kategori AS category_name FROM buku b LEFT JOIN kategori c ON c.id_kategori=b.id_kategori ORDER BY b.id_buku DESC LIMIT 500')->fetchAll();
require __DIR__ . '/../template/sidebar.php';
?>
<div class="panel-heading admin-list-heading"><div><h2>Daftar Koleksi</h2><p class="muted">Buku dengan riwayat pesanan tidak bisa dihapus agar laporan transaksi aman.</p></div><a class="btn btn-primary" href="<?= path('admin/tambah_buku.php') ?>">+ Tambah Buku</a></div><div class="admin-panel"><div class="table-scroll"><table><thead><tr><th>Buku</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach ($books as $book): ?><tr><td><div class="admin-book-row"><img src="<?= cover_url($book['cover_image']) ?>" alt=""><div><strong><?= e($book['title']) ?></strong><small><?= e($book['author']) ?></small></div></div></td><td><?= e($book['category_name']) ?></td><td><?= rupiah((int)$book['price']) ?></td><td><?= (int)$book['stock'] ?></td><td><span class="status status-selesai">Aktif</span></td><td><div class="row-actions"><a href="<?= path('admin/edit_buku.php?id=' . (int)$book['id']) ?>">Edit</a><form method="post"><?= csrf_input() ?><input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>"><input type="hidden" name="action" value="delete"><button type="submit" class="text-button" onclick="return confirm('Hapus buku ini? Buku yang sudah masuk pesanan tidak bisa dihapus.')">Hapus</button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php if (!$books): ?><div class="admin-empty">Belum ada buku. Klik “Tambah Buku” untuk mengisi katalog.</div><?php endif; ?><?php if (count($books) === 500): ?><p class="muted admin-limit">Menampilkan 500 buku terbaru.</p><?php endif; ?></div>
<?php require __DIR__ . '/../template/admin_footer.php'; ?>
