<?php
/**
 * config.php — Front End. Nilai asli diisi tim infrastruktur di .env
 * (lihat .env.example). SESSION_SAVE_PATH WAJIB berupa folder shared
 * yang sama persis terlihat di semua salinan FE (lihat Bab 8 dokumen teknis).
 */

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

$feEnv = array_merge($_ENV, fe_env_load(__DIR__ . '/.env'));

function fe_cfg(string $key, $default = null) {
    global $feEnv;
    return $feEnv[$key] ?? getenv($key) ?: $default;
}

return [
    'api_base_url'      => fe_cfg('API_BASE_URL', 'http://localhost:8080/api'),
    'api_key'           => fe_cfg('API_KEY', 'mbg-secret-key-2024'),
    'session_save_path' => fe_cfg('SESSION_SAVE_PATH', null),
    'port'              => fe_cfg('PORT', '80'),
];
