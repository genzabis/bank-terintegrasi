<?php
require_once __DIR__ . '/../_layout.php';

$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    add_prestasi(trim($_POST['siswa_id']), trim($_POST['judul']), trim($_POST['tingkat']), $_POST['tanggal']);
    $msg='Prestasi tercatat';
}
$siswa = get_siswa();
$prestasi = get_prestasi();
$siswaMap = []; foreach ($siswa as $s) $siswaMap[$s['id']] = $s;

layout_start('Prestasi Siswa', 'Catatan pencapaian akademik dan non-akademik');
?>

<?php if ($msg): ?><div class="alert success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2-1">
  <div class="card no-pad">
    <div class="card-header"><h3>Daftar Prestasi</h3><span class="badge badge-muted"><?= count($prestasi) ?> entri</span></div>
    <table class="table">
      <thead><tr><th>Siswa</th><th>Judul</th><th>Tingkat</th><th>Tanggal</th></tr></thead>
      <tbody>
        <?php foreach ($prestasi as $p): $s = $siswaMap[$p['siswa_id']] ?? null; ?>
          <tr>
            <td><div class="flex gap" style="align-items:center"><div class="avatar" style="width:30px;height:30px;font-size:.75rem"><?= inisial($s['nama']??'?') ?></div><span><?= htmlspecialchars($s['nama']??$p['siswa_id']) ?></span></div></td>
            <td class="bold"><?= htmlspecialchars($p['judul']) ?></td>
            <td><span class="badge badge-accent"><?= htmlspecialchars($p['tingkat']) ?></span></td>
            <td class="small muted"><?= htmlspecialchars($p['tanggal']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header"><h3>+ Catat Prestasi</h3></div>
    <form method="post">
      <div class="field"><label>Siswa</label>
        <select class="input" name="siswa_id" required>
          <option value="">-- pilih --</option>
          <?php foreach ($siswa as $s): ?>
            <option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['nama'].' · '.$s['kelas']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Judul Prestasi</label><input class="input" name="judul" placeholder="Juara 1 Olimpiade..." required></div>
      <div class="field"><label>Tingkat</label>
        <select class="input" name="tingkat">
          <option>Sekolah</option><option>Kabupaten</option><option>Provinsi</option><option>Nasional</option><option>Internasional</option>
        </select>
      </div>
      <div class="field"><label>Tanggal</label><input class="input" type="date" name="tanggal" required></div>
      <button class="btn btn-primary btn-block">Simpan</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
