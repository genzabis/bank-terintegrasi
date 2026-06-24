<?php
require_once __DIR__ . '/../_layout.php';

$rek = fetch_rekening_bank();
$user = current_user()['username'] ?? 'guest';
if ($user !== 'guest') {
    $rek = array_values(array_filter($rek, fn($r) => ($r['username'] ?? '') === $user));
}

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $r = proses_pesan_hotel(
        $_POST['hotel_id'],
        max(1,(int)$_POST['malam']),
        trim($_POST['pemesan']) ?: 'Tamu',
        trim($_POST['no_rek']) ?: null
    );
    if (!empty($r['success'])) { $msg = "Booking sukses · ".$r['data']['kode']." · Total ".format_rupiah($r['data']['total']); $cls='success'; }
    else { $msg = $r['message']; $cls='danger'; }
}
$hotel = get_hotel();

layout_start('Hotel & Penginapan', 'Pesan hotel dengan pembayaran terintegrasi Bank');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-3 mb-2">
  <?php foreach ($hotel as $h): ?>
    <div class="card no-pad" style="overflow:hidden">
      <div style="height:110px;background:linear-gradient(135deg, var(--accent-soft), #fff);display:grid;place-items:center;border-bottom:1px solid var(--border)"><i data-lucide="bed-double" style="width:46px;height:46px;color:var(--accent);stroke-width:1.4;opacity:.75"></i></div>
      <div style="padding:1rem">
        <h4 style="margin:0;font-size:1rem"><?= htmlspecialchars($h['nama']) ?></h4>
        <div class="small muted"><?= htmlspecialchars($h['kota']??'') ?></div>
        <div class="flex-between mt">
          <span class="rating flex gap" style="align-items:center"><i data-lucide="star" style="width:14px;height:14px;fill:currentColor;stroke-width:0"></i><?= number_format($h['rating']??4.5,1) ?></span>
          <span class="big bold" style="color:var(--accent)"><?= format_rupiah($h['harga_per_malam']) ?>/mlm</span>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-header"><h3>Booking Hotel</h3></div>
  <form method="post">
    <div class="field"><label>Hotel</label>
      <select class="input" name="hotel_id" required>
        <option value="">-- pilih --</option>
        <?php foreach ($hotel as $h): ?>
          <option value="<?= htmlspecialchars($h['id']) ?>"><?= htmlspecialchars($h['nama'].' · '.format_rupiah($h['harga_per_malam']).'/mlm') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row cols-2">
      <div class="field"><label>Jumlah Malam</label><input class="input" type="number" name="malam" value="1" min="1" required></div>
      <div class="field"><label>Pemesan</label><input class="input" name="pemesan" required></div>
    </div>
    <div class="field"><label>Rekening Pembayar (opsional)</label>
      <select class="input" name="no_rek">
        <option value="">-- booking saja --</option>
        <?php foreach ($rek as $r): ?>
          <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama'].' ('.format_rupiah($r['saldo']).')') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary btn-block">Pesan Hotel</button>
  </form>
</div>

<?php layout_end(); ?>
