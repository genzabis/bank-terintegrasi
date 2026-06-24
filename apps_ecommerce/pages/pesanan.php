<?php
require_once __DIR__ . '/../_layout.php';
$pesanan = get_pesanan();
layout_start('Riwayat Pesanan', 'Daftar transaksi yang sudah lunas');
?>



<?php foreach ($pesanan as $p): ?>
  <div class="card mb">
    <div class="flex-between mb">
      <div>
        <code><?= htmlspecialchars($p['id']) ?></code>
        <span class="muted small"> · <?= htmlspecialchars($p['tanggal']) ?></span>
      </div>
      <div class="flex gap">
        <span class="badge badge-muted"><?= htmlspecialchars($p['metode'] ?? 'BANK') ?></span>
        <span class="badge badge-success"><?= htmlspecialchars($p['status']) ?></span>
      </div>
    </div>
    <div class="small muted mb">Pembayar: <code><?= htmlspecialchars($p['no_rek']) ?></code></div>
    <div class="card no-pad" style="background:var(--bg-elevated)">
      <table class="table" style="margin:0">
        <thead><tr><th>Produk</th><th>Qty</th><th class="right">Sub</th></tr></thead>
        <tbody>
          <?php foreach (($p['items']??[]) as $it): ?>
            <tr><td><?= htmlspecialchars($it['nama']) ?></td><td><?= (int)$it['qty'] ?></td><td class="right"><?= format_rupiah($it['sub']??$it['harga']*$it['qty']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <?php if (!empty($p['diskon']) && $p['diskon']>0): ?>
            <tr><td colspan="2" class="right muted">Subtotal</td><td class="right"><?= format_rupiah($p['subtotal']??0) ?></td></tr>
            <tr><td colspan="2" class="right muted">Diskon (<?= htmlspecialchars($p['voucher']??'') ?>)</td><td class="right" style="color:var(--success)">-<?= format_rupiah($p['diskon']) ?></td></tr>
          <?php endif; ?>
          <tr><td colspan="2" class="right bold">Total</td><td class="right bold" style="color:var(--accent)"><?= format_rupiah($p['total']) ?></td></tr>
        </tfoot>
      </table>
    </div>
    <?php if (!empty($p['bundle'])): ?>
      <div class="alert warning mt"><i data-lucide="ticket-percent"></i><div>Bundle Tiket Travel: <b><?= htmlspecialchars($p['bundle']['nama']??'-') ?></b> · Kode: <code><?= htmlspecialchars($p['bundle']['kode']??'') ?></code> · Diskon <?= (int)($p['bundle']['diskon']??0) ?>%</div></div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<?php if (!$pesanan): ?>
  <div class="card"><div class="no-data">Belum ada pesanan.</div></div>
<?php endif; ?>

<?php layout_end(); ?>
