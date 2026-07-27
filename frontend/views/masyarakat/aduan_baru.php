<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['masyarakat']);
$api = new ApiClient();

$sppgRes = $api->get('/sppg');
$sppgList = $sppgRes['data'] ?? [];

$error = null; $success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'sppg_id'  => $_POST['sppg_id'] ?? '',
        'kategori' => $_POST['kategori'] ?? '',
        'isi'      => $_POST['isi'] ?? '',
    ];
    if (!empty($_FILES['file']['tmp_name'])) {
        $fields['file'] = new CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name']);
    }
    $res = $api->postForm('/aduan', $fields);

    if (!empty($res['success'])) {
        header('Location: /views/masyarakat/aduan.php');
        exit;
    }
    $error = $res['error'] ?? 'Gagal mengirim aduan';
}

$pageTitle = 'Buat Aduan';
$activeNav = 'aduan_baru';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Buat Aduan Baru</h1>
<p class="page-subtitle">Sampaikan aduan Anda terkait pelaksanaan program MBG.</p>

<div class="panel" style="max-width:560px">
  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="field">
      <label for="sppg_id">SPPG Terkait</label>
      <select id="sppg_id" name="sppg_id" required>
        <option value="">-- Pilih SPPG --</option>
        <?php foreach ($sppgList as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="kategori">Kategori</label>
      <select id="kategori" name="kategori" required>
        <option value="">-- Pilih kategori --</option>
        <option value="Kualitas Makanan">Kualitas Makanan</option>
        <option value="Ketepatan Waktu">Ketepatan Waktu</option>
        <option value="Kebersihan">Kebersihan</option>
        <option value="Pelayanan">Pelayanan</option>
        <option value="Lainnya">Lainnya</option>
      </select>
    </div>
    <div class="field">
      <label for="isi">Isi Aduan</label>
      <textarea id="isi" name="isi" rows="5" required placeholder="Jelaskan detail aduan Anda..."></textarea>
    </div>
    <div class="field">
      <label for="file">Foto Bukti (opsional, jpg/png/pdf, maks 5MB)</label>
      <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.pdf">
    </div>
    <button type="submit" class="btn block">Kirim Aduan</button>
  </form>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
