<?php
require_once __DIR__ . '/_auth.php';
if (is_logged_in()) { header('Location: ' . SISTEM_URL . '/index.php'); exit; }

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = register_user($_POST['username']??'', $_POST['nama']??'', $_POST['email']??'', $_POST['password']??'');
    if (!empty($r['success'])) {
        login_user($_POST['username'], $_POST['password']);
        header('Location: ' . SISTEM_URL . '/index.php'); exit;
    }
    $msg = $r['message']; $cls = 'danger';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar · <?= SISTEM_NAMA ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SISTEM_URL ?>/style.css">
</head>
<body class="auth-page">

  <div class="auth-wrapper">
    <div class="auth-header">
      <img src="<?= SISTEM_URL ?>/img/logo.png" alt="Logo" class="auth-logo">
      <h1 class="auth-title">Buat akun baru</h1>
      <p class="auth-subtitle">Daftar gratis untuk akses <?= SISTEM_NAMA ?></p>
    </div>

    <div class="auth-card">
      <?php if ($msg): ?>
        <div class="alert <?= $cls ?>"><i data-lucide="alert-circle"></i><div><?= htmlspecialchars($msg) ?></div></div>
      <?php endif; ?>

      <form method="post">
        <div class="auth-field">
          <label for="nama">Nama Lengkap</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" id="nama" name="nama" class="auth-input has-icon" required placeholder="Nama Anda">
          </div>
        </div>

        <div class="auth-row">
          <div class="auth-field">
            <label for="username">Username</label>
            <div class="input-icon-wrap">
              <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 3H5a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2V8.5L15.5 3Z"/><path d="M14 3v4a2 2 0 0 0 2 2h4"/></svg>
              <input type="text" id="username" name="username" class="auth-input has-icon" required minlength="3" placeholder="min 3 karakter">
            </div>
          </div>
          <div class="auth-field">
            <label for="email">Email</label>
            <div class="input-icon-wrap">
              <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              <input type="email" id="email" name="email" class="auth-input has-icon" placeholder="opsional">
            </div>
          </div>
        </div>

        <div class="auth-field">
          <label for="password">Password</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" id="password" name="password" class="auth-input has-icon has-toggle" required minlength="5" placeholder="min 5 karakter">
            <button type="button" class="toggle-pw" onclick="togglePassword('password', this)" aria-label="Tampilkan password">
              <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-closed" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="auth-btn">Daftar Sekarang</button>
      </form>
    </div>

    <p class="auth-footer">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
  </div>

<script>
function togglePassword(id, btn) {
  var input = document.getElementById(id);
  var open = btn.querySelector('.eye-open');
  var closed = btn.querySelector('.eye-closed');
  if (input.type === 'password') {
    input.type = 'text';
    open.style.display = 'none';
    closed.style.display = 'block';
  } else {
    input.type = 'password';
    open.style.display = 'block';
    closed.style.display = 'none';
  }
}
</script>
</body>
</html>
