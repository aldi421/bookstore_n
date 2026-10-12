<?php
require __DIR__ . '/../config/koneksi.php'; require_admin();
$id = max(0, page_int('id'));
$book = $id ? statement($db, 'SELECT id_buku AS id,id_kategori AS category_id,judul_buku AS title,penulis AS author,harga AS price,stok AS stock,gambar AS cover_image,deskripsi AS description,penerbit AS publisher,tahun AS publication_year,1 AS is_active FROM buku WHERE id_buku=:id', ['id' => $id])->fetch() : null;
if ($id && !$book) { http_response_code(404); exit('Buku tidak ditemukan.'); }
$categories = $db->query('SELECT id_kategori AS id,nama_kategori AS name FROM kategori ORDER BY nama_kategori')->fetchAll();
$errors = [];
$values = $book ?: ['title'=>'','author'=>'','publisher'=>'','publication_year'=>'','price'=>'','stock'=>'','description'=>'','category_id'=>($categories[0]['id'] ?? ''),'cover_image'=>null];
if (is_post()) {
    verify_csrf();
    $values = [
        'title' => input('title',150), 'author' => input('author',100), 'publisher'=>input('publisher',100),
        'publication_year'=>trim((string)($_POST['publication_year'] ?? '')), 'price'=>trim((string)($_POST['price'] ?? '')),
        'stock'=>trim((string)($_POST['stock'] ?? '')), 'description'=>input('description',12000),
        'category_id' => (int)($_POST['category_id'] ?? 0), 'cover_image' => $book['cover_image'] ?? null,
    ];
    $categoryIds = array_map('intval',array_column($categories,'id'));
    if (strlen($values['title']) < 2) $errors[] = 'Judul wajib diisi (min. 2 karakter).';
    if (strlen($values['author']) < 2) $errors[] = 'Penulis wajib diisi (min. 2 karakter).';
    if (!in_array($values['category_id'], $categoryIds, true)) $errors[] = 'Pilih kategori yang tersedia.';
    if (!ctype_digit($values['price']) || (float)$values['price'] > 1000000000) $errors[] = 'Harga harus bilangan bulat Rp 0 hingga Rp 1 miliar.';
    if (!ctype_digit($values['stock']) || (float)$values['stock'] > 1000000) $errors[] = 'Stok harus bilangan bulat 0 sampai 1 juta.';
    if ($values['publication_year'] !== '' && (!ctype_digit($values['publication_year']) || (int)$values['publication_year'] < 1000 || (int)$values['publication_year'] > (int)date('Y') + 2)) $errors[] = 'Tahun terbit tidak valid.';
    $file = $_FILES['cover'] ?? null;
    $newCover = null;
    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 3 * 1024 * 1024) $errors[] = 'Gambar gagal diunggah atau melebihi 3 MB.';
        else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            $dims = @getimagesize($file['tmp_name']);
            if (!isset($extensions[$mime]) || !$dims || $dims[0] > 6000 || $dims[1] > 6000) $errors[] = 'Cover harus berupa JPG, PNG, atau WEBP berukuran maksimal 6000 px.';
            else {
                $newCover = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../images/buku/' . $newCover)) {
                    $errors[] = 'Folder images/buku tidak dapat ditulisi.'; $newCover = null;
                }
            }
        }
    }
    if (!$errors) {
        try {
            $payload = [
                'title' => $values['title'],'author'=>$values['author'],'publisher'=>$values['publisher'] ?: null,
                'publication_year'=>$values['publication_year'] !== '' ? (int)$values['publication_year'] : null,
                'price'=>(int)$values['price'],'stock'=>(int)$values['stock'],
                'description'=>$values['description'] ?: null,'category_id'=>$values['category_id'],
                'cover_image'=>$newCover ?? $values['cover_image'],
            ];
            if ($book) {
                $payload['id'] = $id;
                statement($db,'UPDATE buku SET judul_buku=:title,penulis=:author,penerbit=:publisher,tahun=:publication_year,harga=:price,stok=:stock,deskripsi=:description,id_kategori=:category_id,gambar=:cover_image WHERE id_buku=:id',$payload);
            } else {
                statement($db,'INSERT INTO buku(judul_buku,penulis,penerbit,tahun,harga,stok,deskripsi,id_kategori,gambar) VALUES(:title,:author,:publisher,:publication_year,:price,:stock,:description,:category_id,:cover_image)',$payload);
            }
            if ($newCover && $book && $book['cover_image'] && basename((string)$book['cover_image']) === $book['cover_image']) @unlink(__DIR__ . '/../images/buku/' . $book['cover_image']);
            notice('success', $book ? 'Data buku diperbarui.' : 'Buku berhasil ditambahkan.');
            go('admin/buku.php');
        } catch (Throwable $e) {
            if ($newCover) @unlink(__DIR__ . '/../images/buku/' . $newCover);
            error_log('Book save: ' . $e->getMessage());
            $errors[] = 'Buku belum bisa disimpan. Periksa database dan ulangi.';
        }
    } elseif ($newCover) @unlink(__DIR__ . '/../images/buku/' . $newCover);
}
$adminTitle = $book ? 'Edit Buku' : 'Tambah Buku';
require __DIR__ . '/../template/sidebar.php';
?>
<div class="panel-heading admin-list-heading"><div><h2><?= $book ? 'Perbarui Informasi Buku' : 'Tambahkan Buku Baru' ?></h2><p class="muted">Pastikan informasi judul, harga, stok, dan gambar sesuai.</p></div><a class="btn btn-outline" href="<?= path('admin/buku.php') ?>">← Kembali</a></div>
<?php foreach ($errors as $error): ?><div role="alert" class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
<div class="admin-panel admin-form-panel"><form method="post" enctype="multipart/form-data" class="stack-form"><?= csrf_input() ?><div class="form-two"><label class="field"><span>Judul Buku *</span><input name="title" required maxlength="150" value="<?= e((string)$values['title']) ?>"></label><label class="field"><span>Penulis *</span><input name="author" required maxlength="100" value="<?= e((string)$values['author']) ?>"></label></div><div class="form-two"><label class="field"><span>Kategori *</span><select name="category_id" required><?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= (int)$values['category_id'] === (int)$cat['id'] ? 'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></label><label class="field"><span>Penerbit</span><input name="publisher" maxlength="100" value="<?= e((string)$values['publisher']) ?>"></label></div><div class="form-three"><label class="field"><span>Tahun Terbit</span><input name="publication_year" type="number" min="1000" max="<?= date('Y')+2 ?>" value="<?= e((string)$values['publication_year']) ?>"></label><label class="field"><span>Harga (Rp) *</span><input name="price" type="number" required min="0" max="1000000000" value="<?= e((string)$values['price']) ?>"></label><label class="field"><span>Stok *</span><input name="stock" type="number" required min="0" max="1000000" value="<?= e((string)$values['stock']) ?>"></label></div><label class="field"><span>Deskripsi</span><textarea name="description" rows="6" maxlength="12000"><?= e((string)$values['description']) ?></textarea></label><label class="field"><span>Cover Buku (JPG/PNG/WEBP, max. 3 MB)</span><input name="cover" type="file" accept="image/jpeg,image/png,image/webp"></label><?php if ($values['cover_image']): ?><div class="existing-cover"><img src="<?= cover_url($values['cover_image']) ?>" alt="Cover saat ini"><span>Cover saat ini. Unggah gambar baru untuk mengganti.</span></div><?php endif; ?><div class="form-end"><a href="<?= path('admin/buku.php') ?>" class="btn btn-outline">Batal</a><button type="submit" class="btn btn-primary">Simpan Buku →</button></div></form></div>
<?php require __DIR__ . '/../template/admin_footer.php'; ?>
