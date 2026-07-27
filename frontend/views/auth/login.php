<?php
require_once __DIR__ . '/../../includes/session_init.php';
require_once __DIR__ . '/../../includes/ApiClient.php';

if (fe_current_user()) {
    header('Location: /views/' . fe_current_user()['role'] . '/dashboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $res = (new ApiClient())->postJson('/auth/login', ['email' => $email, 'password' => $pass]);

    if (!empty($res['success'])) {
        $_SESSION['user'] = $res['data'];
        header('Location: /views/' . $res['data']['role'] . '/dashboard.php');
        exit;
    }
    $error = $res['error'] ?? 'Login gagal';
}

$pageTitle = 'Masuk';
require __DIR__ . '/../../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Masuk ke MBG App</h1>
    <p class="sub">Pelaporan, Monitoring &amp; Aduan</p>

    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus placeholder="nama@email.com">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn block">Masuk</button>
    </form>

    <div class="auth-switch">
      Belum punya akun? <a href="/views/auth/register.php">Daftar sebagai Masyarakat</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
