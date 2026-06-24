<?php
require_once __DIR__ . '/../_layout.php';

$uname = current_user()['username'] ?? 'guest';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    toggle_wishlist($uname, $_POST['produk_id']);
    set_flash_msg('Wishlist diperbarui', 'info');
    header('Location: wishlist.php'); exit;
}
$wishIds = get_wishlist($uname);
$produk  = get_produk();
$items   = array_values(array_filter($produk, fn($p)=>in_array($p['id'], $wishIds)));

layout_start('Wishlist', 'Produk-produk yang Anda sukai');
?>

<?php if (!$items): ?>
  <div class="card"><div class="no-data flex" style="flex-direction:column;align-items:center;gap:.5rem"><i data-lucide="heart" class="icon-xl" style="color:var(--text-mute)"></i><div>Belum ada produk di wishlist. Klik ikon hati pada produk di etalase untuk menambahkan.</div></div></div>
<?php else: ?>
  <div class="tile-grid">
    <?php foreach ($items as $p): $iconCat = match($p['kategori']??'lain'){'siswa'=>'graduation-cap','fashion'=>'shirt','makanan'=>'utensils','elektronik'=>'headphones',default=>'package'}; ?>
      <div class="tile">
        <div class="img-area"><i data-lucide="<?= $iconCat ?>"></i></div>
        <div class="body">
          <h4><?= htmlspecialchars($p['nama']) ?></h4>
          <div class="flex-between">
            <span class="price"><?= format_rupiah($p['harga']) ?></span>
            <span class="small rating flex gap" style="align-items:center"><i data-lucide="star" style="width:13px;height:13px;fill:currentColor;stroke-width:0"></i><?= number_format($p['rating']??0,1) ?></span>
          </div>
          <form method="post" style="margin-top:auto">
            <input type="hidden" name="produk_id" value="<?= htmlspecialchars($p['id']) ?>">
            <button class="btn btn-danger btn-block btn-sm"><i data-lucide="trash-2"></i>Hapus dari Wishlist</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php layout_end(); ?>
