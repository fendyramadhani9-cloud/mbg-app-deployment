<?php
// Health check statis FE — WAJIB selalu HTTP 200, tidak perlu memanggil BE.
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
