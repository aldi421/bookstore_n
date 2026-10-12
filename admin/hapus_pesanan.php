<?php
require __DIR__.'/../config/koneksi.php'; require_admin();
notice('error','Riwayat pesanan tidak dihapus demi menjaga integritas transaksi. Gunakan status Dibatalkan jika memenuhi syarat.');
go('admin/pesanan.php');
