<?php
require_once __DIR__ . '/../_layout.php';

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $sid = trim($_POST['siswa_id']);
    $s = find_siswa($sid);
    if (!$s) { $msg='Siswa tidak ditemukan'; $cls='danger'; }
    else {
        add_produk_siswa([
            'nama'=>trim($_POST['nama']),'harga'=>(float)$_POST['harga'],
            'stok'=>(int)($_POST['stok']??1), 'siswa_id'=>$sid,
            'siswa_nama'=>$s['nama'], 'deskripsi'=>trim($_POST['deskripsi']??''),
            'ecommerce_id'=>null,
        ]);
        $msg='Produk karya tersimpan. Buka menu Upload ke Ecommerce untuk publikasi.'; $cls='success';
    }
}
$produk = get_produk_siswa();
$siswa = get_siswa();

layout_start('Produk Karya Siswa', 'Inventori karya yang dapat dijual ke marketplace Ecommerce');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2-1">
  <div class="card no-pad">
    <div class="card-header"><h3>Inventori Karya</h3><span class="badge badge-muted"><?= count($produk) ?> produk</span></div>
    <table class="table">
      <thead><tr><th>Produk</th><th>Pencipta</th><th class="right">Harga</th><th>Stok</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($produk as $p): ?>
          <tr>
            <td><div class="bold"><?= htmlspecialchars($p['nama']) ?></div><div class="small muted"><?= htmlspecialchars($p['deskripsi']??'') ?></div></td>
            <td class="small"><?= htmlspecialchars($p['siswa_nama']??'-') ?></td>
            <td class="right bold"><?= format_rupiah($p['harga']) ?></td>
            <td><?= (int)$p['stok'] ?></td>
            <td><?= !empty($p['ecommerce_id'])?'<span class="badge badge-success">Live</span>':'<span class="badge badge-muted">Lokal</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$produk): ?><tr><td colspan="5"><div class="no-data">Belum ada produk karya.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-header"><h3>+ Tambah Produk</h3></div>
    <form method="post">
      <div class="field"><label>Siswa</label>
        <select class="input" name="siswa_id" required>
          <option value="">-- pilih --</option>
          <?php foreach ($siswa as $s): ?><option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['nama']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Nama Produk</label><input class="input" name="nama" required></div>
      <div class="row cols-2">
        <div class="field"><label>Harga</label><input class="input" type="number" name="harga" min="1" required></div>
        <div class="field"><label>Stok</label><input class="input" type="number" name="stok" min="1" value="1"></div>
      </div>
      <div class="field"><label>Deskripsi</label><textarea class="input" name="deskripsi" rows="2"></textarea></div>
      <button class="btn btn-primary btn-block">Simpan</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
