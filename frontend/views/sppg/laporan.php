<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['sppg']);
$api = new ApiClient();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'jenis_laporan' => $_POST['jenis_laporan'] ?? '',
        'isi'           => $_POST['isi'] ?? '',
    ];
    if (!empty($_FILES['file']['tmp_name'])) {
        $fields['file'] = new CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name']);
    }
    $res = $api->postForm('/laporan', $fields);
    if (!empty($res['success'])) {
        header('Location: /views/sppg/laporan.php');
        exit;
    }
    $error = $res['error'] ?? 'Gagal membuat laporan';
}

$list = ($api->get('/laporan'))['data'] ?? [];

$pageTitle = 'Laporan';
$activeNav = 'laporan';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Laporan SPPG</h1>
<p class="page-subtitle">Buat laporan rutin dan pantau statusnya.</p>

<div class="panel">
  <h3 style="margin-top:0">Buat Laporan Baru</h3>
  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="field-row">
      <div class="field">
        <label for="jenis_laporan">Jenis Laporan</label>
        <input type="text" id="jenis_laporan" name="jenis_laporan" required placeholder="mis. Laporan Harian Distribusi">
      </div>
    </div>
    <div class="field">
      <label for="isi">Isi Laporan</label>
      <textarea id="isi" name="isi" rows="4" required></textarea>
    </div>
    <div class="field">
      <label for="file">Lampiran (opsional)</label>
      <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.pdf">
    </div>
    <button type="submit" class="btn">Kirim Laporan</button>
  </form>
</div>

<div class="panel">
  <h3 style="margin-top:0">Riwayat Laporan</h3>
  <?php if (empty($list)): ?>
    <p style="color:var(--text-muted)">Belum ada laporan.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tanggal</th><th>Jenis</th><th>Isi</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($list as $l): ?>
        <tr>
          <td><?= htmlspecialchars($l['created_at']) ?></td>
          <td><?= htmlspecialchars($l['jenis_laporan']) ?></td>
          <td class="wrap"><?= htmlspecialchars($l['isi']) ?></td>
          <td><span class="badge <?= htmlspecialchars($l['status']) ?>"><?= htmlspecialchars($l['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
