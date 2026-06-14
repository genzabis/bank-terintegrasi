<?php
require_once __DIR__ . '/../_layout.php';

$tiket   = fetch_tiket_travel();
$rekList = fetch_rekening_bank();
$produk  = get_produk();

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid=$_POST['produk_id']??''; $tid=$_POST['tiket_id']??'';
    $qty=max(1,(int)($_POST['qty']??1)); $no=trim($_POST['no_rek']??'');
    $p = find_produk($pid);
    $t = null; foreach ($tiket as $tk) if ($tk['id']===$tid) { $t=$tk; break; }
    if (!$p || !$t || !$no) { $msg='Pilih produk, tiket, dan rekening'; $cls='danger'; }
    else {
        $diskon = 15;
        $hargaTiket = (float)$t['harga'] * (1 - $diskon/100);
        $totProduk = (float)$p['harga'] * $qty;
        $total = $totProduk + $hargaTiket;

        $bay = bayar_via_bank($no, $total, "Bundle: {$p['nama']} + Tiket {$t['nama']}");
        if (empty($bay['success'])) { $msg='Gagal bayar: '.($bay['message']??''); $cls='danger'; }
        else {
            $resvr = pesan_tiket_travel(['tiket_id'=>$tid,'qty'=>1,'pemesan'=>'Bundle Ecommerce','diskon'=>$diskon]);
            $kode = $resvr['data']['kode'] ?? null;
            kurangi_stok($p['id'], $qty);
            simpan_pesanan([
                'user'=>'guest','no_rek'=>$no,
                'items'=>[['produk_id'=>$p['id'],'nama'=>$p['nama'],'qty'=>$qty,'harga'=>$p['harga'],'sub'=>$totProduk]],
                'total'=>$total,'status'=>'LUNAS','metode'=>'BANK+BUNDLE',
                'bundle'=>['tiket_id'=>$tid,'nama'=>$t['nama'],'harga'=>$hargaTiket,'diskon'=>$diskon,'kode'=>$kode],
            ]);
            $msg='Bundle sukses · Total '.format_rupiah($total).' · Kode tiket '.($kode??'-'); $cls='success';
        }
    }
}

layout_start('Bundle Belanja + Travel', 'Beli produk dan tiket dalam satu transaksi · diskon 15% pada tiket');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header"><h3>Pilih Bundle</h3><span class="badge badge-accent">Diskon Tiket 15%</span></div>
  <p class="small muted">Halaman ini menggabungkan 3 sistem: Ecommerce ambil tiket dari <code>AppsTravel</code>, lalu bayar via <code>AppsBank</code>, dan booking otomatis di <code>AppsTravel</code>.</p>
  <form method="post" class="mt">
    <div class="row cols-2">
      <div class="field"><label>Produk</label>
        <select class="input" name="produk_id" required>
          <option value="">-- pilih produk --</option>
          <?php foreach ($produk as $p): ?>
            <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nama'].' · '.format_rupiah($p['harga'])) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Qty</label><input class="input" type="number" name="qty" value="1" min="1"></div>
    </div>
    <div class="field"><label>Tiket Travel (live)</label>
      <select class="input" name="tiket_id" required>
        <option value="">-- pilih tiket --</option>
        <?php foreach ($tiket as $t): ?>
          <option value="<?= htmlspecialchars($t['id']) ?>"><?= htmlspecialchars($t['nama'].' · '.($t['rute']??'').' · '.format_rupiah($t['harga'])) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (!$tiket): ?><div class="small" style="color:var(--danger);margin-top:.3rem">⚠ AppsTravel offline.</div><?php endif; ?>
    </div>
    <div class="field"><label>Rekening Pembayar (live)</label>
      <select class="input" name="no_rek" required>
        <option value="">-- pilih --</option>
        <?php foreach ($rekList as $r): ?>
          <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama'].' ('.format_rupiah($r['saldo']).')') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary btn-block">Beli Bundle</button>
  </form>
</div>

<?php layout_end(); ?>
