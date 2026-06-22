<?php
require_once __DIR__ . '/../_layout.php';

$msg = ''; $cls = '';
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $cls = $_GET['err'] ?? 0 ? 'danger' : 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'add') {
    $no_rek = trim($_POST['no_rek']);
    $nama   = trim($_POST['nama']);
    $saldo  = (float)$_POST['saldo'];

    if (find_rekening($no_rek)) {
        header("Location: rekening.php?msg=" . urlencode('Gagal: Nomor rekening sudah terdaftar!') . "&err=1");
        exit;
    }

    $rek = get_rekening();
    $rek[] = [
        'no_rek' => $no_rek,
        'nama'   => $nama,
        'saldo'  => $saldo,
        'dibuat' => date('Y-m-d H:i:s'),
    ];
    write_json(FILE_REKENING, $rek);
    header("Location: rekening.php?msg=" . urlencode('Rekening baru berhasil dibuka.'));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'topup') {
    $r = update_saldo(trim($_POST['no_rek']), (float)$_POST['jumlah'], 'KREDIT', 'Top up via admin', 'INTERNAL');
    $err = $r['success'] ? 0 : 1;
    header("Location: rekening.php?msg=" . urlencode($r['message']) . "&err=" . $err);
    exit;
}

$rekening = get_rekening();
layout_start('Rekening', 'Kelola rekening, buka akun baru, dan top-up saldo');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><span class="ico">●</span><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2-1">
  <div class="card no-pad">
    <div class="card-header"><h3>Daftar Rekening</h3><span class="badge badge-muted"><?= count($rekening) ?> akun</span></div>
    <table class="table">
      <thead><tr><th>No Rekening</th><th>Nama Pemilik</th><th class="right">Saldo</th><th>Dibuat</th></tr></thead>
      <tbody>
        <?php foreach ($rekening as $r): ?>
          <tr>
            <td><code><?= htmlspecialchars($r['no_rek']) ?></code></td>
            <td><?= htmlspecialchars($r['nama']) ?></td>
            <td class="right bold"><?= format_rupiah($r['saldo']) ?></td>
            <td class="small muted"><?= htmlspecialchars($r['dibuat']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div>
    <div class="card mb">
      <div class="card-header"><h3>+ Buka Rekening</h3></div>
      <form method="post">
        <input type="hidden" name="act" value="add">
        <div class="field"><label>No Rekening</label><input class="input" name="no_rek" required></div>
        <div class="field"><label>Nama Pemilik</label><input class="input" name="nama" required></div>
        <div class="field"><label>Saldo Awal</label><input type="number" min="0" class="input" name="saldo" value="0" required></div>
        <button class="btn btn-primary btn-block">Simpan Rekening</button>
      </form>
    </div>
    <div class="card">
      <div class="card-header"><h3>Top Up Saldo</h3></div>
      <form method="post">
        <input type="hidden" name="act" value="topup">
        <div class="field"><label>Rekening</label>
          <select class="input" name="no_rek" required>
            <option value="">-- pilih --</option>
            <?php foreach ($rekening as $r): ?>
              <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Jumlah</label><input type="number" min="1" class="input" name="jumlah" required></div>
        <button class="btn btn-success btn-block">Top Up</button>
      </form>
    </div>
  </div>
</div>

<?php layout_end(); ?>
