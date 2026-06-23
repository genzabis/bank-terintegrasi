<?php
require_once __DIR__ . '/../_layout.php';

$peers = ping_peers();
$audit = get_audit(80);

// Statistik per peer
$stat = [];
foreach (['AppsEcommerce','AppsPendidikan','AppsTravel'] as $p) $stat[$p] = ['in'=>0,'out'=>0,'fail'=>0];
foreach ($audit as $a) {
    if (isset($stat[$a['peer']])) {
        $stat[$a['peer']][strtolower($a['arah'])] = ($stat[$a['peer']][strtolower($a['arah'])] ?? 0) + 1;
        if (!$a['ok']) $stat[$a['peer']]['fail']++;
    }
}

layout_start('Monitor Integrasi', 'Live status dan log komunikasi REST API antar 4 sistem', true);
?>

<div class="row cols-3 mb-2">
  <?php foreach ($peers as $name => $p): ?>
    <div class="card" style="position:relative">
      <div class="flex-between">
        <div>
          <div class="muted small"><?= $p['url'] ?></div>
          <h3 style="margin:.25rem 0;font-size:1.1rem"><?= $name ?></h3>
        </div>
        <span class="pulse-wrap">
          <span class="<?= $p['up']?'pulse':'' ?>" style="<?= !$p['up']?'background:#444a5e;width:9px;height:9px;border-radius:50%;display:inline-block':'' ?>"></span>
          <span class="badge <?= $p['up']?'badge-success':'badge-danger' ?>"><?= $p['up']?'ONLINE':'OFFLINE' ?></span>
        </span>
      </div>
      <div class="row cols-3 mt">
        <div><div class="muted small">Latency</div><div class="bold"><?= $p['up'] ? $p['latency'].' ms' : '—' ?></div></div>
        <div><div class="muted small">Incoming</div><div class="bold"><?= $stat[$name]['in'] ?? 0 ?></div></div>
        <div><div class="muted small">Failed</div><div class="bold" style="color:var(--danger)"><?= $stat[$name]['fail'] ?? 0 ?></div></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card no-pad">
  <div class="card-header">
    <h3>Audit Log Integrasi</h3>
    <div class="flex gap small muted">
      <span>Auto-refresh 5s</span>
      <a href="integrasi.php" class="badge badge-accent">⟳ refresh</a>
    </div>
  </div>
  <div class="scroll" style="max-height:540px">
    <ul class="log-list">
      <?php foreach ($audit as $a): ?>
        <li class="log-item">
          <span class="log-time"><?= htmlspecialchars($a['tanggal']) ?></span>
          <span class="badge <?= $a['arah']==='IN'?'badge-info':'badge-accent' ?>"><?= $a['arah'] ?></span>
          <span class="badge badge-muted"><?= htmlspecialchars($a['peer']) ?></span>
          <span><span class="log-ep"><?= htmlspecialchars($a['action']) ?></span> · <span class="muted"><?= htmlspecialchars($a['endpoint']) ?></span>
            <?php if (!$a['ok']): ?> <span class="badge badge-danger">FAIL</span><?php endif; ?>
            <?php if ($a['message']): ?><div class="small muted"><?= htmlspecialchars($a['message']) ?></div><?php endif; ?>
          </span>
        </li>
      <?php endforeach; ?>
      <?php if (!$audit): ?><div class="no-data">Belum ada aktivitas integrasi. Lakukan transaksi dari sistem lain untuk melihat log di sini.</div><?php endif; ?>
    </ul>
  </div>
</div>

<script>setTimeout(()=>location.reload(), 5000);</script>

<?php layout_end(); ?>
