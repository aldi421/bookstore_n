# Laporan pemeriksaan — VELORA BOOKS + database Bahasa Indonesia

Tanggal: 12 Oktober 2026

## Yang diperiksa
- Semua file `.php` dipindai menggunakan `php -l` (lint), tanpa syntax error.
- Fungsi format harga, escape HTML, CSRF, URL Laragon, filter katalog berbasis parameter, validasi cover lama, dan kompatibilitas password lama diuji via `scripts/smoke_test.php`.
- Semua query aplikasi telah diganti ke tujuh tabel Indonesia `buku`, `kategori`, `users`, `keranjang`, `pesanan`, `detail_pesanan`, dan `pesan` serta nama kolom yang sesuai backup LENTERA.
- Original tiga cover dari database backup disertakan dalam `images/buku`.
- Tautan portofolio yang digunakan: https://portofolio-anisa.lovestoblog.com/ (beranda, Tentang Kami, footer).

## Belum dapat diuji di lingkungan penyusunan
- Server MariaDB/MySQL dengan data `bookstore_n` aktual di mesin pengguna tidak terhubung ke sistem ini. PHP CLI yang tersedia tidak memiliki driver `pdo_mysql`.
- Karena itu, **belum dapat diklaim** semua query, registrasi/login, transaksi checkout, pengurangan stok, ubah status, atau panel admin sudah diuji end-to-end.
- Untuk uji secara lokal, buka Laragon, jalankan `php scripts/cek_database.php`, kemudian uji alur website dengan browser.

## Catatan keterbatasan skema lama
- Alamat disimpan dalam `users.alamat`, bukan pada pesanan (perubahan alamat profil memengaruhi tampilan alamat akun pada riwayat).
- Riwayat `detail_pesanan.harga` bisa NULL dan tidak akan direka-reka oleh aplikasi.
- Status pembayaran adalah catatan internal; gateway pembayaran digital tidak ada.
- Arsip buku tidak tersedia tanpa penambahan kolom; aksi Hapus dilindungi referensi transaksi.
