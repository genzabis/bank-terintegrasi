<?php
require_once __DIR__ . '/_auth.php';
require_login();

function layout_start(string $title, string $subtitle = '', bool $admin_only = false): void {
    if ($admin_only) require_admin();
    $cur = basename($_SERVER['SCRIPT_NAME']);
    $peers = function_exists('ping_peers') ? ping_peers() : [];
    $u = current_user();
    $cartCount = function_exists('get_keranjang') ? count(get_keranjang($u['username'] ?? 'guest')) : 0;
    ?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($title) ?> · <?= SISTEM_NAMA ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SISTEM_URL ?>/style.css">
</head><body>
<div class="app-shell" id="app">
  <div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>
  <aside class="sidebar">
    <div class="brand">
      <div class="logo"><?= SISTEM_KODE ?></div>
      <div><h1><?= SISTEM_NAMA ?></h1><small>Sistem B · :<?= SISTEM_PORT ?></small></div>
      <button class="sidebar-close" onclick="document.body.classList.remove('sidebar-open')" aria-label="close"><i data-lucide="x"></i></button>
    </div>

    <div class="nav-section">
      <div class="nav-title">Marketplace</div>
      <a class="nav-link <?= $cur==='index.php'?'active':'' ?>"     href="<?= SISTEM_URL ?>/index.php"><i data-lucide="store"></i>Beranda</a>
      <a class="nav-link <?= $cur==='keranjang.php'?'active':'' ?>" href="<?= SISTEM_URL ?>/pages/keranjang.php"><i data-lucide="shopping-cart"></i>Keranjang <?php if($cartCount): ?><span class="badge badge-accent" style="margin-left:auto"><?= $cartCount ?></span><?php endif; ?></a>
      <a class="nav-link <?= $cur==='checkout.php'?'active':'' ?>"  href="<?= SISTEM_URL ?>/pages/checkout.php"><i data-lucide="credit-card"></i>Checkout</a>
      <a class="nav-link <?= $cur==='wishlist.php'?'active':'' ?>"  href="<?= SISTEM_URL ?>/pages/wishlist.php"><i data-lucide="heart"></i>Wishlist</a>
      <a class="nav-link <?= $cur==='pesanan.php'?'active':'' ?>"   href="<?= SISTEM_URL ?>/pages/pesanan.php"><i data-lucide="package"></i>Pesanan</a>
    </div>

    <div class="nav-section">
      <div class="nav-title">Integrasi</div>
      <a class="nav-link <?= $cur==='bundle.php'?'active':'' ?>"    href="<?= SISTEM_URL ?>/pages/bundle.php"><i data-lucide="box"></i>Bundle Travel</a>
      <a class="nav-link <?= $cur==='integrasi.php'?'active':'' ?>" href="<?= SISTEM_URL ?>/pages/integrasi.php"><i data-lucide="git-branch"></i>Monitor Integrasi</a>
    </div>

    <?php if (is_admin()): ?>
    <div class="nav-section">
      <div class="nav-title">Admin</div>
      <a class="nav-link <?= $cur==='users.php'?'active':'' ?>" href="<?= SISTEM_URL ?>/pages/users.php"><i data-lucide="shield-check"></i>Kelola User</a>
    </div>
    <?php endif; ?>

    <div class="nav-section">
      <div class="nav-title">Sistem Lain</div>
      <a class="nav-link ext" target="_blank" href="<?= BANK_URL ?>"><i data-lucide="landmark"></i>Bank <span class="dot <?= ($peers['AppsBank']['up']??false)?'up':'down' ?>"></span></a>
      <a class="nav-link ext" target="_blank" href="<?= PENDIDIKAN_URL ?>"><i data-lucide="graduation-cap"></i>Pendidikan <span class="dot <?= ($peers['AppsPendidikan']['up']??false)?'up':'down' ?>"></span></a>
      <a class="nav-link ext" target="_blank" href="<?= TRAVEL_URL ?>"><i data-lucide="plane"></i>Travel <span class="dot <?= ($peers['AppsTravel']['up']??false)?'up':'down' ?>"></span></a>
    </div>

    <div class="user-card">
      <div class="avatar"><?= inisial_nama($u['nama']) ?></div>
      <div style="flex:1;min-width:0">
        <div class="bold small" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($u['nama']) ?></div>
        <div class="muted" style="font-size:.7rem"><?= htmlspecialchars($u['username']) ?> · <?= $u['role']==='admin'?'<span class="badge badge-accent" style="padding:1px 5px;font-size:.6rem">ADMIN</span>':'user' ?></div>
      </div>
      <a href="<?= SISTEM_URL ?>/logout.php" class="icon-btn" title="Logout"><i data-lucide="log-out"></i></a>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <button class="hamburger" onclick="document.body.classList.add('sidebar-open')" aria-label="menu"><i data-lucide="menu"></i></button>
      <div style="flex:1;min-width:0">
        <h2><?= htmlspecialchars($title) ?></h2>
        <?php if ($subtitle): ?><div class="subtitle"><?= htmlspecialchars($subtitle) ?></div><?php endif; ?>
      </div>
      <div class="actions">
        <span class="pulse-wrap small muted hide-sm"><span class="pulse"></span>Sistem B · :<?= SISTEM_PORT ?></span>
        <a href="<?= SISTEM_URL ?>/logout.php" class="btn btn-ghost btn-sm hide-md"><i data-lucide="log-out"></i>Keluar</a>
      </div>
    </div>
<?php
}
function layout_end(): void { ?>
  </main>
</div>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>if(window.lucide) lucide.createIcons();</script>
</body></html>
<?php }
