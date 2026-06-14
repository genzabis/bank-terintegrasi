<?php
require_once __DIR__ . '/../_layout.php';

$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $r = add_voucher([
        'kode'=>$_POST['kode'],'persen'=>(int)$_POST['persen'],
        'untuk'=>$_POST['untuk']??null,'sumber'=>'INTERNAL',
    ]);
    $msg = !empty($r['success']) ? 'Voucher berhasil ditambahkan' : 'Gagal: '.($r['message']??'');
}
$voucher = get_voucher();

layout_start('Manajemen Voucher', 'Voucher diskon yang berlaku · termasuk kiriman dari AppsPendidikan');
?>

<?php if ($msg): ?><div class="alert info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2-1">
  <div>
    <div class="row cols-2">
      <?php foreach ($voucher as $v): ?>
        <div class="voucher">
          <div>
            <div class="flex gap" style="align-items:center"><span class="badge <?= ($v['sumber']??'INTERNAL')==='AppsPendidikan'?'badge-info':'badge-muted' ?>"><?= htmlspecialchars($v['sumber']??'-') ?></span><span class="muted small">dipakai <?= (int)($v['dipakai']??0) ?>x</span></div>
            <div class="kode mt"><?= htmlspecialchars($v['kode']) ?></div>
            <div class="small muted"><?= htmlspecialchars($v['untuk']??'Semua pengguna') ?></div>
          </div>
          <div class="pct"><?= (int)$v['persen'] ?>%</div>
        </div>
      <?php endforeach; ?>
      <?php if (!$voucher): ?><div class="card" style="grid-column:1/-1"><div class="no-data">Belum ada voucher.</div></div><?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>+ Voucher Baru</h3></div>
    <form method="post">
      <div class="field"><label>Kode</label><input class="input" name="kode" placeholder="HOLIDAY25" required></div>
      <div class="field"><label>Diskon (%)</label><input class="input" type="number" name="persen" min="1" max="80" value="10" required></div>
      <div class="field"><label>Untuk</label><input class="input" name="untuk" placeholder="Semua / segmen tertentu"></div>
      <button class="btn btn-primary btn-block">Simpan Voucher</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
