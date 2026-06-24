<?php
require_once __DIR__ . '/../_layout.php';

$id = $_GET['id'] ?? '';
$p = $id ? find_produk($id) : null;
$reviews = $p ? get_review($p['id']) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'review' && $p) {
    add_review($p['id'], trim($_POST['user'] ?: 'Tamu'), (int)$_POST['star'], trim($_POST['komentar']));
    set_flash_msg('Ulasan berhasil dikirim', 'success');
    header('Location: produk.php?id='.urlencode($p['id'])); exit;
}

layout_start('Detail Produk', $p ? $p['nama'] : '');
?>

<?php if (!$p): ?>
  <div class="alert warning">Produk tidak ditemukan. <a href="../index.php">Kembali ke etalase →</a></div>
<?php else:
    $iconCat = match($p['kategori']) {
        'siswa'=>'graduation-cap','fashion'=>'shirt','makanan'=>'utensils','elektronik'=>'headphones',default=>'package'
    };
?>
  <div class="row cols-1-2">
    <div class="card no-pad">
      <div class="img-area" style="height:340px;background:linear-gradient(135deg, var(--accent-soft), #fff);display:grid;place-items:center;border-bottom:1px solid var(--border)"><i data-lucide="<?= $iconCat ?>" style="width:90px;height:90px;color:var(--accent);stroke-width:1.3;opacity:.75"></i></div>
    </div>
    <div class="card">
      <span class="badge badge-muted"><?= htmlspecialchars($p['kategori']) ?></span>
      <h2 style="margin:.5rem 0;font-size:1.5rem"><?= htmlspecialchars($p['nama']) ?></h2>
      <div class="flex gap small" style="margin-bottom:.6rem">
        <span class="rating flex gap" style="align-items:center"><i data-lucide="star" style="width:14px;height:14px;fill:currentColor;stroke-width:0"></i><?= number_format($p['rating']??0,1) ?></span>
        <span class="muted">·</span>
        <span class="muted"><?= (int)($p['terjual']??0) ?> terjual</span>
        <span class="muted">·</span>
        <span class="muted">Stok <?= (int)$p['stok'] ?></span>
      </div>
      <p class="muted"><?= htmlspecialchars($p['deskripsi'] ?? '') ?></p>
      <div style="font-size:1.6rem" class="price bold"><?= format_rupiah($p['harga']) ?></div>
      <div class="mt small muted">Sumber: <span class="badge <?= ($p['sumber']??'INTERNAL')==='INTERNAL'?'badge-muted':'badge-info' ?>"><?= htmlspecialchars($p['sumber']??'INTERNAL') ?></span></div>
      <form method="post" action="../index.php" class="mt">
        <input type="hidden" name="act" value="addcart">
        <input type="hidden" name="produk_id" value="<?= htmlspecialchars($p['id']) ?>">
        <div class="input-group">
          <input class="input" type="number" name="qty" value="1" min="1" max="<?= (int)$p['stok'] ?>" style="width:90px">
          <button class="btn btn-primary" style="flex:1">+ Tambah ke Keranjang</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card mt-2 no-pad">
    <div class="card-header"><h3>Ulasan Pembeli</h3><span class="badge badge-muted"><?= count($reviews) ?> review</span></div>
    <div style="padding:1.25rem 1.5rem">
      <?php foreach ($reviews as $r): ?>
        <div style="border-bottom:1px solid var(--border);padding:.65rem 0">
          <div class="flex-between">
            <div><b><?= htmlspecialchars($r['user']) ?></b> <span class="rating small flex gap" style="align-items:center;display:inline-flex"><i data-lucide="star" style="width:11px;height:11px;fill:currentColor;stroke-width:0"></i><?= $r['star'] ?>/5</span></div>
            <span class="muted small"><?= htmlspecialchars($r['tanggal']) ?></span>
          </div>
          <div class="small"><?= htmlspecialchars($r['komentar']) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (!$reviews): ?><div class="no-data">Belum ada ulasan. Jadilah yang pertama!</div><?php endif; ?>

      <form method="post" class="mt-2">
        <input type="hidden" name="act" value="review">
        <div class="row cols-2">
          <div class="field"><label>Nama</label><input class="input" name="user" placeholder="Nama Anda"></div>
          <div class="field"><label>Bintang</label>
            <select class="input" name="star">
              <option value="5">★★★★★ (5)</option>
              <option value="4">★★★★ (4)</option>
              <option value="3">★★★ (3)</option>
              <option value="2">★★ (2)</option>
              <option value="1">★ (1)</option>
            </select>
          </div>
        </div>
        <div class="field"><label>Komentar</label><textarea class="input" name="komentar" rows="2" required></textarea></div>
        <button class="btn btn-primary">Kirim Ulasan</button>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php layout_end(); ?>
