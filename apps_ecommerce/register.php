<?php
require_once __DIR__ . '/_auth.php';
if (is_logged_in()) { header('Location: ' . SISTEM_URL . '/index.php'); exit; }

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = register_user($_POST['username']??'', $_POST['nama']??'', $_POST['email']??'', $_POST['password']??'');
    if (!empty($r['success'])) {
        set_flash_msg('Registrasi sukses, silakan masuk', 'success');
        login_user($_POST['username'], $_POST['password']);
        header('Location: ' . SISTEM_URL . '/index.php'); exit;
    }
    $msg = $r['message']; $cls = 'danger';
}
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar · <?= SISTEM_NAMA ?></title>
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
      <h1 style="font-size:1.85rem;line-height:1.2;margin:1.25rem 0 .65rem;letter-spacing:-.5px">Buat akun baru</h1>
      <p style="opacity:.9;line-height:1.55;max-width:380px">Daftar gratis untuk akses semua fitur <?= SISTEM_NAMA ?>. Tugas Komputasi Paralel dan Terdistribusi.</p>
    </div>
  </div>
  <div class="auth-form">
    <div class="auth-form-inner">
      <h2 style="font-size:1.45rem;margin:0 0 .35rem">Daftar</h2>
      <p class="muted" style="margin:0 0 1.5rem">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>

      <?php if ($msg): ?><div class="alert <?= $cls ?>"><i data-lucide="alert-circle"></i><div><?= htmlspecialchars($msg) ?></div></div><?php endif; ?>

      <form method="post">
        <div class="field"><label>Nama Lengkap</label><input class="input" name="nama" required placeholder="Nama Anda"></div>
        <div class="row cols-2">
          <div class="field"><label>Username</label><input class="input" name="username" required minlength="3" placeholder="minimal 3 karakter"></div>
          <div class="field"><label>Email</label><input class="input" type="email" name="email" placeholder="opsional"></div>
        </div>
        <div class="field"><label>Password</label><input class="input" type="password" name="password" required minlength="5" placeholder="minimal 5 karakter"></div>
        <button class="btn btn-primary btn-block btn-lg"><i data-lucide="user-plus"></i>Daftar Sekarang</button>
      </form>
    </div>
  </div>
</div>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>if(window.lucide) lucide.createIcons();</script>
</body></html>
