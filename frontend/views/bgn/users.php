<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['bgn']);
$api = new ApiClient();

$error = null;
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    
    if ($action === 'create_user') {
        $res = $api->postJson('/users', [
            'nama'     => $_POST['nama'] ?? '',
            'email'    => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'role'     => $_POST['role'] ?? 'sppg',
            'sppg_id'  => !empty($_POST['sppg_id']) ? (int)$_POST['sppg_id'] : null,
        ]);
        if (!empty($res['success'])) {
            $message = $res['message'] ?? 'Akun berhasil dibuat.';
        } else {
            $error = $res['error'] ?? 'Gagal membuat akun.';
        }
    } elseif ($action === 'update_status') {
        $userId = (int)($_POST['id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        
        if ($userId > 0 && in_array($newStatus, ['aktif', 'ditolak'], true)) {
            $res = $api->patchJson("/users/$userId/status", ['status' => $newStatus]);
            if (!empty($res['success'])) {
                $message = $res['message'] ?? 'Status akun berhasil diperbarui.';
            } else {
                $error = $res['error'] ?? 'Gagal memperbarui status akun.';
            }
        }
    }
}

// Fetch data
$allUsers = ($api->get('/users'))['data'] ?? [];
$sppgList = ($api->get('/sppg'))['data'] ?? [];

$pendingUsers = array_filter($allUsers, fn($u) => ($u['status'] ?? '') === 'pending');

$pageTitle = 'Kelola Akun';
$activeNav = 'users';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Kelola &amp; Persetujuan Akun</h1>
<p class="page-subtitle">Tambah akun baru atau setujui pengajuan akun dari Operator SPPG.</p>

<?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- Panel 1: Pengajuan Akun Menunggu Persetujuan -->
<div class="panel">
  <h3 style="margin-top:0">Pengajuan Akun (Menunggu Persetujuan)</h3>
  <?php if (empty($pendingUsers)): ?>
    <p style="color:var(--text-muted)">Tidak ada pengajuan akun yang menunggu persetujuan.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>SPPG Unit</th>
          <th>Tgl Pengajuan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($pendingUsers as $pu): ?>
        <tr>
          <td><strong><?= htmlspecialchars($pu['nama']) ?></strong></td>
          <td><?= htmlspecialchars($pu['email']) ?></td>
          <td><?= htmlspecialchars($pu['sppg_nama'] ?? '-') ?></td>
          <td><?= htmlspecialchars($pu['created_at']) ?></td>
          <td>
            <div style="display:flex; gap:6px;">
              <form method="post" style="display:inline;">
                <input type="hidden" name="_action" value="update_status">
                <input type="hidden" name="id" value="<?= (int)$pu['id'] ?>">
                <input type="hidden" name="status" value="aktif">
                <button type="submit" class="btn small success">Setujui</button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Tolak pengajuan akun ini?')">
                <input type="hidden" name="_action" value="update_status">
                <input type="hidden" name="id" value="<?= (int)$pu['id'] ?>">
                <input type="hidden" name="status" value="ditolak">
                <button type="submit" class="btn small danger">Tolak</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Panel 2: Form Tambah Akun Langsung oleh BGN -->
<div class="panel">
  <h3 style="margin-top:0">Tambah Akun Baru (Langsung Aktif)</h3>
  <form method="post">
    <input type="hidden" name="_action" value="create_user">
    <div class="field-row">
      <div class="field">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required placeholder="Contoh: Ahmad Subagyo">
      </div>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" required placeholder="nama@email.com">
      </div>
    </div>
    <div class="field-row">
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter">
      </div>
      <div class="field">
        <label>Role / Peran</label>
        <select name="role" id="roleSelect" onchange="toggleSppgSelect()">
          <option value="sppg">Operator SPPG</option>
          <option value="bgn">Admin BGN</option>
        </select>
      </div>
    </div>
    <div class="field" id="sppgField">
      <label>Unit SPPG</label>
      <select name="sppg_id">
        <option value="">-- Pilih Unit SPPG --</option>
        <?php foreach ($sppgList as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['nama']) ?> (<?= htmlspecialchars($s['wilayah'] ?? '-') ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn">Buat Akun</button>
  </form>
</div>

<!-- Panel 3: Daftar Semua User -->
<div class="panel">
  <h3 style="margin-top:0">Daftar Pengguna Terdaftar</h3>
  <?php if (empty($allUsers)): ?>
    <p style="color:var(--text-muted)">Belum ada pengguna terdaftar.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th>SPPG Unit</th>
          <th>Status</th>
          <th>Terdaftar</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($allUsers as $u): ?>
        <tr>
          <td><strong><?= htmlspecialchars($u['nama']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge blue"><?= strtoupper(htmlspecialchars($u['role'])) ?></span></td>
          <td><?= htmlspecialchars($u['sppg_nama'] ?? '-') ?></td>
          <td>
            <?php 
              $st = $u['status'] ?? 'aktif';
              $badgeClass = $st === 'aktif' ? 'success' : ($st === 'pending' ? 'warning' : 'danger');
            ?>
            <span class="badge <?= $badgeClass ?>"><?= ucfirst(htmlspecialchars($st)) ?></span>
          </td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
function toggleSppgSelect() {
  var role = document.getElementById('roleSelect').value;
  var sppgField = document.getElementById('sppgField');
  if (role === 'bgn') {
    sppgField.style.display = 'none';
  } else {
    sppgField.style.display = 'block';
  }
}
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
