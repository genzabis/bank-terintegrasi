<?php
require_once __DIR__ . '/../_layout.php';



$u = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'add') {
    $no_rek = trim($_POST['no_rek']);
    $nama   = trim($_POST['nama']);
    $saldo  = (float)$_POST['saldo'];
    if ($saldo < 0) {
        set_flash_msg('Gagal: Saldo awal tidak boleh negatif', 'danger');
        header("Location: rekening.php"); exit;
    }

    if (find_rekening($no_rek)) {
        set_flash_msg('Gagal: Nomor rekening sudah terdaftar!', 'danger');
        header("Location: rekening.php");
        exit;
    }

    // Pakai read_json langsung agar dapat semua data sebelum ditambah
    $rek = read_json(FILE_REKENING);
    $rek[] = [
        'no_rek'   => $no_rek,
        'nama'     => $nama,
        'username' => $u['username'],
        'saldo'    => $saldo,
        'dibuat'   => date('Y-m-d H:i:s'),
    ];
    write_json(FILE_REKENING, $rek);
    set_flash_msg('Rekening baru berhasil dibuka.', 'success');
    header("Location: rekening.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'topup') {
    $jumlah = (float)$_POST['jumlah'];
    if ($jumlah <= 0) {
        set_flash_msg('Jumlah top up harus lebih dari 0', 'danger');
        header("Location: rekening.php"); exit;
    }
    $r = update_saldo(trim($_POST['no_rek']), $jumlah, 'KREDIT', 'Top up via mandiri', 'INTERNAL');
    set_flash_msg($r['message'], $r['success'] ? 'success' : 'danger');
    header("Location: rekening.php");
    exit;
}

$is_adm = is_admin();
$rekening = get_rekening($is_adm ? null : $u['username']);
layout_start('Rekening', 'Kelola rekening, buka akun baru, dan top-up saldo');
?>



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
