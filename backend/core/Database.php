<?php

/**
 * Database.php
 *
 * Kelas singleton untuk koneksi PDO.
 * Fitur:
 *  - Reset (DROP) seluruh tabel → migrasi ulang (CREATE) → seeding data dummy.
 *  - Guard flag agar proses reset+migrate+seed hanya berjalan SEKALI per siklus hidup
 *    proses PHP, meskipun Database::get() dipanggil berkali-kali.
 */
class Database
{
    /** @var PDO|null Instance PDO tunggal (singleton). */
    private static ?PDO $pdo = null;

    /** @var bool Pastikan reset+migrate+seed hanya berjalan sekali. */
    private static bool $initialized = false;

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Kembalikan instance PDO.
     * Inisialisasi (reset → migrate → seed) otomatis dilakukan satu kali.
     */
    public static function get(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::createConnection();
        }

        if (!self::$initialized) {
            self::$initialized = true;
            self::reset(self::$pdo);
            self::migrate(self::$pdo);
            self::seed(self::$pdo);
        }

        return self::$pdo;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Buat koneksi PDO dari konfigurasi backend/config.php.
     */
    private static function createConnection(): PDO
    {
        $cfg = require __DIR__ . '/../config.php';
        $db  = $cfg['db'];

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $db['host'],
            $db['port'],
            $db['name']
        );

        return new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // Step 1 – Reset: hapus semua tabel agar database selalu bersih (fresh)
    // -------------------------------------------------------------------------

    /**
     * Nonaktifkan FK checks, drop semua tabel, aktifkan kembali FK checks.
     */
    private static function reset(PDO $pdo): void
    {
        // Matikan sementara pengecekan Foreign Key agar DROP bisa dilakukan
        // tanpa harus memperhatikan urutan dependensi antar tabel.
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');

        // Drop setiap tabel — urutannya tidak masalah karena FK checks dimatikan.
        $tables = ['aduan', 'laporan', 'users', 'sppg'];
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`;");
        }

        // Aktifkan kembali pengecekan Foreign Key.
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
    }

    // -------------------------------------------------------------------------
    // Step 2 – Migrate: buat ulang seluruh skema tabel
    // -------------------------------------------------------------------------

    /**
     * Buat semua tabel dengan skema lengkap.
     * Urutan pembuatan penting: tabel induk (sppg, users) lebih dulu sebelum
     * tabel yang mereferensikannya (laporan, aduan).
     */
    private static function migrate(PDO $pdo): void
    {
        // --- Tabel: sppg ---
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sppg` (
                `id`               INT          NOT NULL AUTO_INCREMENT,
                `nama_sppg`        VARCHAR(150) NOT NULL,
                `alamat`           VARCHAR(255)          DEFAULT NULL,
                `penanggung_jawab` VARCHAR(150)          DEFAULT NULL,
                `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // --- Tabel: users ---
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id`         INT                               NOT NULL AUTO_INCREMENT,
                `nama`       VARCHAR(150)                      NOT NULL,
                `email`      VARCHAR(150)                      NOT NULL,
                `password`   VARCHAR(255)                      NOT NULL,
                `role`       ENUM('bgn','sppg','masyarakat')   NOT NULL DEFAULT 'masyarakat',
                `sppg_id`    INT                                        DEFAULT NULL,
                `created_at` DATETIME                          NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_users_email` (`email`),
                INDEX  `idx_users_role`    (`role`),
                CONSTRAINT `fk_users_sppg`
                    FOREIGN KEY (`sppg_id`) REFERENCES `sppg` (`id`)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // --- Tabel: laporan ---
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `laporan` (
                `id`            INT                                 NOT NULL AUTO_INCREMENT,
                `sppg_id`       INT                                 NOT NULL,
                `jenis_laporan` VARCHAR(100)                        NOT NULL,
                `isi`           TEXT                                NOT NULL,
                `file_url`      VARCHAR(500)                                 DEFAULT NULL,
                `status`        ENUM('menunggu','ditinjau','selesai') NOT NULL DEFAULT 'menunggu',
                `created_at`    DATETIME                            NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_laporan_sppg`   (`sppg_id`),
                INDEX `idx_laporan_status` (`status`),
                CONSTRAINT `fk_laporan_sppg`
                    FOREIGN KEY (`sppg_id`) REFERENCES `sppg` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // --- Tabel: aduan ---
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `aduan` (
                `id`         INT                              NOT NULL AUTO_INCREMENT,
                `user_id`    INT                              NOT NULL,
                `sppg_id`    INT                              NOT NULL,
                `kategori`   VARCHAR(100)                     NOT NULL,
                `isi`        TEXT                             NOT NULL,
                `file_url`   VARCHAR(500)                              DEFAULT NULL,
                `status`     ENUM('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
                `tanggapan`  TEXT                                       DEFAULT NULL,
                `created_at` DATETIME                         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME                         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_aduan_sppg`   (`sppg_id`),
                INDEX `idx_aduan_status` (`status`),
                INDEX `idx_aduan_user`   (`user_id`),
                CONSTRAINT `fk_aduan_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE CASCADE,
                CONSTRAINT `fk_aduan_sppg`
                    FOREIGN KEY (`sppg_id`) REFERENCES `sppg` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    // -------------------------------------------------------------------------
    // Step 3 – Seed: isi data dummy agar aplikasi bisa langsung dipakai
    // -------------------------------------------------------------------------

    /**
     * Masukkan data dummy ke tabel sppg dan users.
     * Semua password di-hash menggunakan PASSWORD_BCRYPT.
     */
    private static function seed(PDO $pdo): void
    {
        // ------------------------------------------------------------------
        // Seed tabel: sppg
        // ------------------------------------------------------------------
        $sppgData = [
            [
                'nama_sppg'        => 'SPPG Pusat Jakarta',
                'alamat'           => 'Jl. Sudirman No. 1',
                'penanggung_jawab' => 'Budi Santoso',
            ],
            [
                'nama_sppg'        => 'SPPG Wilayah Bandung',
                'alamat'           => 'Jl. Asia Afrika No. 45',
                'penanggung_jawab' => 'Siti Rahma',
            ],
        ];

        $stmtSppg = $pdo->prepare("
            INSERT INTO `sppg` (`nama_sppg`, `alamat`, `penanggung_jawab`)
            VALUES (:nama_sppg, :alamat, :penanggung_jawab)
        ");

        foreach ($sppgData as $row) {
            $stmtSppg->execute($row);
        }

        // ------------------------------------------------------------------
        // Seed tabel: users
        // ------------------------------------------------------------------
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);

        $usersData = [
            // Role: bgn (admin pusat, tidak terkait SPPG)
            [
                'nama'     => 'Admin BGN',
                'email'    => 'admin@bgn.go.id',
                'password' => $hashedPassword,
                'role'     => 'bgn',
                'sppg_id'  => null,
            ],
            // Role: sppg (operator, terhubung ke SPPG Pusat Jakarta / id=1)
            [
                'nama'     => 'Operator SPPG',
                'email'    => 'operator@sppg.com',
                'password' => $hashedPassword,
                'role'     => 'sppg',
                'sppg_id'  => 1,
            ],
            // Role: masyarakat (pengguna umum, tidak terkait SPPG)
            [
                'nama'     => 'Masyarakat Umum',
                'email'    => 'masyarakat@gmail.com',
                'password' => $hashedPassword,
                'role'     => 'masyarakat',
                'sppg_id'  => null,
            ],
        ];

        $stmtUser = $pdo->prepare("
            INSERT INTO `users` (`nama`, `email`, `password`, `role`, `sppg_id`)
            VALUES (:nama, :email, :password, :role, :sppg_id)
        ");

        foreach ($usersData as $row) {
            $stmtUser->execute($row);
        }
    }
}
