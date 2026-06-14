<?php
require_once __DIR__ . '/_layout.php';

$tiket   = get_tiket();
$hotel   = get_hotel();
$paket   = get_paket();
$voucher = get_voucher();
$pesanan = get_pesanan();
$audit   = get_audit(8);

$revenue = array_sum(array_column($pesanan, 'total'));
$bookCount = count($pesanan);

layout_start('Dashboard Travel', 'Tiket, hotel, paket wisata, dan voucher dari sistem lain');
?>

<div class="hero">
  <h2>Petualangan dimulai dari sini</h2>
  <p>Sistem D — AppsTravel · Pemesanan tiket dengan pembayaran AppsBank, voucher diskon dari AppsPendidikan, dan paket bundle dengan AppsEcommerce.</p>
</div>

<div class="stats-grid">
  <div class="stat-card"><div class="label">Tiket Tersedia</div><div class="value"><?= count($tiket) ?></div><div class="delta"><?= array_sum(array_column($tiket,'kuota')) ?> kursi</div></div>
  <div class="stat-card"><div class="label">Hotel Mitra</div><div class="value"><?= count($hotel) ?></div><div class="delta"><?= count($paket) ?> paket wisata</div></div>
  <div class="stat-card"><div class="label">Voucher Aktif</div><div class="value"><?= count($voucher) ?></div><div class="delta"><?= count(array_filter($voucher, fn($v)=>($v['sumber']??'')==='AppsPendidikan')) ?> dari Pendidikan</div></div>
  <div class="stat-card"><div class="label">Total Booking</div><div class="value"><?= $bookCount ?></div><div class="delta"><?= format_rupiah($revenue) ?> revenue</div></div>
</div>

<div class="row cols-2-1 mb-2">
  <div>
    <h3 style="margin:0 0 .75rem; font-size:1rem">Tiket Populer</h3>
    <div class="row cols-2">
      <?php foreach (array_slice($tiket, 0, 4) as $t): $ico = match($t['tipe']??'pesawat'){'kereta'=>'train-front','wisata'=>'palmtree',default=>'plane'}; ?>
        <div class="ticket">
          <div class="flex-between">
            <span class="badge badge-accent"><?= htmlspecialchars($t['tipe']??'tiket') ?></span>
            <span class="rating small flex gap" style="align-items:center"><i data-lucide="star" style="width:11px;height:11px;fill:currentColor;stroke-width:0"></i>4.<?= rand(5,9) ?></span>
          </div>
          <div style="margin:.5rem 0"><i data-lucide="<?= $ico ?>" style="width:32px;height:32px;color:var(--accent);stroke-width:1.5"></i></div>
          <div class="bold"><?= htmlspecialchars($t['nama']) ?></div>
          <div class="small muted"><?= htmlspecialchars($t['rute']??'-') ?></div>
          <div class="flex-between mt">
            <span class="big bold" style="color:var(--accent)"><?= format_rupiah($t['harga']) ?></span>
            <span class="small muted">Kuota <?= (int)$t['kuota'] ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card no-pad">
    <div class="card-header"><h3>Aktivitas Antar Sistem</h3><a href="pages/integrasi.php" class="small muted">lihat semua →</a></div>
    <div class="scroll" style="max-height:380px">
      <?php foreach ($audit as $a): ?>
        <div style="padding:.7rem 1.25rem;border-bottom:1px solid var(--border);font-size:.83rem">
          <div class="flex-between">
            <span class="badge <?= $a['arah']==='IN'?'badge-info':'badge-accent' ?>"><?= $a['arah'] ?></span>
            <span class="muted small"><?= relative_time($a['tanggal']) ?></span>
          </div>
          <div style="margin-top:.3rem"><b><?= htmlspecialchars($a['peer']) ?></b> · <code><?= htmlspecialchars($a['action']) ?></code></div>
          <div class="muted small"><?= htmlspecialchars($a['message']?:'-') ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (!$audit): ?><div class="no-data">Belum ada aktivitas integrasi.</div><?php endif; ?>
    </div>
  </div>
</div>

<div class="card no-pad">
  <div class="card-header"><h3>Booking Terbaru</h3><a href="pages/riwayat.php" class="small muted">selengkapnya →</a></div>
  <table class="table">
    <thead><tr><th>Kode</th><th>Jenis</th><th>Nama</th><th>Pemesan</th><th class="right">Total</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach (array_slice($pesanan, 0, 8) as $p): ?>
        <tr>
          <td><code><?= htmlspecialchars($p['kode']??$p['id']) ?></code></td>
          <td><span class="badge badge-muted"><?= htmlspecialchars($p['jenis']??'TIKET') ?></span></td>
          <td><?= htmlspecialchars($p['nama']) ?></td>
          <td class="small muted"><?= htmlspecialchars($p['pemesan']??'-') ?></td>
          <td class="right bold"><?= format_rupiah($p['total']) ?></td>
          <td><span class="badge badge-success"><?= htmlspecialchars($p['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$pesanan): ?><tr><td colspan="6"><div class="no-data">Belum ada booking.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php layout_end(); ?>
