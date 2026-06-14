<?php
require_once __DIR__ . '/../_layout.php';

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='add') {
    tambah_tagihan_spp($_POST['siswa_id'], (float)$_POST['jumlah'], $_POST['bulan']);
    $msg='Tagihan SPP ditambahkan'; $cls='success';
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='bayar') {
    $id=$_POST['spp_id']; $no=trim($_POST['no_rek']);
    $list=get_spp(); $tag=null;
    foreach ($list as $t) if ($t['id']===$id) { $tag=$t; break; }
    if (!$tag) { $msg='Tagihan tidak ditemukan'; $cls='danger'; }
    elseif ($tag['status']==='LUNAS') { $msg='Sudah lunas'; $cls='warning'; }
    else {
        $r = bayar_via_bank($no, (float)$tag['jumlah'], "SPP {$tag['bulan']} - siswa {$tag['siswa_id']}");
        if (!empty($r['success'])) { set_spp_status($id,'LUNAS',$no); $msg="SPP {$tag['bulan']} dilunasi · sisa saldo ".format_rupiah($r['saldo']??0); $cls='success'; }
        else { $msg='Gagal: '.($r['message']??'-'); $cls='danger'; }
    }
}
$spp=get_spp(); $siswa=get_siswa();
$siswaMap=[]; foreach ($siswa as $s) $siswaMap[$s['id']]=$s;
$rek = http_get(BANK_URL.'/api.php?action=rekening',3)['data'] ?? [];

layout_start('Pembayaran SPP', 'Lunasi tagihan SPP otomatis melalui debit Bank');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2-1">
  <div class="card no-pad">
    <div class="card-header"><h3>Tagihan SPP</h3><span class="badge badge-muted"><?= count($spp) ?> tagihan</span></div>
    <table class="table">
      <thead><tr><th>ID</th><th>Siswa</th><th>Bulan</th><th class="right">Jumlah</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($spp as $t): $s=$siswaMap[$t['siswa_id']]??null; ?>
          <tr>
            <td><code><?= htmlspecialchars($t['id']) ?></code></td>
            <td><b><?= htmlspecialchars($s['nama']??$t['siswa_id']) ?></b><div class="small muted"><?= htmlspecialchars($s['kelas']??'') ?></div></td>
            <td><?= htmlspecialchars($t['bulan']) ?></td>
            <td class="right bold"><?= format_rupiah($t['jumlah']) ?></td>
            <td><span class="badge <?= $t['status']==='LUNAS'?'badge-success':'badge-warning' ?>"><?= htmlspecialchars($t['status']) ?></span></td>
            <td>
              <?php if ($t['status']==='BELUM'): ?>
                <form method="post" style="display:flex;gap:.4rem">
                  <input type="hidden" name="act" value="bayar">
                  <input type="hidden" name="spp_id" value="<?= htmlspecialchars($t['id']) ?>">
                  <select class="input" name="no_rek" required style="padding:.35rem .5rem;font-size:.8rem">
                    <option value="">- rek -</option>
                    <?php foreach ($rek as $r): ?>
                      <option value="<?= htmlspecialchars($r['no_rek']) ?>" <?= ($s['no_rek']??'')===$r['no_rek']?'selected':'' ?>><?= htmlspecialchars($r['no_rek'].' '.$r['nama']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-success btn-sm">Bayar</button>
                </form>
              <?php else: ?>
                <span class="small muted"><?= htmlspecialchars($t['waktu_bayar']??'') ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header"><h3>+ Tagihan Baru</h3></div>
    <form method="post">
      <input type="hidden" name="act" value="add">
      <div class="field"><label>Siswa</label>
        <select class="input" name="siswa_id" required>
          <option value="">-- pilih --</option>
          <?php foreach ($siswa as $s): ?><option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['nama']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Bulan</label><input class="input" name="bulan" placeholder="Maret 2026" required></div>
      <div class="field"><label>Jumlah</label><input class="input" type="number" name="jumlah" min="1" required></div>
      <button class="btn btn-primary btn-block">Simpan</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
