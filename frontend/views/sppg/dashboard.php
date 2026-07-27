<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['sppg']);

$res = (new ApiClient())->get('/monitoring/sppg');
$d = $res['data'] ?? ['laporan_by_status' => [], 'aduan_by_status' => []];

function count_status(array $rows, string $status): int {
    foreach ($rows as $r) if ($r['status'] === $status) return (int)$r['total'];
    return 0;
}

$pageTitle = 'Dashboard SPPG';
$activeNav = 'dashboard';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Dashboard SPPG</h1>
<p class="page-subtitle">Ringkasan laporan &amp; aduan untuk SPPG Anda.</p>

<div class="grid">
  <div class="card"><h3>Laporan Menunggu</h3><div class="stat"><?= count_status($d['laporan_by_status'], 'menunggu') ?></div></div>
  <div class="card"><h3>Laporan Ditinjau</h3><div class="stat"><?= count_status($d['laporan_by_status'], 'ditinjau') ?></div></div>
  <div class="card"><h3>Aduan Baru</h3><div class="stat"><?= count_status($d['aduan_by_status'], 'baru') ?></div></div>
  <div class="card"><h3>Aduan Selesai</h3><div class="stat"><?= count_status($d['aduan_by_status'], 'selesai') ?></div></div>
</div>

<div class="panel" style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap">
  <a class="btn" href="/views/sppg/laporan.php">+ Buat Laporan</a>
  <a class="btn secondary" href="/views/sppg/aduan.php">Lihat Aduan Masuk</a>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
