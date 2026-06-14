<?php
require_once __DIR__ . '/../_layout.php';

$filterRek = $_GET['no_rek'] ?? '';
$filterTipe = $_GET['tipe'] ?? '';
$mutasi = get_mutasi($filterRek ?: null);
if ($filterTipe) $mutasi = array_values(array_filter($mutasi, fn($m)=>$m['tipe']===$filterTipe));
$rekening = get_rekening();

layout_start('Mutasi · Histori Transaksi', 'Filter dan telusuri pergerakan dana setiap rekening');
?>

<form class="card mb" method="get">
  <div class="row cols-3">
    <div class="field" style="margin:0">
      <label>Rekening</label>
      <select class="input" name="no_rek" onchange="this.form.submit()">
        <option value="">Semua rekening</option>
        <?php foreach ($rekening as $r): ?>
          <option value="<?= htmlspecialchars($r['no_rek']) ?>" <?= $filterRek===$r['no_rek']?'selected':'' ?>>
            <?= htmlspecialchars($r['no_rek'].' · '.$r['nama']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" style="margin:0">
      <label>Tipe</label>
      <select class="input" name="tipe" onchange="this.form.submit()">
        <option value="">Semua tipe</option>
        <option value="DEBIT"  <?= $filterTipe==='DEBIT'?'selected':'' ?>>DEBIT</option>
        <option value="KREDIT" <?= $filterTipe==='KREDIT'?'selected':'' ?>>KREDIT</option>
      </select>
    </div>
    <div class="field" style="margin:0;display:flex;align-items:flex-end">
      <a class="btn btn-ghost" href="transaksi.php">Reset</a>
    </div>
  </div>
</form>

<div class="card no-pad">
  <div class="card-header"><h3>Mutasi (<?= count($mutasi) ?> baris)</h3></div>
  <table class="table">
    <thead><tr><th>Tanggal</th><th>No Rek</th><th>Tipe</th><th>Keterangan</th><th>Sumber</th><th class="right">Jumlah</th></tr></thead>
    <tbody>
      <?php foreach ($mutasi as $m): ?>
        <tr>
          <td class="small muted"><?= htmlspecialchars($m['tanggal']) ?></td>
          <td><code><?= htmlspecialchars($m['no_rek']) ?></code></td>
          <td><span class="badge <?= $m['tipe']==='DEBIT'?'badge-danger':'badge-success' ?>"><?= $m['tipe'] ?></span></td>
          <td><?= htmlspecialchars($m['keterangan']) ?></td>
          <td><span class="badge badge-muted"><?= htmlspecialchars($m['sumber']) ?></span></td>
          <td class="right bold"><?= format_rupiah($m['jumlah']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$mutasi): ?><tr><td colspan="6"><div class="no-data">Tidak ada mutasi sesuai filter.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php layout_end(); ?>
