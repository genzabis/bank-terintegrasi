<?php
require_once __DIR__ . '/../_layout.php';

$rek = fetch_rekening_bank();

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $r = proses_pesan_paket($_POST['paket_id'], trim($_POST['pemesan']) ?: 'Tamu', trim($_POST['no_rek']));
    $msg = $r['message'] . (isset($r['data']['kode']) ? ' · '.$r['data']['kode'] : '');
    $cls = !empty($r['success']) ? 'success' : 'danger';
}
$paket = get_paket();

layout_start('Paket Wisata', 'Liburan all-inclusive untuk pengalaman tanpa repot');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2 mb-2">
  <?php foreach ($paket as $p): ?>
    <div class="card" style="position:relative">
      <div style="position:absolute;right:1.25rem;top:1.25rem"><span class="badge badge-accent flex gap" style="align-items:center"><i data-lucide="star" style="width:11px;height:11px;fill:currentColor;stroke-width:0"></i><?= number_format($p['rating']??4.5,1) ?></span></div>
      <div class="muted small"><?= htmlspecialchars($p['destinasi']) ?></div>
      <h3 style="margin:.25rem 0 .5rem;font-size:1.25rem"><?= htmlspecialchars($p['nama']) ?></h3>
      <ul style="padding-left:1.2rem; margin:.5rem 0; color:var(--text-dim); font-size:.85rem">
        <?php foreach (($p['include']??[]) as $i): ?><li><?= htmlspecialchars($i) ?></li><?php endforeach; ?>
      </ul>
      <div class="flex-between mt">
        <span style="font-size:1.4rem;font-weight:800;color:var(--accent)"><?= format_rupiah($p['harga']) ?></span>
        <form method="post" style="display:flex;gap:.4rem">
          <input type="hidden" name="paket_id" value="<?= htmlspecialchars($p['id']) ?>">
          <input type="hidden" name="pemesan" value="Tamu">
          <select class="input" name="no_rek" required style="font-size:.78rem;padding:.4rem .55rem">
            <option value="">- pilih rek -</option>
            <?php foreach ($rek as $r): ?>
              <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.format_rupiah($r['saldo'])) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-primary btn-sm">Booking</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php layout_end(); ?>
