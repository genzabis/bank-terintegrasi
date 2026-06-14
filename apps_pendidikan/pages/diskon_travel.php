<?php
require_once __DIR__ . '/../_layout.php';

$msg=''; $cls=''; $kode='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $sid = trim($_POST['siswa_id']);
    $persen = max(1, min(50, (int)$_POST['persen']));
    $s = find_siswa($sid);
    if (!$s) { $msg='Siswa tidak ditemukan'; $cls='danger'; }
    else {
        $kode = generate_kode_diskon($sid, $persen);
        $r = daftar_diskon_travel($kode, $persen, $sid, $s['nama']);
        if (!empty($r['success'])) { $msg='Voucher diskon terdaftar di AppsTravel · siap digunakan'; $cls='success'; }
        else { $msg='Gagal: '.($r['message']??''); $cls='danger'; }
    }
}
$siswa = get_siswa();
$voucherList = http_get(TRAVEL_URL.'/api.php?action=voucher', 3)['data'] ?? [];
// Hanya tampilkan yang sumber Pendidikan
$voucherList = array_values(array_filter($voucherList, fn($v)=>($v['sumber']??'')===SISTEM_NAMA));

layout_start('Voucher Diskon untuk Travel', 'Generate kode diskon eksklusif siswa untuk pemesanan tiket travel');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($kode && $cls==='success'): ?>
  <div class="voucher mb-2"><div><div class="muted small">Kode voucher</div><div class="kode"><?= htmlspecialchars($kode) ?></div></div><div class="pct"><?= (int)$_POST['persen'] ?>%</div></div>
<?php endif; ?>

<div class="row cols-2-1">
  <div class="card no-pad">
    <div class="card-header"><h3>Voucher Diterbitkan oleh Pendidikan</h3><span class="badge badge-muted"><?= count($voucherList) ?> voucher</span></div>
    <table class="table">
      <thead><tr><th>Kode</th><th>Untuk</th><th>Diskon</th><th>Dipakai</th></tr></thead>
      <tbody>
        <?php foreach ($voucherList as $v): ?>
          <tr>
            <td><code><?= htmlspecialchars($v['kode']) ?></code></td>
            <td><?= htmlspecialchars($v['untuk']??'-') ?></td>
            <td><span class="badge badge-accent"><?= (int)$v['persen'] ?>%</span></td>
            <td><?= (int)($v['dipakai']??0) ?>x</td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$voucherList): ?><tr><td colspan="4"><div class="no-data">Belum ada voucher dari Pendidikan.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header"><h3>Generate Voucher</h3></div>
    <p class="small muted">Sistem akan memanggil <code>POST <?= TRAVEL_URL ?>/api.php?action=tambah_voucher</code> via REST API.</p>
    <form method="post">
      <div class="field"><label>Siswa</label>
        <select class="input" name="siswa_id" required>
          <option value="">-- pilih --</option>
          <?php foreach ($siswa as $s): ?><option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['nama'].' · '.$s['kelas']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Persen Diskon</label><input class="input" type="number" name="persen" min="1" max="50" value="20" required></div>
      <button class="btn btn-primary btn-block">Generate & Kirim ke Travel</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
