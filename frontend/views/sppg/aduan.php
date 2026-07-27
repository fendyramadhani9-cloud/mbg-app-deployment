<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['sppg']);
$api = new ApiClient();

$notice = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aduan_id'])) {
    $res = $api->patchJson('/aduan/' . (int)$_POST['aduan_id'] . '/tanggapan', [
        'tanggapan' => $_POST['tanggapan'] ?? '',
    ]);
    $notice = !empty($res['success']) ? ['success', 'Tanggapan berhasil dikirim'] : ['error', $res['error'] ?? 'Gagal mengirim tanggapan'];
}

$list = ($api->get('/aduan'))['data'] ?? [];

$pageTitle = 'Aduan Masuk';
$activeNav = 'aduan';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Aduan Masuk</h1>
<p class="page-subtitle">Aduan masyarakat untuk SPPG Anda. Berikan tanggapan untuk setiap aduan.</p>

<?php if ($notice): ?><div class="alert <?= $notice[0] ?>"><?= htmlspecialchars($notice[1]) ?></div><?php endif; ?>

<?php if (empty($list)): ?>
  <div class="panel"><p style="color:var(--text-muted)">Belum ada aduan masuk.</p></div>
<?php else: foreach ($list as $a): ?>
  <div class="panel">
    <div class="panel-header">
      <div>
        <strong><?= htmlspecialchars($a['kategori']) ?></strong>
        <span class="badge <?= htmlspecialchars($a['status']) ?>" style="margin-left:8px"><?= htmlspecialchars($a['status']) ?></span>
      </div>
      <span style="color:var(--text-muted); font-size:.85rem"><?= htmlspecialchars($a['created_at']) ?></span>
    </div>
    <p style="margin:0 0 12px"><?= htmlspecialchars($a['isi']) ?></p>
    <?php if ($a['file_url']): ?>
      <p><a href="<?= htmlspecialchars($a['file_url']) ?>" target="_blank" rel="noopener">Lihat lampiran</a></p>
    <?php endif; ?>

    <?php if ($a['tanggapan']): ?>
      <div class="alert success" style="margin-top:10px">Tanggapan Anda: <?= htmlspecialchars($a['tanggapan']) ?></div>
    <?php else: ?>
      <form method="post" style="margin-top:10px">
        <input type="hidden" name="aduan_id" value="<?= (int)$a['id'] ?>">
        <div class="field">
          <label>Tanggapan</label>
          <textarea name="tanggapan" rows="3" required placeholder="Tulis tanggapan Anda..."></textarea>
        </div>
        <button type="submit" class="btn small">Kirim Tanggapan</button>
      </form>
    <?php endif; ?>
  </div>
<?php endforeach; endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
