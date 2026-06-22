<?php
require_once __DIR__ . '/../_layout.php';

$msg = ''; $cls = '';
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $cls = $_GET['err'] ?? 0 ? 'danger' : 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dari = trim($_POST['dari']);
    $ke   = trim($_POST['ke']);
    $amt  = (float)$_POST['jumlah'];
    $ket  = trim($_POST['keterangan'] ?? 'Transfer');
    if (!$dari || !$ke || $amt <= 0) { 
        header("Location: transfer.php?msg=" . urlencode('Parameter tidak lengkap') . "&err=1"); exit;
    } elseif ($dari === $ke) { 
        header("Location: transfer.php?msg=" . urlencode('Rekening asal & tujuan sama') . "&err=1"); exit;
    } else {
        $r1 = update_saldo($dari, $amt, 'DEBIT', "Transfer ke $ke - $ket", 'INTERNAL');
        if (!$r1['success']) { 
            header("Location: transfer.php?msg=" . urlencode($r1['message']) . "&err=1"); exit;
        } else {
            update_saldo($ke, $amt, 'KREDIT', "Transfer dari $dari - $ket", 'INTERNAL');
            header("Location: transfer.php?msg=" . urlencode('Transfer sukses · Sisa saldo ' . format_rupiah($r1['saldo'])) . "&err=0"); exit;
        }
    }
}
$rekening = get_rekening();
layout_start('Transfer Antar Rekening', 'Pindahkan dana antar rekening internal Bank');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><span class="ico">●</span><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="row cols-1-2" style="max-width:900px">
  <div class="card">
    <div class="card-header"><h3>Tips</h3></div>
    <p class="small muted">Transfer akan menambahkan 2 baris mutasi: DEBIT pada rekening asal dan KREDIT pada rekening tujuan. Saldo divalidasi sebelum proses dijalankan.</p>
    <p class="small muted">Untuk pembayaran lintas sistem (Ecommerce/Travel/Pendidikan), gunakan endpoint <code>POST /api.php?action=debit</code>.</p>
  </div>
  <div class="card">
    <div class="card-header"><h3>Form Transfer</h3></div>
    <form method="post">
      <div class="row cols-2">
        <div class="field"><label>Dari</label>
          <select class="input" name="dari" required>
            <option value="">-- pilih --</option>
            <?php foreach ($rekening as $r): ?>
              <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama'].' ('.format_rupiah($r['saldo']).')') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Ke</label>
          <select class="input" name="ke" required>
            <option value="">-- pilih --</option>
            <?php foreach ($rekening as $r): ?>
              <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="field"><label>Jumlah</label><input type="number" min="1" class="input" name="jumlah" required></div>
      <div class="field"><label>Keterangan</label><input class="input" name="keterangan" value="Transfer"></div>
      <button class="btn btn-primary btn-block">Eksekusi Transfer</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
