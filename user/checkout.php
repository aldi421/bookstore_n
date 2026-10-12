<?php
require __DIR__ . '/../config/koneksi.php';
require_login();
$cartBooks = cart_products($db);
if (!$cartBooks) { notice('error','Keranjang masih kosong.'); go('user/keranjang.php'); }
$errors = [];
$addressCurrent = statement($db,'SELECT alamat FROM users WHERE id_user=:id',['id'=>(int)current_user()['id']])->fetchColumn() ?: '';
if (is_post()) {
    verify_csrf();
    $address = input('address', 2000);
    $method = input('payment_method',50);
    if (strlen($address) < 12) $errors[] = 'Masukkan alamat lengkap (minimal 12 karakter).';
    if (!in_array($method,['Transfer Bank','COD','E-Wallet'],true)) $errors[] = 'Pilih metode pembayaran.';
    if (!$errors) {
        try {
            $db->beginTransaction();
            $cart = session_cart();
            $orderLines = []; $total = 0;
            $ids = array_keys($cart); sort($ids, SORT_NUMERIC);
            foreach ($ids as $id) {
                $qty = (int)$cart[$id];
                if ($qty < 1 || $qty > 99) throw new RuntimeException('Jumlah buku tidak valid.');
                $row = statement($db,'SELECT id_buku AS id,judul_buku AS title,harga AS price,stok AS stock FROM buku WHERE id_buku=:id FOR UPDATE',['id'=>(int)$id])->fetch();
                if (!$row || (int)$row['stock'] < $qty) throw new RuntimeException('Stok berubah; periksa keranjangmu.');
                $lineTotal = $qty * (int)$row['price']; $total += $lineTotal;
                $orderLines[] = ['book'=>$row,'qty'=>$qty,'subtotal'=>$lineTotal];
            }
            if (!$orderLines) throw new RuntimeException('Keranjang kosong.');
            statement($db,'UPDATE users SET alamat=:alamat WHERE id_user=:id',['alamat'=>$address,'id'=>(int)current_user()['id']]);
            statement($db,"INSERT INTO pesanan(id_user,tanggal,total,metode_pembayaran,status_pembayaran,status) VALUES(:user,CURDATE(),:total,:method,'Belum Bayar','Menunggu Pembayaran')",[
                'user'=>(int)current_user()['id'],'total'=>$total,'method'=>$method,
            ]);
            $orderId = (int)$db->lastInsertId();
            foreach ($orderLines as $line) {
                $book = $line['book'];
                statement($db,'INSERT INTO detail_pesanan(id_pesanan,id_buku,jumlah,harga) VALUES(:order_id,:book_id,:qty,:unit)',[
                    'order_id'=>$orderId,'book_id'=>(int)$book['id'],'qty'=>$line['qty'],'unit'=>(int)$book['price'],
                ]);
                statement($db,'UPDATE buku SET stok=stok-:qty WHERE id_buku=:id',['qty'=>$line['qty'],'id'=>(int)$book['id']]);
            }
            $db->commit(); unset($_SESSION['cart']);
            notice('success','Pesanan #'.$orderId.' dibuat. Pembayaran belum diproses otomatis.');
            go('user/pesanan.php');
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            if ($e instanceof RuntimeException) $errors[]=$e->getMessage();
            else { error_log('Checkout: '.$e->getMessage());$errors[]='Gagal membuat pesanan. Ulangi lagi.'; }
        }
    }
}
$pageTitle = 'Checkout';
$total = array_sum(array_column($cartBooks, 'subtotal'));
require __DIR__ . '/../template/navbar.php';
?>
<div class="page-intro"><div class="container"><span class="eyebrow">ALMOST THERE</span><h1>Konfirmasi <em>Pesanan</em></h1><p>Lengkapi data untuk memesan buku pilihanmu.</p></div></div>
<section class="section"><div class="container checkout-grid"><div><h2 class="form-heading">Informasi Pengiriman</h2><?php foreach ($errors as $error): ?><div role="alert" class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?><form method="post" class="stack-form checkout-form" id="checkout-form"><?= csrf_input() ?>
<label class="field"><span>Nama Pemesan</span><input disabled value="<?= e(current_user()['name']) ?>"></label>
<label class="field"><span>Alamat Akun / Pengiriman *</span><textarea name="address" rows="5" required minlength="12" maxlength="2000" placeholder="Masukkan alamat lengkap"><?= e($_POST['address'] ?? $addressCurrent) ?></textarea></label>
<label class="field"><span>Metode Pembayaran *</span><select name="payment_method" required><option value="">Pilih metode</option><?php foreach(['Transfer Bank','COD','E-Wallet'] as $method): ?><option value="<?= e($method) ?>" <?= ($_POST['payment_method']??'')===$method?'selected':'' ?>><?= e($method) ?></option><?php endforeach; ?></select></label>
<div class="checkout-disclaimer"><strong>Catatan:</strong> Database lama menyimpan alamat pada profil pengguna, bukan per pesanan. Alamat yang kamu isi memperbarui alamat akun. Pembayaran tidak otomatis diproses; tidak ada payment gateway.</div>
<button type="submit" class="btn btn-primary">Buat Pesanan <span aria-hidden="true">→</span></button></form></div><aside class="order-summary"><span class="eyebrow">PESANANMU</span><h2>Ringkasan</h2><?php foreach ($cartBooks as $book): ?><div class="summary-line"><span><?= e($book['title']) ?> × <?= (int)$book['quantity'] ?></span><strong><?= rupiah((int)$book['subtotal']) ?></strong></div><?php endforeach; ?><div class="summary-total"><span>Total produk</span><strong><?= rupiah($total) ?></strong></div><p class="muted summary-note">Harga tidak termasuk ongkir jika berlaku.</p></aside></div></section>
<?php require __DIR__ . '/../template/footer.php'; ?>
