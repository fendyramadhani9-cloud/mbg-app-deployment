<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['bgn']);
$api = new ApiClient();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['_action'] ?? '') === 'create') {
        $res = $api->postJson('/sppg', [
            'nama' => $_POST['nama'] ?? '',
            'alamat' => $_POST['alamat'] ?? '',
            'wilayah' => $_POST['wilayah'] ?? '',
            'penanggung_jawab' => $_POST['penanggung_jawab'] ?? '',
        ]);
        if (empty($res['success'])) $error = $res['error'] ?? 'Gagal menambah SPPG';
    } elseif (($_POST['_action'] ?? '') === 'delete' && !empty($_POST['id'])) {
        $api->delete('/sppg/' . (int)$_POST['id']);
    }
    if (!$error) { header('Location: /views/bgn/sppg.php'); exit; }
}

$list = ($api->get('/sppg'))['data'] ?? [];

$pageTitle = 'Master SPPG';
$activeNav = 'sppg';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Master Data SPPG</h1>
<p class="page-subtitle">Kelola data SPPG yang terdaftar di sistem.</p>

<div class="panel">
  <h3 style="margin-top:0">Tambah SPPG</h3>
  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="_action" value="create">
    <div class="field-row">
      <div class="field"><label>Nama SPPG</label><input type="text" name="nama" required></div>
      <div class="field"><label>Wilayah</label><input type="text" name="wilayah"></div>
    </div>
    <div class="field-row">
      <div class="field"><label>Alamat</label><input type="text" name="alamat"></div>
      <div class="field"><label>Penanggung Jawab</label><input type="text" name="penanggung_jawab"></div>
    </div>
    <button type="submit" class="btn">Tambah SPPG</button>
  </form>
</div>

<div class="panel">
  <h3 style="margin-top:0">Daftar SPPG</h3>
  <?php if (empty($list)): ?>
    <p style="color:var(--text-muted)">Belum ada SPPG terdaftar.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Nama</th><th>Wilayah</th><th>Alamat</th><th>PJ</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($list as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['nama']) ?></td>
          <td><?= htmlspecialchars($s['wilayah'] ?? '-') ?></td>
          <td class="wrap"><?= htmlspecialchars($s['alamat'] ?? '-') ?></td>
          <td><?= htmlspecialchars($s['penanggung_jawab'] ?? '-') ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Hapus SPPG ini?')">
              <input type="hidden" name="_action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <button type="submit" class="btn small danger">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
