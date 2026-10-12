<?php
require __DIR__.'/../config/koneksi.php';require_admin();
$adminTitle='Informasi Pembayaran';require __DIR__.'/../template/sidebar.php';
?>
<div class="admin-panel"><h2>Integrasi Pembayaran Belum Diaktifkan</h2><p>Velora saat ini mencatat pesanan dan status pemrosesannya, tetapi belum memiliki gateway pembayaran ataupun verifikasi bukti transfer. Demi akurasi, admin tidak dapat menandai pesanan sebagai sudah dibayar tanpa integrasi yang sah.</p><a class="btn btn-primary" href="<?= path('admin/pesanan.php') ?>">Kelola Pesanan</a></div>
<?php require __DIR__.'/../template/admin_footer.php'; ?>
