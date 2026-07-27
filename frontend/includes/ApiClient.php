<?php
/**
 * Wrapper pemanggilan REST API ke Back End (curl + header X-API-Key).
 * Dipakai oleh seluruh halaman/views FE.
 */
class ApiClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $cfg = require __DIR__ . '/../config.php';
        $this->baseUrl = rtrim($cfg['api_base_url'], '/');
        $this->apiKey  = $cfg['api_key'];
    }

    public function get(string $path, array $query = []): array
    {
        $url = $this->baseUrl . $path;
        if ($query) $url .= '?' . http_build_query($query);
        return $this->request('GET', $url);
    }

    public function postJson(string $path, array $body): array
    {
        return $this->request('POST', $this->baseUrl . $path, $body, true);
    }

    public function patchJson(string $path, array $body): array
    {
        return $this->request('PATCH', $this->baseUrl . $path, $body, true);
    }

    public function putJson(string $path, array $body): array
    {
        return $this->request('PUT', $this->baseUrl . $path, $body, true);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $this->baseUrl . $path);
    }

    /** POST multipart, untuk endpoint yang menerima file upload. */
    public function postForm(string $path, array $fields): array
    {
        return $this->request('POST', $this->baseUrl . $path, $fields, false, true);
    }

    private function request(string $method, string $url, array $body = [], bool $json = false, bool $multipart = false): array
    {
        $ch = curl_init($url);

        $headers = ['X-API-Key: ' . $this->apiKey];
        $cookie  = session_id() ? ('PHPSESSID=' . session_id()) : '';

        $opts = [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIE         => $cookie,
            CURLOPT_TIMEOUT        => 15,
        ];

        if ($method !== 'GET' && $method !== 'DELETE') {
            if ($json) {
                $headers[] = 'Content-Type: application/json';
                $opts[CURLOPT_POSTFIELDS] = json_encode($body);
            } elseif ($multipart) {
                $opts[CURLOPT_POSTFIELDS] = $body; // array => multipart/form-data otomatis
            }
        }

        $opts[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $opts);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return ['success' => false, 'error' => 'Gagal terhubung ke server: ' . $err, 'code' => 'CONN_ERROR', '_http' => 0];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'error' => 'Response server tidak valid', 'code' => 'BAD_RESPONSE', '_http' => $httpCode];
        }
        $decoded['_http'] = $httpCode;
        return $decoded;
    }
}
