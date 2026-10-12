<article class="book-card">
  <a class="book-cover" href="<?= path('user/detail_buku.php?id=' . (int)$book['id']) ?>" aria-label="Lihat <?= e($book['title']) ?>">
    <img src="<?= cover_url($book['cover_image']) ?>" loading="lazy" alt="Sampul buku <?= e($book['title']) ?>">
    <?php if ((int)$book['stock'] === 0): ?><span class="stock-ribbon">Stok habis</span><?php endif; ?>
  </a>
  <div class="book-meta"><p class="eyebrow book-category"><?= e($book['category_name']) ?></p><h3><a href="<?= path('user/detail_buku.php?id=' . (int)$book['id']) ?>"><?= e($book['title']) ?></a></h3><p class="muted book-author"><?= e($book['author']) ?></p><div class="book-bottom"><strong><?= rupiah((int)$book['price']) ?></strong><a href="<?= path('user/detail_buku.php?id=' . (int)$book['id']) ?>" class="book-detail-link" aria-label="Detail <?= e($book['title']) ?>">Lihat <span aria-hidden="true">↗</span></a></div></div>
</article>
