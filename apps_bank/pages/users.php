<?php
require_once __DIR__ . '/../_layout.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    $list = get_users();
    if ($act === 'role') {
        foreach ($list as &$u) if ($u['username']===$_POST['username']) { $u['role'] = $_POST['role']; break; }
        unset($u);
        write_json(FILE_USERS, $list);
        $msg = 'Role diperbarui';
    } elseif ($act === 'delete' && $_POST['username'] !== current_user()['username']) {
        $list = array_values(array_filter($list, fn($u)=>$u['username']!==$_POST['username']));
        write_json(FILE_USERS, $list);
        $msg = 'User dihapus';
    }
}

$users = get_users();
layout_start('Kelola User', 'Halaman khusus admin · manajemen akun di '.SISTEM_NAMA, true);
?>

<?php if ($msg): ?><div class="alert success"><i data-lucide="check-circle-2"></i><div><?= htmlspecialchars($msg) ?></div></div><?php endif; ?>

<div class="card no-pad">
  <div class="card-header"><h3>Daftar User · <?= count($users) ?></h3><span class="badge badge-accent">ADMIN ONLY</span></div>
  <table class="table">
    <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Dibuat</th><th class="right">Aksi</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <div class="flex gap" style="align-items:center">
              <div class="avatar"><?= inisial_nama($u['nama']) ?></div>
              <div><div class="bold"><?= htmlspecialchars($u['nama']) ?></div><div class="small muted"><code><?= htmlspecialchars($u['username']) ?></code></div></div>
            </div>
          </td>
          <td class="small muted"><?= htmlspecialchars($u['email']??'-') ?></td>
          <td>
            <form method="post" style="display:inline-flex;gap:.4rem;align-items:center">
              <input type="hidden" name="act" value="role">
              <input type="hidden" name="username" value="<?= htmlspecialchars($u['username']) ?>">
              <select class="input" name="role" onchange="this.form.submit()" style="padding:.3rem .5rem;font-size:.78rem;width:auto" <?= $u['username']===current_user()['username']?'disabled':'' ?>>
                <option value="user"  <?= $u['role']==='user'?'selected':'' ?>>user</option>
                <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
              </select>
            </form>
          </td>
          <td class="small muted"><?= htmlspecialchars($u['dibuat']) ?></td>
          <td class="right">
            <?php if ($u['username'] !== current_user()['username']): ?>
              <form method="post" onsubmit="return confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?')" style="display:inline">
                <input type="hidden" name="act" value="delete">
                <input type="hidden" name="username" value="<?= htmlspecialchars($u['username']) ?>">
                <button class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i>Hapus</button>
              </form>
            <?php else: ?>
              <span class="small muted">(Anda)</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php layout_end(); ?>
