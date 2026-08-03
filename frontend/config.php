<?php
/**
 * config.php — Front End. Nilai asli diisi tim infrastruktur di .env
 * (lihat .env.example). SESSION_SAVE_PATH WAJIB berupa folder shared
 * yang sama persis terlihat di semua salinan FE (lihat Bab 8 dokumen teknis).
 */

if (!function_exists('fe_env_load')) {
    function fe_env_load(string $path): array {
        $vars = [];
        if (is_file($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) continue;
                [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
                $vars[trim($k)] = trim($v);
            }
        }
        return $vars;
    }
}

$feEnv = array_merge($_ENV, fe_env_load(__DIR__ . '/.env'));

if (!function_exists('fe_cfg')) {
    function fe_cfg(string $key, $default = null) {
        global $feEnv;
        return $feEnv[$key] ?? getenv($key) ?: $default;
    }
}

// 1. Ambil Nilai Konfigurasi
$sessionSavePath = fe_cfg('SESSION_SAVE_PATH', '/mnt/efs/mbg-session');
$apiBaseUrl      = fe_cfg('API_BASE_URL', 'http://localhost:8080/api');
$apiKey          = fe_cfg('API_KEY', 'mbg-secret-key-2024');

// 2. Set Session Save Path ke EFS SEBELUM session_start()
if (session_status() === PHP_SESSION_NONE) {
    if ($sessionSavePath) {
        session_save_path($sessionSavePath);
    }
    session_start();
}

return [
    'api_base_url'      => $apiBaseUrl,
    'api_key'           => $apiKey,
    'session_save_path' => $sessionSavePath,
    'port'              => fe_cfg('PORT', '80'),
];
