<?php
/**
 * health.php — Health Check untuk Load Balancer AWS.
 *
 * WAJIB selalu mengembalikan HTTP 200 selama aplikasi & koneksi DB sehat.
 *
 * PENTING: Menggunakan Database::connect() (bukan Database::get()) agar
 * health check TIDAK memicu reset/migrate/seed. Setiap request health check
 * hanya membuat koneksi PDO baru dan menjalankan SELECT 1.
 */
require_once __DIR__ . '/../core/Database.php';

header('Content-Type: application/json');

try {
    // connect() membuat PDO langsung tanpa trigger initialize()
    Database::connect()->query('SELECT 1');
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'db' => 'connected', 'ts' => time()]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'db' => 'disconnected']);
}
