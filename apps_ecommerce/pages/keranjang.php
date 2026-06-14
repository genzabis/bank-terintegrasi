<?php
require_once __DIR__ . '/../_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'remove') {
    hapus_item_keranjang('guest', $_POST['produk_id']);
    header('Location: keranjang.php'); exit;
}

$cart = get_keranjang('guest');
$rows = []; $total = 0;
foreach ($cart as $it) {
    $p = find_produk($it['produk_id']);
    if (!$p) continue;
    $sub = $p['harga']*$it['qty']; $total += $sub;
    $rows[] = ['p'=>$p,'qty'=>$it['qty'],'sub'=>$sub];
}

layout_start('Keranjang Belanja', 'Periksa ulang item sebelum checkout');
?>

<div class="row cols-3-1">
  <div class="card no-pad">
    <div class="card-header"><h3>Item di Keranjang</h3><span class="badge badge-muted"><?= count($rows) ?> item</span></div>
    <table class="table">
      <thead><tr><th>Produk</th><th class="right">Harga</th><th>Qty</th><th class="right">Subtotal</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><b><?= htmlspecialchars($r['p']['nama']) ?></b><div class="small muted"><?= htmlspecialchars($r['p']['kategori']) ?> · <?= htmlspecialchars($r['p']['sumber']??'INTERNAL') ?></div></td>
            <td class="right"><?= format_rupiah($r['p']['harga']) ?></td>
            <td><?= (int)$r['qty'] ?></td>
            <td class="right bold"><?= format_rupiah($r['sub']) ?></td>
            <td class="right">
              <form method="post" onsubmit="return confirm('Hapus item?')">
                <input type="hidden" name="act" value="remove">
                <input type="hidden" name="produk_id" value="<?= htmlspecialchars($r['p']['id']) ?>">
                <button class="btn btn-sm btn-ghost">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5"><div class="no-data">Keranjang kosong. <a href="../index.php">Belanja sekarang →</a></div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header"><h3>Ringkasan</h3></div>
    <div class="flex-between mb"><span class="muted">Subtotal</span><b><?= format_rupiah($total) ?></b></div>
    <div class="flex-between mb"><span class="muted">Pengiriman</span><b>—</b></div>
    <hr style="border:0;border-top:1px solid var(--border);margin:1rem 0">
    <div class="flex-between mb-2"><span class="bold">Total</span><b style="font-size:1.3rem;color:var(--accent)"><?= format_rupiah($total) ?></b></div>
    <a class="btn btn-primary btn-block" href="checkout.php" <?= $rows?'':'style="pointer-events:none;opacity:.45"' ?>>Lanjut ke Checkout →</a>
  </div>
</div>

<?php layout_end(); ?>
