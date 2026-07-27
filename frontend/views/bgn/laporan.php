<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['bgn']);
$api = new ApiClient();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['laporan_id'])) {
    $api->patchJson('/laporan/' . (int)$_POST['laporan_id'] . '/status', ['status' => $_POST['status']]);
    header('Location: /views/bgn/laporan.php');
    exit;
}

$filterStatus = $_GET['status'] ?? '';
$list = ($api->get('/laporan', $filterStatus ? ['status' => $filterStatus] : []))['data'] ?? [];

$pageTitle = 'Semua Laporan';
$activeNav = 'laporan';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Semua Laporan</h1>
<p class="page-subtitle">Tinjau dan perbarui status laporan dari seluruh SPPG.</p>

<div class="panel">
  <form method="get" class="field-row" style="align-items:flex-end">
    <div class="field" style="max-width:220px">
      <label for="status">Filter Status</label>
      <select id="status" name="status" onchange="this.form.submit()">
        <option value="">Semua</option>
        <option value="menunggu" <?= $filterStatus === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
        <option value="ditinjau" <?= $filterStatus === 'ditinjau' ? 'selected' : '' ?>>Ditinjau</option>
        <option value="selesai" <?= $filterStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
      </select>
    </div>
  </form>
</div>

<div class="panel">
  <?php if (empty($list)): ?>
    <p style="color:var(--text-muted)">Tidak ada laporan.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tanggal</th><th>Jenis</th><th>Isi</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($list as $l): ?>
        <tr>
          <td><?= htmlspecialchars($l['created_at']) ?></td>
          <td><?= htmlspecialchars($l['jenis_laporan']) ?></td>
          <td class="wrap"><?= htmlspecialchars($l['isi']) ?></td>
          <td><span class="badge <?= htmlspecialchars($l['status']) ?>"><?= htmlspecialchars($l['status']) ?></span></td>
          <td>
            <form method="post" style="display:flex; gap:6px">
              <input type="hidden" name="laporan_id" value="<?= (int)$l['id'] ?>">
              <select name="status" style="width:auto">
                <option value="menunggu" <?= $l['status']==='menunggu'?'selected':'' ?>>Menunggu</option>
                <option value="ditinjau" <?= $l['status']==='ditinjau'?'selected':'' ?>>Ditinjau</option>
                <option value="selesai" <?= $l['status']==='selesai'?'selected':'' ?>>Selesai</option>
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
