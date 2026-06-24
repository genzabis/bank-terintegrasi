<?php
require_once __DIR__ . '/_auth.php';

if (is_logged_in()) { header('Location: ' . SISTEM_URL . '/index.php'); exit; }

$err = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '/index.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = login_user($_POST['username'] ?? '', $_POST['password'] ?? '');
    if (!empty($r['success'])) { set_flash_msg('Login sukses, selamat datang!', 'success'); header('Location: ' . SISTEM_URL . $redirect); exit; }
    $err = $r['message'];
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Masuk · <?= SISTEM_NAMA ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SISTEM_URL ?>/style.css">
</head>
<body class="auth-page">

  <div class="auth-wrapper">
    <div class="auth-header">
      <img src="<?= SISTEM_URL ?>/img/logo.png" alt="Logo" class="auth-logo">
      <h1 class="auth-title">Masuk ke <?= SISTEM_NAMA ?></h1>
      <p class="auth-subtitle">Kelola ekosistem bank terdistribusi Anda</p>
    </div>

    <div class="auth-card">
      <?php if ($err): ?>
        <div class="alert danger"><i data-lucide="alert-circle"></i><div><?= htmlspecialchars($err) ?></div></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <div class="auth-field">
          <label for="username">Username</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" id="username" name="username" class="auth-input has-icon" required autofocus placeholder="Masukkan username">
          </div>
        </div>

        <div class="auth-field">
          <label for="password">Password</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" id="password" name="password" class="auth-input has-icon has-toggle" required placeholder="Masukkan password">
            <button type="button" class="toggle-pw" onclick="togglePassword('password', this)" aria-label="Tampilkan password">
              <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="eye-closed" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="auth-btn">Masuk</button>
      </form>
    </div>

    <p class="auth-footer">Belum punya akun? <a href="register.php">Daftar dulu</a></p>
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
