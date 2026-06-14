<?php
require_once __DIR__ . '/../_layout.php';

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act']??'')==='add') {
    add_siswa(['nama'=>trim($_POST['nama']),'kelas'=>trim($_POST['kelas']),'no_rek'=>trim($_POST['no_rek']??'')]);
    $msg='Siswa berhasil ditambahkan'; $cls='success';
}
$siswa = get_siswa();
$prestasiAll = get_prestasi();
$prestasiPer = []; foreach ($prestasiAll as $p) $prestasiPer[$p['siswa_id']] = ($prestasiPer[$p['siswa_id']] ?? 0) + 1;

layout_start('Manajemen Siswa', 'Daftar siswa beserta jumlah prestasi dan rekening pembayaran');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2-1">
  <div class="card no-pad">
    <div class="card-header"><h3>Siswa Aktif</h3><span class="badge badge-muted"><?= count($siswa) ?> orang</span></div>
    <table class="table">
      <thead><tr><th>Siswa</th><th>Kelas</th><th>Rek Bank</th><th class="right">Prestasi</th></tr></thead>
      <tbody>
        <?php foreach ($siswa as $s): ?>
          <tr>
            <td><div class="flex gap" style="align-items:center"><div class="avatar"><?= inisial($s['nama']) ?></div><div><div class="bold"><?= htmlspecialchars($s['nama']) ?></div><div class="small muted"><?= htmlspecialchars($s['id']) ?></div></div></div></td>
            <td><span class="badge badge-muted"><?= htmlspecialchars($s['kelas']) ?></span></td>
            <td><code><?= htmlspecialchars($s['no_rek']??'-') ?></code></td>
            <td class="right"><span class="badge badge-accent"><?= $prestasiPer[$s['id']] ?? 0 ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header"><h3>+ Tambah Siswa</h3></div>
    <form method="post">
      <input type="hidden" name="act" value="add">
      <div class="field"><label>Nama Lengkap</label><input class="input" name="nama" required></div>
      <div class="field"><label>Kelas</label><input class="input" name="kelas" placeholder="12 IPA 1" required></div>
      <div class="field"><label>No Rekening (opsional)</label><input class="input" name="no_rek" placeholder="dari AppsBank"></div>
      <button class="btn btn-primary btn-block">Daftarkan</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
