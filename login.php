<?php
require __DIR__ . '/config/koneksi.php';
if (current_user()) go(current_user()['role'] === 'admin' ? 'admin/index.php' : 'user/pesanan.php');
$error = '';
if (is_post()) {
    verify_csrf();
    $email = strtolower(input('email', 100));
    $password = (string)($_POST['password'] ?? '');
    $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'first' => time()];
    if (time() - $attempts['first'] > 900) $attempts = ['count' => 0, 'first' => time()];
    if ($attempts['count'] >= 10) $error = 'Terlalu banyak percobaan masuk. Coba lagi beberapa saat.';
    else {
        $user = statement($db, 'SELECT id_user AS id,nama AS name,email,password AS password_hash,role FROM users WHERE email=:email', ['email' => $email])->fetch();
        if (!$user || !legacy_password_matches($password, (string)$user['password_hash'])) {
            $attempts['count']++; $_SESSION['login_attempts'] = $attempts;
            $error = 'Email atau password tidak sesuai.';
        } else {
            if (password_needs_rehash_safe((string)$user['password_hash'])) {
                statement($db, 'UPDATE users SET password=:hash WHERE id_user=:id', ['hash'=>password_hash($password,PASSWORD_DEFAULT),'id'=>(int)$user['id']]);
            }
            session_regenerate_id(true);
            unset($_SESSION['login_attempts']);
            $_SESSION['user'] = ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role'] === 'admin' ? 'admin' : 'customer'];
            notice('success', 'Selamat datang kembali, ' . $user['name'] . '!');
            go($user['role'] === 'admin' ? 'admin/index.php' : 'user/pesanan.php');
        }
    }
}
$pageTitle = 'Masuk'; require __DIR__ . '/template/navbar.php';
?>
<section class="auth-section"><div class="auth-side"><span class="eyebrow">A LITTLE MORE EVERY DAY</span><h2>Your next chapter <em>starts here.</em></h2><p>Jelajahi dunia lewat kata-kata. Masuk untuk melanjutkan perjalananmu bersama Velora Books.</p><div class="auth-decoration">V.</div></div><div class="auth-panel"><div class="auth-card"><span class="eyebrow">SELAMAT DATANG KEMBALI</span><h1>Masuk ke <em>Velora.</em></h1><p class="muted">Belum punya akun? <a href="<?= path('register.php') ?>">Daftar di sini</a></p><?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><?= csrf_input() ?><label class="field"><span>Alamat Email</span><input type="email" name="email" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>" maxlength="100" placeholder="nama@email.com" required></label><label class="field"><span>Password</span><input type="password" name="password" autocomplete="current-password" required placeholder="Masukkan password"></label><button class="btn btn-primary btn-block" type="submit">Masuk ke Akun <span aria-hidden="true">→</span></button></form></div></div></section>
<?php require __DIR__ . '/template/footer.php'; ?>
