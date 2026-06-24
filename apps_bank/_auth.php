<?php
require_once __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_name('AD_' . SISTEM_KODE);
    session_start();
}

function ensure_users_seed(): void {
    if (file_exists(FILE_USERS)) return;
    $seed = [
        ['username'=>'admin','nama'=>'Administrator','email'=>'admin@apps.local','password'=>password_hash('admin', PASSWORD_DEFAULT),'role'=>'admin','dibuat'=>date('Y-m-d H:i:s')],
        ['username'=>'niam','nama'=>'Niam','email'=>'niam@apps.local','password'=>password_hash('niam', PASSWORD_DEFAULT),'role'=>'user','dibuat'=>date('Y-m-d H:i:s')],
        ['username'=>'isna','nama'=>'Isna','email'=>'isna@apps.local','password'=>password_hash('isna', PASSWORD_DEFAULT),'role'=>'user','dibuat'=>date('Y-m-d H:i:s')],
        ['username'=>'linda','nama'=>'Linda','email'=>'linda@apps.local','password'=>password_hash('linda', PASSWORD_DEFAULT),'role'=>'user','dibuat'=>date('Y-m-d H:i:s')],
        ['username'=>'osama','nama'=>'Osama','email'=>'osama@apps.local','password'=>password_hash('osama', PASSWORD_DEFAULT),'role'=>'user','dibuat'=>date('Y-m-d H:i:s')],
    ];
    write_json(FILE_USERS, $seed);
}
ensure_users_seed();

function get_users(): array { return read_json(FILE_USERS); }
function find_user(string $username): ?array {
    foreach (get_users() as $u) if (strcasecmp($u['username'], $username) === 0) return $u;
    return null;
}
function register_user(string $username, string $nama, string $email, string $password): array {
    $username = trim($username); $nama = trim($nama); $email = trim($email);
    if (strlen($username) < 3) return ['success'=>false,'message'=>'Username minimal 3 karakter'];
    if (strlen($password) < 5) return ['success'=>false,'message'=>'Password minimal 5 karakter'];
    if (find_user($username))  return ['success'=>false,'message'=>'Username sudah dipakai'];
    $list = get_users();
    $list[] = [
        'username'=>$username,'nama'=>$nama ?: $username,'email'=>$email,
        'password'=>password_hash($password, PASSWORD_DEFAULT),'role'=>'user',
        'dibuat'=>date('Y-m-d H:i:s'),
    ];
    write_json(FILE_USERS, $list);
    return ['success'=>true,'message'=>'Registrasi sukses'];
}
function login_user(string $username, string $password): array {
    $u = find_user($username);
    if (!$u) return ['success'=>false,'message'=>'Username tidak ditemukan'];
    if (!password_verify($password, $u['password'])) return ['success'=>false,'message'=>'Password salah'];
    session_regenerate_id(true);
    $_SESSION['user'] = ['username'=>$u['username'],'nama'=>$u['nama'],'role'=>$u['role']];
    return ['success'=>true,'message'=>'Login sukses','data'=>$_SESSION['user']];
}
function logout_user(): void { unset($_SESSION['user']); session_destroy(); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return !empty($_SESSION['user']); }
function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }

function require_login(): void {
    if (!is_logged_in()) {
        $back = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . SISTEM_URL . '/login.php?redirect=' . urlencode($back));
        exit;
    }
}
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        echo '<div style="max-width:520px;margin:80px auto;font-family:system-ui;text-align:center"><h2>Akses ditolak</h2><p>Halaman ini khusus admin.</p><p><a href="'.SISTEM_URL.'/index.php">Kembali</a></p></div>';
        exit;
    }
}

function inisial_nama(string $n): string {
    $p = preg_split('/\s+/', trim($n));
    $i = strtoupper(substr($p[0] ?? '?', 0, 1));
    if (isset($p[1])) $i .= strtoupper(substr($p[1], 0, 1));
    return $i ?: '?';
}

function set_flash_msg(string $msg, string $type = 'success'): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash_msgs'][] = ['msg' => $msg, 'type' => $type];
}
function get_flash_msgs(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $msgs = $_SESSION['flash_msgs'] ?? [];
    unset($_SESSION['flash_msgs']);
    return $msgs;
}
