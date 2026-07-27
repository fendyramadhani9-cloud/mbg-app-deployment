<?php
/**
 * WAJIB di-include paling atas di setiap halaman FE, SEBELUM output apa pun.
 * Mengarahkan session.save_path ke folder shared antar-server (Bab 8),
 * karena FE dijalankan di banyak server sekaligus dan tidak boleh
 * menyimpan session di disk lokal /tmp masing-masing server.
 */

$cfg = require __DIR__ . '/../config.php';

if (!empty($cfg['session_save_path'])) {
    ini_set('session.save_path', $cfg['session_save_path']);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function fe_current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function fe_require_login(): array
{
    $user = fe_current_user();
    if (!$user) {
        header('Location: /views/auth/login.php');
        exit;
    }
    return $user;
}

function fe_require_role(array $roles): array
{
    $user = fe_require_login();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo 'Anda tidak berhak mengakses halaman ini.';
        exit;
    }
    return $user;
}
