<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

function handle_users(string $method, ?string $id, ?string $subAction): void
{
    $pdo = Database::get();

    // GET /api/users — List user (BGN dapat melihat semua, SPPG dapat melihat operator di SPPG nya)
    if ($method === 'GET' && !$id) {
        $user = Auth::requireRole(['bgn', 'sppg']);

        $sql = 'SELECT u.id, u.nama, u.email, u.role, u.sppg_id, u.status, u.created_at, s.nama AS sppg_nama 
                FROM users u 
                LEFT JOIN sppg s ON u.sppg_id = s.id 
                WHERE 1=1';
        $params = [];

        if ($user['role'] === 'sppg') {
            $sql .= ' AND u.sppg_id = ? AND u.role = "sppg"';
            $params[] = $user['sppg_id'];
        } else {
            // Role BGN: opsional filter by status, role, sppg_id
            if (!empty($_GET['status'])) {
                $sql .= ' AND u.status = ?';
                $params[] = $_GET['status'];
            }
            if (!empty($_GET['role'])) {
                $sql .= ' AND u.role = ?';
                $params[] = $_GET['role'];
            }
            if (!empty($_GET['sppg_id'])) {
                $sql .= ' AND u.sppg_id = ?';
                $params[] = $_GET['sppg_id'];
            }
        }

        $sql .= ' ORDER BY u.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        Response::success($stmt->fetchAll());
    }

    // POST /api/users — Admin BGN buat akun langsung (aktif), atau SPPG buat pengajuan akun (pending)
    if ($method === 'POST' && !$id) {
        $currentUser = Auth::requireRole(['bgn', 'sppg']);
        $in = json_decode(file_get_contents('php://input'), true) ?? [];

        $nama   = trim($in['nama'] ?? '');
        $email  = trim($in['email'] ?? '');
        $pass   = (string)($in['password'] ?? '');
        $role   = trim($in['role'] ?? 'sppg');
        $sppgId = !empty($in['sppg_id']) ? (int)$in['sppg_id'] : null;

        if ($nama === '' || $email === '' || strlen($pass) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Nama, email valid, dan password (min 6 karakter) wajib diisi', 'VALIDATION_ERROR', 400);
        }

        // Cek duplikasi email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Response::error('Email sudah terdaftar', 'VALIDATION_ERROR', 400);
        }

        if ($currentUser['role'] === 'bgn') {
            // Admin BGN menambah akun secara langsung
            if (!in_array($role, ['bgn', 'sppg'], true)) {
                $role = 'sppg';
            }
            if ($role === 'sppg' && !$sppgId) {
                Response::error('sppg_id wajib dipilih untuk akun operator SPPG', 'VALIDATION_ERROR', 400);
            }
            $status = 'aktif';
            $msg = 'Akun berhasil dibuat dan langsung aktif';
        } else {
            // Operator SPPG mengajukan pembuatan akun baru
            $role   = 'sppg';
            $sppgId = $currentUser['sppg_id']; // Otomatis disesuaikan dengan SPPG pengaju
            $status = 'pending';
            $msg    = 'Pengajuan pembuatan akun berhasil dikirim, menunggu persetujuan Admin BGN';
        }

        $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role, sppg_id, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nama, $email, Auth::hashPassword($pass), $role, $sppgId, $status]);

        Response::success([
            'id'     => $pdo->lastInsertId(),
            'status' => $status
        ], $msg, 201);
    }

    // PATCH /api/users/{id}/status — Persetujuan (Approve / Reject) oleh Admin BGN
    if ($method === 'PATCH' && $id && $subAction === 'status') {
        Auth::requireRole(['bgn']);
        $in = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = $in['status'] ?? '';

        if (!in_array($status, ['aktif', 'pending', 'ditolak'], true)) {
            Response::error('Status tidak valid (aktif, pending, atau ditolak)', 'VALIDATION_ERROR', 400);
        }

        $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);

        $label = $status === 'aktif' ? 'disetujui (aktif)' : ($status === 'ditolak' ? 'ditolak' : 'diperbarui');
        Response::success(null, "Status akun berhasil $label");
    }

    Response::error('Endpoint tidak ditemukan', 'NOT_FOUND', 404);
}
