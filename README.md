# VELORA BOOKS — Laragon + database Bahasa Indonesia

Versi ini menggunakan **database existing `bookstore_n` yang berisi tabel Indonesia dari LENTERA**. Tampilan premium VELORA tetap sama, termasuk tombol **Portofolio Saya** menuju https://portofolio-anisa.lovestoblog.com/ di halaman Tentang Kami, beranda, dan footer.

## Instal di Laragon (database sudah ada seperti screenshot)

1. **Backup** folder `C:\laragon\www\bookstore_n` dan ekspor database `bookstore_n` melalui HeidiSQL/phpMyAdmin. Jangan hapus database.
2. Ekstrak ZIP ini. Salin **isi folder `bookstore_n/`** ke `C:\laragon\www\bookstore_n\` (ganti file PHP/CSS/JS setelah backup).
3. Bila sudah punya `config.php`, pertahankan file itu. Bila belum, salin `config.example.php` menjadi `config.php`.
4. Periksa `config.php`: `db.name` harus `bookstore_n`, `user` umumnya `root`, `pass` kosong bila konfigurasi Laragon default, port sesuai Laragon. `base_url` kosong untuk autodeteksi (baik `.test` maupun localhost).
5. Jalankan Laragon > **Start All**. Buka `http://bookstore_n.test` (atau `http://localhost/bookstore_n/`). Refresh Ctrl+F5.
6. **JANGAN mengimpor SQL lagi** jika 7 tabel sudah ada. File `bookstore_n.sql` di ZIP adalah skema *opsional* untuk instalasi kosong, bukan perbaikan yang harus diimpor.
7. Uji halaman beranda, katalog, detail, login, admin, pencarian, keranjang, checkout, pesanan, dan pesan. Untuk cek struktur, jalankan dari terminal Laragon `php scripts/cek_database.php`.

## Tabel yang dipakai langsung

- `buku` — `id_buku`, `id_kategori`, `judul_buku`, `penulis`, `harga`, `stok`, `gambar`, `deskripsi`, `penerbit`, `tahun`
- `kategori` — `id_kategori`, `nama_kategori`
- `users` — `id_user`, `nama`, `email`, `password`, `alamat`, `role` (`admin` atau `user`)
- `keranjang` — tabel lama dipertahankan; keranjang belanja VELORA masih disimpan di session untuk tidak merusak isi tabel lama
- `pesanan` — `id_pesanan`, `id_user`, `tanggal`, `total`, `metode_pembayaran`, `status_pembayaran`, `status`
- `detail_pesanan` — `id_detail`, `id_pesanan`, `id_buku`, `jumlah`, `harga`
- `pesan` — `id_pesan`, `id_user`, `judul_pesan`, `isi_pesan`, `balasan_admin`, `tanggal`, `status`

## Catatan kompatibilitas

- Password lama dari LENTERA, jika masih plaintext, akan diubah menjadi hash PHP saat login berhasil. Tidak perlu mereset akun. **Ganti password lama setelah masuk**.
- Karena skema lama tidak menyimpan nomor HP/nama penerima/alamat **per pesanan**, halaman checkout kini menyimpan alamat terbaru pada `users.alamat`. Halaman detail menampilkan *alamat akun saat ini* dan menandainya, bukan mengarang alamat saat pemesanan.
- `detail_pesanan.harga` pada pesanan lama bisa `NULL`; aplikasi menampilkan 'Tidak tercatat' untuk harga satuan lama, sedangkan total pesanan historis tetap dibaca dari `pesanan.total`.
- Karena tabel `buku` tidak mempunyai `is_active`, tidak ada fitur arsip terselubung; admin dapat **hapus hanya buku yang tidak tercatat dalam riwayat pesanan**.
- Pesan kontak kini hanya bisa dikirim setelah login, sebab tabel lama tidak menyediakan kolom nama/email pengirim independen.
- Checkout **tidak memproses pembayaran digital otomatis**; status pembayaran default 'Belum Bayar'.
- SQL lama LENTERA memakai bahasa Indonesia dan itulah target aplikasi ini; **tidak perlu membuat `books`, `categories`, `orders` ataupun tabel Inggris lainnya**.

## Tes dan error

Pemeriksaan sintaks `php -l` dan smoke test helper dijalankan sebelum pengemasan. Tes MySQL transaksi langsung belum bisa dijalankan di lingkungan penyusunan karena `pdo_mysql`/server MariaDB tidak tersedia; uji fungsi penuh di Laragon. Periksa file `TEST_REPORT.md` untuk catatan detail.
