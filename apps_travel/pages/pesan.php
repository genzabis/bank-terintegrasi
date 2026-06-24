<?php
require_once __DIR__ . '/../_layout.php';

$tiket = get_tiket();
$rek = fetch_rekening_bank();
$user = current_user()['username'] ?? 'guest';
if ($user !== 'guest') {
    $rek = array_values(array_filter($rek, fn($r) => ($r['username'] ?? '') === $user));
}
$pre = $_GET['tiket_id'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $r = proses_pesan_tiket(
        $_POST['tiket_id'],
        max(1,(int)$_POST['qty']),
        trim($_POST['pemesan']) ?: 'Tamu',
        trim($_POST['no_rek']) ?: null,
        trim($_POST['kode_voucher']) ?: null,
        0,
        'WEB-PESAN'
    );
    if (!empty($r['success'])) {
        set_flash_msg("Booking sukses · Kode {$r['data']['kode']} · Total ".format_rupiah($r['data']['total']), 'success');
        header('Location: pesan.php'); exit;
    } else { 
        set_flash_msg($r['message']??'Gagal', 'danger');
        header('Location: pesan.php'); exit; 
    }
}

layout_start('Pesan Tiket', 'Booking tiket dengan pembayaran via AppsBank dan voucher dari Pendidikan');
?>



<div class="row cols-1-2">
  <div class="card">
    <div class="card-header"><h3>Tips</h3></div>
    <p class="small muted">Pemesanan tanpa <code>no_rek</code> akan langsung berstatus <b>TERBOOKING</b> tanpa pembayaran. Mengisi rekening akan otomatis debit Bank dan menjadi <b>LUNAS</b>.</p>
    <p class="small muted">Voucher dari AppsPendidikan (format <code>EDU-XXXXXX-NN</code>) akan otomatis diapply diskonnya saat pembayaran.</p>
  </div>

  <div class="card">
    <div class="card-header"><h3>Form Pemesanan</h3></div>
    <form method="post">
      <div class="field"><label>Tiket</label>
        <select class="input" name="tiket_id" required>
          <option value="">-- pilih tiket --</option>
          <?php foreach ($tiket as $t): ?>
            <option value="<?= htmlspecialchars($t['id']) ?>" <?= $pre===$t['id']?'selected':'' ?>>
              <?= htmlspecialchars($t['nama'].' · '.($t['rute']??'').' · '.format_rupiah($t['harga']).' · sisa '.$t['kuota']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row cols-2">
        <div class="field"><label>Jumlah</label><input class="input" type="number" name="qty" value="1" min="1" required></div>
        <div class="field"><label>Pemesan</label><input class="input" name="pemesan" placeholder="Nama" required></div>
      </div>
      <div class="field"><label>Voucher (opsional)</label><input class="input" name="kode_voucher" placeholder="EDU-XXXXXX-25"></div>
      <div class="field"><label>Rekening Pembayar (opsional)</label>
        <select class="input" name="no_rek">
          <option value="">-- tanpa pembayaran (booking saja) --</option>
          <?php foreach ($rek as $r): ?>
            <option value="<?= htmlspecialchars($r['no_rek']) ?>"><?= htmlspecialchars($r['no_rek'].' · '.$r['nama'].' ('.format_rupiah($r['saldo']).')') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary btn-block">Pesan Sekarang</button>
    </form>
  </div>
</div>

<?php layout_end(); ?>
