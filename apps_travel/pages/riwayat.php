<?php
require_once __DIR__ . '/../_layout.php';

$jenis = $_GET['jenis'] ?? '';
$pesanan = get_pesanan($jenis ?: null);

layout_start('Riwayat Booking', 'Daftar transaksi semua jenis: tiket, hotel, paket, bundle');
?>

<div class="chips">
  <a class="chip <?= !$jenis?'active':'' ?>" href="riwayat.php">Semua</a>
  <a class="chip <?= $jenis==='TIKET'?'active':'' ?>"  href="?jenis=TIKET">Tiket</a>
  <a class="chip <?= $jenis==='HOTEL'?'active':'' ?>"  href="?jenis=HOTEL">Hotel</a>
  <a class="chip <?= $jenis==='PAKET'?'active':'' ?>"  href="?jenis=PAKET">Paket</a>
  <a class="chip <?= $jenis==='BUNDLE'?'active':'' ?>" href="?jenis=BUNDLE">Bundle</a>
</div>

<div class="card no-pad">
  <table class="table">
    <thead><tr><th>Tanggal</th><th>Kode</th><th>Jenis</th><th>Detail</th><th>Pemesan</th><th>Voucher</th><th class="right">Total</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($pesanan as $p): ?>
        <tr>
          <td class="small muted"><?= htmlspecialchars($p['tanggal']) ?></td>
          <td><code><?= htmlspecialchars($p['kode']??$p['id']) ?></code></td>
          <td><span class="badge badge-accent"><?= htmlspecialchars($p['jenis']??'TIKET') ?></span></td>
          <td><b><?= htmlspecialchars($p['nama']) ?></b><?php if (!empty($p['qty'])): ?> <span class="small muted">x<?= (int)$p['qty'] ?></span><?php endif; ?>
            <?php if (!empty($p['malam'])): ?> <span class="small muted"><?= (int)$p['malam'] ?> malam</span><?php endif; ?>
          </td>
          <td><?= htmlspecialchars($p['pemesan']??'-') ?></td>
          <td><?= !empty($p['voucher'])?'<code>'.htmlspecialchars($p['voucher']).'</code>':'-' ?></td>
          <td class="right bold"><?= format_rupiah($p['total']) ?></td>
          <td><span class="badge <?= $p['status']==='LUNAS'?'badge-success':'badge-warning' ?>"><?= htmlspecialchars($p['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$pesanan): ?><tr><td colspan="8"><div class="no-data">Belum ada booking.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php layout_end(); ?>
