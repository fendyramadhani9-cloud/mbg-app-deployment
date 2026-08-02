<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

function handle_auth(string $action, string $method): void
{
    $pdo = Database::get();

    if ($action === 'register' && $method === 'POST') {
        $in = json_decode(file_get_contents('php://input'), true) ?? [];
        $nama  = trim($in['nama'] ?? '');
        $email = trim($in['email'] ?? '');
        $pass  = (string)($in['password'] ?? '');

        if ($nama === '' || $email === '' || strlen($pass) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Nama, email valid, dan password (min 6 karakter) wajib diisi', 'VALIDATION_ERROR', 400);
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Response::error('Email sudah terdaftar', 'VALIDATION_ERROR', 400);
        }

        $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role, status) VALUES (?, ?, ?, ?, "aktif")');
        $stmt->execute([$nama, $email, Auth::hashPassword($pass), 'masyarakat']);

        Response::success(['id' => $pdo->lastInsertId()], 'Registrasi berhasil', 201);
    }

    if ($action === 'login' && $method === 'POST') {
        $in = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = trim($in['email'] ?? '');
        $pass  = (string)($in['password'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !Auth::verifyPassword($pass, $user['password'])) {
            Response::error('Email atau password salah', 'UNAUTHORIZED', 401);
        }

        if (($user['status'] ?? 'aktif') === 'pending') {
            Response::error('Akun Anda masih menunggu persetujuan (approval) Admin BGN.', 'ACCOUNT_PENDING', 403);
        }

        if (($user['status'] ?? 'aktif') === 'ditolak') {
            Response::error('Akun Anda telah ditolak oleh Admin BGN.', 'ACCOUNT_REJECTED', 403);
        }

        Auth::login($user);
        unset($user['password']);
        Response::success($user, 'Login berhasil');
    }

    if ($action === 'me' && $method === 'GET') {
        $user = Auth::requireLogin();
        Response::success($user);
    }

    if ($action === 'logout' && $method === 'POST') {
        Auth::requireLogin();
        Auth::logout();
        Response::success(null, 'Logout berhasil');
    }

    Response::error('Endpoint tidak ditemukan', 'NOT_FOUND', 404);
}
