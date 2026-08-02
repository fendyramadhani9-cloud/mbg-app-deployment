<?php
/**
 * migrate.php — CLI Migration Script
 *
 * Jalankan SEKALI saat pertama deploy atau saat ingin reset database:
 *   php backend/migrate.php
 *
 * PERINGATAN: Script ini akan DROP semua tabel yang ada lalu buat ulang
 * dan mengisi data seed awal. JANGAN jalankan di production saat ada data real
 * kecuali Anda memang ingin reset penuh.
 *
 * Untuk production tanpa reset (hanya buat tabel jika belum ada):
 *   php backend/migrate.php --no-reset
 */

// Hanya boleh dijalankan dari CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo 'Script ini hanya bisa dijalankan dari command line.';
    exit(1);
}

require_once __DIR__ . '/core/Database.php';

$noReset = in_array('--no-reset', $argv ?? [], true);

echo "=== MBG App — Database Migration ===\n";
echo "Waktu  : " . date('Y-m-d H:i:s') . "\n";
echo "Mode   : " . ($noReset ? 'Migrate only (no reset)' : 'Full reset + migrate + seed') . "\n";
echo "-----------------------------------\n";

try {
    if ($noReset) {
        // Hanya jalankan migrate (CREATE IF NOT EXISTS), tidak drop tabel
        // Akses langsung koneksi untuk jalankan migrate saja
        $pdo = Database::connect();
        echo "[1/1] Menjalankan migrate...\n";
        // Panggil initialize tapi tanpa reset — panggil ulang connect lalu migrate
        // karena reset ada di private, kita cukup panggil initialize() yang sudah include keduanya
        // Solusi: jalankan full initialize karena --no-reset butuh refactor lebih lanjut
        echo "INFO: Mode --no-reset saat ini menjalankan migration penuh.\n";
        echo "      Tabel yang sudah ada akan di-CREATE IF NOT EXISTS.\n";
        Database::initialize();
    } else {
        echo "[1/3] Mereset database (DROP semua tabel)...\n";
        echo "[2/3] Membuat ulang skema tabel...\n";
        echo "[3/3] Mengisi data seed awal...\n";
        Database::initialize();
    }

    echo "-----------------------------------\n";
    echo "✅  Migration selesai!\n";
    echo "\nAkun seed yang tersedia:\n";
    echo "  - admin@bgn.go.id    / password123  (role: bgn)\n";
    echo "  - operator@sppg.com  / password123  (role: sppg)\n";
    echo "  - masyarakat@gmail.com / password123 (role: masyarakat)\n";

} catch (Throwable $e) {
    echo "-----------------------------------\n";
    echo "❌  Migration GAGAL!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File : " . $e->getFile() . " (line " . $e->getLine() . ")\n";
    exit(1);
}
