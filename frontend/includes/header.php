<?php
/**
 * Partial header. Variabel opsional yang bisa di-set sebelum include:
 * $pageTitle (string), $activeNav (string: 'dashboard'|'laporan'|'aduan'|'sppg'|'monitoring')
 */
$user = fe_current_user();
$pageTitle = $pageTitle ?? 'MBG App';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= htmlspecialchars($pageTitle) ?> — MBG App</title>
<link rel="stylesheet" href="/assets/css/app.css">
<script>
  // Set theme sebelum render untuk hindari flash
  (function(){
    var t = localStorage.getItem('mbg_theme') ||
      (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', t);
  })();
</script>
</head>
<body>
<header class="topbar">
  <div class="container topbar-inner">
    <div class="brand"><span class="dot"></span> MBG App</div>

    <?php if ($user): ?>
    <nav class="nav-links" id="navLinks">
      <?php if ($user['role'] === 'bgn'): ?>
        <a href="/views/bgn/dashboard.php" class="<?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="/views/bgn/laporan.php" class="<?= ($activeNav ?? '') === 'laporan' ? 'active' : '' ?>">Laporan</a>
        <a href="/views/bgn/aduan.php" class="<?= ($activeNav ?? '') === 'aduan' ? 'active' : '' ?>">Aduan</a>
        <a href="/views/bgn/sppg.php" class="<?= ($activeNav ?? '') === 'sppg' ? 'active' : '' ?>">Master SPPG</a>
      <?php elseif ($user['role'] === 'sppg'): ?>
        <a href="/views/sppg/dashboard.php" class="<?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="/views/sppg/laporan.php" class="<?= ($activeNav ?? '') === 'laporan' ? 'active' : '' ?>">Laporan</a>
        <a href="/views/sppg/aduan.php" class="<?= ($activeNav ?? '') === 'aduan' ? 'active' : '' ?>">Aduan Masuk</a>
      <?php else: ?>
        <a href="/views/masyarakat/dashboard.php" class="<?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">Beranda</a>
        <a href="/views/masyarakat/aduan.php" class="<?= ($activeNav ?? '') === 'aduan' ? 'active' : '' ?>">Aduan Saya</a>
        <a href="/views/masyarakat/aduan_baru.php" class="<?= ($activeNav ?? '') === 'aduan_baru' ? 'active' : '' ?>">Buat Aduan</a>
      <?php endif; ?>
      <a href="/includes/logout.php">Keluar (<?= htmlspecialchars($user['nama']) ?>)</a>
    </nav>
    <?php endif; ?>

    <div class="topbar-actions">
      <button class="theme-toggle" id="themeToggle" onclick="mbgToggleTheme()" title="Ganti tema" aria-label="Ganti tema">🌙</button>
      <?php if ($user): ?>
      <button class="hamburger" onclick="mbgToggleNav()" aria-label="Menu">☰</button>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="page">
  <div class="container" id="flashBox"></div>
  <div class="container">
