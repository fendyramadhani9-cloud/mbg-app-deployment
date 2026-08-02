# Aplikasi MBG — Pelaporan, Monitoring & Aduan

Implementasi sesuai *Dokumen Teknis Pengembangan Aplikasi* yang diberikan:
- **Back End**: REST API PHP (PDO, auto-migration, S3 upload, notifikasi SNS, API key, session-based auth untuk role check).
- **Front End**: Dashboard PHP multi-role (BGN, SPPG, Masyarakat), memanggil BE lewat `ApiClient`, dengan **dark/light mode** dan **desain responsif** (mobile, tablet, desktop).

## Struktur

```
mbg-app/
├── backend/          # REST API — jalankan sebagai 1 instance
│   ├── api/          # auth, laporan, aduan, monitoring, sppg, upload
│   ├── core/         # Database, Auth, Response, S3Uploader, SnsNotifier
│   ├── public/        # index.php (router), health.php
│   ├── config.php
│   └── .env.example
└── frontend/         # Dashboard/UI — bisa dijalankan di banyak server sekaligus
    ├── includes/      # session_init.php, ApiClient.php, header/footer, logout
    ├── views/         # auth/, bgn/, sppg/, masyarakat/
    ├── assets/        # css/app.css (tema), js/app.js (toggle tema & nav)
    ├── health.php
    ├── index.php
    ├── config.php
    └── .env.example
```

## Menjalankan secara lokal (development)

**1. Siapkan database MySQL** (nama bebas, mis. `mbg_db`), lalu isi `backend/.env` (copy dari `.env.example`) dengan kredensial lokal Anda.

**2. Jalankan migrasi database (WAJIB, sekali saat pertama kali):**
```bash
cd backend
cp .env.example .env   # lalu edit sesuai DB lokal Anda
php migrate.php
```
Script ini akan DROP → CREATE → seed tabel. Jalankan lagi hanya jika ingin reset DB.

**3. Jalankan Back End:**
```bash
php -S 0.0.0.0:8080 router.php
```

**4. Jalankan Front End** (di terminal lain):
```bash
cd frontend
cp .env.example .env
# isi API_BASE_URL=http://localhost:8080/api
# untuk dev lokal, SESSION_SAVE_PATH boleh dikosongkan (pakai default PHP)
php -S 0.0.0.0:8000
```

**5. Buka** `http://localhost:8000` di browser. Akun seed yang tersedia:
- `admin@bgn.go.id` / `password123` (role: bgn)
- `operator@sppg.com` / `password123` (role: sppg)
- `masyarakat@gmail.com` / `password123` (role: masyarakat)

## Fitur UI (Front End)

- **Dark / Light mode**: tombol 🌙/☀️ di kanan atas, tersimpan di `localStorage`, otomatis mengikuti preferensi sistem saat pertama kali dibuka.
- **Responsif**: layout grid otomatis menyesuaikan (desktop → tablet → mobile), menu navigasi berubah jadi hamburger di layar sempit, tabel bisa di-scroll horizontal di layar kecil.
- **Role-based views**: BGN (rekap semua SPPG, kelola master SPPG, update status laporan/aduan), SPPG (buat laporan, tanggapi aduan masuk), Masyarakat (buat aduan + upload foto, lihat riwayat aduan).

## Catatan Production (sesuai dokumen teknis)

- **Migration**: Jalankan `php backend/migrate.php` **sekali** saat pertama deploy atau saat perlu reset DB. `Database::get()` tidak lagi otomatis menjalankan migration — aman dipanggil tiap request.
- Nilai `.env` (`DB_*`, `S3_BUCKET_NAME`, `AWS_REGION`, `SNS_TOPIC_ARN`, `API_BASE_URL`, `SESSION_SAVE_PATH`, `FRONTEND_URL`) **diisi oleh tim infrastruktur**, bukan di-hardcode.
- Set `APP_ENV=production` di `.env` backend untuk menonaktifkan `display_errors` dan mengaktifkan CORS terbatas.
- `S3Uploader` & `SnsNotifier` memakai AWS SDK for PHP (`composer require aws/aws-sdk-php`) — kredensial AWS otomatis lewat mekanisme server, tidak perlu ditulis manual. Bila SDK belum terpasang, keduanya otomatis fallback ke penyimpanan lokal/log (khusus untuk development, **jangan dipakai di production**).
- `frontend/includes/session_init.php` WAJIB di-include di setiap halaman sebelum output, agar session diarahkan ke folder shared (bukan disk lokal), karena FE berjalan di banyak server sekaligus.
- Endpoint `/health.php` di kedua sisi selalu HTTP 200 selama sehat (dipakai Load Balancer). Backend health check menggunakan `Database::connect()` (bukan `::get()`) agar tidak memicu migration.

📌 Checklist sebelum deploy AWS
Yang perlu dilakukan tim infra:

Isi .env di kedua instance (backend & frontend)
Set APP_ENV=production dan FRONTEND_URL di backend .env
Jalankan migration satu kali setelah instance pertama naik:
php backend/migrate.php
Pastikan SESSION_SAVE_PATH (EFS) di frontend .env sudah mount dan writable
Kode sudah production-ready. 🚀