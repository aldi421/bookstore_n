<?php
require __DIR__ . '/config/koneksi.php';
if (current_user()) go('index.php');
$errors = [];
if (is_post()) {
    verify_csrf();
    $name = input('name', 100);
    $email = strtolower(input('email', 100));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (strlen($name) < 2) $errors[] = 'Nama minimal dua karakter.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Masukkan alamat email yang valid.';
    if (strlen($password) < 10) $errors[] = 'Password minimal 10 karakter.';
    if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak cocok.';
    if (!$errors) {
        try {
            statement($db, "INSERT INTO users (nama,email,password,role) VALUES (:name,:email,:password,'user')", ['name' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$db->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'customer'];
            notice('success', 'Akun berhasil dibuat. Selamat datang di Velora Books!'); go('index.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') $errors[] = 'Email sudah terdaftar. Silakan masuk.';
            else { error_log('Register: ' . $e->getMessage()); $errors[] = 'Pendaftaran gagal. Coba kembali.'; }
        }
    }
}
$pageTitle = 'Daftar'; require __DIR__ . '/template/navbar.php';
?>
<section class="auth-section"><div class="auth-side"><span class="eyebrow">WELCOME TO OUR WORLD</span><h2>Every reader has <em>a story.</em></h2><p>Temukan bacaan baru, simpan pesanan, dan mulai perjalanan membaca bersama Velora Books.</p><div class="auth-decoration">V.</div></div><div class="auth-panel"><div class="auth-card"><span class="eyebrow">GABUNG BERSAMA KAMI</span><h1>Buat <em>Akun Baru.</em></h1><p class="muted">Sudah punya akun? <a href="<?= path('login.php') ?>">Masuk di sini</a></p><?php foreach ($errors as $error): ?><div role="alert" class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?><form method="post" class="stack-form"><?= csrf_input() ?><label class="field"><span>Nama Lengkap</span><input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required maxlength="100" autocomplete="name"></label><label class="field"><span>Alamat Email</span><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required maxlength="100" autocomplete="email"></label><label class="field"><span>Password (min. 10 karakter)</span><input type="password" name="password" required minlength="10" autocomplete="new-password"></label><label class="field"><span>Konfirmasi Password</span><input type="password" name="confirm_password" required minlength="10" autocomplete="new-password"></label><button class="btn btn-primary btn-block" type="submit">Daftar Sekarang <span aria-hidden="true">→</span></button></form></div></div></section>
<?php require __DIR__ . '/template/footer.php'; ?>
