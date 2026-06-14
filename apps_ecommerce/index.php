<?php
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'addcart') {
    add_keranjang('guest', $_POST['produk_id'], (int)($_POST['qty'] ?? 1));
    header('Location: pages/keranjang.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'wish') {
    toggle_wishlist('guest', $_POST['produk_id']);
    header('Location: index.php'); exit;
}

$produk    = get_produk();
$kategoriList = array_unique(array_column($produk, 'kategori'));
$kategori  = $_GET['kategori'] ?? '';
$q         = trim($_GET['q'] ?? '');
$sort      = $_GET['sort'] ?? 'baru';

$filtered = $produk;
if ($kategori) $filtered = array_values(array_filter($filtered, fn($p)=>$p['kategori']===$kategori));
if ($q !== '') $filtered = array_values(array_filter($filtered, fn($p)=>stripos($p['nama'],$q)!==false));
if ($sort === 'murah')   usort($filtered, fn($a,$b)=>$a['harga']<=>$b['harga']);
if ($sort === 'mahal')   usort($filtered, fn($a,$b)=>$b['harga']<=>$a['harga']);
if ($sort === 'rating')  usort($filtered, fn($a,$b)=>($b['rating']??0)<=>($a['rating']??0));
if ($sort === 'terjual') usort($filtered, fn($a,$b)=>($b['terjual']??0)<=>($a['terjual']??0));

$wishlist = get_wishlist('guest');
$pesanan  = get_pesanan();
$totalRevenue = array_sum(array_column($pesanan, 'total'));
$totalSold    = array_sum(array_column($produk, 'terjual'));
layout_start('Marketplace', 'Jual produk dengan integrasi langsung ke Bank dan Travel');
?>

<div class="hero">
  <h2>Etalase produk dengan integrasi penuh</h2>
  <p>Sistem B — AppsEcommerce · Pembayaran realtime via AppsBank, produk siswa dari AppsPendidikan otomatis tampil, dan paket bundle dengan tiket AppsTravel.</p>
</div>

<div class="stats-grid">
  <div class="stat-card"><div class="label">Total Produk</div><div class="value"><?= count($produk) ?></div><div class="delta">+<?= count(array_filter($produk, fn($p)=>($p['sumber']??'')!=='INTERNAL')) ?> dari Pendidikan</div></div>
  <div class="stat-card"><div class="label">Total Terjual</div><div class="value"><?= $totalSold ?></div><div class="delta">unit produk</div></div>
  <div class="stat-card"><div class="label">Pesanan</div><div class="value"><?= count($pesanan) ?></div><div class="delta">transaksi</div></div>
  <div class="stat-card"><div class="label">Total Revenue</div><div class="value"><?= format_rupiah($totalRevenue) ?></div><div class="delta">via Bank</div></div>
</div>

<form method="get" class="card mb">
  <div class="row cols-3">
    <div class="field" style="margin:0"><label>Cari produk</label><input class="input" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="nama produk..."></div>
    <div class="field" style="margin:0"><label>Sort</label>
      <select class="input" name="sort">
        <option value="baru"    <?= $sort==='baru'?'selected':'' ?>>Terbaru</option>
        <option value="murah"   <?= $sort==='murah'?'selected':'' ?>>Termurah</option>
        <option value="mahal"   <?= $sort==='mahal'?'selected':'' ?>>Termahal</option>
        <option value="rating"  <?= $sort==='rating'?'selected':'' ?>>Rating tertinggi</option>
        <option value="terjual" <?= $sort==='terjual'?'selected':'' ?>>Paling laris</option>
      </select>
    </div>
    <div class="field" style="margin:0;display:flex;align-items:flex-end;gap:.5rem">
      <button class="btn btn-primary" style="flex:1"><i data-lucide="search"></i>Cari</button>
      <a class="btn btn-ghost" href="index.php">Reset</a>
    </div>
  </div>
</form>

<div class="chips">
  <a class="chip <?= !$kategori?'active':'' ?>" href="?<?= http_build_query(['q'=>$q,'sort'=>$sort]) ?>">Semua kategori</a>
  <?php foreach ($kategoriList as $k): ?>
    <a class="chip <?= $kategori===$k?'active':'' ?>" href="?<?= http_build_query(['kategori'=>$k,'q'=>$q,'sort'=>$sort]) ?>"><?= htmlspecialchars($k) ?></a>
  <?php endforeach; ?>
</div>

<div class="tile-grid">
  <?php foreach ($filtered as $p): ?>
    <?php
      $iconCat = match($p['kategori']) {
        'siswa'      => 'graduation-cap',
        'fashion'    => 'shirt',
        'makanan'    => 'utensils',
        'elektronik' => 'headphones',
        default      => 'package',
      };
      $isWish = in_array($p['id'], $wishlist);
    ?>
    <div class="tile">
      <div class="img-area" style="position:relative">
        <i data-lucide="<?= $iconCat ?>"></i>
        <form method="post" style="position:absolute;top:.5rem;right:.5rem">
          <input type="hidden" name="act" value="wish">
          <input type="hidden" name="produk_id" value="<?= htmlspecialchars($p['id']) ?>">
          <button type="submit" class="btn btn-sm btn-ghost" style="background:#fff;border:1px solid var(--border);padding:.35rem;border-radius:50%;color:<?= $isWish?'var(--danger)':'var(--text-mute)' ?>" aria-label="wishlist"><i data-lucide="heart" style="<?= $isWish?'fill:currentColor':'' ?>"></i></button>
        </form>
      </div>
      <div class="body">
        <h4><?= htmlspecialchars($p['nama']) ?></h4>
        <div class="small muted"><?= htmlspecialchars($p['deskripsi'] ?? '') ?></div>
        <div class="flex-between">
          <span class="small rating flex gap" style="align-items:center"><i data-lucide="star" style="width:13px;height:13px;fill:currentColor;stroke-width:0"></i><?= number_format($p['rating']??0,1) ?></span>
          <span class="small muted"><?= (int)($p['terjual']??0) ?> terjual</span>
        </div>
        <div class="flex-between">
          <span class="price"><?= format_rupiah($p['harga']) ?></span>
          <span class="badge badge-muted">stok <?= (int)$p['stok'] ?></span>
        </div>
        <span class="badge <?= ($p['sumber']??'INTERNAL')==='INTERNAL'?'badge-muted':'badge-info' ?>"><?= htmlspecialchars($p['sumber']??'INTERNAL') ?></span>
        <form method="post" style="margin-top:auto">
          <input type="hidden" name="act" value="addcart">
          <input type="hidden" name="produk_id" value="<?= htmlspecialchars($p['id']) ?>">
          <div class="input-group">
            <input class="input" type="number" name="qty" value="1" min="1" max="<?= (int)$p['stok'] ?>" style="width:70px">
            <button class="btn btn-primary" style="flex:1" <?= $p['stok']<=0?'disabled':'' ?>><i data-lucide="shopping-cart"></i>Cart</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$filtered): ?>
    <div class="card" style="grid-column:1/-1"><div class="no-data">Tidak ada produk yang cocok.</div></div>
  <?php endif; ?>
</div>

<?php layout_end(); ?>
