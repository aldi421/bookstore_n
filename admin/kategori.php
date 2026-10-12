<?php
require __DIR__ . '/../config/koneksi.php'; require_admin();
$errors = [];
if (is_post()) {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    $name = input('name',100);
    if (!in_array($action,['add','rename'],true)) $errors[]='Aksi kategori tidak valid.';
    if (strlen($name) < 2) $errors[] = 'Nama kategori minimal dua karakter.';
    if (!$errors) {
        try {
            if ($action === 'add') {
                statement($db,'INSERT INTO kategori(nama_kategori) VALUES(:name)',['name'=>$name]); notice('success','Kategori berhasil ditambahkan.'); go('admin/kategori.php');
            } elseif ($action === 'rename') {
                $id = (int)($_POST['id'] ?? 0);
                statement($db,'UPDATE kategori SET nama_kategori=:name WHERE id_kategori=:id',['name'=>$name,'id'=>$id]); notice('success','Kategori berhasil diubah.'); go('admin/kategori.php');
            }
        } catch (PDOException $e) { $errors[] = $e->getCode() === '23000' ? 'Nama kategori sudah digunakan.' : 'Kategori gagal disimpan.'; }
    }
}
$adminTitle = 'Kategori Buku';
$categories = $db->query('SELECT c.id_kategori AS id,c.nama_kategori AS name,COUNT(b.id_buku) AS total FROM kategori c LEFT JOIN buku b ON b.id_kategori=c.id_kategori GROUP BY c.id_kategori,c.nama_kategori ORDER BY c.nama_kategori')->fetchAll();
require __DIR__ . '/../template/sidebar.php';
?>
<div class="panel-heading admin-list-heading"><div><h2>Kelola Kategori</h2><p class="muted">Kategori terhubung langsung dengan filter katalog.</p></div></div>
<?php foreach ($errors as $error): ?><div role="alert" class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
<div class="admin-two-col"><div class="admin-panel"><span class="eyebrow">KATEGORI BARU</span><h2>Tambah Kategori</h2><form method="post" class="stack-form"><?= csrf_input() ?><input type="hidden" name="action" value="add"><label class="field"><span>Nama Kategori</span><input name="name" maxlength="100" required placeholder="Contoh: Biografi"></label><button class="btn btn-primary" type="submit">Tambah Kategori</button></form></div><div class="admin-panel"><span class="eyebrow">SEMUA KATEGORI</span><h2>Daftar Kategori</h2><div class="category-admin-list"><?php foreach ($categories as $cat): ?><form method="post" class="category-admin-item"><?= csrf_input() ?><input type="hidden" name="action" value="rename"><input type="hidden" name="id" value="<?= (int)$cat['id'] ?>"><label class="field"><span><?= (int)$cat['total'] ?> buku</span><input name="name" value="<?= e($cat['name']) ?>" required maxlength="100"></label><button class="btn btn-sm btn-outline" type="submit">Simpan</button></form><?php if((int)$cat['total']===0): ?><form method="post" action="<?= path('admin/hapus_kategori.php') ?>" class="category-admin-item"><?= csrf_input() ?><input type="hidden" name="id" value="<?= (int)$cat['id'] ?>"><button type="submit" class="btn btn-sm btn-outline">Hapus kategori kosong</button></form><?php endif; ?><?php endforeach; ?></div></div></div>
<?php require __DIR__ . '/../template/admin_footer.php'; ?>
