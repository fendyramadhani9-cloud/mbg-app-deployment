<?php
/**
 * Entry point Back End — routing seluruh endpoint REST API.
 * Struktur path: /api/{resource}/{id?}/{subAction?}
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../api/laporan.php';
require_once __DIR__ . '/../api/aduan.php';
require_once __DIR__ . '/../api/monitoring.php';
require_once __DIR__ . '/../api/sppg.php';
require_once __DIR__ . '/../api/upload.php';

Auth::startSession();

// CORS dasar — sesuaikan origin di production sesuai domain Front End
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path   = preg_replace('#^/(index\.php)?#', '', $path);
$parts  = array_values(array_filter(explode('/', $path)));

// Health check tidak butuh API key
if (($parts[0] ?? '') === 'health.php' || ($parts[0] ?? '') === 'health') {
    require __DIR__ . '/health.php';
    exit;
}

if (($parts[0] ?? '') !== 'api') {
    Response::error('Endpoint tidak ditemukan', 'NOT_FOUND', 404);
}

Auth::requireApiKey();

$resource   = $parts[1] ?? '';
$sub1       = $parts[2] ?? null; // bisa id, atau action seperti 'register'
$sub2       = $parts[3] ?? null; // bisa subAction, mis. 'status'

switch ($resource) {
    case 'auth':
        handle_auth((string)$sub1, $method);
        break;

    case 'laporan':
        handle_laporan($method, $sub1, $sub2);
        break;

    case 'aduan':
        handle_aduan($method, $sub1, $sub2);
        break;

    case 'monitoring':
        handle_monitoring((string)$sub1, $method);
        break;

    case 'sppg':
        handle_sppg($method, $sub1);
        break;

    case 'upload':
        handle_upload($method);
        break;

    default:
        Response::error('Resource tidak ditemukan', 'NOT_FOUND', 404);
}
