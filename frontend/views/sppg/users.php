<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['sppg']);
$api = new ApiClient();

$error = null;
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $api->postJson('/users', [
        'nama'     => $_POST['nama'] ?? '',
        'email'    => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
    ]);
    if (!empty($res['success'])) {
        $message = $res['message'] ?? 'Pengajuan akun berhasil dikirim ke Admin BGN.';
    } else {
        $error = $res['error'] ?? 'Gagal mengajukan akun.';
    }
}

// Fetch list of user requests for this SPPG
$myUsers = ($api->get('/users'))['data'] ?? [];

$pageTitle = 'Pengajuan Akun Operator';
$activeNav = 'users';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Pengajuan Akun Operator SPPG</h1>
<p class="page-subtitle">Ajukan pembuatan akun operator baru untuk unit SPPG Anda. Pengajuan membutuhkan persetujuan (approval) dari Admin BGN.</p>

<?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="panel">
  <h3 style="margin-top:0">Form Pengajuan Akun Operator</h3>
  <form method="post">
    <div class="field-row">
      <div class="field">
        <label>Nama Lengkap Operator</label>
        <input type="text" name="nama" required placeholder="Nama operator baru">
      </div>
      <div class="field">
        <label>Email Operator</label>
        <input type="email" name="email" required placeholder="operator@email.com">
      </div>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter">
    </div>
    <button type="submit" class="btn">Kirim Pengajuan Akun</button>
  </form>
</div>

<div class="panel">
  <h3 style="margin-top:0">Daftar &amp; Status Pengajuan Akun Operator</h3>
  <?php if (empty($myUsers)): ?>
    <p style="color:var(--text-muted)">Belum ada akun / pengajuan operator untuk SPPG ini.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Status</th>
          <th>Tgl Pengajuan</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($myUsers as $u): ?>
        <tr>
          <td><strong><?= htmlspecialchars($u['nama']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <?php 
              $st = $u['status'] ?? 'aktif';
              $badgeClass = $st === 'aktif' ? 'success' : ($st === 'pending' ? 'warning' : 'danger');
              $stLabel = $st === 'aktif' ? 'Aktif (Disetujui)' : ($st === 'pending' ? 'Menunggu Approval BGN' : 'Ditolak');
            ?>
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($stLabel) ?></span>
          </td>
          <td><?= htmlspecialchars($u['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
