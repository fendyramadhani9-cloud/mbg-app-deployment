<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['masyarakat']);

$res = (new ApiClient())->get('/aduan');
$list = $res['data'] ?? [];

$pageTitle = 'Aduan Saya';
$activeNav = 'aduan';
require __DIR__ . '/../../includes/header.php';
?>
<div class="panel-header">
  <div>
    <h1 class="page-title" style="margin-bottom:0">Aduan Saya</h1>
    <p class="page-subtitle" style="margin-bottom:0">Riwayat aduan yang pernah Anda kirim.</p>
  </div>
  <a class="btn" href="/views/masyarakat/aduan_baru.php">+ Aduan Baru</a>
</div>

<div class="panel">
  <?php if (empty($res['success'])): ?>
    <div class="alert error"><?= htmlspecialchars($res['error'] ?? 'Gagal memuat data') ?></div>
  <?php elseif (empty($list)): ?>
    <p style="color:var(--text-muted)">Belum ada aduan yang Anda kirim.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tanggal</th><th>Kategori</th><th>Isi</th><th>Status</th><th>Tanggapan</th></tr></thead>
      <tbody>
      <?php foreach ($list as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['created_at']) ?></td>
          <td><?= htmlspecialchars($a['kategori']) ?></td>
          <td class="wrap"><?= htmlspecialchars($a['isi']) ?></td>
          <td><span class="badge <?= htmlspecialchars($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></span></td>
          <td class="wrap"><?= $a['tanggapan'] ? htmlspecialchars($a['tanggapan']) : '<span style="color:var(--text-muted)">Belum ada</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
