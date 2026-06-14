<?php
require_once __DIR__ . '/_layout.php';

$siswa     = get_siswa();
$produk    = get_produk_siswa();
$spp       = get_spp();
$prestasi  = get_prestasi();
$audit     = get_audit(8);

$totalBelum = array_sum(array_map(fn($s)=>$s['status']==='BELUM' ? (float)$s['jumlah']:0, $spp));
$totalLunas = array_sum(array_map(fn($s)=>$s['status']==='LUNAS' ? (float)$s['jumlah']:0, $spp));
$lunasCount = count(array_filter($spp, fn($s)=>$s['status']==='LUNAS'));
$totalSpp = count($spp);

layout_start('Dashboard Pendidikan', 'Manajemen siswa, SPP, produk karya, dan integrasi diskon travel');
?>

<div class="hero">
  <h2>Pusat data sekolah & lintas sistem</h2>
  <p>Sistem C — AppsPendidikan · Mengelola siswa, SPP via Bank, mengirim karya siswa ke Ecommerce, dan menerbitkan voucher diskon untuk Travel.</p>
</div>

<div class="stats-grid">
  <div class="stat-card"><div class="label">Siswa Aktif</div><div class="value"><?= count($siswa) ?></div><div class="delta">+<?= count($prestasi) ?> prestasi tercatat</div></div>
  <div class="stat-card"><div class="label">Produk Karya</div><div class="value"><?= count($produk) ?></div><div class="delta"><?= count(array_filter($produk, fn($p)=>!empty($p['ecommerce_id']))) ?> live di Ecommerce</div></div>
  <div class="stat-card"><div class="label">SPP Belum Lunas</div><div class="value"><?= format_rupiah($totalBelum) ?></div><div class="delta neg">menunggu</div></div>
  <div class="stat-card"><div class="label">SPP Lunas</div><div class="value"><?= format_rupiah($totalLunas) ?></div><div class="delta"><?= $totalSpp?round($lunasCount/$totalSpp*100):0 ?>% terbayar</div></div>
</div>

<div class="row cols-2-1 mb-2">
  <div class="card no-pad">
    <div class="card-header"><h3>Daftar Siswa</h3><a href="pages/siswa.php" class="small muted">kelola →</a></div>
    <table class="table">
      <thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Rek Bank</th></tr></thead>
      <tbody>
        <?php foreach ($siswa as $s): ?>
          <tr>
            <td><div class="flex gap" style="align-items:center"><div class="avatar"><?= inisial($s['nama']) ?></div><code><?= htmlspecialchars($s['id']) ?></code></div></td>
            <td><?= htmlspecialchars($s['nama']) ?></td>
            <td><span class="badge badge-muted"><?= htmlspecialchars($s['kelas']) ?></span></td>
            <td class="muted small"><?= htmlspecialchars($s['no_rek']??'-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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
          <div class="muted small"><?= htmlspecialchars($a['message']?:'-') ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (!$audit): ?><div class="no-data">Belum ada aktivitas integrasi.</div><?php endif; ?>
    </div>
  </div>
</div>

<div class="row cols-2 gap-2">
  <div class="card no-pad">
    <div class="card-header"><h3>Produk Karya Terbaru</h3><a href="pages/produk_siswa.php" class="small muted">lihat semua →</a></div>
    <table class="table">
      <thead><tr><th>Produk</th><th>Pencipta</th><th class="right">Harga</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach (array_slice($produk, 0, 5) as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['nama']) ?></td>
            <td class="small muted"><?= htmlspecialchars($p['siswa_nama']??'-') ?></td>
            <td class="right bold"><?= format_rupiah($p['harga']) ?></td>
            <td><?= !empty($p['ecommerce_id'])?'<span class="badge badge-success">Live</span>':'<span class="badge badge-muted">Lokal</span>' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card no-pad">
    <div class="card-header"><h3>Prestasi Siswa</h3><a href="pages/prestasi.php" class="small muted">selengkapnya →</a></div>
    <div style="padding:1rem 1.5rem">
      <?php foreach (array_slice($prestasi, 0, 5) as $pr): $s=find_siswa($pr['siswa_id']); ?>
        <div class="flex gap" style="padding:.6rem 0;border-bottom:1px solid var(--border);align-items:center">
          <div class="avatar" style="width:32px;height:32px"><i data-lucide="award" style="width:15px;height:15px;color:#fff"></i></div>
          <div style="flex:1">
            <div class="bold small"><?= htmlspecialchars($pr['judul']) ?></div>
            <div class="muted small"><?= htmlspecialchars($s['nama']??$pr['siswa_id']) ?> · <?= htmlspecialchars($pr['tingkat']) ?></div>
          </div>
          <span class="muted small"><?= htmlspecialchars($pr['tanggal']) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$prestasi): ?><div class="no-data">Belum ada prestasi tercatat.</div><?php endif; ?>
    </div>
  </div>
</div>

<?php layout_end(); ?>
