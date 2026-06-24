<?php
require_once __DIR__ . '/../_layout.php';

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='add') {
    $r = tambah_tagihan_spp($_POST['siswa_id'], (float)$_POST['jumlah'], $_POST['bulan']);
    if (!empty($r['success'])) {
        set_flash_msg('Tagihan SPP ditambahkan', 'success');
    } else {
        set_flash_msg($r['message'] ?? 'Gagal menambahkan tagihan', 'danger');
    }
    header('Location: spp.php'); exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='bayar') {
    $id=$_POST['spp_id']; $no=trim($_POST['no_rek']);
    $list=get_spp(); $tag=null;
    foreach ($list as $t) if ($t['id']===$id) { $tag=$t; break; }
    if (!$tag) { set_flash_msg('Tagihan tidak ditemukan', 'danger'); header('Location: spp.php'); exit; }
    elseif ($tag['status']==='LUNAS') { set_flash_msg('Sudah lunas', 'warning'); header('Location: spp.php'); exit; }
    else {
        $r = bayar_via_bank($no, (float)$tag['jumlah'], "SPP {$tag['bulan']} - siswa {$tag['siswa_id']}");
        if (!empty($r['success'])) { set_spp_status($id,'LUNAS',$no); set_flash_msg("SPP {$tag['bulan']} dilunasi · sisa saldo ".format_rupiah($r['saldo']??0), 'success'); header('Location: spp.php'); exit; }
        else { set_flash_msg('Gagal: '.($r['message']??'-'), 'danger'); header('Location: spp.php'); exit; }
    }
}
$spp=get_spp(); $siswa=get_siswa();
$siswaMap=[]; foreach ($siswa as $s) $siswaMap[$s['id']]=$s;
$rek = http_get(BANK_URL.'/api.php?action=rekening',3)['data'] ?? [];
$user = current_user()['username'] ?? 'guest';
if ($user !== 'guest') {
    $rek = array_values(array_filter($rek, fn($r) => ($r['username'] ?? '') === $user));
}

layout_start('Pembayaran SPP', 'Lunasi tagihan SPP otomatis melalui debit Bank');
?>



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
