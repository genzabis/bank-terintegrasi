<?php
require_once __DIR__ . '/../_layout.php';
$tipe = $_GET['tipe'] ?? '';
$tiket = get_tiket();
if ($tipe) $tiket = array_values(array_filter($tiket, fn($t)=>($t['tipe']??'')===$tipe));
$tipeList = array_unique(array_column(get_tiket(),'tipe'));

layout_start('Daftar Tiket', 'Pilih moda transportasi untuk perjalanan Anda');
?>

<div class="chips">
  <a class="chip <?= !$tipe?'active':'' ?>" href="tiket.php">Semua</a>
  <?php foreach ($tipeList as $t): ?>
    <a class="chip <?= $tipe===$t?'active':'' ?>" href="?tipe=<?= urlencode($t) ?>"><?= htmlspecialchars(ucfirst($t)) ?></a>
  <?php endforeach; ?>
</div>

<div class="row cols-3">
  <?php foreach ($tiket as $t): $ico = match($t['tipe']??'pesawat'){'kereta'=>'train-front','wisata'=>'palmtree',default=>'plane'}; ?>
    <div class="ticket">
      <div class="flex-between">
        <span class="badge badge-accent"><?= htmlspecialchars($t['tipe']??'') ?></span>
        <span class="badge badge-muted">Kuota <?= (int)$t['kuota'] ?></span>
      </div>
      <div style="display:grid;place-items:center;margin:.6rem 0"><i data-lucide="<?= $ico ?>" style="width:38px;height:38px;color:var(--accent);stroke-width:1.5"></i></div>
      <h3 style="font-size:1rem;margin:0"><?= htmlspecialchars($t['nama']) ?></h3>
      <div class="small muted"><?= htmlspecialchars($t['rute']??'') ?></div>
      <div class="flex-between mt">
        <span style="font-size:1.2rem;font-weight:700;color:var(--accent)"><?= format_rupiah($t['harga']) ?></span>
        <a class="btn btn-primary btn-sm" href="pesan.php?tiket_id=<?= urlencode($t['id']) ?>">Pesan →</a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$tiket): ?><div class="card" style="grid-column:1/-1"><div class="no-data">Tidak ada tiket sesuai filter.</div></div><?php endif; ?>
</div>

<?php layout_end(); ?>
