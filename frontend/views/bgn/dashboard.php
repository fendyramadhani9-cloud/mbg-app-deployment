<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';
$user = fe_require_role(['bgn']);

$res = (new ApiClient())->get('/monitoring/bgn');
$d = $res['data'] ?? ['laporan_by_status' => [], 'aduan_by_status' => [], 'per_sppg' => []];

function bgn_count(array $rows, string $status): int {
    foreach ($rows as $r) if ($r['status'] === $status) return (int)$r['total'];
    return 0;
}

$pageTitle = 'Dashboard BGN';
$activeNav = 'dashboard';
require __DIR__ . '/../../includes/header.php';
?>
<h1 class="page-title">Dashboard BGN</h1>
<p class="page-subtitle">Rekap agregat seluruh SPPG.</p>

<div class="grid">
  <div class="card"><h3>Laporan Menunggu</h3><div class="stat"><?= bgn_count($d['laporan_by_status'], 'menunggu') ?></div></div>
  <div class="card"><h3>Laporan Selesai</h3><div class="stat"><?= bgn_count($d['laporan_by_status'], 'selesai') ?></div></div>
  <div class="card"><h3>Aduan Baru</h3><div class="stat"><?= bgn_count($d['aduan_by_status'], 'baru') ?></div></div>
  <div class="card"><h3>Aduan Selesai</h3><div class="stat"><?= bgn_count($d['aduan_by_status'], 'selesai') ?></div></div>
</div>

<div class="panel" style="margin-top:20px">
  <h3 style="margin-top:0">Rekap per SPPG</h3>
  <?php if (empty($d['per_sppg'])): ?>
    <p style="color:var(--text-muted)">Belum ada data SPPG.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>SPPG</th><th>Total Laporan</th><th>Total Aduan</th></tr></thead>
      <tbody>
      <?php foreach ($d['per_sppg'] as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['nama']) ?></td>
          <td><?= (int)$s['total_laporan'] ?></td>
          <td><?= (int)$s['total_aduan'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
