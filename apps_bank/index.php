<?php
require_once __DIR__ . '/_layout.php';

$is_adm = is_admin();
$u = current_user();
$usr = $is_adm ? null : $u['username'];

$rekening = get_rekening($usr);
$kartu    = get_kartu($usr);
$audit    = $is_adm ? get_audit(8) : [];

$semua_mutasi = get_mutasi();
if ($is_adm) {
    $mutasi = $semua_mutasi;
} else {
    $my_reks = array_column($rekening, 'no_rek');
    $mutasi = array_values(array_filter($semua_mutasi, fn($m) => in_array($m['no_rek'], $my_reks)));
}

$totalSaldo  = array_sum(array_column($rekening, 'saldo'));
$totalRek    = count($rekening);
$totalMutasi = count($mutasi);
$totalDebit  = array_sum(array_column(array_filter($mutasi, fn($m)=>$m['tipe']==='DEBIT'),  'jumlah'));
$totalKredit = array_sum(array_column(array_filter($mutasi, fn($m)=>$m['tipe']==='KREDIT'), 'jumlah'));
$perHari     = mutasi_per_hari(7, $is_adm ? null : array_column($rekening, 'no_rek'));
$maxHari     = max(1, max(array_map(fn($d)=>$d['debit']+$d['kredit'], $perHari)));

layout_start('Dashboard Bank', 'Ringkasan keuangan, kartu, dan mutasi transaksi');
?>

<div class="hero">
  <div style="flex:1">
    <h2>Selamat datang di AppsBank</h2>
    <p style="opacity: 0.9; line-height: 1.6;">Pusat layanan keuangan terdistribusi. Mengelola rekening, kartu debit, dan mutasi saldo nasabah secara real-time.</p>
  </div>
  <i data-lucide="wallet" class="hero-icon hide-sm" style="width: 80px; height: 80px; opacity: 0.2;"></i>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon info"><i data-lucide="users"></i></div>
    <div>
      <div class="label">Rekening Aktif</div>
      <div class="value"><?= $totalRek ?></div>
      <div class="delta">+<?= count($kartu) ?> kartu terbit</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon primary"><i data-lucide="landmark"></i></div>
    <div>
      <div class="label">Total Saldo Sistem</div>
      <div class="value"><?= format_rupiah($totalSaldo) ?></div>
      <div class="delta">aset terkelola</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon danger"><i data-lucide="trending-down"></i></div>
    <div>
      <div class="label">Total Debit</div>
      <div class="value"><?= format_rupiah($totalDebit) ?></div>
      <div class="delta neg">outflow sistem</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon success"><i data-lucide="trending-up"></i></div>
    <div>
      <div class="label">Total Kredit</div>
      <div class="value"><?= format_rupiah($totalKredit) ?></div>
      <div class="delta">inflow sistem</div>
    </div>
  </div>
</div>

<?php if ($is_adm): ?>
<div class="row cols-2-1 mb-2">
<?php else: ?>
<div class="mb-2">
<?php endif; ?>
  <div class="card no-pad">
    <div class="card-header">
      <div style="display:flex; align-items:center; gap:0.5rem;"><i data-lucide="bar-chart-2" style="color:var(--text-mute)"></i><h3>Volume Transaksi (7 Hari)</h3></div>
    </div>
    <div style="padding: 1rem 1.5rem 2rem">
      <div class="bar-chart">
        <?php foreach ($perHari as $tgl=>$d): $tot = $d['debit']+$d['kredit']; ?>
          <div class="bar" style="height: <?= $tot===0?4:max(8, ($tot/$maxHari)*145) ?>px" title="<?= $tgl ?>: <?= format_rupiah($tot) ?>">
            <span class="val"><?= $tot>0 ? round($tot/1000).'k' : '' ?></span>
            <span class="lbl"><?= date('d/m', strtotime($tgl)) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php if ($is_adm): ?>
  <div class="card no-pad">
    <div class="card-header">
      <div style="display:flex; align-items:center; gap:0.5rem;"><i data-lucide="activity" style="color:var(--text-mute)"></i><h3>Aktivitas API</h3></div>
      <a href="pages/integrasi.php" class="small muted">lihat semua &rarr;</a>
    </div>
    <div class="scroll" style="max-height:280px">
      <?php foreach ($audit as $a): ?>
        <div class="list-item">
          <div class="flex-between">
            <span class="badge <?= $a['arah']==='IN'?'badge-info':'badge-accent' ?>"><?= $a['arah'] ?></span>
            <span class="muted small"><?= relative_time($a['tanggal']) ?></span>
          </div>
          <div style="margin-top:.4rem"><b><?= htmlspecialchars($a['peer']) ?></b> &middot; <code><?= htmlspecialchars($a['action']) ?></code></div>
          <div class="muted small" style="margin-top:0.2rem;"><?= htmlspecialchars($a['message'] ?: '-') ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (!$audit): ?><div class="no-data">Belum ada aktivitas integrasi.</div><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="row cols-2 gap-2">
  <div class="card no-pad">
    <div class="card-header">
      <div style="display:flex; align-items:center; gap:0.5rem;"><i data-lucide="wallet" style="color:var(--text-mute)"></i><h3>Rekening Aktif</h3></div>
      <a href="pages/rekening.php" class="small muted">kelola &rarr;</a>
    </div>
    <table class="table">
      <thead><tr><th>No Rekening</th><th>Nama</th><th class="right">Saldo</th></tr></thead>
      <tbody>
        <?php foreach ($rekening as $r): ?>
          <tr>
            <td><code><?= htmlspecialchars($r['no_rek']) ?></code></td>
            <td><?= htmlspecialchars($r['nama']) ?></td>
            <td class="right bold"><?= format_rupiah($r['saldo']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card no-pad">
    <div class="card-header">
      <div style="display:flex; align-items:center; gap:0.5rem;"><i data-lucide="arrow-left-right" style="color:var(--text-mute)"></i><h3>Mutasi Terbaru</h3></div>
      <a href="pages/transaksi.php" class="small muted">semua mutasi &rarr;</a>
    </div>
    <div class="scroll" style="max-height:340px">
      <table class="table">
        <thead><tr><th>Tanggal</th><th>Rek</th><th>Tipe</th><th class="right">Jumlah</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($mutasi, 0, 12) as $m): ?>
            <tr>
              <td class="small muted"><?= htmlspecialchars($m['tanggal']) ?></td>
              <td><code><?= htmlspecialchars($m['no_rek']) ?></code></td>
              <td><span class="badge <?= $m['tipe']==='DEBIT'?'badge-danger':'badge-success' ?>"><?= $m['tipe'] ?></span></td>
              <td class="right bold"><?= format_rupiah($m['jumlah']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$mutasi): ?><tr><td colspan="4"><div class="no-data">Belum ada mutasi.</div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php layout_end(); ?>
