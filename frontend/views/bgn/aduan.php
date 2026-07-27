<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['bgn']);
$api = new ApiClient();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aduan_id'])) {
    $api->patchJson('/aduan/' . (int)$_POST['aduan_id'] . '/status', ['status' => $_POST['status']]);
    header('Location: /views/bgn/aduan.php');
    exit;
}

$filterStatus = $_GET['status'] ?? '';
$list = ($api->get('/aduan', $filterStatus ? ['status' => $filterStatus] : []))['data'] ?? [];

$pageTitle = 'Semua Aduan';
$activeNav = 'aduan';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Semua Aduan</h1>
<p class="page-subtitle">Pantau dan perbarui status aduan dari seluruh masyarakat.</p>

<div class="panel">
  <form method="get" class="field-row" style="align-items:flex-end">
    <div class="field" style="max-width:220px">
      <label for="status">Filter Status</label>
      <select id="status" name="status" onchange="this.form.submit()">
        <option value="">Semua</option>
        <option value="baru" <?= $filterStatus === 'baru' ? 'selected' : '' ?>>Baru</option>
        <option value="diproses" <?= $filterStatus === 'diproses' ? 'selected' : '' ?>>Diproses</option>
        <option value="selesai" <?= $filterStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
      </select>
    </div>
  </form>
</div>

<div class="panel">
  <?php if (empty($list)): ?>
    <p style="color:var(--text-muted)">Tidak ada aduan.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tanggal</th><th>Kategori</th><th>Isi</th><th>Tanggapan SPPG</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($list as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['created_at']) ?></td>
          <td><?= htmlspecialchars($a['kategori']) ?></td>
          <td class="wrap"><?= htmlspecialchars($a['isi']) ?></td>
          <td class="wrap"><?= $a['tanggapan'] ? htmlspecialchars($a['tanggapan']) : '-' ?></td>
          <td><span class="badge <?= htmlspecialchars($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></span></td>
          <td>
            <form method="post" style="display:flex; gap:6px">
              <input type="hidden" name="aduan_id" value="<?= (int)$a['id'] ?>">
              <select name="status" style="width:auto">
                <option value="baru" <?= $a['status']==='baru'?'selected':'' ?>>Baru</option>
                <option value="diproses" <?= $a['status']==='diproses'?'selected':'' ?>>Diproses</option>
                <option value="selesai" <?= $a['status']==='selesai'?'selected':'' ?>>Selesai</option>
              </select>
              <button type="submit" class="btn small">Simpan</button>
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
