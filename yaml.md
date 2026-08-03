# Panduan Upload `mbg.yaml` di AWS CloudFormation

---

## Langkah 1: Persiapkan File di Komputer

- Pastikan seluruh isi kode YAML yang sudah disesuaikan sebelumnya sudah kamu simpan di komputer.
- Beri nama file tersebut: `mbg.yaml`.

---

## Langkah 2: Masuk ke AWS CloudFormation

1. Buka dashboard **AWS Academy Learner Lab** kamu.
2. Klik tombol **AWS** (lampu hijau) untuk masuk ke AWS Management Console.
3. Di kolom pencarian bagian atas, ketik **CloudFormation**, lalu pilih layanan **CloudFormation**.

---

## Langkah 3: Buat Stack Baru

1. Pada halaman CloudFormation, klik tombol **Create stack** (di kanan atas).
2. Pilih opsi **With new resources (standard)**.

---

## Langkah 4: Upload File `mbg.yaml`

Pada bagian **Prerequisite - Prepare template**:

1. Pilih **Template is ready**.

Pada bagian **Specify template**:

2. Pilih **Upload a template file**.
3. Klik tombol **Choose file / Browse**, lalu cari dan pilih file `mbg.yaml` dari komputermu.
4. Klik tombol **Next** di kanan bawah.

---

## Langkah 5: Isi Detail Stack & Parameter

- **Stack name:** Ketik `mbg-stack-fendy` (atau nama stack pilihanmu).

**Parameters** (Periksa parameter yang muncul secara otomatis):

| Parameter | Nilai |
|---|---|
| `UserIdentifier` | `15671` (NIS kamu) |
| `AdminEmail` | `24.tjkt1.16@smkn1bms.sch.id` |
| `DBPassword` | Bebas / biarkan default (`password123`) |
| `KeyPairName` | Pilih `vockey` (KeyPair standar AWS Academy) |

Setelah selesai, klik tombol **Next**.

---

## Langkah 6: Review dan Submit

1. Pada halaman **Configure stack options**, biarkan semua setting default, lalu klik **Next**.
2. Pada halaman **Review mbg-stack-fendy**:
   - Gulir ke bagian paling bawah.
   - Jika ada kotak centang pengakuan IAM (*"I acknowledge that AWS CloudFormation might create IAM resources..."*), **centang kotak tersebut**.
   - Klik tombol **Submit** (atau **Create stack**).

---

## Langkah 7: Proses Deployment & Ambil Hasil (Outputs)

1. Stack kamu akan berstatus **`CREATE_IN_PROGRESS`**.
2. Tunggu proses pembuatan seluruh infrastruktur sekitar **5 – 8 menit** hingga statusnya berubah menjadi **`CREATE_COMPLETE`**.

### Penting setelah selesai:

> **Konfirmasi Email SNS:**
> Buka inbox email `24.tjkt1.16@smkn1bms.sch.id`, cari email dari **AWS Notification**, lalu klik **Confirm Subscription**.

> **Isi Lembar Ujian Bab G:**
> Klik tab **Outputs** pada stack kamu di CloudFormation. Salin seluruh ID/ARN/DNS Name yang muncul di situ ke lembar jawaban ujianmu!