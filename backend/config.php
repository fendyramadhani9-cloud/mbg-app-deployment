<?php
/**
 * config.php — memuat seluruh nilai konfigurasi Back End.
 * Nilai asli diisi oleh tim infrastruktur di file .env (lihat .env.example).
 * Programmer tidak perlu tahu bagaimana resource AWS dibuat, cukup pakai
 * nilai-nilai berikut.
 */

function env_load(string $path): array {
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

$envFile = __DIR__ . '/.env';
$env = array_merge($_ENV, env_load($envFile));

function cfg(string $key, $default = null) {
    global $env;
    return $env[$key] ?? getenv($key) ?: $default;
}

return [
    'db' => [
        'host' => cfg('DB_HOST', '127.0.0.1'),
        'port' => cfg('DB_PORT', '3306'),
        'user' => cfg('DB_USER', 'root'),
        'pass' => cfg('DB_PASS', ''),
        'name' => cfg('DB_NAME', 'mbg_db'),
    ],
    's3' => [
        'bucket' => cfg('S3_BUCKET_NAME'),
        'region' => cfg('AWS_REGION'),
    ],
    'sns' => [
        'topic_arn' => cfg('SNS_TOPIC_ARN'),
    ],
    'api_key' => cfg('API_KEY', 'mbg-secret-key-2024'),
    'app_port' => cfg('APP_PORT', '8080'),
];
