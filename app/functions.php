<?php
declare(strict_types=1);

function e(mixed $value): string { return htmlspecialchars(is_scalar($value) ? (string)$value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function path(string $route = ''): string
{
    global $config;
    $base = rtrim((string)($config['base_url'] ?? ''), '/');
    if ($base === '') {
        $scriptDir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php')));
        if (in_array(basename($scriptDir), ['admin','user'], true)) $scriptDir = dirname($scriptDir);
        $base = $scriptDir === '/' || $scriptDir === '.' ? '' : '/' . trim($scriptDir, '/');
    }
    return $base . '/' . ltrim($route, '/');
}
function go(string $route): never { header('Location: ' . path($route), true, 303); exit; }
function csrf_token(): string
{
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_input(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function verify_csrf(): void
{
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419); exit('Form kedaluwarsa. Kembali dan muat ulang halaman.');
    }
}
function notice(string $type, string $message): void { $_SESSION['flash'][] = ['type' => $type, 'message' => $message]; }
function pop_notices(): array { $messages = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $messages; }
function rupiah(int|float $amount): string { return 'Rp ' . number_format($amount, 0, ',', '.'); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void
{
    if (!current_user()) { notice('error', 'Masuk terlebih dahulu untuk melanjutkan.'); go('login.php'); }
}
function require_admin(): void
{
    global $db;
    if (!current_user() || current_user()['role'] !== 'admin') { http_response_code(403); exit('Akses hanya untuk administrator.'); }
    $user = statement($db, 'SELECT role FROM users WHERE id_user=:id', ['id' => (int)current_user()['id']])->fetch();
    if (!$user || $user['role'] !== 'admin') { unset($_SESSION['user']); http_response_code(403); exit('Akses administrator sudah tidak berlaku.'); }
}
function session_cart(): array
{
    $cart = $_SESSION['cart'] ?? [];
    return is_array($cart) ? $cart : [];
}
function cart_count(): int { return array_sum(array_map('intval', session_cart())); }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function input(string $name, int $max = 255): string
{
    return trim(function_exists('mb_substr') ? mb_substr((string)($_POST[$name] ?? ''), 0, $max) : substr((string)($_POST[$name] ?? ''), 0, $max));
}
function page_int(string $key, int $default = 0): int
{
    $n = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    return $n !== false && $n !== null ? (int)$n : $default;
}
function cover_url(?string $cover): string
{
    if (!$cover || strlen($cover)>220 || basename($cover)!==$cover || !preg_match('/^[\p{L}\p{N} _().-]+\.(?:jpe?g|png|webp)$/iu',$cover)) return path('images/fallback-cover.svg');
    return path('images/buku/' . rawurlencode($cover));
}
function book_query_parts(array $filters): array
{
    $clauses = ['1=1']; $bindings = [];
    if (($filters['q'] ?? '') !== '') {
        $clauses[] = '(b.judul_buku LIKE :q1 OR b.penulis LIKE :q2)';
        $bindings['q1'] = '%' . $filters['q'] . '%';
        $bindings['q2'] = '%' . $filters['q'] . '%';
    }
    if (($filters['category'] ?? 0) > 0) {
        $clauses[] = 'b.id_kategori = :category';
        $bindings['category'] = (int)$filters['category'];
    }
    return [' WHERE ' . implode(' AND ', $clauses), $bindings];
}
function statement(PDO $db, string $sql, array $bindings = []): PDOStatement
{
    $stmt = $db->prepare($sql);
    foreach ($bindings as $key => $val) $stmt->bindValue(':' . $key, $val, ($val === null ? PDO::PARAM_NULL : (is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR)));
    $stmt->execute();
    return $stmt;
}
function cart_products(PDO $db): array
{
    $cart = session_cart();
    if (!$cart) return [];
    $ids = array_filter(array_map('intval', array_keys($cart)), fn($id) => $id > 0);
    if (!$ids) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("SELECT id_buku AS id,judul_buku AS title,penulis AS author,harga AS price,stok AS stock,gambar AS cover_image,1 AS is_active FROM buku WHERE id_buku IN ($placeholders)");
    $stmt->execute(array_values($ids));
    $results = [];
    foreach ($stmt as $book) {
        $book['quantity'] = max(1, (int)($cart[$book['id']] ?? 1));
        $book['subtotal'] = (int)$book['price'] * $book['quantity'];
        $results[] = $book;
    }
    return $results;
}

/** Verifikasi hash PHP modern atau password teks lama dari LENTERA.
 * Password plaintext lama di-upgrade ke hash saat login sukses. */
function legacy_password_matches(string $candidate, string $stored): bool
{
    $info = password_get_info($stored);
    if (($info['algoName'] ?? 'unknown') !== 'unknown') return password_verify($candidate,$stored);
    return hash_equals($stored,$candidate);
}
function password_needs_rehash_safe(string $stored): bool
{
    return ($info = password_get_info($stored))['algoName'] === 'unknown' || password_needs_rehash($stored,PASSWORD_DEFAULT);
}
