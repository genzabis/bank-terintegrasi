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
<?php
$flashes = function_exists('get_flash_msgs') ? get_flash_msgs() : [];
if ($flashes):
?>
<div class="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:12px;">
  <?php foreach ($flashes as $f): 
      $icon = 'info';
      if ($f['type'] === 'success') $icon = 'check-circle-2';
      if ($f['type'] === 'danger') $icon = 'alert-triangle';
      if ($f['type'] === 'warning') $icon = 'alert-circle';
  ?>
    <div class="alert <?= htmlspecialchars($f['type']) ?> toast" style="margin:0; padding:1.1rem 1.25rem; font-size:.95rem; box-shadow:0 12px 35px rgba(0,0,0,0.15); border-left: 5px solid currentColor; animation:slide-in 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; min-width: 300px; display: flex; gap: 0.8rem; align-items: center; border-radius: 8px; background: #fff;">
      <i data-lucide="<?= $icon ?>" style="min-width:24px;height:24px;color:currentColor;"></i>
      <span style="font-weight:600; color:var(--text); line-height:1.4;"><?= htmlspecialchars($f['msg']) ?></span>
    </div>
  <?php endforeach; ?>
</div>
<script>
setTimeout(() => {
  document.querySelectorAll('.toast').forEach(t => {
    t.style.opacity = '0';
    t.style.transform = 'translateX(120%)';
    t.style.transition = 'all 0.5s ease-in';
    setTimeout(() => t.remove(), 500);
  });
}, 4500);
</script>
<style>
@keyframes slide-in { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>
<?php endif; ?>
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
<!-- Modal PIN Pembayaran -->
<div id="pinModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
  <form id="pinForm" onsubmit="event.preventDefault(); verifyPin();" style="background:#fff; padding:2rem; border-radius:12px; width:100%; max-width:320px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
    <h3 style="margin-top:0; margin-bottom:.5rem;">Verifikasi Keamanan</h3>
    <p class="small muted" style="margin-bottom:1rem;">Masukkan PIN transaksi Anda untuk melanjutkan pembayaran.</p>
    <div id="pinError" class="small" style="color:var(--danger); display:none; margin-bottom:.5rem;">PIN yang dimasukkan salah!</div>
    
    <div style="position:relative; margin-bottom:1rem;">
      <input type="password" id="pinInput" class="input" placeholder="Masukkan PIN (12345)" style="text-align:center; font-size:1.2rem; letter-spacing:0.2em; font-family:monospace; width:100%; padding-right:2.5rem;" maxlength="6" autocomplete="off">
      <button type="button" onclick="togglePin()" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#888;"><i data-lucide="eye" id="pinEyeIcon" style="width:18px;height:18px;"></i></button>
    </div>

    <div style="display:flex; gap:.5rem;">
      <button type="button" class="btn" style="flex:1" onclick="closePinModal()">Batal</button>
      <button type="submit" class="btn btn-primary" style="flex:1">Konfirmasi</button>
    </div>
  </form>
</div>

<script>
let pendingFormToSubmit = null;

document.addEventListener('submit', function(e) {
  const form = e.target;
  if (!form || form.tagName !== 'FORM') return;
  if (form.id === 'pinForm') return;
  
  const actionStr = form.action || '';
  const isPaymentForm = actionStr.includes('checkout') || 
                        actionStr.includes('pesan_tiket') || 
                        actionStr.includes('bayar') || 
                        actionStr.includes('transfer') || 
                        form.querySelector('select[name="no_rek"]') ||
                        form.querySelector('input[name="no_rek"]');
                        
  if (isPaymentForm) {
    if (form.dataset.pinVerified === 'true') return; // let it pass
    
    e.preventDefault();
    pendingFormToSubmit = form;
    document.getElementById('pinModal').style.display = 'flex';
    document.getElementById('pinError').style.display = 'none';
    document.getElementById('pinInput').value = '';
    setTimeout(() => document.getElementById('pinInput').focus(), 50);
  }
});

function togglePin() {
  const input = document.getElementById('pinInput');
  const icon = document.getElementById('pinEyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.setAttribute('data-lucide', 'eye-off');
  } else {
    input.type = 'password';
    icon.setAttribute('data-lucide', 'eye');
  }
  if(window.lucide) lucide.createIcons();
}

function closePinModal() {
  document.getElementById('pinModal').style.display = 'none';
  pendingFormToSubmit = null;
}

function verifyPin() {
  const pin = document.getElementById('pinInput').value;
  if (pin === '12345') {
    const form = pendingFormToSubmit;
    closePinModal();
    if (form) {
      form.dataset.pinVerified = 'true';
      HTMLFormElement.prototype.submit.call(form);
    }
    pendingFormToSubmit = null;
  } else {
    document.getElementById('pinError').style.display = 'block';
    document.getElementById('pinInput').value = '';
    document.getElementById('pinInput').focus();
  }
}
</script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>if(window.lucide) lucide.createIcons();</script>
</body></html>
<?php }