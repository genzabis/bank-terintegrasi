<?php
require_once __DIR__ . '/../_layout.php';

$msg=''; $cls='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = trim($_POST['produk_id']);
    $found = null;
    foreach (get_produk_siswa() as $p) if ($p['id']===$id) { $found=$p; break; }
    if (!$found) { $msg='Produk tidak ditemukan'; $cls='danger'; }
    else {
        $r = upload_produk_ke_ecommerce($found);
        if (!empty($r['success'])) {
            $newId = $r['data']['id'] ?? null;
            set_produk_siswa_field($id, 'ecommerce_id', $newId);
            $msg = "Berhasil! Produk terdaftar di Ecommerce id=$newId · siap dijual."; $cls='success';
        } else { $msg='Gagal upload: '.($r['message']??''); $cls='danger'; }
    }
}
$produk = get_produk_siswa();
layout_start('Upload Karya ke Ecommerce', 'Publikasi produk siswa ke marketplace via REST API');
?>

<?php if ($msg): ?><div class="alert <?= $cls ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="card mb-2">
  <p class="small muted" style="margin:0">
    Saat klik <b>Upload</b>, sistem ini memanggil <code>POST <?= ECOMMERCE_URL ?>/api.php?action=add_produk</code> dengan payload JSON berisi nama, harga, stok, dan kategori "siswa". Produk akan langsung tampil di etalase Ecommerce.
  </p>
</div>

<div class="card no-pad">
  <div class="card-header"><h3>Pilih Produk untuk Dipublikasi</h3></div>
  <table class="table">
    <thead><tr><th>Produk</th><th>Pencipta</th><th class="right">Harga</th><th>Stok</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($produk as $p): ?>
        <tr>
          <td><div class="bold"><?= htmlspecialchars($p['nama']) ?></div><div class="small muted"><?= htmlspecialchars($p['deskripsi']??'') ?></div></td>
          <td class="small"><?= htmlspecialchars($p['siswa_nama']??'-') ?></td>
          <td class="right bold"><?= format_rupiah($p['harga']) ?></td>
          <td><?= (int)$p['stok'] ?></td>
          <td>
            <?php if (!empty($p['ecommerce_id'])): ?>
              <span class="badge badge-success">Live</span>
              <div class="small muted"><?= htmlspecialchars($p['ecommerce_id']) ?></div>
            <?php else: ?>
              <span class="badge badge-muted">Belum</span>
            <?php endif; ?>
          </td>
          <td class="right">
            <?php if (empty($p['ecommerce_id'])): ?>
              <form method="post" onsubmit="return confirm('Upload ke AppsEcommerce?')">
                <input type="hidden" name="produk_id" value="<?= htmlspecialchars($p['id']) ?>">
                <button class="btn btn-primary btn-sm"><i data-lucide="upload-cloud"></i>Upload</button>
              </form>
            <?php else: ?>
              <a class="btn btn-ghost btn-sm" target="_blank" href="<?= ECOMMERCE_URL ?>/index.php?kategori=siswa">Lihat di Ecommerce</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$produk): ?><tr><td colspan="6"><div class="no-data">Belum ada produk siswa. Buat di menu Produk Karya.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php layout_end(); ?>
