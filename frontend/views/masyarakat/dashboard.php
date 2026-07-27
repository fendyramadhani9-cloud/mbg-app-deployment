<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['masyarakat']);

$api = new ApiClient();
$stat = $api->get('/monitoring/publik');
$d = $stat['data'] ?? [];

$pageTitle = 'Beranda';
$activeNav = 'dashboard';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Halo, <?= htmlspecialchars($user['nama']) ?> 👋</h1>
<p class="page-subtitle">Statistik transparansi publik program MBG.</p>

<div class="grid">
  <div class="card"><h3>Total SPPG</h3><div class="stat"><?= (int)($d['total_sppg'] ?? 0) ?></div></div>
  <div class="card"><h3>Total Laporan</h3><div class="stat"><?= (int)($d['total_laporan'] ?? 0) ?></div></div>
  <div class="card"><h3>Total Aduan</h3><div class="stat"><?= (int)($d['total_aduan'] ?? 0) ?></div></div>
  <div class="card"><h3>Aduan Selesai</h3><div class="stat"><?= (int)($d['aduan_selesai'] ?? 0) ?></div></div>
</div>

<div class="panel" style="margin-top:20px">
  <h3 style="margin-top:0">Butuh melapor sesuatu?</h3>
  <p style="color:var(--text-muted)">Sampaikan aduan Anda terkait pelaksanaan program MBG di SPPG terdekat.</p>
  <a class="btn" href="/views/masyarakat/aduan_baru.php">+ Buat Aduan Baru</a>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
