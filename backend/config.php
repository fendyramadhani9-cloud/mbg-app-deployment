<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!function_exists('env_load')) {
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
}

$envFile = __DIR__ . '/.env';
$env = array_merge($_ENV, env_load($envFile));

if (!function_exists('cfg')) {
    function cfg(string $key, $default = null) {
        global $env;
        return $env[$key] ?? getenv($key) ?: $default;
    }
}

return [
    'db' => [
        'host' => cfg('DB_HOST', 'mbg-rds-mysql.cdsxe0eeudnq.us-east-1.rds.amazonaws.com'),
        'port' => cfg('DB_PORT', '3306'),
        'user' => cfg('DB_USER', 'mbg_admin'),
        'pass' => cfg('DB_PASS', 'mbgmysql'),
        'name' => cfg('DB_NAME', 'mbg_db'),
    ],
    's3' => [
        'bucket' => cfg('S3_BUCKET', 'mbg-upload-15671-503038630796-us-east-1-an'),
        'region' => cfg('AWS_REGION', 'us-east-1'),
    ],
    'sns' => [
        'topic_arn' => cfg('SNS_TOPIC_ARN', 'arn:aws:sns:us-east-1:503038630796:mbg-sns-notifikasi'),
    ],
    'api_key'  => cfg('API_KEY', 'mbg-secret-key-2024'),
    'app_port' => cfg('APP_PORT', '8080'),
];