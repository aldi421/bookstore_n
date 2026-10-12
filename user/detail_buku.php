<?php
require __DIR__ . '/../config/koneksi.php';
$id = max(0, page_int('id'));
$book = statement($db, 'SELECT b.id_buku AS id,b.id_kategori AS category_id,b.judul_buku AS title,b.penulis AS author,b.harga AS price,b.stok AS stock,b.gambar AS cover_image,b.deskripsi AS description,b.penerbit AS publisher,b.tahun AS publication_year,1 AS is_active,c.nama_kategori AS category_name FROM buku b JOIN kategori c ON c.id_kategori=b.id_kategori WHERE b.id_buku=:id', ['id' => $id])->fetch();
if (!$book) {
    http_response_code(404); $pageTitle = 'Buku Tidak Ditemukan';
    require __DIR__ . '/../template/navbar.php';
    echo '<div class="container section empty-state"><h1>Buku tidak ditemukan</h1><p>Judul ini mungkin telah dihapus atau tidak tersedia.</p><a class="btn btn-primary" href="' . path('katalog.php') . '">Kembali ke katalog</a></div>';
    require __DIR__ . '/../template/footer.php'; exit;
}
$pageTitle = $book['title'];
$description = substr(trim(strip_tags((string)($book['description'] ?: 'Buku karya ' . $book['author']))), 0, 155);
require __DIR__ . '/../template/navbar.php';
?>
<section class="section"><div class="container"><nav class="breadcrumbs"><a href="<?= path() ?>">Beranda</a><span>/</span><a href="<?= path('katalog.php') ?>">Koleksi Buku</a><span>/</span><span><?= e($book['title']) ?></span></nav><div class="product-grid"><div class="product-image"><img src="<?= cover_url($book['cover_image']) ?>" alt="Sampul <?= e($book['title']) ?>"></div><div class="product-info"><span class="eyebrow"><?= e($book['category_name']) ?></span><h1><?= e($book['title']) ?></h1><p class="product-author">Ditulis oleh <strong><?= e($book['author']) ?></strong></p><p class="product-price"><?= rupiah((int)$book['price']) ?></p><div class="product-facts"><div><span>Penerbit</span><strong><?= e($book['publisher'] ?: 'Belum dicantumkan') ?></strong></div><div><span>Tahun Terbit</span><strong><?= $book['publication_year'] ? (int)$book['publication_year'] : 'Belum dicantumkan' ?></strong></div><div><span>Ketersediaan</span><strong class="<?= (int)$book['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>"><?= (int)$book['stock'] > 0 ? (int)$book['stock'] . ' buku tersedia' : 'Stok habis' ?></strong></div></div><form action="<?= path('user/keranjang.php') ?>" method="post" class="add-form"><?= csrf_input() ?><input type="hidden" name="action" value="add"><input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>"><label for="quantity">Jumlah</label><input type="number" id="quantity" name="quantity" min="1" max="<?= max(1,(int)$book['stock']) ?>" value="1" <?= (int)$book['stock'] < 1 ? 'disabled' : '' ?>><button class="btn btn-primary" type="submit" <?= (int)$book['stock'] < 1 ? 'disabled' : '' ?>>Tambah ke Keranjang <span aria-hidden="true">→</span></button></form><div class="description"><h2>Tentang Buku Ini</h2><p><?= nl2br(e($book['description'] ?: 'Belum ada deskripsi untuk buku ini.')) ?></p></div><a class="back-link" href="<?= path('katalog.php') ?>">← Kembali ke katalog</a></div></div></div></section>
<?php require __DIR__ . '/../template/footer.php'; ?>
