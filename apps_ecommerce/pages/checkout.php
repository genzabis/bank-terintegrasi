<?php
require_once __DIR__ . '/../_layout.php';

$user = current_user()['username'] ?? 'guest';
$cart = get_keranjang($user);
$items = []; $total = 0;
foreach ($cart as $it) {
    $p = find_produk($it['produk_id']);
    if (!$p) continue;
    $items[] = ['produk_id'=>$p['id'],'nama'=>$p['nama'],'qty'=>$it['qty'],'harga'=>$p['harga']];
    $total += $p['harga']*$it['qty'];
}

$rekList = fetch_rekening_bank();
if ($user !== 'guest') {
    $rekList = array_values(array_filter($rekList, fn($r) => ($r['username'] ?? '') === $user));
}

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!$items) { $msg='Keranjang kosong'; $cls='warning'; }
    else {
        // Cek voucher dulu (kalau ada) ke Travel
        $diskonPct = 0; $voucher = trim($_POST['voucher'] ?? '');
        if ($voucher) {
            $v = http_post_json(TRAVEL_URL.'/api.php?action=apply_diskon', ['kode'=>$voucher,'sumber'=>SISTEM_NAMA]);
            audit_log('OUT','AppsTravel','apply_diskon', TRAVEL_URL.'/api.php?action=apply_diskon',
                !empty($v['success']), $v['message']??'', ['kode'=>$voucher]);
            if (!empty($v['success'])) $diskonPct = (float)$v['data']['persen'];
            else { $msg = 'Voucher tidak valid: '.($v['message']??''); $cls='danger'; }
        }
        if ($cls !== 'danger') {
            $r = proses_checkout($user, trim($_POST['no_rek']),
                array_map(fn($it)=>['produk_id'=>$it['produk_id'],'qty'=>$it['qty']], $items),
                $voucher, $diskonPct);
            if (!empty($r['success'])) { set_flash_msg('Pembayaran berhasil! Saldo bank telah terdebit otomatis dan stok produk terupdate.', 'success'); header('Location: pesanan.php'); exit; }
            $msg = $r['message'] ?? 'Checkout gagal'; $cls='danger';
        }
    }
}

layout_start('Checkout', 'Bayar via AppsBank dan dapatkan diskon dari voucher AppsTravel');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2-1">
  <div class="card no-pad">
    <div class="card-header"><h3>Ringkasan Pesanan</h3></div>
    <table class="table">
      <thead><tr><th>Produk</th><th>Qty</th><th class="right">Sub</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr><td><?= htmlspecialchars($it['nama']) ?></td><td><?= (int)$it['qty'] ?></td><td class="right"><?= format_rupiah($it['harga']*$it['qty']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$items): ?><tr><td colspan="3"><div class="no-data">Keranjang kosong.</div></td></tr><?php endif; ?>
      </tbody>
      <tfoot><tr><td colspan="2" class="right bold">Total</td><td class="right" style="color:var(--accent);font-size:1.15rem"><b><?= format_rupiah($total) ?></b></td></tr></tfoot>
    </table>
  </div>

  <?php if ($items): ?>
  <div class="card">
    <div class="card-header"><h3>Pembayaran</h3></div>
    <form method="post">
      <div class="field"><label>Kode Voucher (opsional)</label>
        <input class="input" name="voucher" placeholder="EDU-XXXXXX-25 / WELCOME10">
        <div class="small muted" style="margin-top:.3rem">Voucher dari AppsTravel/AppsPendidikan akan otomatis terapply.</div>
      </div>
      <div class="field"><label>Rekening Pembayar (live dari AppsBank)</label>
        <select class="input" name="no_rek" required>
          <option value="">-- pilih --</option>
          <?php foreach ($rekList as $r): ?>
            <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama'].' ('.format_rupiah($r['saldo']).')') ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (!$rekList): ?><div class="small" style="color:var(--danger);margin-top:.3rem">⚠ AppsBank offline · checkout tidak bisa dilanjutkan.</div><?php endif; ?>
      </div>
      <button class="btn btn-primary btn-block" <?= $rekList?'':'disabled' ?>>Bayar <?= format_rupiah($total) ?> via Bank</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<?php layout_end(); ?>
