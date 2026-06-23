<?php
require_once __DIR__ . '/../_layout.php';

$msg = ''; $cls = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'add') {
    $r = add_kartu(trim($_POST['no_rek']), $_POST['tipe'], (float)$_POST['limit_harian']);
    $msg = 'Kartu baru terbit · ' . $r['data']['no_kartu']; $cls='success';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['act'] ?? '') === 'toggle') {
    toggle_kartu($_POST['id']); $msg = 'Status kartu diperbarui'; $cls='info';
}

$u = current_user();
$is_adm = is_admin();
$kartu    = get_kartu($is_adm ? null : $u['username']);
$rekening = get_rekening($is_adm ? null : $u['username']);
layout_start('Kartu Debit', 'Terbitkan dan kelola kartu debit untuk setiap rekening');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-2-1">
  <div>
    <div class="row cols-2">
      <?php foreach ($kartu as $k): ?>
        <?php
          $rek = null; foreach ($rekening as $r) if ($r['no_rek']===$k['no_rek']) { $rek=$r; break; }
          $aktif = !empty($k['aktif']);
        ?>
        <div class="card" style="background:linear-gradient(135deg, color-mix(in srgb, var(--accent) 35%, var(--bg-card)), var(--bg-card)); border-color: color-mix(in srgb, var(--accent) 40%, transparent); position:relative; overflow:hidden">
          <div style="position:absolute;right:-30px;top:-30px;width:140px;height:140px;background:radial-gradient(circle,var(--accent-glow),transparent 70%);opacity:.5"></div>
          <div class="flex-between">
            <span class="badge badge-accent"><?= htmlspecialchars($k['tipe']) ?></span>
            <span class="badge <?= $aktif?'badge-success':'badge-danger' ?>"><?= $aktif?'AKTIF':'BLOKIR' ?></span>
          </div>
          <div style="font-family:'JetBrains Mono',monospace; font-size:1.15rem; letter-spacing:2px; margin: 1.5rem 0 .5rem;"><?= htmlspecialchars($k['no_kartu']) ?></div>
          <div class="flex-between small muted">
            <span><?= htmlspecialchars($rek['nama'] ?? '-') ?></span>
            <span>EXP <?= htmlspecialchars($k['expired']) ?></span>
          </div>
          <div class="small muted mt">Limit harian <b><?= format_rupiah($k['limit_harian']) ?></b></div>
          <form method="post" style="margin-top:.75rem">
            <input type="hidden" name="act" value="toggle">
            <input type="hidden" name="id" value="<?= htmlspecialchars($k['id']) ?>">
            <button class="btn btn-ghost btn-sm btn-block"><?= $aktif?'Blokir Kartu':'Aktifkan Kartu' ?></button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (!$kartu): ?><div class="card" style="grid-column: 1/-1"><div class="no-data">Belum ada kartu terbit. Buat di sebelah kanan.</div></div><?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Terbitkan Kartu</h3></div>
    <form method="post">
      <input type="hidden" name="act" value="add">
      <div class="field"><label>Rekening</label>
        <select class="input" name="no_rek" required>
          <option value="">-- pilih --</option>
          <?php foreach ($rekening as $r): ?>
            <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Tipe</label>
        <select class="input" name="tipe">
          <option>SILVER</option><option>GOLD</option><option>PLATINUM</option>
        </select>
      </div>
      <div class="field"><label>Limit Harian</label>
        <input class="input" type="number" name="limit_harian" value="2000000" min="100000">
      </div>
      <button class="btn btn-primary btn-block">Terbitkan Kartu</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
