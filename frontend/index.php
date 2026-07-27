<?php
require_once __DIR__ . '/includes/session_init.php';

$user = fe_current_user();
if ($user) {
    header('Location: /views/' . $user['role'] . '/dashboard.php');
} else {
    header('Location: /views/auth/login.php');
}
exit;
