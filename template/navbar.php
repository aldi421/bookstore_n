<?php
if (!defined('ROOT_DIR')) { http_response_code(403); exit('Forbidden'); }
$pageTitle = $pageTitle ?? 'Velora Books';
$description = $description ?? 'Temukan buku favorit di Velora Books. More Books, Brighter Minds.';
$categoriesNav = $db->query('SELECT id_kategori AS id,nama_kategori AS name FROM kategori ORDER BY nama_kategori LIMIT 10')->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?= e($description) ?>">
<title><?= e($pageTitle) ?> | Velora Books</title>
<link rel="icon" type="image/svg+xml" href="<?= path('images/favicon.svg') ?>">
<link rel="stylesheet" href="<?= path('css/style.css') ?>">
<script src="<?= path('js/ui.js') ?>" defer></script>
</head>
<body>
<a href="#main" class="skip-link">Langsung ke konten</a>
<div class="topbar"><span>More Books, Brighter Minds</span><span class="topbar-right">Koleksi pilihan untuk setiap cerita</span></div>
<header class="site-header">
  <div class="container navbar">
    <a class="brand" href="<?= path() ?>" aria-label="Velora Books — beranda">
      <img src="<?= path('images/brand-mark.svg') ?>" width="42" height="42" alt="">
      <span class="brand-wordmark"><strong>VELORA</strong><small>BOOKS</small></span>
    </a>
    <button class="menu-toggle icon-button" aria-expanded="false" aria-controls="site-nav" aria-label="Buka menu navigasi" data-menu-toggle>
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
    </button>
    <nav class="nav-links" id="site-nav" aria-label="Menu utama">
      <a href="<?= path('index.php') ?>">Beranda</a>
      <a href="<?= path('katalog.php') ?>">Koleksi Buku</a>
      <a href="<?= path('index.php#categories') ?>">Kategori</a>
      <a href="<?= path('tentang.php') ?>">Tentang Kami</a>
      <a href="<?= path('user/contact.php') ?>">Kontak</a>
      <a class="mobile-account" href="https://portofolio-anisa.lovestoblog.com/" target="_blank" rel="noopener noreferrer">Portofolio Saya ↗</a>
      <a class="mobile-account" href="<?= path(current_user() ? (current_user()['role'] === 'admin' ? 'admin/index.php' : 'user/pesanan.php') : 'login.php') ?>"><?= current_user() ? 'Akun / Pesanan Saya' : 'Masuk / Daftar' ?></a>
      <?php if (current_user()): ?><a class="mobile-account" href="<?= path('user/profil.php') ?>">Profil Saya</a><a class="mobile-account" href="<?= path('user/pesan_saya.php') ?>">Pesan Saya</a><form class="mobile-account" method="post" action="<?= path('logout.php') ?>"><?= csrf_input() ?><button type="submit">Keluar Akun</button></form><?php endif; ?>
    </nav>
    <div class="nav-actions">
      <a class="icon-button" href="<?= path('katalog.php') ?>" aria-label="Cari buku" title="Cari buku"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></a>
      <a class="icon-button user-link" href="<?= path(current_user() ? (current_user()['role'] === 'admin' ? 'admin/index.php' : 'user/pesanan.php') : 'login.php') ?>" aria-label="Akun saya" title="Akun"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.6"/><path d="M4.7 20c.8-4.1 3.1-6.2 7.3-6.2s6.5 2.1 7.3 6.2"/></svg></a>
      <a class="icon-button cart-link" href="<?= path('user/keranjang.php') ?>" aria-label="Keranjang: <?= cart_count() ?> barang" title="Keranjang"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 11.2h11.6l2-8.2H6.1M9 20h.1M18 20h.1"/></svg><?php if (cart_count() > 0): ?><span class="cart-badge"><?= cart_count() ?></span><?php endif; ?></a>
      <?php if (!current_user()): ?><a class="btn btn-sm btn-outline nav-login" href="<?= path('login.php') ?>">Masuk</a><?php else: ?><form method="post" action="<?= path('logout.php') ?>" class="nav-logout"><?= csrf_input() ?><button class="btn btn-sm btn-outline" type="submit">Keluar</button></form><?php endif; ?>
    </div>
  </div>
</header>
<?php foreach (pop_notices() as $notification): ?><div class="container"><div role="status" class="alert alert-<?= $notification['type'] === 'success' ? 'success' : 'error' ?>"><?= e($notification['message']) ?></div></div><?php endforeach; ?>
<main id="main">
