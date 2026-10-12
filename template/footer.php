</main>
<footer class="site-footer" id="contact">
<div class="container footer-grid">
  <div class="footer-about"><a href="<?= path() ?>" class="footer-brand"><img src="<?= path('images/brand-mark.svg') ?>" alt="" width="42" height="42"><span>VELORA <small>BOOKS</small></span></a><p>Ruang untuk menemukan bacaan baru, ilmu yang berharga, dan cerita yang akan terus tinggal bersamamu.</p><p class="footer-motto">More Books, Brighter Minds.</p></div>
  <div><h3>Jelajahi</h3><a href="<?= path('index.php') ?>">Beranda</a><a href="<?= path('katalog.php') ?>">Koleksi Buku</a><a href="<?= path('index.php#categories') ?>">Kategori</a><a href="<?= path('tentang.php') ?>">Tentang Kami</a></div>
  <div><h3>Kategori Buku</h3><?php foreach (array_slice($categoriesNav, 0, 5) as $navCategory): ?><a href="<?= path('katalog.php?category=' . (int)$navCategory['id']) ?>"><?= e($navCategory['name']) ?></a><?php endforeach; ?></div>
  <div><h3>Informasi</h3><p>Untuk bantuan dan pertanyaan terkait pesanan, silakan masuk ke akun dan lihat status pesananmu.</p><a href="<?= path('user/pesanan.php') ?>">Pesanan Saya</a><a href="<?= path('user/profil.php') ?>">Profil Saya</a><a href="<?= path('user/contact.php') ?>">Hubungi Kami</a><a href="https://portofolio-anisa.lovestoblog.com/" target="_blank" rel="noopener noreferrer" class="portfolio-footer-link">Portofolio Saya <span aria-hidden="true">↗</span></a></div>
</div>
<div class="container footer-bottom"><span>© <?= date('Y') ?> Velora Books. All rights reserved.</span><span>Dibuat untuk pengalaman membaca yang lebih baik.</span></div>
</footer>
</body></html>
