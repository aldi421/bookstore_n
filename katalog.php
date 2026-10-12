<?php
require __DIR__ . '/config/koneksi.php';
$pageTitle = 'Koleksi Buku';
$q = trim(substr((string)($_GET['q'] ?? ''), 0, 100));
$category = max(0, page_int('category'));
$sort = (string)($_GET['sort'] ?? 'newest');
$sortOptions = ['newest' => 'b.id_buku DESC', 'title_asc' => 'b.judul_buku ASC,b.id_buku ASC', 'price_asc' => 'b.harga ASC,b.id_buku ASC', 'price_desc' => 'b.harga DESC,b.id_buku DESC'];
if (!isset($sortOptions[$sort])) $sort = 'newest';
$page = max(1, page_int('page', 1));
$perPage = 12;
[$where, $bindings] = book_query_parts(['q' => $q, 'category' => $category]);
$count = (int)statement($db, 'SELECT COUNT(*) FROM buku b' . $where, $bindings)->fetchColumn();
$pages = max(1, (int)ceil($count / $perPage));
$page = min($page, $pages);
$sql = 'SELECT b.id_buku AS id,b.id_kategori AS category_id,b.judul_buku AS title,b.penulis AS author,b.harga AS price,b.stok AS stock,b.gambar AS cover_image,b.deskripsi AS description,b.penerbit AS publisher,b.tahun AS publication_year,1 AS is_active,c.nama_kategori AS category_name FROM buku b JOIN kategori c ON b.id_kategori=c.id_kategori' . $where . ' ORDER BY ' . $sortOptions[$sort] . ' LIMIT :limit OFFSET :offset';
$stmt = $db->prepare($sql);
foreach ($bindings as $key => $value) $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll();
$categories = $db->query('SELECT id_kategori AS id,nama_kategori AS name FROM kategori ORDER BY nama_kategori')->fetchAll();
$queryUrl = fn(int $newPage): string => path('katalog.php?' . http_build_query(['q' => $q, 'category' => $category ?: null, 'sort' => $sort, 'page' => $newPage]));
require __DIR__ . '/template/navbar.php';
?>
<div class="page-intro"><div class="container"><span class="eyebrow">THE VELORA COLLECTION</span><h1>Koleksi <em>Buku</em></h1><p>Temukan judul, penulis, dan cerita yang paling sesuai untukmu.</p></div></div>
<section class="section catalog-section"><div class="container"><form class="filter-bar" action="<?= path('katalog.php') ?>" method="get" aria-label="Cari dan filter buku"><label class="field search-field"><span>Cari Buku</span><input type="search" name="q" value="<?= e($q) ?>" placeholder="Judul atau nama penulis..."></label><label class="field"><span>Kategori</span><select name="category"><option value="0">Semua Kategori</option><?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= $category === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></label><label class="field"><span>Urutkan</span><select name="sort"><option value="newest" <?= $sort==='newest'?'selected':'' ?>>Terbaru</option><option value="title_asc" <?= $sort==='title_asc'?'selected':'' ?>>Judul A–Z</option><option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Harga Terendah</option><option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Harga Tertinggi</option></select></label><button class="btn btn-primary filter-button" type="submit">Terapkan <span aria-hidden="true">→</span></button></form><div class="catalog-count"><span><strong><?= $count ?></strong> buku ditemukan</span><?php if ($q !== '' || $category): ?><a href="<?= path('katalog.php') ?>">Reset filter <span aria-hidden="true">×</span></a><?php endif; ?></div><?php if ($books): ?><div class="books-grid"><?php foreach ($books as $book) require __DIR__ . '/template/book-card.php'; ?></div><?php else: ?><div class="empty-state"><span class="empty-symbol">⌕</span><h2>Belum ada buku yang cocok</h2><p>Coba kata kunci atau kategori lain. Jika katalog masih kosong, administrator dapat menambahkan buku dari dashboard.</p><a class="btn btn-primary" href="<?= path('katalog.php') ?>">Tampilkan Semua</a></div><?php endif; ?><?php if ($pages > 1): ?><nav class="pagination" aria-label="Halaman katalog"><?php if ($page > 1): ?><a href="<?= e($queryUrl($page-1)) ?>">← Sebelumnya</a><?php endif; ?><span>Halaman <?= $page ?> dari <?= $pages ?></span><?php if ($page < $pages): ?><a href="<?= e($queryUrl($page+1)) ?>">Berikutnya →</a><?php endif; ?></nav><?php endif; ?></div></section>
<?php require __DIR__ . '/template/footer.php'; ?>
