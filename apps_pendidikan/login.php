<?php
require_once __DIR__ . '/_auth.php';

if (is_logged_in()) { header('Location: ' . SISTEM_URL . '/index.php'); exit; }

$err = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '/index.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = login_user($_POST['username'] ?? '', $_POST['password'] ?? '');
    if (!empty($r['success'])) { header('Location: ' . SISTEM_URL . $redirect); exit; }
    $err = $r['message'];
}
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk · <?= SISTEM_NAMA ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SISTEM_URL ?>/style.css">
</head><body class="auth-body">
<div class="auth-shell">
  <div class="auth-side">
    <div class="auth-side-inner">
      <div class="brand-lg">
        <div class="logo-lg"><?= SISTEM_KODE ?></div>
        <div><div class="bold" style="font-size:1.15rem"><?= SISTEM_NAMA ?></div><div class="small" style="opacity:.85">Distributed Integration System</div></div>
      </div>
      <h1 style="font-size:1.85rem;line-height:1.2;margin:1.25rem 0 .65rem;letter-spacing:-.5px">Selamat datang kembali</h1>
      <p style="opacity:.9;line-height:1.55;max-width:380px">Masuk untuk akses penuh ke <?= SISTEM_NAMA ?>. Sistem ini terhubung langsung ke 3 aplikasi lain via REST API: Bank, Ecommerce, Pendidikan, Travel.</p>
      <div class="auth-tags mt-2">
        <span class="auth-tag">REST API</span>
        <span class="auth-tag">Full Mesh</span>
        <span class="auth-tag">JSON Payload</span>
        <span class="auth-tag">Realtime</span>
      </div>
    </div>
  </div>

  <div class="auth-form">
    <div class="auth-form-inner">
      <h2 style="font-size:1.45rem;margin:0 0 .35rem">Masuk</h2>
      <p class="muted" style="margin:0 0 1.5rem">Belum punya akun? <a href="register.php">Daftar dulu</a></p>

      <?php if ($err): ?><div class="alert danger"><i data-lucide="alert-circle"></i><div><?= htmlspecialchars($err) ?></div></div><?php endif; ?>

      <form method="post">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <div class="field"><label>Username</label><input class="input" name="username" required autofocus placeholder="admin atau user"></div>
        <div class="field"><label>Password</label><input class="input" type="password" name="password" required placeholder="••••••••"></div>
        <button class="btn btn-primary btn-block btn-lg"><i data-lucide="log-in"></i>Masuk</button>
      </form>

      <div class="card-soft mt-2">
        <div class="bold small mb">Akun demo</div>
        <div class="small muted">Admin: <code>admin</code> / <code>admin123</code></div>
        <div class="small muted">User: <code>user</code> / <code>user123</code></div>
      </div>
    </div>
  </div>
</div>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>if(window.lucide) lucide.createIcons();</script>
</body></html>
