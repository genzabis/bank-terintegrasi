<?php
require_once __DIR__ . '/../_layout.php';

$produk = fetch_produk_ecommerce();
$tiket  = get_tiket();
$rek    = fetch_rekening_bank();

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $produkId = $_POST['produk_id'];
    $tiketId  = $_POST['tiket_id'];
    $no       = trim($_POST['no_rek']);
    $pSel = null; foreach ($produk as $p) if ($p['id']===$produkId) { $pSel=$p; break; }
    $tSel = find_tiket($tiketId);
    if (!$pSel||!$tSel||!$no) { $msg='Lengkapi semua field'; $cls='danger'; }
    else {
        $diskon = 15;
        $totProduk = (float)$pSel['harga'];
        $totTiket  = (float)$tSel['harga'] * (1 - $diskon/100);
        $total     = $totProduk + $totTiket;
        $bay = bayar_via_bank($no, $total, "Bundle Travel: {$pSel['nama']} + {$tSel['nama']}");
        if (empty($bay['success'])) { $msg='Gagal bayar: '.($bay['message']??''); $cls='danger'; }
        else {
            kurangi_kuota_tiket($tiketId, 1);
            $kode = 'BDL-'.strtoupper(substr(md5($tiketId.microtime()),0,6));
            simpan_pesanan([
                'jenis'=>'BUNDLE','tiket_id'=>$tiketId,'nama'=>"{$pSel['nama']} + {$tSel['nama']}",
                'qty'=>1,'subtotal'=>$totProduk+$tSel['harga'],'diskon_persen'=>$diskon,
                'total'=>$total,'pemesan'=>'Bundle Travel','no_rek'=>$no,
                'sumber'=>'AppsEcommerce-INTERNAL','kode'=>$kode,'status'=>'LUNAS',
            ]);
            $msg='Bundle sukses · '.$kode.' · Total '.format_rupiah($total); $cls='success';
        }
    }
}

layout_start('Bundle dari Ecommerce', 'Paket gabungan produk Ecommerce + tiket Travel · diskon 15% pada tiket');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="card">
  <div class="card-header"><h3>Buat Bundle</h3><span class="badge badge-accent">Diskon Tiket 15%</span></div>
  <p class="small muted">Halaman ini menggabungkan produk dari <code>AppsEcommerce</code> dengan tiket lokal Travel, lalu menagih ke <code>AppsBank</code>. Stok produk Ecommerce belum dikurangi (booking saja) — untuk pembelian + stok-update, gunakan halaman Bundle di Ecommerce.</p>
  <form method="post">
    <div class="row cols-2">
      <div class="field"><label>Produk Ecommerce (live)</label>
        <select class="input" name="produk_id" required>
          <option value="">-- pilih produk --</option>
          <?php foreach ($produk as $p): ?>
            <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nama'].' · '.format_rupiah($p['harga'])) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (!$produk): ?><div class="small" style="color:var(--danger);margin-top:.3rem">⚠ AppsEcommerce offline.</div><?php endif; ?>
      </div>
      <div class="field"><label>Tiket Travel</label>
        <select class="input" name="tiket_id" required>
          <option value="">-- pilih tiket --</option>
          <?php foreach ($tiket as $t): ?>
            <option value="<?= htmlspecialchars($t['id']) ?>"><?= htmlspecialchars($t['nama'].' · '.format_rupiah($t['harga'])) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="field"><label>Rekening Pembayar</label>
      <select class="input" name="no_rek" required>
        <option value="">-- pilih --</option>
        <?php foreach ($rek as $r): ?>
          <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama'].' ('.format_rupiah($r['saldo']).')') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary btn-block">Beli Bundle</button>
  </form>
</div>

<?php layout_end(); ?>
