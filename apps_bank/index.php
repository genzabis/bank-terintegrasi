<?php
require_once __DIR__ . '/_layout.php';

$rekening = get_rekening();
$mutasi   = get_mutasi();
$kartu    = get_kartu();
$audit    = get_audit(8);

$totalSaldo  = array_sum(array_column($rekening, 'saldo'));
$totalRek    = count($rekening);
$totalMutasi = count($mutasi);
$totalDebit  = array_sum(array_column(array_filter($mutasi, fn($m)=>$m['tipe']==='DEBIT'),  'jumlah'));
$totalKredit = array_sum(array_column(array_filter($mutasi, fn($m)=>$m['tipe']==='KREDIT'), 'jumlah'));
$perHari     = mutasi_per_hari(7);
$maxHari     = max(1, max(array_map(fn($d)=>$d['debit']+$d['kredit'], $perHari)));

layout_start('Dashboard Bank', 'Ringkasan keuangan, kartu, dan integrasi antar sistem');
?>

<div class="hero">
  <h2>Selamat datang di pusat layanan keuangan</h2>
  <p>Sistem A — AppsBank · Mengelola rekening, validasi saldo, kartu debit, dan menerima request debit/kredit dari Ecommerce, Pendidikan, dan Travel via REST API.</p>
</div>

<div class="stats-grid">
  <div class="stat-card"><div class="label">Rekening Aktif</div><div class="value"><?= $totalRek ?></div><div class="delta">+<?= count($kartu) ?> kartu terbit</div></div>
  <div class="stat-card"><div class="label">Total Saldo Sistem</div><div class="value"><?= format_rupiah($totalSaldo) ?></div><div class="delta">aset terkelola</div></div>
  <div class="stat-card"><div class="label">Total Debit</div><div class="value"><?= format_rupiah($totalDebit) ?></div><div class="delta neg">↓ outflow</div></div>
  <div class="stat-card"><div class="label">Total Kredit</div><div class="value"><?= format_rupiah($totalKredit) ?></div><div class="delta">↑ inflow</div></div>
</div>

<div class="row cols-2-1 mb-2">
  <div class="card no-pad">
    <div class="card-header"><h3>Volume Transaksi · 7 hari terakhir</h3><span class="badge badge-accent">DEBIT vs KREDIT</span></div>
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

  <div class="card no-pad">
    <div class="card-header"><h3>Aktivitas Antar Sistem</h3><a href="pages/integrasi.php" class="small muted">lihat semua →</a></div>
    <div class="scroll" style="max-height:280px">
      <?php foreach ($audit as $a): ?>
        <div style="padding:.7rem 1.25rem;border-bottom:1px solid var(--border);font-size:.83rem">
          <div class="flex-between">
            <span class="badge <?= $a['arah']==='IN'?'badge-info':'badge-accent' ?>"><?= $a['arah'] ?></span>
            <span class="muted small"><?= relative_time($a['tanggal']) ?></span>
          </div>
          <div style="margin-top:.3rem"><b><?= htmlspecialchars($a['peer']) ?></b> · <code><?= htmlspecialchars($a['action']) ?></code></div>
          <div class="muted small"><?= htmlspecialchars($a['message'] ?: '-') ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (!$audit): ?><div class="no-data">Belum ada aktivitas integrasi.</div><?php endif; ?>
    </div>
  </div>
</div>

<div class="row cols-2 gap-2">
  <div class="card no-pad">
    <div class="card-header"><h3>Rekening Aktif</h3><a href="pages/rekening.php" class="small muted">kelola →</a></div>
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
    <div class="card-header"><h3>Mutasi Terbaru</h3><a href="pages/transaksi.php" class="small muted">selengkapnya →</a></div>
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
